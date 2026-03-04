<?php require_once "block/header.php"; ?>
<body>
    <div class="dashboard">
        <!-- Шапка -->
        <div class="header">
            <div class="logo">
                <i class="fas fa-language"></i>
                DocTranslate
            </div>
            <div class="user-menu" id="userMenu">
                <!-- Динамически заполняется через JS -->
                <span><i class="far fa-user"></i> Гость</span>
                <div class="avatar" id="avatarBtn"><i class="fas fa-sign-in-alt"></i></div>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="main-content">
            <!-- Левая панель: перевод -->
            <div class="translation-panel">
                <div class="panel-title">
                    <i class="fas fa-exchange-alt"></i>
                    Перевод текста и документов
                </div>

                <!-- Переключатель языка -->
                <div class="language-switch">
                    <button class="lang-btn active" data-lang="ru">Русский</button>
                    <i class="fas fa-arrow-right switch-icon" id="swapLanguages"></i>
                    <button class="lang-btn" data-lang="en">English</button>
                </div>

                <!-- Поля ввода текста -->
                <div class="text-input-area">
                    <div class="input-box">
                        <div class="box-header">
                            <span>Исходный текст</span>
                            <i class="far fa-copy" title="Копировать" onclick="copyText('sourceText')"></i>
                        </div>
                        <textarea id="sourceText" placeholder="Введите текст для перевода..."></textarea>
                    </div>
                    <div class="output-box">
                        <div class="box-header">
                            <span>Перевод</span>
                            <i class="far fa-copy" title="Копировать" onclick="copyText('translatedText')"></i>
                        </div>
                        <textarea id="translatedText" placeholder="Здесь появится перевод..." readonly></textarea>
                    </div>
                </div>

                <!-- Загрузка файлов -->
                <div class="file-upload-area" id="fileUpload">
                    <div class="file-upload-content">
                        <i class="fas fa-file-word"></i>
                        <span>Загрузите DOC или DOCX файл</span>
                        <small>или перетащите файл сюда</small>
                    </div>
                    <input type="file" id="fileInput" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" style="display: none;">
                </div>

                <!-- Кнопка перевода -->
                <button class="translate-btn" id="translateBtn">
                    <i class="fas fa-magic"></i>
                    Перевести
                </button>
            </div>

            <!-- Правая панель: история -->
            <div class="history-panel">
                <div class="history-header">
                    <h3><i class="fas fa-history"></i> История переводов</h3>
                    <!-- Кнопки очистки будут добавлены динамически через JS -->
                </div>
                <div class="history-list" id="historyList">
                    <!-- История будет загружаться сюда -->
                </div>
            </div>
        </div>

        <?php require_once "block/footer.php"; ?>
    </div>

    <!-- Модальное окно авторизации/регистрации -->
    <div class="modal" id="authModal">
        <div class="modal-content">
            <i class="fas fa-times modal-close" id="closeModal"></i>
            <div class="modal-tabs">
                <div class="modal-tab active" id="loginTab">Вход</div>
                <div class="modal-tab" id="registerTab">Регистрация</div>
            </div>
            
            <!-- Форма входа -->
            <div class="auth-form" id="loginForm">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="loginEmail" placeholder="example@mail.com">
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" id="loginPassword" placeholder="••••••••">
                </div>
                <div class="auth-error" id="loginError"></div>
                <button class="auth-btn" id="loginBtn">Войти</button>
                <div class="auth-switch">
                    Нет аккаунта? <span id="switchToRegister">Зарегистрироваться</span>
                </div>
                <div class="google-auth">
                    <a href="google-callback.php" class="google-btn">
                        <i class="fab fa-google"></i> Войти через Google
                    </a>
                </div>
            </div>
            <!-- Форма регистрации (скрыта по умолчанию) -->
            <div class="auth-form" id="registerForm" style="display: none;">
                <div class="form-group">
                    <label>Имя</label>
                    <input type="text" id="registerName" placeholder="Иван Иванов">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="registerEmail" placeholder="example@mail.com">
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" id="registerPassword" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Подтверждение пароля</label>
                    <input type="password" id="registerConfirm" placeholder="••••••••">
                </div>
                <div class="auth-error" id="registerError"></div>
                <button class="auth-btn" id="registerBtn">Зарегистрироваться</button>
                <div class="auth-switch">
                    Уже есть аккаунт? <span id="switchToLogin">Войти</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Уведомление -->
    <div class="toast" id="toast">Скопировано!</div>
</body>
</html>