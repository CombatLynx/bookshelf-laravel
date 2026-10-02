<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Полка — каталог библиотеки</title>
    <style>
        :root {
            --paper: #f4efe6;
            --card: #fffdf8;
            --ink: #1d1914;
            --muted: #6d6458;
            --line: #e3d8c6;
            --stamp: #8d3b2f;
            --stamp-dark: #6e2c23;
            --ok: #1f6b45;
            --ok-bg: #e7f4ec;
            --bad: #8d3b2f;
            --bad-bg: #f8ebe7;
            --shelf: #245c4a;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(141, 59, 47, 0.08), transparent 28rem),
                var(--paper);
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            line-height: 1.5;
        }

        main {
            width: min(1120px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 2.5rem 0 3rem;
        }

        header h1 {
            margin: 0;
            font-family: Georgia, "Palatino Linotype", serif;
            font-size: 3rem;
            font-weight: 600;
            letter-spacing: -0.03em;
        }

        header p {
            max-width: 40rem;
            margin: 0.4rem 0 0;
            color: var(--muted);
        }

        .layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 1.25rem;
            margin-top: 1.75rem;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 16px 40px rgba(60, 42, 20, 0.05);
        }

        form.card, .catalog {
            padding: 1.15rem;
        }

        h2 {
            margin: 0 0 0.9rem;
            font-family: Georgia, "Palatino Linotype", serif;
            font-size: 1.35rem;
        }

        label {
            display: block;
            margin-bottom: 0.7rem;
            font-size: 0.92rem;
        }

        label span {
            display: block;
            margin-bottom: 0.28rem;
            color: var(--muted);
        }

        input {
            width: 100%;
            padding: 0.65rem 0.75rem;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            color: var(--ink);
            font: inherit;
        }

        input:focus {
            outline: 2px solid rgba(36, 92, 74, 0.25);
            border-color: var(--shelf);
        }

        button {
            border: 0;
            border-radius: 999px;
            padding: 0.55rem 0.95rem;
            background: var(--stamp);
            color: #fff;
            font: inherit;
            cursor: pointer;
        }

        button:hover { background: var(--stamp-dark); }

        button.ghost {
            background: transparent;
            color: var(--shelf);
            border: 1px solid #b7d2c6;
        }

        button.ghost:hover { background: #f3faf6; }

        .loan-form, .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            align-items: center;
        }

        .loan-form input {
            width: 14rem;
            padding: 0.45rem 0.6rem;
        }

        .who { display: block; margin-top: 0.2rem; }

        .submit { width: 100%; margin-top: 0.35rem; }

        .field-error {
            margin: 0.3rem 0 0;
            color: var(--bad);
            font-size: 0.82rem;
        }

        .banner {
            margin: 1rem 0 0;
            padding: 0.75rem 0.9rem;
            border-radius: 12px;
        }

        .banner-ok { background: var(--ok-bg); color: var(--ok); }
        .banner-bad { background: var(--bad-bg); color: var(--bad); }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.75rem 0.35rem;
            text-align: left;
            vertical-align: middle;
            border-bottom: 1px solid var(--line);
        }

        th {
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 650;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        td strong { font-family: Georgia, "Palatino Linotype", serif; font-size: 1.05rem; }
        td small { color: var(--muted); }

        .isbn {
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        .badge {
            display: inline-block;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            background: #eef6f2;
            color: var(--shelf);
            font-size: 0.82rem;
            white-space: nowrap;
        }

        .badge.out {
            background: #f8ebe7;
            color: var(--stamp);
        }

        .empty {
            margin: 0.5rem 0 0;
            color: var(--muted);
        }

        footer {
            margin-top: 1.5rem;
            color: var(--muted);
            font-size: 0.85rem;
        }

        code { font-family: Consolas, "Courier New", monospace; }

        @media (max-width: 760px) {
            .layout { grid-template-columns: 1fr; }
            header h1 { font-size: 2.4rem; }
            .loan-form input { width: 100%; }
            table, thead, tbody, tr, th, td { display: block; }
            thead { display: none; }
            tr { padding: 0.4rem 0 0.8rem; }
            td { border: 0; padding: 0.15rem 0; }
        }
    </style>
</head>
<body>
<main>
    <header>
        <h1>Полка</h1>
        <p>Тестовый каталог библиотеки. Книгу выдают читателю на 14 дней и один раз можно продлить.</p>
    </header>

    @if (session('status'))
        <p class="banner banner-ok">{{ session('status') }}</p>
    @endif
    @error('book')
        <p class="banner banner-bad">{{ $message }}</p>
    @enderror
    @error('borrower')
        <p class="banner banner-bad">{{ $message }}</p>
    @enderror

    <div class="layout">
        <form class="card" method="post" action="{{ route('books.store') }}">
            @csrf
            <h2>Новая книга</h2>
            <label>
                <span>Название</span>
                <input name="title" value="{{ old('title') }}" maxlength="255" required>
                @error('title') <p class="field-error">{{ $message }}</p> @enderror
            </label>
            <label>
                <span>Автор</span>
                <input name="author" value="{{ old('author') }}" maxlength="255" required>
                @error('author') <p class="field-error">{{ $message }}</p> @enderror
            </label>
            <label>
                <span>ISBN-13</span>
                <input name="isbn" value="{{ old('isbn') }}" maxlength="32" placeholder="978-0-321-12521-7" required>
                @error('isbn') <p class="field-error">{{ $message }}</p> @enderror
            </label>
            <button class="submit" type="submit">Поставить на полку</button>
        </form>

        <section class="card catalog">
            <h2>Каталог</h2>
            @if (count($books) === 0)
                <p class="empty">На полке пока пусто. Добавьте первую книгу — подойдёт ISBN 978-0-321-12521-7.</p>
            @else
                <table>
                    <thead>
                    <tr>
                        <th>Книга</th>
                        <th>ISBN</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($books as $book)
                        <tr>
                            <td>
                                <strong>{{ $book->title }}</strong><br>
                                <small>{{ $book->author }}</small>
                            </td>
                            <td class="isbn">{{ $book->isbn }}</td>
                            <td>
                                <span class="badge {{ $book->isBorrowed() ? 'out' : '' }}">{{ $book->statusLabel() }}</span>
                                @if ($book->borrower)
                                    <small class="who">{{ $book->borrower }}, до {{ $book->dueOn }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($book->isBorrowed())
                                    <div class="actions">
                                        @if ($book->canBeRenewed())
                                            <form method="post" action="{{ route('books.renew', $book->id) }}">
                                                @csrf
                                                <button class="ghost" type="submit">Продлить</button>
                                            </form>
                                        @endif
                                        <form method="post" action="{{ route('books.return', $book->id) }}">
                                            @csrf
                                            <button class="ghost" type="submit">Вернуть</button>
                                        </form>
                                    </div>
                                @else
                                    <form class="loan-form" method="post" action="{{ route('books.borrow', $book->id) }}">
                                        @csrf
                                        <input name="borrower" maxlength="80" placeholder="Читатель" required>
                                        <button type="submit">Выдать</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </div>

    <footer>
        Слои: <code>Domain</code> → <code>Application</code> → <code>Infrastructure</code> → <code>Http</code>
    </footer>
</main>
</body>
</html>
