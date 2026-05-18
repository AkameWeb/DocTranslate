(function() {
    let currentImageFile = null;

    // Базовый URL проекта (автоматически определяет /DocLang/ или корень)
    const BASE_URL = window.location.pathname.includes('/DocLang/') ? '/DocLang/' : '/';

    function showToastMessage(message) {
        if (typeof showToast === 'function') showToast(message);
        else alert(message);
    }

    function checkAuth() {
        if (typeof currentUser !== 'undefined' && currentUser) return true;
        showToastMessage('⚠️ Только авторизованные пользователи могут конвертировать изображения');
        return false;
    }

    function uploadAndConvertImage(file, format, quality, width, height, keepAspect) {
        if (!checkAuth()) return;
        const formData = new FormData();
        formData.append('image', file);
        formData.append('format', format);
        formData.append('quality', quality);
        formData.append('width', width);
        formData.append('height', height);
        formData.append('keepAspect', keepAspect);
        showToastMessage('🔄 Конвертация изображения...');
        fetch(BASE_URL + 'convert-image.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToastMessage(`✅ Конвертация завершена! Размер: ${data.size}`);
                    if (typeof loadConversionsHistory === 'function') loadConversionsHistory();
                    window.location.href = BASE_URL + 'download-converted.php?id=' + data.converted_id;
                } else {
                    showToastMessage('❌ Ошибка: ' + data.error);
                }
            })
            .catch(err => {
                console.error(err);
                showToastMessage('❌ Ошибка соединения с сервером');
            });
    }

    function handleImageForConvert(file) {
        if (!checkAuth()) return false;
        const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'];
        if (!allowed.includes(file.type)) {
            showToastMessage('❌ Поддерживаются JPEG, PNG, GIF, BMP, WEBP');
            return false;
        }
        currentImageFile = file;
        showToastMessage(`✅ Изображение "${file.name}" загружено`);
        return true;
    }

    window.loadConversionsHistory = async function() {
        // Гость: из localStorage
        if (typeof currentUser === 'undefined' || !currentUser) {
            const saved = localStorage.getItem('guestHistory');
            renderConversionsHistory(saved ? JSON.parse(saved) : []);
            return;
        }
        // Авторизованный: с сервера, без кэша
        try {
            const res = await fetch(BASE_URL + 'get-conversions.php?t=' + Date.now(), { cache: 'no-store' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            renderConversionsHistory(data);
        } catch (e) {
            console.error(e);
            showToastMessage('❌ Ошибка загрузки истории');
        }
    };

    function renderConversionsHistory(conversions) {
        const container = document.getElementById('conversions-history-list');
        if (!container) return;
        if (!conversions || conversions.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: var(--text-muted); padding: 20px;">📭 История конвертаций пуста</p>';
            return;
        }
        container.innerHTML = conversions.map(conv => `
            <div class="conversion-item">
                <div class="conversion-info">
                    <div class="conversion-name">${escapeHtml(conv.original_name)}</div>
                    <div class="conversion-meta">
                        <span>📄 ${(conv.target_format || '').toUpperCase()}</span>
                        <span>💾 ${conv.file_size}</span>
                        <span>📅 ${conv.created_at}</span>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button class="download-converted-btn" data-id="${conv.id}">📥 Скачать</button>
                    <button class="delete-conversion-btn" data-id="${conv.id}" title="Удалить"><i class="far fa-trash-alt"></i></button>
                </div>
            </div>
        `).join('');

        document.querySelectorAll('.download-converted-btn').forEach(btn => {
            btn.addEventListener('click', () => window.location.href = BASE_URL + 'download-converted.php?id=' + btn.dataset.id);
        });
        document.querySelectorAll('.delete-conversion-btn').forEach(btn => {
            btn.addEventListener('click', () => deleteConversion(btn.dataset.id));
        });
    }

    async function deleteConversion(id) {
        if (!confirm('Удалить эту запись из истории?')) return;
        // Гость
        if (typeof currentUser === 'undefined' || !currentUser) {
            let guestHistory = JSON.parse(localStorage.getItem('guestHistory') || '[]');
            guestHistory = guestHistory.filter(item => item.id != id);
            localStorage.setItem('guestHistory', JSON.stringify(guestHistory));
            showToastMessage('✅ Запись удалена');
            renderConversionsHistory(guestHistory);
            return;
        }
        // Авторизованный
        const numericId = parseInt(id, 10);
        if (isNaN(numericId)) {
            showToastMessage('❌ Некорректный ID');
            return;
        }
        try {
            const res = await fetch(BASE_URL + 'delete-conversion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: numericId })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                showToastMessage('✅ Запись удалена');
                // Принудительно загружаем свежую историю с сервера (игнорируем кэш)
                const freshRes = await fetch(BASE_URL + 'get-conversions.php?t=' + Date.now(), { cache: 'no-store' });
                const freshData = await freshRes.json();
                renderConversionsHistory(freshData);
            } else {
                console.error('Delete error:', data);
                showToastMessage('❌ Ошибка: ' + (data.error || 'Не удалось удалить'));
            }
        } catch (e) {
            console.error(e);
            showToastMessage('❌ Ошибка соединения');
        }
    }

    async function clearAllConversions() {
        if (!confirm('⚠️ Очистить ВСЮ историю конвертаций?')) return;
        if (typeof currentUser === 'undefined' || !currentUser) {
            localStorage.removeItem('guestHistory');
            showToastMessage('✅ История очищена');
            renderConversionsHistory([]);
            return;
        }
        try {
            const res = await fetch(BASE_URL + 'clear-conversions.php');
            const data = await res.json();
            if (res.ok && data.success) {
                showToastMessage('✅ История очищена');
                const freshRes = await fetch(BASE_URL + 'get-conversions.php?t=' + Date.now(), { cache: 'no-store' });
                const freshData = await freshRes.json();
                renderConversionsHistory(freshData);
            } else {
                showToastMessage('❌ Ошибка: ' + (data.error || 'Не удалось очистить'));
            }
        } catch (e) {
            showToastMessage('❌ Ошибка соединения');
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function init() {
        const convertImageBtn = document.getElementById('convertImageBtn');
        const imageFormat = document.getElementById('imageFormat');
        const imageQuality = document.getElementById('imageQuality');
        const qualityValue = document.getElementById('qualityValue');
        const keepAspect = document.getElementById('keepAspect');
        const imageWidth = document.getElementById('imageWidth');
        const imageHeight = document.getElementById('imageHeight');
        const imageUploadArea = document.getElementById('imageUploadArea');
        const imageInput = document.getElementById('imageConverterInput');
        const clearAllBtn = document.getElementById('clearAllConversionsBtn');

        if (imageQuality && qualityValue) {
            imageQuality.addEventListener('input', () => qualityValue.textContent = imageQuality.value);
        }

        if (convertImageBtn) {
            convertImageBtn.addEventListener('click', () => {
                if (!currentImageFile) {
                    showToastMessage('📸 Сначала загрузите изображение');
                    return;
                }
                if (!checkAuth()) return;
                const format = imageFormat ? imageFormat.value : 'png';
                const quality = imageQuality ? parseInt(imageQuality.value) : 90;
                const width = imageWidth ? parseInt(imageWidth.value) || 0 : 0;
                const height = imageHeight ? parseInt(imageHeight.value) || 0 : 0;
                const keep = keepAspect ? keepAspect.checked : true;
                uploadAndConvertImage(currentImageFile, format, quality, width, height, keep);
            });
        }

        if (clearAllBtn) clearAllBtn.addEventListener('click', clearAllConversions);

        if (imageUploadArea && imageInput) {
            imageUploadArea.addEventListener('click', () => imageInput.click());
            imageUploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                imageUploadArea.style.borderColor = '#667eea';
                imageUploadArea.style.background = 'var(--history-item-hover)';
            });
            imageUploadArea.addEventListener('dragleave', () => {
                imageUploadArea.style.borderColor = 'var(--border-color)';
                imageUploadArea.style.background = 'var(--input-bg)';
            });
            imageUploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                imageUploadArea.style.borderColor = 'var(--border-color)';
                imageUploadArea.style.background = 'var(--input-bg)';
                const file = e.dataTransfer.files[0];
                if (file && handleImageForConvert(file)) {
                    const fileNameSpan = document.getElementById('selectedFileName');
                    if (fileNameSpan) fileNameSpan.textContent = file.name;
                }
            });
            imageInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file && handleImageForConvert(file)) {
                    const fileNameSpan = document.getElementById('selectedFileName');
                    if (fileNameSpan) fileNameSpan.textContent = file.name;
                }
            });
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();