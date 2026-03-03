# # DocTranslate

**DocTranslate** — это веб-приложение для перевода текста и документов (DOC/DOCX) с русского на английский и обратно.  
Поддерживает авторизацию (включая вход через Google), сохраняет историю переводов в базе данных и предоставляет удобный интерфейс.

---

## 🚀 Возможности

- ✍️ **Перевод текста** в реальном времени (через Google Translate)
- 📄 **Загрузка и перевод DOC/DOCX файлов** (извлечение текста + перевод)
- 🔐 **Авторизация и регистрация** (email/пароль + Google OAuth)
- 📜 **История переводов** (сохраняется для каждого пользователя)
- 🔄 **Умный переключатель языков** с визуальным поворотом стрелки
- 📱 **Адаптивный дизайн** (работает на ПК, планшетах и телефонах)
- 🛡️ **Безопасное хранение секретов** через переменные окружения

---

## 🧰 Технологии

### Backend
- PHP 8.0+
- MySQL / MariaDB
- Composer
- Библиотеки:
  - `phpoffice/phpword` — чтение DOC/DOCX
  - `stichoza/google-translate-php` — перевод текста
  - `league/oauth2-google` — вход через Google
  - `vlucas/phpdotenv` — работа с .env файлами

### Frontend
- HTML5, CSS3, JavaScript (Vanilla)
- Font Awesome 6 — иконки
- Fetch API — взаимодействие с бэкендом

---

## 📦 Установка

### 1. Клонирование репозитория
```bash
git clone https://github.com/AkameWeb/DocTranslate.git
cd DocTranslate
```

### 2. Настройка базы данных
Создайте базу данных MySQL, например `doclang`, и выполните SQL из файла `database.sql` (см. раздел ниже).

### 3. Установка зависимостей
Убедитесь, что установлен [Composer](https://getcomposer.org/), затем выполните:
```bash
composer install
```
Если возникнут проблемы с расширением `ext-gd`, можно использовать флаг:
```bash
composer install --ignore-platform-req=ext-gd
```

### 4. Настройка окружения
Создайте файл `.env` в корне проекта:
```ini
GOOGLE_CLIENT_ID=ваш_google_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=ваш_google_client_secret
```
Скопируйте пример конфигурации (если есть) и отредактируйте под себя.

### 5. Настройка веб-сервера
- Убедитесь, что Apache (или другой сервер) указывает на папку `DocLang` как корневую.
- Включите модуль `mod_rewrite`, если требуется.
- Проверьте права на папку `uploads/` (должна быть доступна для записи).

### 6. Запуск
Откройте в браузере:
```
http://localhost/DocLang
```

---

## 🗄️ Структура базы данных

```sql
CREATE DATABASE doclang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE doclang;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NULL,
    google_id VARCHAR(255) UNIQUE NULL,
    name VARCHAR(255) NOT NULL,
    avatar VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    source_text TEXT NOT NULL,
    translated_text TEXT NOT NULL,
    source_lang VARCHAR(10) NOT NULL,
    target_lang VARCHAR(10) NOT NULL,
    type ENUM('text', 'doc') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 🔧 Конфигурация Google OAuth

1. Перейдите в [Google Cloud Console](https://console.cloud.google.com/).
2. Создайте проект или выберите существующий.
3. Перейдите в **APIs & Services** → **Credentials**.
4. Нажмите **Create Credentials** → **OAuth 2.0 Client IDs**.
5. Выберите тип **Web application**, укажите имя и добавьте **Authorized redirect URIs**:
   ```
   http://localhost/DocLang/google-callback.php
   ```
6. Скопируйте полученные **Client ID** и **Client Secret** в файл `.env`.

---

## 🖥️ Использование

- **Гость**: может переводить текст и документы, история хранится локально в браузере.
- **Авторизованный пользователь**: история сохраняется на сервере и доступна после входа с любого устройства.
- **Перевод текста**: введите текст в левое поле и нажмите «Перевести».
- **Перевод документа**: перетащите файл в область загрузки или выберите его через проводник.

---

## 📁 Структура проекта

```
DocLang/
├── block/
│   ├── script/
│   │   └── main.js          # Основной фронтенд-код
│   ├── style/
│   │   └── style.css        # Стили
│   ├── footer.php           # Подвал
│   └── header.php           # Шапка (DOCTYPE, meta, стили)
├── config/
│   └── db.php               # Подключение к БД
├── uploads/                  # Временные файлы (не в git)
├── vendor/                   # Composer-пакеты (не в git)
├── .env                      # Переменные окружения (не в git)
├── .gitignore
├── composer.json
├── composer.lock
├── index.php                 # Главная страница
├── login.php                 # Обработчик входа
├── register.php              # Обработчик регистрации
├── logout.php                # Выход
├── check-session.php         # Проверка сессии
├── save-translation.php      # Сохранение перевода в БД
├── get-history.php           # Получение истории пользователя
├── translate.php             # Обработка DOC/DOCX файлов
├── translate-text.php        # Обработка перевода текста
├── google-auth.php           # Конфигурация Google (ID, секрет)
├── google-callback.php       # Callback для Google OAuth
└── README.md
```

---

## ⚠️ Важные замечания

- Файлы `.env` и `google-auth.php` **не должны попадать в репозиторий** — они уже добавлены в `.gitignore`.
- Для работы перевода документов требуется расширение PHP `ext-zip` (обычно включено в XAMPP).
- Для старых форматов `.doc` может потребоваться установка `antiword` на сервере.
- При деплое на продакшен не забудьте сменить `redirectUri` на реальный домен.

---

## 🧪 Тестирование

Ручное тестирование:
1. Регистрация нового пользователя.
2. Вход через Google.
3. Перевод текста (проверка сохранения в историю).
4. Загрузка `.docx` файла.
5. Проверка истории на сервере после перезагрузки страницы.

---

## 🤝 Вклад в проект

Мы приветствуем contributions! Если вы хотите помочь:
1. Форкните репозиторий.
2. Создайте ветку для фичи (`git checkout -b feature/amazing-feature`).
3. Закоммитьте изменения (`git commit -m 'Add some amazing feature'`).
4. Запушьте ветку (`git push origin feature/amazing-feature`).
5. Откройте Pull Request.

---

## 📄 Лицензия

```
Copyright 2026 Akame

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```

---

## 📬 Контакты

- GitHub: [AkameWeb](https://github.com/AkameWeb)
- Email: *(добавьте свой email)*

---

**DocTranslate** — свободный проект с открытым исходным кодом, созданный для удобного перевода документов.



