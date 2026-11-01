<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Регистрация — Полка</title>
    @include('auth.style')
</head>
<body>
<main>
    <form class="card" method="post" action="{{ route('register') }}">
        @csrf
        <h1>Регистрация</h1>
        <p>Имя попадёт в каталог, когда вы возьмёте книгу.</p>
        <label>
            <span>Имя</span>
            <input name="name" value="{{ old('name') }}" maxlength="80" required autofocus>
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </label>
        <label>
            <span>Email</span>
            <input type="email" name="email" value="{{ old('email') }}" required>
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </label>
        <label>
            <span>Пароль</span>
            <input type="password" name="password" required>
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </label>
        <label>
            <span>Пароль ещё раз</span>
            <input type="password" name="password_confirmation" required>
        </label>
        <button type="submit">Создать учётную запись</button>
        <p class="aside">Уже регистрировались? <a href="{{ route('login') }}">Войти</a></p>
    </form>
</main>
</body>
</html>
