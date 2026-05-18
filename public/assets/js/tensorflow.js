// ==================== TensorFlow.js для распознавания объектов ====================

let objectModel = null;
let isModelLoading = false;

// Загрузка модели COCO-SSD (одна из лучших для распознавания 90 классов)
async function loadObjectDetectionModel() {
    if (objectModel !== null) return objectModel;
    if (isModelLoading) {
        // Ожидаем загрузку, если уже идёт
        while (isModelLoading) await new Promise(r => setTimeout(r, 100));
        return objectModel;
    }
    isModelLoading = true;
    showToast('⏳ Загрузка нейросети для распознавания объектов...');
    try {
        // COCO-SSD модель ~ 5MB, загружается из CDN
        objectModel = await cocoSsd.load();
        showToast('✅ Нейросеть готова к работе');
        console.log('Model loaded');
    } catch (err) {
        console.error('Model load error:', err);
        showToast('❌ Ошибка загрузки нейросети');
    } finally {
        isModelLoading = false;
    }
    return objectModel;
}

// Распознавание объектов на изображении (HTMLImageElement)
async function detectObjectsOnImage(imageElement) {
    const model = await loadObjectDetectionModel();
    if (!model) throw new Error('Модель не загружена');
    const predictions = await model.detect(imageElement);
    return predictions; // [{bbox: [x,y,w,h], class: "person", score: 0.92}, ...]
}

// Распознавание по загруженному файлу (File)
async function detectObjectsFromFile(file) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const url = URL.createObjectURL(file);
        img.onload = async () => {
            try {
                const result = await detectObjectsOnImage(img);
                URL.revokeObjectURL(url);
                resolve(result);
            } catch (err) {
                URL.revokeObjectURL(url);
                reject(err);
            }
        };
        img.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('Не удалось загрузить изображение'));
        };
        img.src = url;
    });
}

// Отрисовка bounding box на canvas (опционально)
function drawBoundingBoxes(canvas, predictions, originalWidth, originalHeight) {
    const ctx = canvas.getContext('2d');
    canvas.width = originalWidth;
    canvas.height = originalHeight;
    ctx.drawImage(canvas, 0, 0); // очистка
    predictions.forEach(pred => {
        const [x, y, w, h] = pred.bbox;
        ctx.strokeStyle = '#00ff00';
        ctx.lineWidth = 2;
        ctx.strokeRect(x, y, w, h);
        ctx.fillStyle = '#00ff00';
        ctx.font = 'bold 16px sans-serif';
        ctx.fillText(`${pred.class} (${Math.round(pred.score * 100)}%)`, x, y - 5);
    });
}

// Отправка результата на сервер для сохранения в ai_vision
async function saveDetectionResult(file, predictions) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('detections', JSON.stringify(predictions));
    try {
        const response = await fetch('api/ai/save-vision.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            showToast('✅ Результат распознавания сохранён');
        }
    } catch (err) {
        console.error('Save vision result error:', err);
    }
}

// Инициализация блока распознавания (создайте отдельный блок в index.php с id="vision-upload")
function initVision() {
    const uploadArea = document.getElementById('visionUploadArea');
    const imageInput = document.getElementById('visionImageInput');
    const resultCanvas = document.getElementById('visionCanvas');
    const predictionsDiv = document.getElementById('visionPredictions');

    if (!uploadArea || !imageInput) return;

    uploadArea.addEventListener('click', () => imageInput.click());
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '#667eea';
    });
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.style.borderColor = 'var(--border-color)';
    });
    uploadArea.addEventListener('drop', async (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = 'var(--border-color)';
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            await processVisionImage(file);
        } else {
            showToast('Пожалуйста, загрузите изображение');
        }
    });
    imageInput.addEventListener('change', async (e) => {
        if (e.target.files[0]) {
            await processVisionImage(e.target.files[0]);
        }
    });

    async function processVisionImage(file) {
        showToast('🔍 Распознавание объектов...');
        try {
            const predictions = await detectObjectsFromFile(file);
            // Отрисовка на canvas
            if (resultCanvas) {
                const img = new Image();
                const url = URL.createObjectURL(file);
                img.onload = () => {
                    drawBoundingBoxes(resultCanvas, predictions, img.width, img.height);
                    URL.revokeObjectURL(url);
                };
                img.src = url;
            }
            // Отображение списка
            if (predictionsDiv) {
                predictionsDiv.innerHTML = '<h4>Обнаружено:</h4><ul>' +
                    predictions.map(p => `<li>${p.class} (${Math.round(p.score * 100)}%)</li>`).join('') +
                    '</ul>';
            }
            // Сохраняем на сервер
            await saveDetectionResult(file, predictions);
            showToast('✅ Распознавание завершено');
        } catch (err) {
            console.error(err);
            showToast('❌ Ошибка распознавания');
        }
    }
}

// Загружаем модель при старте (опционально, но лучше по первому требованию)
// document.addEventListener('DOMContentLoaded', () => {
//     // предзагрузка модели не обязательна, она загрузится при первом распознавании
// });