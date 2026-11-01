<style>
    :root {
        --paper: #f4efe6;
        --card: #fffdf8;
        --ink: #1d1914;
        --muted: #6d6458;
        --line: #e3d8c6;
        --stamp: #8d3b2f;
        --stamp-dark: #6e2c23;
        --bad: #8d3b2f;
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
        width: min(420px, calc(100% - 2rem));
        margin: 0 auto;
        padding: 4rem 0;
    }

    .card {
        padding: 1.25rem;
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 18px;
    }

    h1 {
        margin: 0;
        font-family: Georgia, "Palatino Linotype", serif;
        font-size: 2rem;
    }

    p { color: var(--muted); }

    label { display: block; margin-bottom: 0.75rem; }

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
        width: 100%;
        margin-top: 0.35rem;
        border: 0;
        border-radius: 999px;
        padding: 0.6rem 0.95rem;
        background: var(--stamp);
        color: #fff;
        font: inherit;
        cursor: pointer;
    }

    button:hover { background: var(--stamp-dark); }

    .field-error { margin: 0.3rem 0 0; color: var(--bad); font-size: 0.82rem; }

    .aside { margin: 1rem 0 0; }

    a { color: var(--shelf); }
</style>
