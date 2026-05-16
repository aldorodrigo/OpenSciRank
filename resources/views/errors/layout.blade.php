{{-- Layout base para todas las páginas de error. Se incluye desde 404, 500, 419, 503, 403, etc.
     Sin dependencias de DB, sesión, ni Livewire — debe renderizar incluso si la app está en maintenance. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ?? 'es') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $code ?? 'Error' }} · Editorial Standards Platform</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-from: #fafafa;
            --bg-to: #f4f4f5;
            --card-bg: #ffffff;
            --text-primary: #18181b;
            --text-secondary: #52525b;
            --text-muted: #a1a1aa;
            --brand: #4f46e5;
            --brand-hover: #4338ca;
            --border: #e4e4e7;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-from: #09090b;
                --bg-to: #18181b;
                --card-bg: #18181b;
                --text-primary: #fafafa;
                --text-secondary: #d4d4d8;
                --text-muted: #71717a;
                --brand: #818cf8;
                --brand-hover: #a5b4fc;
                --border: #27272a;
            }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--bg-from), var(--bg-to));
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            -webkit-font-smoothing: antialiased;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            max-width: 32rem;
            width: 100%;
            padding: 3rem 2.5rem;
            text-align: center;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--brand);
            margin-bottom: 1.5rem;
        }
        .badge svg { width: 1rem; height: 1rem; }
        .code {
            font-size: 5rem;
            font-weight: 700;
            line-height: 1;
            color: var(--brand);
            margin-bottom: 0.5rem;
            letter-spacing: -0.05em;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--text-primary);
        }
        p {
            font-size: 0.9375rem;
            line-height: 1.6;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }
        .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.625rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }
        .btn-primary {
            background: var(--brand);
            color: white;
        }
        .btn-primary:hover { background: var(--brand-hover); }
        .btn-secondary {
            background: transparent;
            color: var(--text-secondary);
            border-color: var(--border);
        }
        .btn-secondary:hover {
            background: var(--bg-from);
            color: var(--text-primary);
        }
        .footer {
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        .footer a {
            color: var(--brand);
            text-decoration: none;
        }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">
            {!! $badgeIcon ?? '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>' !!}
            <span>Editorial Standards</span>
        </div>
        <div class="code">{{ $code ?? '500' }}</div>
        <h1>{{ $title ?? __('Algo salió mal') }}</h1>
        <p>{{ $message ?? __('Estamos investigando el problema. Por favor intentá nuevamente en unos minutos.') }}</p>
        <div class="actions">
            <a href="{{ url('/') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                {{ __('Volver al inicio') }}
            </a>
            @if(! ($hideBack ?? false))
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    {{ __('Atrás') }}
                </a>
            @endif
        </div>
        <div class="footer">
            <a href="{{ url('/contact') }}">{{ __('Reportar este problema') }}</a> · {{ now()->format('Y') }} Editorial Standards Platform
        </div>
    </div>
</body>
</html>
