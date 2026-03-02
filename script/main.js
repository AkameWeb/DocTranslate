// Состояние приложения
        let currentLangFrom = 'ru';
        let currentLangTo = 'en';
        let history = JSON.parse(localStorage.getItem('translateHistory')) || [];

        // Элементы DOM
        const langBtns = document.querySelectorAll('.lang-btn');
        const swapBtn = document.getElementById('swapLanguages');
        const sourceText = document.getElementById('sourceText');
        const translatedText = document.getElementById('translatedText');
        const translateBtn = document.getElementById('translateBtn');
        const fileUpload = document.getElementById('fileUpload');
        const fileInput = document.getElementById('fileInput');
        const historyList = document.getElementById('historyList');
        const clearHistoryBtn = document.getElementById('clearHistory');
        const toast = document.getElementById('toast');

        // Инициализация: рендер истории
        function renderHistory() {
            if (history.length === 0) {
                historyList.innerHTML = `
                    <div style="text-align: center; color: #94a3b8; padding: 40px 20px;">
                        <i class="fas fa-history" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                        <p>История переводов пуста</p>
                    </div>
                `;
                return;
            }

            historyList.innerHTML = history.map(item => `
                <div class="history-item" data-id="${item.id}" onclick="loadFromHistory('${item.id}')">
                    <div class="history-item-header">
                        <span class="history-lang">${item.from} → ${item.to}</span>
                        <span class="history-date"><i class="far fa-clock"></i> ${item.date}</span>
                    </div>
                    <div class="history-preview">${item.source}</div>
                    <div class="history-type">
                        <i class="fas ${item.type === 'doc' ? 'fa-file-word' : 'fa-font'}"></i>
                        ${item.type === 'doc' ? 'Документ' : 'Текст'}
                    </div>
                </div>
            `).join('');
        }

        // Добавление в историю
        function addToHistory(source, translated, from, to, type = 'text') {
            const now = new Date();
            const formattedDate = now.toLocaleString('ru-RU', { 
                day: '2-digit', 
                month: '2-digit', 
                hour: '2-digit', 
                minute: '2-digit' 
            });

            const newItem = {
                id: Date.now().toString(),
                source: source.substring(0, 100) + (source.length > 100 ? '...' : ''),
                translated: translated.substring(0, 100) + (translated.length > 100 ? '...' : ''),
                fullSource: source,
                fullTranslated: translated,
                from: from === 'ru' ? 'Русский' : 'English',
                to: to === 'ru' ? 'Русский' : 'English',
                date: formattedDate,
                type: type
            };

            history.unshift(newItem);
            if (history.length > 20) history.pop(); // Храним не больше 20 записей
            localStorage.setItem('translateHistory', JSON.stringify(history));
            renderHistory();
        }

        // Загрузка из истории
        window.loadFromHistory = function(id) {
            const item = history.find(h => h.id === id);
            if (item) {
                sourceText.value = item.fullSource;
                translatedText.value = item.fullTranslated;
                
                // Устанавливаем языки
                document.querySelectorAll('.lang-btn').forEach(btn => btn.classList.remove('active'));
                if (item.from === 'Русский') {
                    document.querySelector('[data-lang="ru"]').classList.add('active');
                    document.querySelector('[data-lang="en"]').classList.remove('active');
                    currentLangFrom = 'ru';
                    currentLangTo = 'en';
                } else {
                    document.querySelector('[data-lang="en"]').classList.add('active');
                    document.querySelector('[data-lang="ru"]').classList.remove('active');
                    currentLangFrom = 'en';
                    currentLangTo = 'ru';
                }

                showToast('Загружено из истории');
            }
        };

        // Переключение языков
        langBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.classList.contains('active')) return;
                
                langBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                if (btn.dataset.lang === 'ru') {
                    currentLangFrom = 'ru';
                    currentLangTo = 'en';
                } else {
                    currentLangFrom = 'en';
                    currentLangTo = 'ru';
                }
            });
        });

        // Смена языков местами
        swapBtn.addEventListener('click', () => {
            const ruBtn = document.querySelector('[data-lang="ru"]');
            const enBtn = document.querySelector('[data-lang="en"]');
            
            ruBtn.classList.toggle('active');
            enBtn.classList.toggle('active');
            
            // Меняем местами текст
            const temp = sourceText.value;
            sourceText.value = translatedText.value;
            translatedText.value = temp;
            
            // Обновляем направления
            if (ruBtn.classList.contains('active')) {
                currentLangFrom = 'ru';
                currentLangTo = 'en';
            } else {
                currentLangFrom = 'en';
                currentLangTo = 'ru';
            }
        });

        // Копирование текста
        window.copyText = function(elementId) {
            const textarea = document.getElementById(elementId);
            if (textarea.value) {
                navigator.clipboard.writeText(textarea.value);
                showToast('Скопировано!');
            }
        };

        // Показать уведомление
        function showToast(message) {
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2000);
        }

        // Симуляция перевода
        function simulateTranslation(text, from, to) {
            if (!text.trim()) return '';
            
            // Простая симуляция для демонстрации
            const mockTranslations = {
                'ru-en': {
                    'привет': 'hello',
                    'как дела': 'how are you',
                    'документ': 'document',
                    'перевод': 'translation'
                },
                'en-ru': {
                    'hello': 'привет',
                    'how are you': 'как дела',
                    'document': 'документ',
                    'translation': 'перевод'
                }
            };

            const key = `${from}-${to}`;
            const lowerText = text.toLowerCase().trim();
            
            if (mockTranslations[key] && mockTranslations[key][lowerText]) {
                return mockTranslations[key][lowerText];
            }
            
            // Если нет в словаре, добавляем суффикс
            return from === 'ru' ? text + ' [en]' : text + ' [ru]';
        }

        // Обработка перевода
        translateBtn.addEventListener('click', () => {
            const text = sourceText.value.trim();
            if (!text) {
                showToast('Введите текст для перевода');
                return;
            }

            // Имитация перевода
            const result = simulateTranslation(text, currentLangFrom, currentLangTo);
            translatedText.value = result;

            // Сохраняем в историю
            addToHistory(
                text, 
                result, 
                currentLangFrom, 
                currentLangTo, 
                'text'
            );

            showToast('Перевод выполнен');
        });

        // Загрузка файла
        fileUpload.addEventListener('click', () => {
            fileInput.click();
        });

        fileUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUpload.style.borderColor = '#667eea';
            fileUpload.style.background = '#eef2ff';
        });

        fileUpload.addEventListener('dragleave', () => {
            fileUpload.style.borderColor = '#cbd5e1';
            fileUpload.style.background = '#f1f5f9';
        });

        fileUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUpload.style.borderColor = '#cbd5e1';
            fileUpload.style.background = '#f1f5f9';
            
            const file = e.dataTransfer.files[0];
            if (file && (file.name.endsWith('.doc') || file.name.endsWith('.docx'))) {
                handleFile(file);
            } else {
                showToast('Пожалуйста, загрузите DOC или DOCX файл');
            }
        });

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                handleFile(file);
            }
        });

        function handleFile(file) {
            // Имитация чтения файла
            showToast(`Файл "${file.name}" загружен`);
            
            // Для демонстрации заполняем текст
            sourceText.value = `[Содержимое файла: ${file.name}]\n\nЭто пример текста, который будет переведен. В реальном приложении здесь будет содержимое DOC файла.`;
            
            // Автоматически переводим
            setTimeout(() => {
                const result = simulateTranslation(sourceText.value, currentLangFrom, currentLangTo);
                translatedText.value = result;
                
                addToHistory(
                    sourceText.value, 
                    result, 
                    currentLangFrom, 
                    currentLangTo, 
                    'doc'
                );
                
                showToast('Файл обработан и переведен');
            }, 500);
        }

        // Очистка истории
        clearHistoryBtn.addEventListener('click', () => {
            if (confirm('Очистить всю историю переводов?')) {
                history = [];
                localStorage.setItem('translateHistory', JSON.stringify(history));
                renderHistory();
                showToast('История очищена');
            }
        });

        // Инициализация
        renderHistory();