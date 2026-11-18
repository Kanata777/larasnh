<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login LARAS</title>

    <style>
        /* Layout mengikuti desain laras-login: kartu login kecil, proporsional, tepat di tengah layar.
           Ukuran desktop dibatasi agar tidak membesar pada monitor lebar. */
        :root {
            /* Ukuran kartu: ubah angka 74 (persen layar) dan 1050px (batas maksimal) untuk besar/kecil */
            --card-width: min(74vw, 1050px, calc(74vh * 1.3407));
            /* 1 satuan desain (px pada ukuran 610px) = 1/610 lebar kartu, supaya semua isi ikut membesar */
            --u: 0.16393cqw;
            --top: #ecebe6;
            --middle: #b9cbd4;
            --bottom: #176caf;
            --button: #0870bd;
            --text: #383536;
            --border: #d6d6d8;
            --radius: 22px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
        }

        /* Latar halaman: gradasi biru lembut + dua cahaya blur (biru & oranye) dari warna logo */
        body {
            min-height: 100vh;
            background: linear-gradient(160deg, #f6faff 0%, #e6f0fa 55%, #d3e5f5 100%);
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
            isolation: isolate;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            z-index: -1;
            border-radius: 50%;
            filter: blur(70px);
            pointer-events: none;
        }

        /* cahaya biru, pojok kiri atas */
        body::before {
            width: 46vmax;
            height: 46vmax;
            top: -16vmax;
            left: -14vmax;
            background: rgba(23, 108, 175, .26);
        }

        /* cahaya oranye, pojok kanan bawah */
        body::after {
            width: 40vmax;
            height: 40vmax;
            right: -14vmax;
            bottom: -16vmax;
            background: rgba(255, 140, 60, .22);
        }

        .shell {
            container-type: inline-size;
            width: var(--card-width);
            aspect-ratio: 610 / 455;
            min-width: 560px;
            display: grid;
            grid-template-columns: 50% 50%;
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: calc(var(--u) * 22);
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(23, 70, 120, .18), 0 4px 14px rgba(23, 70, 120, .08);
        }

        /* Panel kiri: satu gambar utuh (logo LARAS + foto anak-anak) */
        .visual {
            min-height: 0;
            background: linear-gradient(var(--top), var(--middle) 50%, var(--bottom));
            overflow: hidden;
        }

        .visual img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Panel kanan */
        .panel {
            min-width: 0;
            min-height: 0;
            padding: calc(var(--u) * 22) 6% calc(var(--u) * 14);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .logos {
            width: min(100%, calc(var(--u) * 245));
            height: calc(var(--u) * 76);
            margin: 0 auto calc(var(--u) * 25);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logos img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .content {
            width: min(100%, calc(var(--u) * 268));
        }

        h1 {
            text-align: center;
            font-size: calc(var(--u) * 24);
            margin: 0 0 calc(var(--u) * 22);
        }

        label {
            display: block;
            font-size: calc(var(--u) * 11);
            margin: 0 0 calc(var(--u) * 4);
        }

        input {
            width: 100%;
            height: calc(var(--u) * 36);
            border: 1px solid var(--border);
            border-radius: calc(var(--u) * 6);
            padding: 0 calc(var(--u) * 10);
            font-size: calc(var(--u) * 14);
            margin-bottom: calc(var(--u) * 9);
            outline: none;
        }

        input:focus {
            border-color: var(--button);
        }

        .pw {
            position: relative;
        }

        .pw input {
            padding-right: calc(var(--u) * 58);
        }

        #toggle {
            position: absolute;
            right: calc(var(--u) * 10);
            top: calc(var(--u) * 9);
            border: 0;
            background: none;
            color: #666;
            font-size: calc(var(--u) * 11);
            cursor: pointer;
        }

        .submit {
            width: 100%;
            height: calc(var(--u) * 36);
            margin-top: calc(var(--u) * 14);
            border: 0;
            border-radius: calc(var(--u) * 6);
            background: var(--button);
            color: white;
            font-size: calc(var(--u) * 14);
            cursor: pointer;
        }

        .submit:hover {
            background: #06599f;
        }

        /* Pesan dari Laravel (error / sukses) */
        .alert {
            padding: calc(var(--u) * 5) calc(var(--u) * 7);
            border-radius: calc(var(--u) * 5);
            font-size: calc(var(--u) * 10);
            line-height: 1.5;
            margin-bottom: calc(var(--u) * 8);
        }

        .alert-error {
            color: #a32020;
            background: #fff0f0;
            border: 1px solid #ffd6d6;
        }

        .alert-success {
            color: #17633c;
            background: #eafaf0;
            border: 1px solid #c9efd8;
        }

        footer {
            padding-top: calc(var(--u) * 16);
            font-size: calc(var(--u) * 9);
        }

        @media (max-width: 800px) {
            :root {
                --u: 1px;
            }

            body {
                padding: 16px;
            }

            .shell {
                width: min(100%, 430px);
                aspect-ratio: auto;
                min-width: 0;
                display: block;
            }

            .visual {
                display: none;
            }

            .panel {
                padding: 32px 9% 24px;
            }

            .logos {
                height: 82px;
                width: min(100%, 270px);
                margin-bottom: 22px;
            }

            h1 {
                font-size: 26px;
                margin-bottom: 20px;
            }

            .content {
                width: 100%;
                max-width: 300px;
                margin: 0 auto;
            }

            label {
                font-size: 13px;
            }

            input {
                height: 46px;
                font-size: 16px;
                margin-bottom: 12px;
            }

            #toggle {
                top: 14px;
                font-size: 13px;
            }

            .submit {
                height: 46px;
                font-size: 16px;
                margin-top: 10px;
            }

            .alert {
                font-size: 11px;
                padding: 8px 10px;
            }

            footer {
                padding-top: 17px;
                font-size: 11px;
                text-align: center;
            }
        }

    </style>
</head>

<body>

    <main class="shell">

        <!-- Panel kiri: gambar LARAS + anak-anak (satu file) -->
        <section class="visual">
            <img src="{{ asset('images/panel-kiri.png') }}" alt="Ilustrasi LARAS">
        </section>

        <!-- Panel kanan: logo sekolah + form login -->
        <section class="panel">

            <div class="logos">
                <img src="{{ asset('images/logo-gabungan.png') }}" alt="Logo Yayasan dan SDIT Nur Hidayah">
            </div>

            <div class="content">

                <h1>Login</h1>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login.process') }}">
                    @csrf

                    <label for="email">Email Pengguna</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username"
                        required autofocus>

                    <label for="password">Password</label>
                    <div class="pw">
                        <input id="password" name="password" type="password" autocomplete="current-password" required>
                        <button id="toggle" type="button">Lihat</button>
                    </div>

                    <button class="submit" type="submit">Masuk</button>
                </form>

            </div>

            <footer>PUSKOMDATIN{{ date('Y') }}</footer>

        </section>

    </main>

    <script>
        // Menampilkan / menyembunyikan password
        const p = document.getElementById('password'),
            t = document.getElementById('toggle');

        t.addEventListener('click', () => {
            const show = p.type === 'password';
            p.type = show ? 'text' : 'password';
            t.textContent = show ? 'Sembunyi' : 'Lihat';
        });
    </script>

</body>

</html>