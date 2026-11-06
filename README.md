# bookshelf-laravel

Тестовый каталог библиотеки на Laravel 8 с архитектурой Domain-Driven Design.

Книгу можно поставить на полку, выдать вошедшему читателю на 14 дней, один раз продлить и вернуть. Чужую книгу можно забронировать: после возврата она три дня ждёт этого читателя. Просроченную выдачу продлить нельзя, чужую книгу вернуть тоже. Вход на сайте идёт через сессию, её проверяет Sanctum; для API тот же Sanctum выдаёт токен. Правила живут в `app/Domain/Library`, сценарии — в `app/Application/Library`, сохранение в SQLite — в `app/Infrastructure`.

## Запуск

Нужен PHP 7.4. Команды выполнять из корня проекта.

Composer в систему не установлен, поэтому сначала скачивается `composer.phar`, затем ставятся зависимости:

```powershell
Invoke-WebRequest https://getcomposer.org/installer -OutFile composer-setup.php
php composer-setup.php
Remove-Item composer-setup.php
php composer.phar install
```

Если `composer` уже есть в PATH, вместо этих четырёх строк достаточно `composer install`.

Дальше окружение, база SQLite и сервер:

```powershell
Copy-Item .env.example .env
php artisan key:generate
New-Item database\database.sqlite -ItemType File
php artisan migrate --seed
php artisan serve
```

Если файл `database\database.sqlite` уже есть, строку `New-Item` пропустите и выполните только `php artisan migrate --seed` — так добавятся колонки выдачи.

Каталог откроется на http://127.0.0.1:8000. Остановить сервер в том же терминале: Ctrl+C.

Если база уже была создана раньше, выполните `php artisan migrate`, чтобы появилась колонка `borrower_id`.

Читатель регистрируется на http://127.0.0.1:8000/register. Токен для API:

```powershell
curl.exe -X POST http://127.0.0.1:8000/api/token -H "Content-Type: application/json" -d "{\"email\":\"anna@example.com\",\"password\":\"secret-pass\",\"device_name\":\"notebook\"}"
```

Дальше заголовок `Authorization: Bearer <token>` на `POST /api/books/{id}/borrow`, `/renew` и `/return`.

Тесты:

```powershell
php vendor/phpunit/phpunit/phpunit
```
