<?php require_once __DIR__ . "/partials/header.php"; ?>
<div class="dashboard">
    <!-- Навигационная панель Bootstrap -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-language"></i> OmniLang
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <button class="btn btn-link nav-link" id="themeToggle" style="border: none; background: none;">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="toolsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-grid"></i> Инструменты
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="toolsDropdown">
                            <li><a class="dropdown-item" href="#" data-tool="translate">🌐 Перевод текста и документов</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" data-tool="music-converter">🎵 Конвертер аудио</a></li>
                            <li><a class="dropdown-item" href="#" data-tool="image-converter">🖼️ Конвертер изображений</a></li>
                            <li><a class="dropdown-item" href="#" data-tool="ai-generator">✨ Генератор текста</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <div class="user-menu" id="userMenu">
                            <span><i class="far fa-user"></i> Гость</span>
                            <div class="avatar" id="avatarBtn"><i class="fas fa-sign-in-alt"></i></div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <!-- Блок перевода (активен по умолчанию) -->
        <div id="translate-block" class="tool-block active-block">
            <div class="translation-panel">
                <div class="panel-title"><i class="fas fa-exchange-alt"></i> Перевод текста и документов</div>
                <div class="language-switch">
                    <select id="sourceLang" class="lang-select">
                        <option value="ru">Русский</option>
                        <option value="en">English</option>
                        <option value="de">Deutsch</option>
                        <option value="fr">Français</option>
                        <option value="es">Español</option>
                        <option value="it">Italiano</option>
                        <option value="zh">中文</option>
                        <option value="ja">日本語</option>
                        <option value="ko">한국어</option>
                        <option value="ar">العربية</option>
                        <option value="tr">Türkçe</option>
                        <option value="pl">Polski</option>
                        <option value="uk">Українська</option>
                        <option value="pt">Português</option>
                        <option value="nl">Nederlands</option>
                        <option value="sv">Svenska</option>
                        <option value="da">Dansk</option>
                        <option value="fi">Suomi</option>
                        <option value="no">Norsk</option>
                        <option value="cs">Čeština</option>
                        <option value="sk">Slovenčina</option>
                        <option value="hu">Magyar</option>
                        <option value="el">Ελληνικά</option>
                        <option value="he">עברית</option>
                        <option value="th">ไทย</option>
                        <option value="vi">Tiếng Việt</option>
                        <option value="hi">हिन्दी</option>
                        <option value="bn">বাংলা</option>
                        <option value="id">Bahasa Indonesia</option>
                        <option value="ms">Bahasa Melayu</option>
                        <option value="tl">Filipino</option>
                    </select>
                    <i class="fas fa-arrow-right switch-icon" id="swapLanguages"></i>
                    <select id="targetLang" class="lang-select">
                        <option value="en">English</option>
                        <option value="ru">Русский</option>
                        <option value="de">Deutsch</option>
                        <option value="fr">Français</option>
                        <option value="es">Español</option>
                        <option value="it">Italiano</option>
                        <option value="zh">中文</option>
                        <option value="ja">日本語</option>
                        <option value="ko">한국어</option>
                        <option value="ar">العربية</option>
                        <option value="tr">Türkçe</option>
                        <option value="pl">Polski</option>
                        <option value="uk">Українська</option>
                        <option value="pt">Português</option>
                        <option value="nl">Nederlands</option>
                        <option value="sv">Svenska</option>
                        <option value="da">Dansk</option>
                        <option value="fi">Suomi</option>
                        <option value="no">Norsk</option>
                        <option value="cs">Čeština</option>
                        <option value="sk">Slovenčina</option>
                        <option value="hu">Magyar</option>
                        <option value="el">Ελληνικά</option>
                        <option value="he">עברית</option>
                        <option value="th">ไทย</option>
                        <option value="vi">Tiếng Việt</option>
                        <option value="hi">हिन्दी</option>
                        <option value="bn">বাংলা</option>
                        <option value="id">Bahasa Indonesia</option>
                        <option value="ms">Bahasa Melayu</option>
                        <option value="tl">Filipino</option>
                    </select>
                </div>
                <div class="text-input-area">
                    <div class="input-box">
                        <div class="box-header"><span>Исходный текст</span><i class="far fa-copy" title="Копировать" onclick="copyText('sourceText')"></i></div>
                        <textarea id="sourceText" placeholder="Введите текст для перевода..."></textarea>
                    </div>
                    <div class="output-box">
                        <div class="box-header"><span>Перевод</span><i class="far fa-copy" title="Копировать" onclick="copyText('translatedText')"></i></div>
                        <textarea id="translatedText" placeholder="Здесь появится перевод..."></textarea>
                    </div>
                </div>
                <div class="file-upload-area" id="fileUpload">
                    <div class="file-upload-content"><i class="fas fa-file-pdf"></i><span>Загрузите DOC, DOCX или PDF файл</span><small>или перетащите файл сюда</small></div>
                    <input type="file" id="fileInput" accept=".doc,.docx,.pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/pdf" style="display: none;">
                </div>
                <div class="file-upload-area" id="imageUpload">
                    <div class="file-upload-content"><i class="fas fa-image"></i><span>Загрузите изображение для перевода</span><small>Поддерживаются JPEG, PNG, GIF, BMP</small></div>
                    <input type="file" id="imageInput" accept="image/jpeg,image/png,image/gif,image/bmp" style="display: none;">
                </div>
                <button class="translate-btn" id="translateBtn"><i class="fas fa-magic"></i> Перевести</button>
                <div class="export-buttons">
                    <button class="export-btn pdf-btn" id="exportPdfBtn"><i class="fas fa-file-pdf"></i> Скачать как PDF</button>
                    <button class="export-btn docx-btn" id="exportDocxBtn"><i class="fas fa-file-word"></i> Скачать как DOCX</button>
                </div>
            </div>
            <div class="history-panel">
                <div class="history-header"><h3><i class="fas fa-history"></i> История переводов</h3></div>
                <div class="history-list" id="historyList"></div>
            </div>
        </div>

        <!-- Блок конвертера аудио (с визуализацией и обрезкой) -->
        <div id="music-converter-block" class="tool-block">
            <div class="translation-panel">
                <div class="panel-title"><i class="fas fa-music"></i> Конвертер аудио</div>
                <div class="file-upload-area" id="audioUploadArea">
                    <div class="file-upload-content">
                        <i class="fas fa-headphones"></i>
                        <span>Нажмите или перетащите аудиофайл</span>
                        <small>Поддерживаются MP3, WAV, OGG, FLAC, M4A</small>
                    </div>
                    <input type="file" id="audioInput" accept=".mp3,.wav,.ogg,.flac,.m4a" style="display: none;">
                </div>
                <div id="selectedAudioFileName" style="font-size: 12px; color: var(--text-muted); text-align: center; margin-top: -10px; margin-bottom: 15px;"></div>

                <!-- Плеер и визуализация -->
                <div class="audio-player-container" style="margin-bottom: 20px;">
                    <div id="waveform" style="margin-bottom: 10px;"></div>
                    <audio id="audioPlayer" controls style="width: 100%;"></audio>
                    <div class="cut-controls" style="margin-top: 10px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <label>Начало (с): <input type="number" id="cutStart" step="0.1" value="0" style="width: 80px; padding: 5px;"></label>
                        <label>Конец (с): <input type="number" id="cutEnd" step="0.1" value="0" style="width: 80px; padding: 5px;"></label>
                        <button id="cutAudioBtn" class="translate-btn" style="margin-top: 0; width: auto; padding: 8px 16px;">✂️ Вырезать фрагмент</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="audioFormat">Целевой формат</label>
                    <select id="audioFormat" class="form-control">
                        <option value="mp3">MP3</option>
                        <option value="wav">WAV</option>
                        <option value="ogg">OGG</option>
                        <option value="flac">FLAC</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="audioBitrate">Битрейт (kbps)</label>
                    <select id="audioBitrate" class="form-control">
                        <option value="64">64 kbps</option>
                        <option value="128" selected>128 kbps</option>
                        <option value="192">192 kbps</option>
                        <option value="256">256 kbps</option>
                        <option value="320">320 kbps</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="audioSampleRate">Частота дискретизации (Hz)</label>
                    <select id="audioSampleRate" class="form-control">
                        <option value="22050">22050 Hz</option>
                        <option value="44100" selected>44100 Hz</option>
                        <option value="48000">48000 Hz</option>
                    </select>
                </div>
                <button class="translate-btn" id="convertAudioBtn"> Конвертировать и скачать</button>
            </div>
            <div class="history-panel">
                <div class="history-header" style="margin-bottom: 15px;">
                    <h3><i class="fas fa-history"></i> История конвертаций аудио</h3>
                    <button class="clear-history" id="clearAllAudioBtn"><i class="far fa-trash-alt"></i> Очистить всё</button>
                </div>
                <div id="audio-conversions-history-list" class="conversions-history-list">
                    <p style="text-align: center; color: var(--text-muted); padding: 20px;">Загрузка истории...</p>
                </div>
            </div>
        </div>

        <!-- Блок конвертера изображений -->
        <div id="image-converter-block" class="tool-block">
            <div class="translation-panel">
                <div class="panel-title"><i class="fas fa-image"></i> Конвертер изображений</div>
                <div class="file-upload-area" id="imageUploadArea">
                    <div class="file-upload-content">
                        <i class="fas fa-upload"></i>
                        <span>Нажмите или перетащите изображение</span>
                        <small>Поддерживаются JPEG, PNG, GIF, BMP, WEBP</small>
                    </div>
                    <input type="file" id="imageConverterInput" accept="image/jpeg,image/png,image/gif,image/bmp,image/webp" style="display: none;">
                </div>
                <div id="selectedFileName" style="font-size: 12px; color: var(--text-muted); text-align: center; margin-top: -10px; margin-bottom: 15px;"></div>
                <div class="form-group">
                    <label for="imageFormat">Целевой формат</label>
                    <select id="imageFormat" class="form-control">
                        <option value="png">PNG</option>
                        <option value="jpg">JPEG</option>
                        <option value="webp">WEBP</option>
                        <option value="gif">GIF</option>
                        <option value="bmp">BMP</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="imageQuality">Качество (только для JPEG/WEBP)</label>
                    <input type="range" id="imageQuality" min="1" max="100" value="90">
                    <span id="qualityValue">90</span>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" id="keepAspect" checked> Сохранять пропорции</label>
                </div>
                <div class="form-group">
                    <label>Размер (опционально)</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="number" id="imageWidth" placeholder="Ширина" class="form-control" style="width: 50%;">
                        <input type="number" id="imageHeight" placeholder="Высота" class="form-control" style="width: 50%;">
                    </div>
                </div>
                <button class="translate-btn" id="convertImageBtn"> Конвертировать и скачать</button>
            </div>
            <div class="history-panel">
                <div class="history-header" style="margin-bottom: 15px;">
                    <h3><i class="fas fa-history"></i> История конвертаций</h3>
                    <button class="clear-history" id="clearAllConversionsBtn"><i class="far fa-trash-alt"></i> Очистить всё</button>
                </div>
                <div id="conversions-history-list" class="conversions-history-list">
                    <p style="text-align: center; color: var(--text-muted); padding: 20px;">Загрузка истории...</p>
                </div>
            </div>
        </div>

        <!-- Блок генератора текста (нейросеть) -->
        <div id="ai-generator-block" class="tool-block">
            <div class="translation-panel">
                <div class="panel-title"><i class="fas fa-brain"></i> Генератор текста (нейросеть)</div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="aiPrompt">Запрос / Тема</label>
                    <textarea id="aiPrompt" rows="3" class="form-control" placeholder="Напишите, что нужно сгенерировать..."></textarea>
                </div>
                <button class="translate-btn" id="generateBtn"><i class="fas fa-magic"></i> Сгенерировать</button>
                <div class="output-box" style="margin-top: 20px;">
                    <div class="box-header">
                        <span>Результат</span>
                        <i class="far fa-copy" title="Копировать" onclick="copyText('aiResult')"></i>
                    </div>
                    <textarea id="aiResult" rows="6" placeholder="Здесь появится сгенерированный текст..." readonly></textarea>
                </div>
            </div>
            <div class="history-panel">
                <div class="history-header" style="margin-bottom: 15px;">
                    <h3><i class="fas fa-history"></i> История генераций</h3>
                    <button class="clear-history" id="clearAiHistoryBtn"><i class="far fa-trash-alt"></i> Очистить всё</button>
                </div>
                <div id="aiHistoryList" class="conversions-history-list">
                    <p style="text-align: center; color: var(--text-muted); padding: 20px;">Загрузка истории...</p>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . "/partials/footer.php"; ?>
</div>

<!-- Модальное окно авторизации -->
<div class="modal" id="authModal">
    <div class="modal-content">
        <i class="fas fa-times modal-close" id="closeModal"></i>
        <div class="modal-tabs">
            <div class="modal-tab active" id="loginTab">Вход</div>
            <div class="modal-tab" id="registerTab">Регистрация</div>
        </div>
        <div class="auth-form" id="loginForm">
            <div class="form-group"><label>Email</label><input type="email" id="loginEmail" placeholder="example@mail.com"></div>
            <div class="form-group"><label>Пароль</label><input type="password" id="loginPassword" placeholder="••••••••"></div>
            <div class="auth-error" id="loginError"></div>
            <button class="auth-btn" id="loginBtn">Войти</button>
            <div class="auth-switch">Нет аккаунта? <span id="switchToRegister">Зарегистрироваться</span></div>
            <div class="google-auth"><a href="google-callback.php" class="google-btn"><i class="fab fa-google"></i> Войти через Google</a></div>
        </div>
        <div class="auth-form" id="registerForm" style="display: none;">
            <div class="form-group"><label>Имя</label><input type="text" id="registerName" placeholder="Иван Иванов"></div>
            <div class="form-group"><label>Email</label><input type="email" id="registerEmail" placeholder="example@mail.com"></div>
            <div class="form-group"><label>Пароль</label><input type="password" id="registerPassword" placeholder="••••••••"></div>
            <div class="form-group"><label>Подтверждение пароля</label><input type="password" id="registerConfirm" placeholder="••••••••"></div>
            <div class="auth-error" id="registerError"></div>
            <button class="auth-btn" id="registerBtn">Зарегистрироваться</button>
            <div class="auth-switch">Уже есть аккаунт? <span id="switchToLogin">Войти</span></div>
        </div>
    </div>
</div>

<div class="toast" id="toast">Скопировано!</div>

<!-- Подключаем WaveSurfer и TensorFlow -->
<script src="https://unpkg.com/wavesurfer.js@7"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd"></script>
<script src="/DocLang/public/assets/js/main.js"></script>
<script src="/DocLang/public/assets/js/ai.js"></script>
<script src="/DocLang/public/assets/js/image-converter.js"></script>
<script src="/DocLang/public/assets/js/audio-converter.js"></script>
<script src="/DocLang/public/assets/js/tensorflow.js"></script>

<script>
    // Переключение блоков
    document.addEventListener('DOMContentLoaded', function() {
        const toolItems = document.querySelectorAll('.dropdown-item[data-tool]');
        const blocks = {
            'translate': document.getElementById('translate-block'),
            'music-converter': document.getElementById('music-converter-block'),
            'image-converter': document.getElementById('image-converter-block'),
            'ai-generator': document.getElementById('ai-generator-block')
        };
        function switchTool(toolId) {
            Object.values(blocks).forEach(block => {
                if (block) {
                    block.classList.remove('active-block');
                    block.style.display = 'none';
                }
            });
            const active = blocks[toolId];
            if (active) {
                active.classList.add('active-block');
                active.style.display = 'flex';
            }
        }
        toolItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const tool = item.getAttribute('data-tool');
                if (tool && blocks[tool]) switchTool(tool);
            });
        });
    });
</script>
</body>
</html>