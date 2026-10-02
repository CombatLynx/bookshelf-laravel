# bookshelf-laravel

Тестовый каталог библиотеки на Laravel 8 с архитектурой Domain-Driven Design.

Книгу можно поставить на полку, выдать читателю на 14 дней, один раз продлить и вернуть. Просроченную выдачу продлить нельзя. Правила живут в `app/Domain/Library`, сценарии — в `app/Application/Library`, сохранение в SQLite — в `app/Infrastructure`.

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

Тесты:

```powershell
php vendor/phpunit/phpunit/phpunit
```
