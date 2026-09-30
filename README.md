# bookshelf-laravel

Тестовый каталог библиотеки на Laravel 8 с архитектурой Domain-Driven Design.

Книгу можно поставить на полку, выдать и вернуть. Правила живут в `app/Domain/Library`, сценарии — в `app/Application/Library`, сохранение в SQLite — в `app/Infrastructure`.

## Запуск

Нужен PHP 7.4 и Composer.

```powershell
php composer.phar install
copy .env.example .env
php artisan key:generate
New-Item database\database.sqlite -ItemType File
php artisan migrate --seed
php artisan serve
```

Каталог откроется на http://127.0.0.1:8000. Остановить сервер в том же терминале: Ctrl+C.

Тесты:

```powershell
php vendor/phpunit/phpunit/phpunit
```
