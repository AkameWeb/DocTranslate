<?php require_once "block/header.php";?>
<body>
    <div class="dashboard">
        <!-- Шапка -->
        <div class="header">
            <div class="logo">
                <i class="fas fa-language"></i>
                DocTranslate
            </div>
            <div class="user-menu">
                <span><i class="far fa-user"></i> </span>
                <div class="avatar"> </div>
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
                    <div class="clear-history" id="clearHistory"><i class="far fa-trash-alt"></i> Очистить</div>
                </div>
                <div class="history-list" id="historyList">
                    <!-- История будет загружаться сюда -->
                </div>
            </div>

          
        </div>
         <?php require_once "block/footer.php"?>
        
    </div>

    <!-- Уведомление -->
    <div class="toast" id="toast">Скопировано!</div>
  
</body>
</html>