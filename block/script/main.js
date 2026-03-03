// ==================== Глобальные переменные ====================
let currentLangFrom = 'ru';
let currentLangTo = 'en';
let history = JSON.parse(localStorage.getItem('translateHistory')) || [];
let currentUser = JSON.parse(localStorage.getItem('currentUser')) || null;

// Элементы DOM (будут инициализированы после загрузки)
let langBtns, swapBtn, sourceText, translatedText, translateBtn, fileUpload, fileInput,
    historyList, clearHistoryBtn, toast, userMenu, modal, closeModal, loginTab, registerTab,
    loginForm, registerForm, switchToRegister, switchToLogin, loginBtn, registerBtn,
    loginError, registerError;

// ==================== Функции ====================

// Показать уведомление
function showToast(message) {
    if (!toast) toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 2000);
}

// Копирование текста (глобальная, вызывается из onclick)
window.copyText = function(elementId) {
    const textarea = document.getElementById(elementId);
    if (textarea && textarea.value) {
        navigator.clipboard.writeText(textarea.value).then(() => {
            showToast('Скопировано!');
        });
    }
};

// Рендер шапки (зависит от currentUser)
function renderUserMenu() {
    if (!userMenu) userMenu = document.getElementById('userMenu');
    if (!userMenu) return;

    if (currentUser) {
        const initials = currentUser.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
        userMenu.innerHTML = `
            <span><i class="far fa-user"></i> ${currentUser.email}</span>
            <div class="avatar" id="avatarBtn">${initials}</div>
            <i class="fas fa-sign-out-alt" id="logoutBtn" style="color: #64748b; cursor: pointer;" title="Выйти"></i>
        `;
        document.getElementById('logoutBtn')?.addEventListener('click', logout);
    } else {
        userMenu.innerHTML = `
            <span><i class="far fa-user"></i> Гость</span>
            <div class="avatar" id="avatarBtn"><i class="fas fa-sign-in-alt"></i></div>
        `;
    }
    // По клику на аватар открываем модалку (если не авторизован)
    document.getElementById('avatarBtn')?.addEventListener('click', () => {
        if (!currentUser) openModal();
    });
}

// Выход
function logout() {
    currentUser = null;
    localStorage.removeItem('currentUser');
    renderUserMenu();
    showToast('Вы вышли из аккаунта');
}

// Модальное окно
function openModal() {
    if (!modal) modal = document.getElementById('authModal');
    if (modal) modal.classList.add('active');
}

function closeModalFunc() {
    if (!modal) modal = document.getElementById('authModal');
    if (modal) modal.classList.remove('active');
}

// Переключение форм
function showLogin() {
    if (!loginTab || !registerTab || !loginForm || !registerForm) return;
    loginTab.classList.add('active');
    registerTab.classList.remove('active');
    loginForm.style.display = 'flex';
    registerForm.style.display = 'none';
}

function showRegister() {
    if (!loginTab || !registerTab || !loginForm || !registerForm) return;
    registerTab.classList.add('active');
    loginTab.classList.remove('active');
    registerForm.style.display = 'flex';
    loginForm.style.display = 'none';
}

// Рендер истории
function renderHistory() {
    if (!historyList) historyList = document.getElementById('historyList');
    if (!historyList) return;

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

// Добавление записи в историю
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
    if (history.length > 20) history.pop();
    localStorage.setItem('translateHistory', JSON.stringify(history));
    renderHistory();
}

// Загрузка перевода из истории (глобальная)
window.loadFromHistory = function(id) {
    const item = history.find(h => h.id === id);
    if (!item) return;

    if (!sourceText) sourceText = document.getElementById('sourceText');
    if (!translatedText) translatedText = document.getElementById('translatedText');
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
    updateSwitchIcon();
    showToast('Загружено из истории');
};

// Обновление иконки стрелки (поворот)
function updateSwitchIcon() {
    const ruBtn = document.querySelector('[data-lang="ru"]');
    if (!swapBtn) swapBtn = document.getElementById('swapLanguages');
    if (ruBtn && swapBtn) {
        if (ruBtn.classList.contains('active')) {
            swapBtn.classList.remove('rotated');
        } else {
            swapBtn.classList.add('rotated');
        }
    }
}

// Симуляция перевода (только для текста, не для файлов)
function simulateTranslation(text, from, to) {
    if (!text.trim()) return '';
    const mockTranslations = {
        'ru-en': { 'привет': 'hello', 'как дела': 'how are you', 'документ': 'document', 'перевод': 'translation' },
        'en-ru': { 'hello': 'привет', 'how are you': 'как дела', 'document': 'документ', 'translation': 'перевод' }
    };
    const key = `${from}-${to}`;
    const lowerText = text.toLowerCase().trim();
    if (mockTranslations[key] && mockTranslations[key][lowerText]) {
        return mockTranslations[key][lowerText];
    }
    return from === 'ru' ? text + ' [en]' : text + ' [ru]';
}

// Отправка файла на сервер
// Отправка файла на сервер
function uploadAndTranslate(file, from, to) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('from', from);
    formData.append('to', to);

    showToast('Загрузка файла...');

    fetch('translate.php', {   // ← исправлено: убран слеш
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (!sourceText) sourceText = document.getElementById('sourceText');
            if (!translatedText) translatedText = document.getElementById('translatedText');
            sourceText.value = data.original || '[Извлечённый текст]';
            translatedText.value = data.translated;
            addToHistory(data.original, data.translated, from, to, 'doc');
            showToast('Перевод готов');
        } else {
            showToast('Ошибка: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Ошибка соединения с сервером');
    });
}

// Обработка загруженного файла
function handleFile(file) {
    const fileName = file.name.toLowerCase();
    if (!fileName.endsWith('.docx') && !fileName.endsWith('.doc')) {
        showToast('Пожалуйста, загрузите DOC или DOCX файл');
        return;
    }

    if (fileName.endsWith('.doc')) {
        showToast('Загружен старый формат .doc. Извлечение текста может работать нестабильно...');
    } else {
        showToast(`Файл "${file.name}" загружается...`);
    }

    uploadAndTranslate(file, currentLangFrom, currentLangTo);
}

// ==================== Инициализация после загрузки DOM ====================
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация элементов DOM
    langBtns = document.querySelectorAll('.lang-btn');
    swapBtn = document.getElementById('swapLanguages');
    sourceText = document.getElementById('sourceText');
    translatedText = document.getElementById('translatedText');
    translateBtn = document.getElementById('translateBtn');
    fileUpload = document.getElementById('fileUpload');
    fileInput = document.getElementById('fileInput');
    historyList = document.getElementById('historyList');
    clearHistoryBtn = document.getElementById('clearHistory');
    toast = document.getElementById('toast');
    userMenu = document.getElementById('userMenu');
    modal = document.getElementById('authModal');
    closeModal = document.getElementById('closeModal');
    loginTab = document.getElementById('loginTab');
    registerTab = document.getElementById('registerTab');
    loginForm = document.getElementById('loginForm');
    registerForm = document.getElementById('registerForm');
    switchToRegister = document.getElementById('switchToRegister');
    switchToLogin = document.getElementById('switchToLogin');
    loginBtn = document.getElementById('loginBtn');
    registerBtn = document.getElementById('registerBtn');
    loginError = document.getElementById('loginError');
    registerError = document.getElementById('registerError');

    // ===== Обработчики для модального окна =====
    if (closeModal) closeModal.addEventListener('click', closeModalFunc);
    window.addEventListener('click', (e) => {
        if (e.target === modal) closeModalFunc();
    });

    if (loginTab) loginTab.addEventListener('click', showLogin);
    if (registerTab) registerTab.addEventListener('click', showRegister);
    if (switchToRegister) switchToRegister.addEventListener('click', showRegister);
    if (switchToLogin) switchToLogin.addEventListener('click', showLogin);

    // ===== Авторизация =====
    if (loginBtn) {
        loginBtn.addEventListener('click', () => {
            const email = document.getElementById('loginEmail')?.value.trim();
            const password = document.getElementById('loginPassword')?.value.trim();
            if (!email || !password) {
                if (loginError) loginError.textContent = 'Заполните все поля';
                return;
            }
            const users = JSON.parse(localStorage.getItem('users')) || [];
            const user = users.find(u => u.email === email && u.password === password);
            if (user) {
                currentUser = { name: user.name, email: user.email };
                localStorage.setItem('currentUser', JSON.stringify(currentUser));
                renderUserMenu();
                closeModalFunc();
                showToast(`Добро пожаловать, ${user.name}!`);
                if (loginError) loginError.textContent = '';
            } else {
                if (loginError) loginError.textContent = 'Неверный email или пароль';
            }
        });
    }

    // ===== Регистрация =====
    if (registerBtn) {
        registerBtn.addEventListener('click', () => {
            const name = document.getElementById('registerName')?.value.trim();
            const email = document.getElementById('registerEmail')?.value.trim();
            const password = document.getElementById('registerPassword')?.value.trim();
            const confirm = document.getElementById('registerConfirm')?.value.trim();

            if (!name || !email || !password || !confirm) {
                if (registerError) registerError.textContent = 'Заполните все поля';
                return;
            }
            if (password !== confirm) {
                if (registerError) registerError.textContent = 'Пароли не совпадают';
                return;
            }
            if (password.length < 4) {
                if (registerError) registerError.textContent = 'Пароль должен быть минимум 4 символа';
                return;
            }
            const users = JSON.parse(localStorage.getItem('users')) || [];
            if (users.some(u => u.email === email)) {
                if (registerError) registerError.textContent = 'Пользователь с таким email уже существует';
                return;
            }
            const newUser = { name, email, password };
            users.push(newUser);
            localStorage.setItem('users', JSON.stringify(users));
            currentUser = { name, email };
            localStorage.setItem('currentUser', JSON.stringify(currentUser));
            renderUserMenu();
            closeModalFunc();
            showToast(`Регистрация прошла успешно, ${name}!`);
            if (registerError) registerError.textContent = '';
        });
    }

    // ===== Переключение языков (кнопки) =====
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
            updateSwitchIcon();
        });
    });

    // ===== Смена языков по стрелке =====
    if (swapBtn) {
        swapBtn.addEventListener('click', () => {
            const ruBtn = document.querySelector('[data-lang="ru"]');
            const enBtn = document.querySelector('[data-lang="en"]');
            ruBtn.classList.toggle('active');
            enBtn.classList.toggle('active');
            const temp = sourceText.value;
            sourceText.value = translatedText.value;
            translatedText.value = temp;
            if (ruBtn.classList.contains('active')) {
                currentLangFrom = 'ru';
                currentLangTo = 'en';
            } else {
                currentLangFrom = 'en';
                currentLangTo = 'ru';
            }
            updateSwitchIcon();
        });
    }

    // ===== Перевод текста =====
    if (translateBtn) {
        translateBtn.addEventListener('click', () => {
            const text = sourceText.value.trim();
            if (!text) {
                showToast('Введите текст для перевода');
                return;
            }
            const result = simulateTranslation(text, currentLangFrom, currentLangTo);
            translatedText.value = result;
            addToHistory(text, result, currentLangFrom, currentLangTo, 'text');
            showToast('Перевод выполнен');
        });
    }

    // ===== Загрузка файлов =====
    if (fileUpload && fileInput) {
        fileUpload.addEventListener('click', () => fileInput.click());
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
            if (file) handleFile(file);
        });
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) handleFile(file);
        });
    }

    // ===== Очистка истории =====
    if (clearHistoryBtn) {
        clearHistoryBtn.addEventListener('click', () => {
            if (confirm('Очистить всю историю переводов?')) {
                history = [];
                localStorage.setItem('translateHistory', JSON.stringify(history));
                renderHistory();
                showToast('История очищена');
            }
        });
    }

    // ===== Инициализация интерфейса =====
    renderHistory();
    renderUserMenu();
    updateSwitchIcon();

    // Сброс ошибок при открытии модалки (дополнительно)
    // (не требуется, т.к. поля сбрасываются в openModal)
});