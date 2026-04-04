// ==================== Глобальные переменные ====================
let appHistory = [];          // переименовано, чтобы избежать конфликта с window.history
let currentUser = null;
let sourceLangSelect = null;
let targetLangSelect = null;

// DOM-элементы
let swapBtn, sourceText, translatedText, translateBtn, fileUpload, fileInput,
    historyList, toast, userMenu, modal, closeModal, loginTab, registerTab,
    loginForm, registerForm, switchToRegister, switchToLogin, loginBtn,
    registerBtn, loginError, registerError;

// ==================== Вспомогательные функции ====================

function showToast(message) {
    if (!toast) toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2000);
}

window.copyText = function(elementId) {
    const textarea = document.getElementById(elementId);
    if (textarea && textarea.value) {
        navigator.clipboard.writeText(textarea.value).then(() => showToast('Скопировано!'));
    }
};

// ==================== Работа с пользователем ====================

async function checkSession() {
    try {
        const res = await fetch('check-session.php');
        const data = await res.json();
        currentUser = data.loggedIn ? data.user : null;
    } catch (e) {
        currentUser = null;
    }
    renderUserMenu();
    renderHistoryActions();
}

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
    document.getElementById('avatarBtn')?.addEventListener('click', () => {
        if (!currentUser) openModal();
    });
}

async function logout() {
    try {
        await fetch('logout.php');
        currentUser = null;
        renderUserMenu();
        renderHistoryActions();
        loadGuestHistory();
        showToast('Вы вышли из аккаунта');
    } catch (e) {
        console.error('Logout error', e);
    }
}

// ==================== Модальное окно ====================

function openModal() {
    if (!modal) modal = document.getElementById('authModal');
    if (!modal) return;
    modal.classList.add('active');
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

// ==================== Авторизация ====================

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
            loadHistoryFromServer();
        } else {
            registerError.textContent = data.error || 'Ошибка регистрации';
        }
    } catch (e) {
        registerError.textContent = 'Ошибка соединения';
    }
}

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
            loadHistoryFromServer();
        } else {
            loginError.textContent = data.error || 'Неверный email или пароль';
        }
    } catch (e) {
        loginError.textContent = 'Ошибка соединения';
    }
}

// ==================== История переводов ====================

async function loadHistoryFromServer() {
    if (!currentUser) {
        loadGuestHistory();
        return;
    }
    try {
        const res = await fetch('get-history.php');
        const data = await res.json();
        appHistory = data;
    } catch (e) {
        appHistory = [];
    }
    renderHistory();
}

function loadGuestHistory() {
    const saved = localStorage.getItem('guestHistory');
    appHistory = saved ? JSON.parse(saved) : [];
    renderHistory();
}

function saveGuestHistory() {
    localStorage.setItem('guestHistory', JSON.stringify(appHistory));
}

async function saveTranslationToServer(source, translated, from, to, type) {
    if (!currentUser) return;
    try {
        await fetch('save-translation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ source, translated, from, to, type })
        });
        loadHistoryFromServer();
    } catch (e) {
        console.error('Failed to save translation', e);
    }
}

function getLanguageName(code) {
    const names = {
        'ru': 'Русский', 'en': 'English', 'de': 'Deutsch', 'fr': 'Français',
        'es': 'Español', 'it': 'Italiano', 'zh': '中文', 'ja': '日本語',
        'ko': '한국어', 'ar': 'العربية', 'tr': 'Türkçe', 'pl': 'Polski',
        'uk': 'Українська', 'pt': 'Português', 'nl': 'Nederlands',
        'sv': 'Svenska', 'da': 'Dansk', 'fi': 'Suomi', 'no': 'Norsk',
        'cs': 'Čeština', 'sk': 'Slovenčina', 'hu': 'Magyar', 'el': 'Ελληνικά',
        'he': 'עברית', 'th': 'ไทย', 'vi': 'Tiếng Việt', 'hi': 'हिन्दी',
        'bn': 'বাংলা', 'id': 'Bahasa Indonesia', 'ms': 'Bahasa Melayu', 'tl': 'Filipino'
    };
    return names[code] || code;
}

function addToHistory(source, translated, from, to, type = 'text') {
    const fullSource = source;
    const fullTranslated = translated;
    const fromLang = from;
    const toLang = to;

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
            from: getLanguageName(from),
            to: getLanguageName(to),
            date: formattedDate,
            type
        };
        appHistory.unshift(newItem);
        if (appHistory.length > 20) appHistory.pop();
        saveGuestHistory();
        renderHistory();
    } else {
        saveTranslationToServer(fullSource, fullTranslated, fromLang, toLang, type);
    }
}

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
                appHistory = appHistory.filter(item => item.id != id);
                renderHistory();
                showToast('Запись удалена');
            } else {
                showToast('Ошибка: ' + (data.error || 'Не удалось удалить'));
            }
        } catch (e) {
            showToast('Ошибка соединения');
        }
    } else {
        const index = appHistory.findIndex(item => item.id == id);
        if (index !== -1) {
            appHistory.splice(index, 1);
            saveGuestHistory();
            renderHistory();
            showToast('Запись удалена');
        }
    }
}

async function clearMyHistory() {
    if (!confirm('Вы уверены, что хотите очистить всю свою историю?')) return;

    try {
        const res = await fetch('clear-my-history.php');
        const data = await res.json();
        if (res.ok && data.success) {
            appHistory = [];
            renderHistory();
            showToast('Ваша история очищена');
        } else {
            showToast('Ошибка: ' + (data.error || 'Не удалось очистить'));
        }
    } catch (e) {
        showToast('Ошибка соединения');
    }
}

function clearGuestHistory() {
    if (confirm('Очистить всю историю переводов?')) {
        appHistory = [];
        saveGuestHistory();
        renderHistory();
        showToast('История очищена');
    }
}

function renderHistoryActions() {
    const header = document.querySelector('.history-header');
    if (!header) return;

    let actionsHtml = '';
    if (currentUser) {
        actionsHtml = `<button class="clear-my-history" id="clearMyHistoryBtn"><i class="far fa-trash-alt"></i> Очистить мою</button>`;
    } else {
        actionsHtml = `<button class="clear-history" id="clearGuestHistoryBtn"><i class="far fa-trash-alt"></i> Очистить</button>`;
    }

    let actionsDiv = header.querySelector('.history-actions');
    if (!actionsDiv) {
        actionsDiv = document.createElement('div');
        actionsDiv.className = 'history-actions';
        header.appendChild(actionsDiv);
    }
    actionsDiv.innerHTML = actionsHtml;

    if (currentUser) {
        const btn = document.getElementById('clearMyHistoryBtn');
        if (btn) btn.addEventListener('click', clearMyHistory);
    } else {
        const btn = document.getElementById('clearGuestHistoryBtn');
        if (btn) btn.addEventListener('click', clearGuestHistory);
    }
}

function renderHistory() {
    if (!historyList) historyList = document.getElementById('historyList');
    if (!historyList) return;

    if (appHistory.length === 0) {
        historyList.innerHTML = `
            <div style="text-align: center; color: #94a3b8; padding: 40px 20px;">
                <i class="fas fa-history" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <p>История переводов пуста</p>
            </div>
        `;
        return;
    }

    historyList.innerHTML = appHistory.map(item => {
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

    document.querySelectorAll('.delete-history-item').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            deleteHistoryItem(btn.dataset.id);
        });
    });
}

window.loadFromHistory = function(id) {
    const item = appHistory.find(h => h.id == id);
    if (!item) return;

    sourceText.value = item.fullSource || item.source;
    translatedText.value = item.fullTranslated || item.translated;

    const langMap = {
        'Русский': 'ru', 'English': 'en', 'Deutsch': 'de', 'Français': 'fr',
        'Español': 'es', 'Italiano': 'it', '中文': 'zh', '日本語': 'ja',
        '한국어': 'ko', 'العربية': 'ar', 'Türkçe': 'tr', 'Polski': 'pl',
        'Українська': 'uk', 'Português': 'pt', 'Nederlands': 'nl', 'Svenska': 'sv',
        'Dansk': 'da', 'Suomi': 'fi', 'Norsk': 'no', 'Čeština': 'cs',
        'Slovenčina': 'sk', 'Magyar': 'hu', 'Ελληνικά': 'el', 'עברית': 'he',
        'ไทย': 'th', 'Tiếng Việt': 'vi', 'हिन्दी': 'hi', 'বাংলা': 'bn',
        'Bahasa Indonesia': 'id', 'Bahasa Melayu': 'ms', 'Filipino': 'tl'
    };

    const fromCode = langMap[item.from] || 'ru';
    const toCode = langMap[item.to] || 'en';

    if (sourceLangSelect) sourceLangSelect.value = fromCode;
    if (targetLangSelect) targetLangSelect.value = toCode;

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
    const allowed = ['.docx', '.doc', '.pdf'];
    const hasAllowed = allowed.some(ext => name.endsWith(ext));
    
    if (!hasAllowed) {
        showToast('Пожалуйста, загрузите DOC, DOCX или PDF файл');
        return;
    }
    if (name.endsWith('.doc')) {
        showToast('Загружен старый формат .doc. Извлечение может работать нестабильно...');
    } else if (name.endsWith('.pdf')) {
        showToast('Загружен PDF файл. Извлечение текста...');
    } else {
        showToast(`Файл "${file.name}" загружается...`);
    }
    uploadAndTranslate(file, sourceLangSelect.value, targetLangSelect.value);
}

// ==================== Экспорт в PDF/DOCX ====================
function downloadFile(url, text) {
    const formData = new FormData();
    formData.append('text', text);

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Ошибка генерации файла');
        return response.blob();
    })
    .then(blob => {
        const link = document.createElement('a');
        const fileUrl = URL.createObjectURL(blob);
        link.href = fileUrl;
        const extension = url.includes('pdf') ? 'pdf' : 'docx';
        link.download = `translation.${extension}`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(fileUrl);
    })
    .catch(err => {
        console.error(err);
        showToast('Ошибка: ' + err.message);
    });
}

// ==================== Инициализация ====================
document.addEventListener('DOMContentLoaded', async function() {
    // Получаем элементы
    sourceLangSelect = document.getElementById('sourceLang');
    targetLangSelect = document.getElementById('targetLang');
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

    // Смена языков по стрелке
    if (swapBtn) {
    swapBtn.addEventListener('click', () => {
        const tempValue = sourceLangSelect.value;
        sourceLangSelect.value = targetLangSelect.value;
        targetLangSelect.value = tempValue;
        // принудительная перерисовка
        void swapBtn.offsetWidth;
        swapBtn.classList.add('rotated');
        setTimeout(() => swapBtn.classList.remove('rotated'), 300);
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

            const translated = await translateText(text, sourceLangSelect.value, targetLangSelect.value);

            translateBtn.disabled = false;
            translateBtn.innerHTML = '<i class="fas fa-magic"></i> Перевести';

            if (translated !== null) {
                translatedText.value = translated;
                addToHistory(text, translated, sourceLangSelect.value, targetLangSelect.value, 'text');
                showToast('Перевод выполнен');
            }
        });
    }

    // ==================== Перевод изображений ====================
    function uploadAndTranslateImage(file, from, to) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('from', from);
        formData.append('to', to);

        showToast('Распознавание и перевод изображения...');

        fetch('translate-image.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                sourceText.value = data.original;
                translatedText.value = data.translated;
                addToHistory(data.original, data.translated, from, to, 'image');
                showToast('Перевод изображения выполнен');
            } else {
                showToast('Ошибка: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Ошибка соединения с сервером');
        });
    }

    function handleImage(file) {
        const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp'];
        if (!allowed.includes(file.type)) {
            showToast('Пожалуйста, загрузите изображение в формате JPEG, PNG, GIF или BMP');
            return;
        }
        showToast(`Изображение "${file.name}" загружается...`);
        uploadAndTranslateImage(file, sourceLangSelect.value, targetLangSelect.value);
    }

    // В DOMContentLoaded добавьте обработчики для новой области загрузки
    const imageUpload = document.getElementById('imageUpload');
    const imageInput = document.getElementById('imageInput');

    if (imageUpload && imageInput) {
        imageUpload.addEventListener('click', () => imageInput.click());
        imageUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageUpload.style.borderColor = '#667eea';
            imageUpload.style.background = '#eef2ff';
        });
        imageUpload.addEventListener('dragleave', () => {
            imageUpload.style.borderColor = '#cbd5e1';
            imageUpload.style.background = '#f1f5f9';
        });
        imageUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            imageUpload.style.borderColor = '#cbd5e1';
            imageUpload.style.background = '#f1f5f9';
            const file = e.dataTransfer.files[0];
            if (file) handleImage(file);
        });
        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) handleImage(file);
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

    // Кнопки экспорта
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    const exportDocxBtn = document.getElementById('exportDocxBtn');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', () => {
            const text = translatedText.value.trim();
            if (!text) {
                showToast('Нет текста для сохранения');
                return;
            }
            downloadFile('generate-pdf.php', text);
        });
    }
    if (exportDocxBtn) {
        exportDocxBtn.addEventListener('click', () => {
            const text = translatedText.value.trim();
            if (!text) {
                showToast('Нет текста для сохранения');
                return;
            }
            downloadFile('generate-docx.php', text);
        });
    }

    // Очистка поля перевода при удалении исходного текста
    if (sourceText) {
        sourceText.addEventListener('input', function() {
            if (this.value.trim() === '') {
                translatedText.value = '';
            }
        });
    }
});