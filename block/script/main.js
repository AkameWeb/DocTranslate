// ==================== Глобальные переменные ====================
let currentLangFrom = 'ru';
let currentLangTo = 'en';
let history = [];                      // будет загружаться с сервера или localStorage
let currentUser = null;                // данные авторизованного пользователя

// DOM-элементы (заполнятся позже)
let langBtns, swapBtn, sourceText, translatedText, translateBtn,
    fileUpload, fileInput, historyList, toast, userMenu,
    modal, closeModal, loginTab, registerTab, loginForm, registerForm,
    switchToRegister, switchToLogin, loginBtn, registerBtn, loginError, registerError;

// ==================== Вспомогательные функции ====================

function showToast(message) {
    if (!toast) toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2000);
}

// Копирование текста (глобальная для onclick)
window.copyText = function(elementId) {
    const textarea = document.getElementById(elementId);
    if (textarea && textarea.value) {
        navigator.clipboard.writeText(textarea.value).then(() => showToast('Скопировано!'));
    }
};

// Поворот стрелки в зависимости от активного языка
function updateSwitchIcon() {
    const ruBtn = document.querySelector('[data-lang="ru"]');
    if (!swapBtn) swapBtn = document.getElementById('swapLanguages');
    if (ruBtn && swapBtn) {
        ruBtn.classList.contains('active')
            ? swapBtn.classList.remove('rotated')
            : swapBtn.classList.add('rotated');
    }
}

// ==================== Работа с пользователем (сессии) ====================

// Проверка текущей сессии при загрузке
async function checkSession() {
    try {
        const res = await fetch('check-session.php');
        const data = await res.json();
        if (data.loggedIn) {
            currentUser = data.user;
        } else {
            currentUser = null;
        }
    } catch (e) {
        console.error('Session check failed', e);
        currentUser = null;
    }
    renderUserMenu();
    renderHistoryActions(); // обновим кнопки в истории
}

// Рендер шапки в зависимости от статуса
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
        document.getElementById('logoutBtn').addEventListener('click', logout);
    } else {
        userMenu.innerHTML = `
            <span><i class="far fa-user"></i> Гость</span>
            <div class="avatar" id="avatarBtn"><i class="fas fa-sign-in-alt"></i></div>
        `;
    }
    document.getElementById('avatarBtn').addEventListener('click', () => {
        if (!currentUser) openModal();
    });
}

// Выход
async function logout() {
    try {
        await fetch('logout.php');
        currentUser = null;
        renderUserMenu();
        renderHistoryActions(); // обновим кнопки
        // Загружаем гостевую историю из localStorage
        loadGuestHistory();
        showToast('Вы вышли из аккаунта');
    } catch (e) {
        console.error('Logout error', e);
    }
}

// ==================== Модальное окно авторизации ====================

function openModal() {
    if (!modal) modal = document.getElementById('authModal');
    if (!modal) return;
    modal.classList.add('active');
    // Очистка полей и ошибок
    if (loginError) loginError.textContent = '';
    if (registerError) registerError.textContent = '';
    ['loginEmail','loginPassword','registerName','registerEmail','registerPassword','registerConfirm']
        .forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
}

function closeModalFunc() {
    if (modal) modal.classList.remove('active');
}

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

// ==================== Запросы к API авторизации ====================

// Обычная регистрация
async function handleRegister() {
    const name = document.getElementById('registerName')?.value.trim();
    const email = document.getElementById('registerEmail')?.value.trim();
    const password = document.getElementById('registerPassword')?.value.trim();
    const confirm = document.getElementById('registerConfirm')?.value.trim();

    if (!name || !email || !password || !confirm) {
        registerError.textContent = 'Заполните все поля';
        return;
    }
    if (password !== confirm) {
        registerError.textContent = 'Пароли не совпадают';
        return;
    }

    try {
        const res = await fetch('register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, email, password })
        });
        const data = await res.json();
        if (res.ok && data.success) {
            currentUser = data.user;
            renderUserMenu();
            renderHistoryActions();
            closeModalFunc();
            showToast(`Добро пожаловать, ${data.user.name}!`);
            // Загружаем историю пользователя
            loadHistoryFromServer();
        } else {
            registerError.textContent = data.error || 'Ошибка регистрации';
        }
    } catch (e) {
        registerError.textContent = 'Ошибка соединения';
    }
}

// Обычный вход
async function handleLogin() {
    const email = document.getElementById('loginEmail')?.value.trim();
    const password = document.getElementById('loginPassword')?.value.trim();
    if (!email || !password) {
        loginError.textContent = 'Заполните все поля';
        return;
    }
    try {
        const res = await fetch('login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const data = await res.json();
        if (res.ok && data.success) {
            currentUser = data.user;
            renderUserMenu();
            renderHistoryActions();
            closeModalFunc();
            showToast(`С возвращением, ${data.user.name}!`);
            // Загружаем историю пользователя
            loadHistoryFromServer();
        } else {
            loginError.textContent = data.error || 'Неверный email или пароль';
        }
    } catch (e) {
        loginError.textContent = 'Ошибка соединения';
    }
}

// ==================== Работа с историей переводов ====================

// Загрузка истории с сервера
async function loadHistoryFromServer() {
    if (!currentUser) {
        loadGuestHistory();
        return;
    }
    try {
        const res = await fetch('get-history.php');
        const data = await res.json();
        history = data; // сервер уже возвращает готовые для отображения объекты
    } catch (e) {
        console.error('Failed to load history', e);
        history = [];
    }
    renderHistory();
}

// Загрузка гостевой истории из localStorage
function loadGuestHistory() {
    const saved = localStorage.getItem('guestHistory');
    history = saved ? JSON.parse(saved) : [];
    renderHistory();
}

// Сохранение гостевой истории в localStorage
function saveGuestHistory() {
    localStorage.setItem('guestHistory', JSON.stringify(history));
}

// Сохранение перевода на сервер
async function saveTranslationToServer(source, translated, from, to, type) {
    if (!currentUser) return; // не сохраняем для гостей
    try {
        await fetch('save-translation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ source, translated, from, to, type })
        });
        // После сохранения можно обновить историю (но не обязательно сразу)
        loadHistoryFromServer();
    } catch (e) {
        console.error('Failed to save translation', e);
    }
}

// Добавление в локальную историю и сохранение на сервер (если авторизован)
function addToHistory(source, translated, from, to, type = 'text') {
    const fullSource = source;
    const fullTranslated = translated;
    const fromLang = from === 'ru' ? 'ru' : 'en';
    const toLang = to === 'ru' ? 'ru' : 'en';

    // Для гостей просто добавляем в локальный массив (без сохранения)
    if (!currentUser) {
        const now = new Date();
        const formattedDate = now.toLocaleString('ru-RU', {
            day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'
        });
        const newItem = {
            id: Date.now().toString() + Math.random().toString(36).substr(2, 5),
            source: source.substring(0, 100) + (source.length > 100 ? '...' : ''),
            translated: translated.substring(0, 100) + (translated.length > 100 ? '...' : ''),
            fullSource,
            fullTranslated,
            from: from === 'ru' ? 'Русский' : 'English',
            to: to === 'ru' ? 'Русский' : 'English',
            date: formattedDate,
            type
        };
        history.unshift(newItem);
        if (history.length > 20) history.pop();
        saveGuestHistory();
        renderHistory();
    } else {
        // Сохраняем на сервер
        saveTranslationToServer(fullSource, fullTranslated, fromLang, toLang, type);
    }
}

// Удаление одной записи
async function deleteHistoryItem(id) {
    if (!confirm('Удалить эту запись из истории?')) return;

    if (currentUser) {
        try {
            const res = await fetch('delete-translation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                history = history.filter(item => item.id != id);
                renderHistory();
                showToast('Запись удалена');
            } else {
                showToast('Ошибка: ' + (data.error || 'Не удалось удалить'));
            }
        } catch (e) {
            showToast('Ошибка соединения');
        }
    } else {
        // Гость: удаляем из localStorage
        const index = history.findIndex(item => item.id == id);
        if (index !== -1) {
            history.splice(index, 1);
            saveGuestHistory();
            renderHistory();
            showToast('Запись удалена');
        }
    }
}

// Очистка всей своей истории (для авторизованных)
async function clearMyHistory() {
    if (!confirm('Вы уверены, что хотите очистить всю свою историю?')) return;

    try {
        const res = await fetch('clear-my-history.php');
        const data = await res.json();
        if (res.ok && data.success) {
            history = [];
            renderHistory();
            showToast('Ваша история очищена');
        } else {
            showToast('Ошибка: ' + (data.error || 'Не удалось очистить'));
        }
    } catch (e) {
        showToast('Ошибка соединения');
    }
}

// Очистка локальной истории (для гостей)
function clearGuestHistory() {
    if (confirm('Очистить всю историю переводов?')) {
        history = [];
        saveGuestHistory();
        renderHistory();
        showToast('История очищена');
    }
}

// Рендер кнопок управления историей в зависимости от статуса пользователя
function renderHistoryActions() {
    const header = document.querySelector('.history-header');
    if (!header) return;

    let actionsHtml = '';
    if (currentUser) {
        // Для авторизованных: кнопка "Очистить мою"
        actionsHtml = `<button class="clear-my-history" id="clearMyHistoryBtn"><i class="far fa-trash-alt"></i> Очистить мою</button>`;
        // Можно добавить проверку на админа и кнопку "Очистить всё", но пока опустим
    } else {
        // Для гостей: кнопка "Очистить"
        actionsHtml = `<button class="clear-history" id="clearGuestHistoryBtn"><i class="far fa-trash-alt"></i> Очистить</button>`;
    }

    // Если уже есть блок с действиями, обновим его, иначе создадим
    let actionsDiv = header.querySelector('.history-actions');
    if (!actionsDiv) {
        actionsDiv = document.createElement('div');
        actionsDiv.className = 'history-actions';
        header.appendChild(actionsDiv);
    }
    actionsDiv.innerHTML = actionsHtml;

    // Назначаем обработчики
    if (currentUser) {
        const btn = document.getElementById('clearMyHistoryBtn');
        if (btn) btn.addEventListener('click', clearMyHistory);
    } else {
        const btn = document.getElementById('clearGuestHistoryBtn');
        if (btn) btn.addEventListener('click', clearGuestHistory);
    }
}

// Рендер истории (из глобального массива history)
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

    historyList.innerHTML = history.map(item => {
        const itemId = item.id || `temp_${Date.now()}_${Math.random()}`;
        return `
            <div class="history-item" data-id="${itemId}">
                <div class="history-item-header">
                    <span class="history-lang">${item.from} → ${item.to}</span>
                    <span class="history-date"><i class="far fa-clock"></i> ${item.date}</span>
                </div>
                <div class="history-preview" onclick="loadFromHistory('${itemId}')">${item.source}</div>
                <div class="history-type">
                    <i class="fas ${item.type === 'doc' ? 'fa-file-word' : 'fa-font'}"></i>
                    ${item.type === 'doc' ? 'Документ' : 'Текст'}
                </div>
                <button class="delete-history-item" data-id="${itemId}" title="Удалить запись"><i class="fas fa-trash-alt"></i></button>
            </div>
        `;
    }).join('');

    // Добавляем обработчики на кнопки удаления
    document.querySelectorAll('.delete-history-item').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = btn.dataset.id;
            deleteHistoryItem(id);
        });
    });
}

// Загрузка перевода из истории (глобальная для onclick)
window.loadFromHistory = function(id) {
    const item = history.find(h => h.id == id);
    if (!item) return;

    sourceText.value = item.fullSource || item.source;
    translatedText.value = item.fullTranslated || item.translated;

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

// ==================== Перевод текста ====================
async function translateText(text, from, to) {
    try {
        const response = await fetch('translate-text.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text, from, to })
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Ошибка перевода');

        return data.translated;
    } catch (error) {
        console.error('Translation error:', error);
        showToast('Ошибка при переводе: ' + error.message);
        return null;
    }
}

// ==================== Работа с файлами ====================
function uploadAndTranslate(file, from, to) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('from', from);
    formData.append('to', to);

    showToast('Загрузка файла...');

    fetch('translate.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
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

function handleFile(file) {
    const name = file.name.toLowerCase();
    if (!name.endsWith('.docx') && !name.endsWith('.doc')) {
        showToast('Пожалуйста, загрузите DOC или DOCX файл');
        return;
    }
    if (name.endsWith('.doc')) {
        showToast('Загружен старый формат .doc. Извлечение может работать нестабильно...');
    } else {
        showToast(`Файл "${file.name}" загружается...`);
    }
    uploadAndTranslate(file, currentLangFrom, currentLangTo);
}

// ==================== Инициализация при загрузке страницы ====================
document.addEventListener('DOMContentLoaded', async function() {
    // Получаем все элементы
    langBtns = document.querySelectorAll('.lang-btn');
    swapBtn = document.getElementById('swapLanguages');
    sourceText = document.getElementById('sourceText');
    translatedText = document.getElementById('translatedText');
    translateBtn = document.getElementById('translateBtn');
    fileUpload = document.getElementById('fileUpload');
    fileInput = document.getElementById('fileInput');
    historyList = document.getElementById('historyList');
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

    // Проверяем сессию и загружаем историю
    await checkSession();
    if (currentUser) {
        await loadHistoryFromServer();
    } else {
        loadGuestHistory();
    }

    // Обработчики модального окна
    if (closeModal) closeModal.addEventListener('click', closeModalFunc);
    window.addEventListener('click', (e) => { if (e.target === modal) closeModalFunc(); });
    if (loginTab) loginTab.addEventListener('click', showLogin);
    if (registerTab) registerTab.addEventListener('click', showRegister);
    if (switchToRegister) switchToRegister.addEventListener('click', showRegister);
    if (switchToLogin) switchToLogin.addEventListener('click', showLogin);
    if (loginBtn) loginBtn.addEventListener('click', handleLogin);
    if (registerBtn) registerBtn.addEventListener('click', handleRegister);

    // Кнопка Google OAuth – просто ссылка, обработчик не нужен

    // Переключение языков (кнопки)
    langBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.classList.contains('active')) return;
            langBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentLangFrom = btn.dataset.lang === 'ru' ? 'ru' : 'en';
            currentLangTo = btn.dataset.lang === 'ru' ? 'en' : 'ru';
            updateSwitchIcon();
        });
    });

    // Смена языков по стрелке
    if (swapBtn) {
        swapBtn.addEventListener('click', () => {
            const ruBtn = document.querySelector('[data-lang="ru"]');
            const enBtn = document.querySelector('[data-lang="en"]');
            ruBtn.classList.toggle('active');
            enBtn.classList.toggle('active');
            const temp = sourceText.value;
            sourceText.value = translatedText.value;
            translatedText.value = temp;
            currentLangFrom = ruBtn.classList.contains('active') ? 'ru' : 'en';
            currentLangTo = ruBtn.classList.contains('active') ? 'en' : 'ru';
            updateSwitchIcon();
        });
    }

    // Перевод текста
    if (translateBtn) {
        translateBtn.addEventListener('click', async () => {
            const text = sourceText.value.trim();
            if (!text) {
                showToast('Введите текст для перевода');
                return;
            }

            translateBtn.disabled = true;
            translateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Перевод...';

            const translated = await translateText(text, currentLangFrom, currentLangTo);

            translateBtn.disabled = false;
            translateBtn.innerHTML = '<i class="fas fa-magic"></i> Перевести';

            if (translated !== null) {
                translatedText.value = translated;
                addToHistory(text, translated, currentLangFrom, currentLangTo, 'text');
                showToast('Перевод выполнен');
            }
        });
    }

    // Загрузка файлов
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

    updateSwitchIcon(); // начальное положение стрелки
});