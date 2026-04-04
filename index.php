<?php require_once "block/header.php"; ?>
<div class="dashboard">
    <!-- Навигационная панель Bootstrap -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-language"></i> DocTranslate
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="toolsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-grid"></i> Инструменты
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="toolsDropdown">
                            <li><a class="dropdown-item" href="#" data-tool="translate">🌐 Перевод текста и документов</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" data-tool="music-converter">🎵 Конвертер музыки</a></li>
                            <li><a class="dropdown-item" href="#" data-tool="image-converter">🖼️ Конвертер изображений</a></li>
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

    <!-- Основной контент: переключаемые блоки -->
    <div class="main-content" style="padding: 20px; background: #f8fafc;">
        <!-- Блок перевода (активен по умолчанию) -->
        <div id="translate-block" class="tool-block active-block">
            <!-- Левая панель: перевод -->
            <div class="translation-panel">
                <div class="panel-title">
                    <i class="fas fa-exchange-alt"></i> Перевод текста и документов
                </div>
                <!-- Переключатель языка (select) -->
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
                <!-- Поля ввода -->
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
                <!-- Загрузка документов -->
                <div class="file-upload-area" id="fileUpload">
                    <div class="file-upload-content"><i class="fas fa-file-pdf"></i><span>Загрузите DOC, DOCX или PDF файл</span><small>или перетащите файл сюда</small></div>
                    <input type="file" id="fileInput" accept=".doc,.docx,.pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/pdf" style="display: none;">
                </div>
                <!-- Загрузка изображений для перевода -->
                <div class="file-upload-area" id="imageUpload">
                    <div class="file-upload-content"><i class="fas fa-image"></i><span>Загрузите изображение для перевода</span><small>Поддерживаются JPEG, PNG, GIF, BMP</small></div>
                    <input type="file" id="imageInput" accept="image/jpeg,image/png,image/gif,image/bmp" style="display: none;">
                </div>
                <!-- Кнопка перевода -->
                <button class="translate-btn" id="translateBtn"><i class="fas fa-magic"></i> Перевести</button>
                <!-- Экспорт -->
                <div class="export-buttons">
                    <button class="export-btn pdf-btn" id="exportPdfBtn"><i class="fas fa-file-pdf"></i> Скачать как PDF</button>
                    <button class="export-btn docx-btn" id="exportDocxBtn"><i class="fas fa-file-word"></i> Скачать как DOCX</button>
                </div>
            </div>
            <!-- Правая панель: история -->
            <div class="history-panel">
                <div class="history-header"><h3><i class="fas fa-history"></i> История переводов</h3></div>
                <div class="history-list" id="historyList"></div>
            </div>
        </div>

        <!-- Блок конвертера музыки (заглушка) -->
        <div id="music-converter-block" class="tool-block">
            <div class="translation-panel">
                <div class="panel-title"><i class="fas fa-music"></i> Конвертер музыки</div>
                <p>Функционал в разработке. Скоро вы сможете конвертировать аудиофайлы в любые форматы (MP3, WAV, OGG, FLAC).</p>
                <div class="file-upload-area" id="musicUpload">
                    <div class="file-upload-content"><i class="fas fa-music"></i><span>Загрузите аудиофайл</span><small>Поддерживаются MP3, WAV, OGG, FLAC</small></div>
                    <input type="file" id="musicInput" accept=".mp3,.wav,.ogg,.flac" style="display: none;">
                </div>
                <button class="translate-btn" id="convertMusicBtn" disabled>Конвертировать (скоро)</button>
            </div>
            <div class="history-panel"><h3><i class="fas fa-history"></i> История конвертаций</h3><p>Здесь будет отображаться история конвертации музыки.</p></div>
        </div>

        <!-- Блок конвертера изображений (заглушка) -->
        <div id="image-converter-block" class="tool-block">
            <div class="translation-panel">
                <div class="panel-title"><i class="fas fa-image"></i> Конвертер изображений</div>
                <p>Функционал в разработке. Скоро вы сможете конвертировать изображения в любые форматы (JPEG, PNG, WEBP, GIF, BMP).</p>
                <div class="file-upload-area" id="imageConverterUpload">
                    <div class="file-upload-content"><i class="fas fa-image"></i><span>Загрузите изображение</span><small>Поддерживаются JPEG, PNG, GIF, BMP, WEBP</small></div>
                    <input type="file" id="imageConverterInput" accept="image/jpeg,image/png,image/gif,image/bmp,image/webp" style="display: none;">
                </div>
                <button class="translate-btn" id="convertImageBtn" disabled>Конвертировать (скоро)</button>
            </div>
            <div class="history-panel"><h3><i class="fas fa-history"></i> История конвертаций</h3><p>Здесь будет отображаться история конвертации изображений.</p></div>
        </div>
    </div>

    <?php require_once "block/footer.php"; ?>
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

<script src="./block/script/main.js"></script>
<script>
    // Дополнительные обработчики для переключения блоков и заглушек конвертеров
    document.addEventListener('DOMContentLoaded', function() {
        // Переключение инструментов
        const toolItems = document.querySelectorAll('.dropdown-item[data-tool]');
        const blocks = {
            'translate': document.getElementById('translate-block'),
            'music-converter': document.getElementById('music-converter-block'),
            'image-converter': document.getElementById('image-converter-block')
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
        // Заглушки для конвертеров (пока просто уведомления)
        const musicUpload = document.getElementById('musicUpload');
        const musicInput = document.getElementById('musicInput');
        if (musicUpload && musicInput) {
            musicUpload.addEventListener('click', () => musicInput.click());
            musicInput.addEventListener('change', (e) => {
                if (e.target.files[0]) showToast('Функция конвертации музыки в разработке');
            });
        }
        const imageConverterUpload = document.getElementById('imageConverterUpload');
        const imageConverterInput = document.getElementById('imageConverterInput');
        if (imageConverterUpload && imageConverterInput) {
            imageConverterUpload.addEventListener('click', () => imageConverterInput.click());
            imageConverterInput.addEventListener('change', (e) => {
                if (e.target.files[0]) showToast('Функция конвертации изображений в разработке');
            });
        }
    });
</script>
</body>
</html>