<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terjadi Kesalahan — {{ config('app.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Kanit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f1dfd5;
            color: #1c1c1c;
            font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
            color: rgba(28, 28, 28, 0.4);
            margin-bottom: 1rem;
        }
        h1 {
            font-family: Kanit, sans-serif;
            font-size: 2.75rem;
            line-height: 1.2;
            font-weight: 700;
        }
        @media (min-width: 768px) {
            h1 { font-size: 4.5rem; }
        }
        p {
            color: rgba(28, 28, 28, 0.6);
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
            background: #1c1c1c;
            color: #fff;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        a.button:hover {
            background: #000;
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
