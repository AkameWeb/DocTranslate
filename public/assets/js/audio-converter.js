(function() {
    let currentAudioFile = null;
    let wavesurfer = null;

    function showToastMessage(message) {
        if (typeof showToast === 'function') showToast(message);
        else alert(message);
    }

    function checkAuth() {
        if (typeof currentUser !== 'undefined' && currentUser) return true;
        showToastMessage('⚠️ Только авторизованные пользователи могут конвертировать аудио');
        return false;
    }

    function initWaveSurfer() {
        if (wavesurfer) wavesurfer.destroy();
        wavesurfer = WaveSurfer.create({
            container: '#waveform',
            waveColor: '#667eea',
            progressColor: '#764ba2',
            cursorColor: '#ef4444',
            height: 100,
            normalize: true,
            responsive: true
        });
        wavesurfer.on('ready', () => {
            const duration = wavesurfer.getDuration();
            document.getElementById('cutEnd').value = duration.toFixed(1);
        });
    }

    function loadAudioForPlayback(file) {
        const url = URL.createObjectURL(file);
        const audioPlayer = document.getElementById('audioPlayer');
        audioPlayer.src = url;
        audioPlayer.load();
        if (wavesurfer) {
            wavesurfer.load(url);
        } else {
            initWaveSurfer();
            wavesurfer.load(url);
        }
    }

    function uploadAndConvertAudio(file, format, bitrate, sampleRate) {
        if (!checkAuth()) return;
        const formData = new FormData();
        formData.append('audio', file);
        formData.append('format', format);
        formData.append('bitrate', bitrate);
        formData.append('sample_rate', sampleRate);
        showToastMessage(' Конвертация аудио...');
        fetch('convert-audio.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToastMessage(`✅ Конвертация завершена! Размер: ${data.size}`);
                if (typeof loadAudioConversionsHistory === 'function') loadAudioConversionsHistory();
                if (data.download_url) window.location.href = data.download_url;
            } else showToastMessage('❌ Ошибка: ' + data.error);
        })
        .catch(err => showToastMessage('❌ Ошибка соединения'));
    }

    async function cutAudioFile(start, end, format, bitrate, sampleRate) {
        if (!currentAudioFile) {
            showToastMessage('📁 Сначала загрузите аудиофайл');
            return;
        }
        const formData = new FormData();
        formData.append('audio', currentAudioFile);
        formData.append('start', start);
        formData.append('end', end);
        formData.append('format', format);
        formData.append('bitrate', bitrate);
        formData.append('sample_rate', sampleRate);
        showToastMessage('✂️ Обрезка аудио...');
        fetch('cut-audio.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToastMessage(`✅ Обрезка завершена! Размер: ${data.size}`);
                if (typeof loadAudioConversionsHistory === 'function') loadAudioConversionsHistory();
                if (data.download_url) window.location.href = data.download_url;
            } else showToastMessage('❌ Ошибка: ' + data.error);
        })
        .catch(err => showToastMessage('❌ Ошибка соединения'));
    }

    function handleAudioFile(file) {
        if (!checkAuth()) return false;
        const allowed = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/flac', 'audio/x-m4a', 'audio/mp4'];
        if (!allowed.includes(file.type) && !file.name.match(/\.(mp3|wav|ogg|flac|m4a)$/i)) {
            showToastMessage('❌ Поддерживаются MP3, WAV, OGG, FLAC, M4A');
            return false;
        }
        currentAudioFile = file;
        showToastMessage(`✅ Аудио "${file.name}" загружено`);
        loadAudioForPlayback(file);
        return true;
    }

    window.loadAudioConversionsHistory = async function() {
        if (typeof currentUser === 'undefined' || !currentUser) return;
        try {
            const res = await fetch('get-audio-conversions.php');
            const data = await res.json();
            renderAudioConversionsHistory(data);
        } catch(e) { console.error(e); }
    };

    function renderAudioConversionsHistory(conversions) {
        const container = document.getElementById('audio-conversions-history-list');
        if (!container) return;
        if (!conversions.length) {
            container.innerHTML = '<p style="text-align:center;color:var(--text-muted);padding:20px;">📭 История пуста</p>';
            return;
        }
        container.innerHTML = conversions.map(conv => `
            <div class="conversion-item">
                <div class="conversion-info">
                    <div class="conversion-name">${escapeHtml(conv.original_name)}</div>
                    <div class="conversion-meta">
                        <span>🎵 ${conv.target_format.toUpperCase()}</span>
                        <span>⚡ ${conv.bitrate} kbps</span>
                        <span>📊 ${conv.sample_rate} Hz</span>
                        <span>💾 ${conv.file_size}</span>
                        <span>📅 ${conv.created_at}</span>
                    </div>
                </div>
                <div style="display:flex; gap:8px;">
                    <button class="download-converted-btn" data-id="${conv.id}">📥 Скачать</button>
                    <button class="delete-conversion-btn" data-id="${conv.id}" title="Удалить"><i class="far fa-trash-alt"></i></button>
                </div>
            </div>
        `).join('');
        document.querySelectorAll('.download-converted-btn').forEach(btn => {
            btn.addEventListener('click', () => window.location.href = `download-audio.php?id=${btn.dataset.id}`);
        });
        document.querySelectorAll('.delete-conversion-btn').forEach(btn => {
            btn.addEventListener('click', () => deleteAudioConversion(btn.dataset.id));
        });
    }

async function deleteAudioConversion(id) {
    if (!confirm('Удалить эту запись из истории?')) return;
    if (typeof currentUser === 'undefined' || !currentUser) {
        // Гость: удаляем из localStorage (если гостевые записи хранятся)
        let guestHistory = JSON.parse(localStorage.getItem('guestAudioHistory') || '[]');
        guestHistory = guestHistory.filter(item => item.id != id);
        localStorage.setItem('guestAudioHistory', JSON.stringify(guestHistory));
        showToastMessage('✅ Запись удалена');
        if (typeof loadAudioConversionsHistory === 'function') loadAudioConversionsHistory();
        return;
    }
    const numericId = parseInt(id, 10);
    if (isNaN(numericId)) {
        showToastMessage('❌ Некорректный ID');
        return;
    }
    try {
        const res = await fetch(BASE_URL + 'delete-audio-conversion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: numericId })
        });
        const data = await res.json();
        if (res.ok && data.success) {
            showToastMessage('✅ Запись удалена');
            // Принудительно обновляем историю
            const freshRes = await fetch(BASE_URL + 'get-audio-conversions.php?t=' + Date.now(), { cache: 'no-store' });
            const freshData = await freshRes.json();
            renderAudioConversionsHistory(freshData);
        } else {
            showToastMessage('❌ Ошибка: ' + (data.error || 'Не удалось удалить'));
        }
    } catch (e) {
        showToastMessage('❌ Ошибка соединения');
    }
}

    async function clearAllAudioConversions() {
        if (!confirm('⚠️ Очистить всю историю аудио конвертаций?')) return;
        try {
            const res = await fetch('clear-audio-conversions.php');
            const data = await res.json();
            if (res.ok && data.success) {
                showToastMessage('✅ История очищена');
                loadAudioConversionsHistory();
            } else showToastMessage('❌ ' + (data.error || 'Ошибка'));
        } catch(e) { showToastMessage('❌ Ошибка'); }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function init() {
        const convertBtn = document.getElementById('convertAudioBtn');
        const cutBtn = document.getElementById('cutAudioBtn');
        const formatSelect = document.getElementById('audioFormat');
        const bitrateSelect = document.getElementById('audioBitrate');
        const sampleRateSelect = document.getElementById('audioSampleRate');
        const uploadArea = document.getElementById('audioUploadArea');
        const fileInput = document.getElementById('audioInput');
        const clearBtn = document.getElementById('clearAllAudioBtn');

        if (convertBtn) {
            convertBtn.addEventListener('click', () => {
                if (!currentAudioFile) {
                    showToastMessage('📁 Сначала загрузите аудиофайл');
                    return;
                }
                if (!checkAuth()) return;
                uploadAndConvertAudio(currentAudioFile, formatSelect.value, bitrateSelect.value, sampleRateSelect.value);
            });
        }
        if (cutBtn) {
            cutBtn.addEventListener('click', () => {
                if (!currentAudioFile) {
                    showToastMessage('📁 Сначала загрузите аудиофайл');
                    return;
                }
                if (!checkAuth()) return;
                const start = parseFloat(document.getElementById('cutStart').value);
                let end = parseFloat(document.getElementById('cutEnd').value);
                const duration = wavesurfer ? wavesurfer.getDuration() : 0;
                if (isNaN(end) || end > duration) end = duration;
                if (start >= end) {
                    showToastMessage('❌ Начало должно быть меньше конца');
                    return;
                }
                cutAudioFile(start, end, formatSelect.value, bitrateSelect.value, sampleRateSelect.value);
            });
        }
        if (clearBtn) clearBtn.addEventListener('click', clearAllAudioConversions);
        if (uploadArea && fileInput) {
            uploadArea.addEventListener('click', () => fileInput.click());
            uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.style.borderColor = '#667eea'; uploadArea.style.background = 'var(--history-item-hover)'; });
            uploadArea.addEventListener('dragleave', () => { uploadArea.style.borderColor = 'var(--border-color)'; uploadArea.style.background = 'var(--input-bg)'; });
            uploadArea.addEventListener('drop', e => {
                e.preventDefault();
                uploadArea.style.borderColor = 'var(--border-color)';
                uploadArea.style.background = 'var(--input-bg)';
                const file = e.dataTransfer.files[0];
                if (file && handleAudioFile(file)) document.getElementById('selectedAudioFileName').textContent = file.name;
            });
            fileInput.addEventListener('change', e => {
                const file = e.target.files[0];
                if (file && handleAudioFile(file)) document.getElementById('selectedAudioFileName').textContent = file.name;
            });
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();