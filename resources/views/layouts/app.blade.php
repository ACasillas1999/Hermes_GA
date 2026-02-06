<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hermes GA')</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <script>
        (function () {
            try {
                const stored = localStorage.getItem('theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = stored || (prefersDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f6fb;
            --panel: #ffffff;
            --card: #ffffff;
            --line: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --accent: #0ea5e9;
            --accent-strong: #0284c7;
            --ok: #16a34a;
            --danger: #dc2626;
            --shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
        }

        html[data-theme="dark"] {
            color-scheme: dark;
            --bg: #0f172a;
            --panel: #0b1220;
            --card: #111a2e;
            --line: #1f2a44;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --accent: #38bdf8;
            --accent-strong: #0ea5e9;
            --ok: #22c55e;
            --danger: #ef4444;
            --shadow: 0 20px 45px rgba(15, 23, 42, 0.4);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            font-size: 14px;
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            color: var(--text);
            min-height: 100vh;
            opacity: 0;
            transform: translateY(6px);
            transition: opacity 180ms ease, transform 180ms ease;
        }

        html[data-theme="dark"] body {
            background: radial-gradient(circle at top, #1e293b 0%, #0f172a 60%, #060a13 100%);
        }

        body.page-ready {
            opacity: 1;
            transform: translateY(0);
        }

        body.page-leave {
            opacity: 0;
            transform: translateY(6px);
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 210px;
            background: var(--panel);
            border-right: 1px solid var(--line);
            padding: 18px 12px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            margin: 0 0 16px;
        }

        .brand img {
            width: 96px;
            height: 96px;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.2);
            background: rgba(255, 255, 255, 0.08);
        }

        .brand span {
            display: none;
        }

        .nav {
            display: grid;
            gap: 6px;
        }

        .nav a {
            text-decoration: none;
            color: var(--text);
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid transparent;
            background: transparent;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav a.active,
        .nav a:hover {
            background: rgba(56, 189, 248, 0.12);
            border-color: rgba(56, 189, 248, 0.35);
        }

        .nav svg {
            width: 16px;
            height: 16px;
            color: currentColor;
        }

        .theme-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: transparent;
            color: var(--text);
            cursor: pointer;
            font-size: 12px;
        }

        .theme-toggle svg {
            width: 14px;
            height: 14px;
        }

        .content {
            flex: 1;
            padding: 20px 20px 32px;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }

        .page-subtitle {
            color: var(--muted);
            font-size: 12px;
            margin-top: 4px;
        }

        .grid {
            display: grid;
            gap: 14px;
        }

        .card {
            background: linear-gradient(145deg, rgba(30, 41, 59, 0.85), rgba(15, 23, 42, 0.95));
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 16px;
            box-shadow: var(--shadow);
        }

        html[data-theme="light"] .card {
            background: var(--card);
        }

        .card h2 {
            margin: 0 0 8px;
            font-size: 16px;
        }

        .muted {
            color: var(--muted);
            font-size: 12px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.35);
            color: #bbf7d0;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fecaca;
        }

        form {
            display: grid;
            gap: 10px;
        }

        label {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 4px;
            display: block;
        }

        select,
        input[type="text"],
        input[type="date"],
        input[type="datetime-local"],
        textarea {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: var(--panel);
            color: var(--text);
            font-size: 13px;
        }

        .row {
            display: grid;
            gap: 8px;
        }

        .row-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .button {
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            color: #02131f;
            background: linear-gradient(135deg, var(--accent), var(--accent-strong));
        }

        .button-secondary {
            color: var(--text);
            background: transparent;
            border: 1px solid var(--line);
        }

        .button-danger {
            color: #fee2e2;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .table th,
        .table td {
            text-align: left;
            padding: 8px 10px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }

        .table th {
            color: var(--muted);
            font-weight: 600;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(56, 189, 248, 0.12);
            color: var(--text);
            border: 1px solid rgba(56, 189, 248, 0.35);
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
        }

        .table input[type="text"] {
            padding: 6px 8px;
            font-size: 12px;
        }

        @media (min-width: 860px) {
            .grid {
                grid-template-columns: 1fr 2fr;
            }
        }

        @media (max-width: 900px) {
            .layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <img src="/logo.png" alt="Hermes GA logo">
        </div>
        <nav class="nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 10.5L12 3l9 7.5"></path>
                    <path d="M5 9.5V21h14V9.5"></path>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('messaging.index') }}" class="{{ request()->routeIs('messaging.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 5h16v10H7l-3 3V5z"></path>
                    <path d="M8 9h8"></path>
                </svg>
                Envio masivo
            </a>
            <a href="{{ route('scheduled.index') }}" class="{{ request()->routeIs('scheduled.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                    <path d="M8 2v4M16 2v4M3 10h18"></path>
                    <path d="M12 14v4l2 1"></path>
                </svg>
                Programados
            </a>
            <a href="{{ route('templates.index') }}" class="{{ request()->routeIs('templates.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16v16H4z"></path>
                    <path d="M8 8h8"></path>
                    <path d="M8 12h8"></path>
                    <path d="M8 16h5"></path>
                </svg>
                Plantillas
            </a>
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Usuarios
            </a>
            <a href="{{ route('history.index') }}" class="{{ request()->routeIs('history.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 7v6l4 2"></path>
                </svg>
                Historial
            </a>
            <a href="{{ route('listados.index') }}" class="{{ request()->routeIs('listados.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 6h16"></path>
                    <path d="M4 12h16"></path>
                    <path d="M4 18h10"></path>
                </svg>
                Listados
            </a>
        </nav>
    </aside>

    <main class="content">
        <div class="page-header">
            <div>
                <h2 class="page-title">@yield('title')</h2>
                @hasSection('subtitle')
                    <div class="page-subtitle">@yield('subtitle')</div>
                @endif
            </div>
            <div class="row-inline">
                @yield('header-actions')
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="button button-secondary" type="submit">Salir</button>
                    </form>
                @endauth
                <button class="theme-toggle" type="button" id="theme-toggle" title="Cambiar tema">
                    <span id="theme-label">Tema oscuro</span>
                    <svg id="theme-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"></path>
                    </svg>
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
    (function () {
        const body = document.body;
        requestAnimationFrame(() => {
            body.classList.add('page-ready');
        });

        document.addEventListener('click', (event) => {
            const link = event.target.closest('a');
            if (!link) return;
            if (link.target && link.target !== '_self') return;
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;

            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin) return;

            event.preventDefault();
            body.classList.remove('page-ready');
            body.classList.add('page-leave');
            setTimeout(() => {
                window.location.href = link.href;
            }, 170);
        });

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            const method = (form.getAttribute('method') || 'GET').toUpperCase();
            if (method !== 'GET') return;
            body.classList.remove('page-ready');
            body.classList.add('page-leave');
        });
    })();
</script>

<script>
    (function () {
        const root = document.documentElement;
        const button = document.getElementById('theme-toggle');
        const label = document.getElementById('theme-label');
        const icon = document.getElementById('theme-icon');

        function updateLabel(theme) {
            if (theme === 'dark') {
                label.textContent = 'Tema oscuro';
                icon.innerHTML = '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"></path>';
            } else {
                label.textContent = 'Tema claro';
                icon.innerHTML = '<circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path>';
            }
        }

        const current = root.getAttribute('data-theme') || 'dark';
        updateLabel(current);

        button?.addEventListener('click', () => {
            const next = (root.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
            updateLabel(next);
        });
    })();
</script>

@stack('scripts')
</body>
</html>
