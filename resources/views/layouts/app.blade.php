<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIM Data Siswa')</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f4f6fb;color:#1d2433;font:14px/1.5 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
        .app{display:flex;min-height:100vh}
        .side{width:230px;background:#fff;border-right:1px solid #e3e7ef;padding:16px 12px;flex-shrink:0;display:flex;flex-direction:column}
        .brand{font-weight:700;font-size:16px;padding:4px 8px 14px}
        .nav a,.nav span{display:block;padding:9px 10px;border-radius:8px;color:#1d2433;text-decoration:none;margin-bottom:2px}
        .nav a.on,.nav a:hover{background:#2563eb18;color:#2563eb}
        .nav span{color:#98a2b3}.nav small{float:right;font-size:11px}
        .me{margin-top:auto;border-top:1px solid #e3e7ef;padding-top:12px;font-size:13px}
        .me button{margin-top:8px;width:100%;background:none;border:1px solid #d0d5dd;border-radius:8px;padding:7px;cursor:pointer;font:inherit}
        .main{flex:1;padding:24px;min-width:0}
        .card{background:#fff;border:1px solid #e3e7ef;border-radius:12px;padding:18px;margin-bottom:14px}
        .badge{display:inline-block;padding:2px 10px;border-radius:99px;background:#2563eb18;color:#2563eb;font-size:12px}
        @media(max-width:720px){.app{flex-direction:column}.side{width:100%;flex-direction:row;flex-wrap:wrap;align-items:center;gap:6px;border-right:0;border-bottom:1px solid #e3e7ef}.me{margin:0 0 0 auto;border:0;padding:0}.main{padding:14px}}
    </style>
</head>
<body>
<div class="app">
    <aside class="side">
        <div class="brand">🎓 SIM Siswa</div>
        <nav class="nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'on' : '' }}">📊 Dashboard</a>
            <span>👤 Data Siswa <small>Tahap 4</small></span>
            <span>🗂 Master <small>Tahap 3</small></span>
            <span>📄 Laporan <small>Tahap 8</small></span>
        </nav>
        <div class="me">
            <strong>{{ auth()->user()->name }}</strong><br>
            <span class="badge">{{ auth()->user()->role->label() }}</span>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
        </div>
    </aside>
    <main class="main">
        @yield('content')
    </main>
</div>
</body>
</html>
