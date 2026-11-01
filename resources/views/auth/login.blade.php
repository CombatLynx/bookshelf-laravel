<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход — Полка</title>
    @include('auth.style')
</head>
<body>
<main>
    <form class="card" method="post" action="{{ route('login') }}">
        @csrf
        <h1>Вход</h1>
        <p>Книгу можно взять только после входа.</p>
        <label>
            <span>Email</span>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </label>
        <label>
            <span>Пароль</span>
            <input type="password" name="password" required>
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </label>
        <button type="submit">Войти</button>
        <p class="aside">Нет учётной записи? <a href="{{ route('register') }}">Регистрация</a></p>
    </form>
</main>
</body>
</html>
