<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <style>
            :root {
                color-scheme: dark;
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                background: #0b0b0d;
                color: #f5f5f4;
            }

            body {
                min-height: 100vh;
                margin: 0;
                display: grid;
                place-items: center;
            }

            main {
                width: min(92vw, 760px);
                border: 1px solid #2f2f35;
                border-radius: 12px;
                padding: 48px;
                background: #151518;
                box-shadow: 0 24px 80px rgb(0 0 0 / 0.35);
            }

            h1 {
                margin: 0 0 12px;
                font-size: clamp(42px, 8vw, 88px);
                line-height: 1;
                color: #ff2d20;
            }

            p {
                margin: 0;
                color: #c9c9c9;
                font-size: 18px;
            }

            strong {
                color: #ffffff;
            }
        </style>
    </head>
    <body>
        <main>
            <h1>Laravel {{ app()->version() }}</h1>
            <p><strong>{{ config('app.name', 'Laravel') }}</strong> is running successfully.</p>
        </main>
    </body>
</html>
