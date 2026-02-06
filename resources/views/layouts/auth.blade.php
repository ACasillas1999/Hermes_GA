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
            --shadow: 0 20px 45px rgba(15, 23, 42, 0.4);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            font-size: 14px;
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            color: var(--text);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            opacity: 0;
            transform: translateY(6px);
            transition: opacity 180ms ease, transform 180ms ease;
        }

        html[data-theme="dark"] body {
            background: radial-gradient(circle at top, #1e293b 0%, #0f172a 60%, #060a13 100%);
        }

        body.page-ready { opacity: 1; transform: translateY(0); }

        .auth-card {
            width: min(420px, 95vw);
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow);
        }

        .auth-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
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

        label {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 4px;
            display: block;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: var(--panel);
            color: var(--text);
            font-size: 13px;
        }

        .row { display: grid; gap: 8px; }

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

        .alert {
            padding: 10px 12px;
            border-radius: 12px;
            margin-bottom: 12px;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fecaca;
        }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-header">
        <div style="font-weight: 700;">@yield('title', 'Hermes GA')</div>
        <button class="theme-toggle" type="button" id="theme-toggle">
            <span id="theme-label">Tema oscuro</span>
        </button>
    </div>

    @if ($errors->any())
        <div class="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @yield('content')
</div>

<script>
    (function () {
        const body = document.body;
        requestAnimationFrame(() => {
            body.classList.add('page-ready');
        });

        const root = document.documentElement;
        const button = document.getElementById('theme-toggle');
        const label = document.getElementById('theme-label');

        function updateLabel(theme) {
            label.textContent = theme === 'dark' ? 'Tema oscuro' : 'Tema claro';
        }

        updateLabel(root.getAttribute('data-theme') || 'dark');
        button?.addEventListener('click', () => {
            const next = (root.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
            updateLabel(next);
        });
    })();
</script>
</body>
</html>
