<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ __('emails.unsubscribe.title') }} · {{ config('app.name') }}</title>
    <style>
        body { margin: 0; background: #fafafa; color: #52525b; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .wrap { max-width: 520px; margin: 8vh auto; padding: 0 20px; }
        .card { background: #fff; border: 1px solid #e4e4e7; border-radius: 10px; padding: 40px 32px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .mark { width: 44px; height: 44px; border-radius: 8px; background: #172554; display: inline-block; margin-bottom: 20px; }
        h1 { color: #18181b; font-size: 20px; margin: 0 0 12px; }
        p { font-size: 15px; line-height: 1.6; margin: 0 0 8px; }
        .muted { color: #a1a1aa; font-size: 13px; margin-top: 20px; }
        a { color: #1E3A8A; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <span class="mark"></span>
            <h1>{{ __('emails.unsubscribe.title') }}</h1>
            <p>{{ __('emails.unsubscribe.message', ['email' => $user->email]) }}</p>
            <p class="muted">{{ __('emails.unsubscribe.note') }}</p>
            <p class="muted"><a href="{{ url('/') }}">{{ config('app.name') }}</a></p>
        </div>
    </div>
</body>
</html>
