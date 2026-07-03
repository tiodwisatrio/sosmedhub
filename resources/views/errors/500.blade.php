<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terjadi Kesalahan — {{ config('app.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #000;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 1.5rem;
        }
        .eyebrow {
            font-size: 10px;
            letter-spacing: 0.32em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.4);
            margin-bottom: 1rem;
        }
        h1 {
            font-size: 2.75rem;
            line-height: 1.2;
            font-weight: 400;
        }
        @media (min-width: 768px) {
            h1 { font-size: 4.5rem; }
        }
        p {
            color: rgba(255, 255, 255, 0.5);
            margin-top: 1.5rem;
            max-width: 28rem;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        a.button {
            display: inline-block;
            margin-top: 2.5rem;
            padding: 0.75rem 2rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        a.button:hover {
            background: rgba(255, 255, 255, 0.18);
        }
    </style>
</head>
<body>

<div>
    <p class="eyebrow">500</p>
    <h1>Terjadi kesalahan pada server.</h1>
    <p>Tim kami sedang menangani masalah ini. Silakan coba lagi beberapa saat lagi.</p>
    <a class="button" href="/">Kembali ke Beranda</a>
</div>

</body>
</html>
