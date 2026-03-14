<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code ?? 500 }} - {{ $title ?? 'Terjadi Kesalahan' }}</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #0f766e;
            --primary-hover: #0b5c56;
            --line: #dbe3ee;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top right, #e6fffa, var(--bg) 45%);
            color: var(--text);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .card {
            width: 100%;
            max-width: 640px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .code {
            display: inline-block;
            margin-bottom: 12px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #e6fffa;
            color: #0f766e;
            font-weight: 700;
            font-size: 14px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: clamp(24px, 4vw, 32px);
            line-height: 1.2;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
            font-size: 16px;
        }

        .actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            text-decoration: none;
            border: 1px solid var(--line);
            color: var(--text);
            background: #fff;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-primary {
            border-color: var(--primary);
            background: var(--primary);
            color: #fff;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="code">Error {{ $code ?? 500 }}</div>
        <h1>{{ $title ?? 'Terjadi Kesalahan' }}</h1>
        <p>{{ $message ?? 'Maaf, terjadi kendala pada sistem. Silakan coba beberapa saat lagi.' }}</p>

        <div class="actions">
            <a class="btn" href="{{ url()->previous() }}">Kembali</a>
            <a class="btn btn-primary" href="{{ auth()->check() ? url('/dashboard') : url('/') }}">
                {{ auth()->check() ? 'Ke Dashboard' : 'Ke Beranda' }}
            </a>
        </div>
    </main>
</body>
</html>
