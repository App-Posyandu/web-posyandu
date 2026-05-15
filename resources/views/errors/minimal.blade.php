<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f7fafc;
            color: #4a5568;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .error-wrap {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 8px 16px;
            max-width: 90vw;
        }

        .error-code {
            margin: 0;
            padding-right: 16px;
            border-right: 1px solid #cbd5e0;
            font-size: 1.125rem;
            line-height: 1.75rem;
            letter-spacing: .05em;
            color: #4a5568;
        }

        .error-message {
            margin: 0;
            font-size: 1.125rem;
            line-height: 1.75rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #4a5568;
            word-break: break-word;
        }

        @media (max-width: 640px) {
            .error-wrap {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .error-code {
                border-right: 0;
                border-bottom: 1px solid #cbd5e0;
                padding-right: 0;
                padding-bottom: 8px;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <main class="error-wrap" role="main">
        <h1 class="error-code">@yield('code')</h1>
        <p class="error-message">@yield('message')</p>
    </main>
</body>

</html>
