<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laras BK')</title>

    {{-- Gaya dasar di bawah ini DISAMAKAN dengan layout larasnh. Tambahan khusus BK ada di public/css/bk.css --}}
    <style>
        :root {
            --navy: #123c70;
            --blue: #1675ce;
            --green: #10b981;
            --orange: #f59e0b;
            --red: #dc2626;
            --text: #1e293b;
            --muted: #64748b;
            --line: #e5eaf1;
            --bg: #f1f5f9
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: var(--bg);
            color: var(--text)
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 245px;
            background: var(--navy);
            color: #fff;
            padding: 24px 16px;
            overflow-y: auto
        }

        .brand {
            font-size: 18px;
            font-weight: 700;
            padding: 8px 10px 22px;
            border-bottom: 1px solid #ffffff30
        }

        .brand small {
            display: block;
            font-size: 11px;
            font-weight: 400;
            margin-top: 6px;
            color: #cbdff7
        }

        .nav-title {
            font-size: 11px;
            color: #a9c4e6;
            margin: 24px 10px 8px;
            text-transform: uppercase;
            letter-spacing: .04em
        }

        .nav-link {
            display: block;
            text-decoration: none;
            color: #e4efff;
            padding: 11px 12px;
            border-radius: 8px;
            margin-bottom: 4px;
            font-size: 13px
        }

        .nav-link:hover,
        .nav-link.active {
            background: #2462a5;
            color: #fff
        }

        .nav-link.active {
            box-shadow: inset 3px 0 0 #8cc8ff;
            font-weight: 600
        }

        /* Layout */
        .main {
            margin-left: 245px;
            min-height: 100vh
        }

        .topbar {
            background: #fff;
            padding: 18px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            border-bottom: 1px solid #e2e8f0
        }

        .topbar h2 {
            font-size: 21px
        }

        .topbar p {
            color: var(--muted);
            font-size: 12px;
            margin-top: 5px
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px
        }

        .logout {
            background: #fee2e2;
            color: #b91c1c;
            border: none;
            padding: 9px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700
        }

        .content {
            padding: 28px;
            max-width: 1280px
        }

        /* Alerts */
        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
            line-height: 1.6
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca
        }

        .alert-error ul {
            margin: 6px 0 0 18px
        }

        /* Panel & table */
        .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 22px;
            min-width: 0;
            margin-bottom: 18px
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap
        }

        .panel-head h3 {
            font-size: 16px
        }

        .panel-head p,
        .muted {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
            line-height: 1.6
        }

        .table-wrap {
            overflow-x: auto
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px
        }

        th {
            text-align: left;
            font-size: 11px;
            color: var(--muted);
            font-weight: 600;
            padding: 0 10px 10px;
            border-bottom: 1px solid var(--line);
            white-space: nowrap
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #f1f4f8;
            vertical-align: middle
        }

        tr:last-child td {
            border-bottom: none
        }

        td.actions {
            white-space: nowrap;
            text-align: right
        }

        .empty {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
            padding: 8px 0
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600
        }

        .badge-green {
            background: #d1fae5;
            color: #065f46
        }

        .badge-gray {
            background: #e2e8f0;
            color: #475569
        }

        .badge-blue {
            background: #dbeafe;
            color: #1e40af
        }

        .badge-orange {
            background: #fef3c7;
            color: #92400e
        }

        .badge-red {
            background: #fee2e2;
            color: #991b1b
        }

        /* Buttons */
        .btn {
            display: inline-block;
            border: none;
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none
        }

        .btn-primary {
            background: var(--blue);
            color: #fff
        }

        .btn-primary:hover {
            background: #125fa9
        }

        .btn-light {
            background: #eef3f9;
            color: var(--navy)
        }

        .btn-light:hover {
            background: #dfe8f3
        }

        .btn-danger {
            background: #fee2e2;
            color: #b91c1c
        }

        .btn-danger:hover {
            background: #fecaca
        }

        .btn-sm {
            padding: 6px 10px
        }

        .btn-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap
        }

        .inline-form {
            display: inline
        }

        .btn:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible,
        a:focus-visible {
            outline: 2px solid var(--blue);
            outline-offset: 2px
        }

        /* Form */
        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px
        }

        input[type=text],
        input[type=date],
        input[type=email],
        input[type=search],
        input[type=number],
        input[type=file],
        select,
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            font-family: inherit;
            background: #fff;
            color: var(--text)
        }

        textarea {
            resize: vertical;
            min-height: 70px
        }

        .field {
            margin-bottom: 16px
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 18px
        }

        .full {
            grid-column: 1 / -1
        }

        .req {
            color: var(--red)
        }

        .err {
            display: block;
            color: #b91c1c;
            font-size: 11px;
            margin-top: 5px
        }

        .field input.invalid,
        .field select.invalid,
        .field textarea.invalid {
            border-color: #f87171
        }

        /* Modal */
        dialog {
            border: none;
            border-radius: 14px;
            padding: 0;
            width: min(480px, calc(100% - 32px));
            margin: auto;
            color: var(--text)
        }

        dialog::backdrop {
            background: #0f172a80
        }

        .modal-head {
            padding: 18px 22px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .modal-head h3 {
            font-size: 16px
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--muted)
        }

        .modal-body {
            padding: 22px
        }

        .modal-foot {
            padding: 14px 22px;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: flex-end;
            gap: 10px
        }

        .pager {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 16px;
            font-size: 12px;
            color: var(--muted);
            flex-wrap: wrap
        }

        .footer {
            text-align: center;
            color: #94a3b8;
            font-size: 11px;
            margin-top: 30px;
            padding-bottom: 10px
        }

        @media (max-width:1100px) {
            .form-grid {
                grid-template-columns: 1fr
            }
        }

        @media (max-width:700px) {
            .sidebar {
                position: static;
                width: 100%
            }

            .main {
                margin-left: 0
            }

            .topbar {
                padding: 16px;
                flex-wrap: wrap
            }

            .content {
                padding: 16px
            }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/bk.css') }}">
    @stack('styles')
</head>

<body>

    {{-- Staff TU tetap memakai sidebar bersama (sama seperti halaman Master/Siswa), role lain memakai sidebar BK --}}
    @if(auth()->user()->role->value === 'staff_tu')
        @include('partials.sidebar')
    @else
        @include('bk.partials.sidebar')
    @endif

    <main class="main">

        <header class="topbar">
            <div>
                <h2>@yield('title')</h2>
                <p>@yield('subtitle')</p>
            </div>

            <div class="user-box">
                <span>👤 {{ auth()->user()->name }}</span>
                <span class="badge badge-blue">{{ auth()->user()->role->label() }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout">Logout</button>
                </form>
            </div>
        </header>

        <section class="content">

            {{-- Controller BK memakai kunci flash berbeda: success / sukses / ok (berhasil), galat (gagal) --}}
            @if($pesanOk = session('success') ?? session('sukses') ?? session('ok'))
                <div class="alert alert-success">{{ $pesanOk }}</div>
            @endif

            @if(session('galat'))
                <div class="alert alert-error">{{ session('galat') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    Data belum bisa diproses. Periksa hal berikut:
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

            <div class="footer">Sistem Bimbingan Konseling &copy; {{ date('Y') }}</div>

        </section>
    </main>

    @stack('scripts')

</body>

</html>