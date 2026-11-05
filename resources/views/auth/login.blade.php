<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login LARAS</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            width: 100%;
            min-height: 100vh;
            min-height: 100dvh;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #ffffff;
            color: #333333;
        }

        /* ===== Layout full layar (mengikuti rasio perangkat) ===== */
        .container {
            width: 100vw;
            height: 100vh;
            height: 100dvh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #ffffff;
        }

        /* ===== Panel kiri ===== */
        .left-panel {
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg,
                    #ebebe7 0%,
                    #c3d0dc 25%,
                    #86a9cd 48%,
                    #2a6fb7 70%,
                    #1f69b3 100%);
        }

        /* Logo LARAS: posisinya "melayang" (absolute), jadi mengubah ukurannya
           TIDAK menggeser atau mengecilkan foto di bawahnya.
           Ubah angka width untuk memperbesar / memperkecil. */
        .laras-logo {
            position: absolute;
            top: -3vh;
            left: 50%;
            transform: translateX(-50%);
            width: min(84%, 52vh);
            height: auto;
            z-index: 1;
        }

        /* Foto: menempel di bawah, tingginya tidak bergantung pada logo */
        .kids-image {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 54vh;
            object-fit: cover;
            object-position: center top;
            display: block;
            z-index: 2;
        }

        /* ===== Panel kanan ===== */
        .right-panel {
            background: #ffffff;
            height: 100%;
            padding: 6vh 5% 3vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-y: auto;
        }

        .school-logo {
            width: min(90%, 620px);
            max-height: 24vh;
            object-fit: contain;
            height: auto;
        }

        .login-area {
            width: 100%;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            width: 100%;
            max-width: 640px;
            padding: 0 6%;
        }

        .login-box h2 {
            text-align: center;
            font-size: clamp(28px, 4.2vh, 44px);
            font-weight: bold;
            color: #333333;
            margin-bottom: clamp(20px, 4vh, 44px);
        }

        .form-group {
            margin-bottom: clamp(10px, 1.6vh, 18px);
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: clamp(13px, 1.8vh, 17px);
            color: #333333;
        }

        input {
            width: 100%;
            height: clamp(46px, 6.6vh, 68px);
            padding: 0 16px;
            border: 2px solid #dcdcdc;
            border-radius: 10px;
            font-size: 16px;
            outline: none;
            background: #ffffff;
            color: #333333;
        }

        input:focus {
            border-color: #0868ba;
        }

        .login-btn {
            width: 100%;
            height: clamp(46px, 6.4vh, 66px);
            margin-top: clamp(24px, 6vh, 64px);
            border: none;
            border-radius: 8px;
            background: #0868ba;
            color: #ffffff;
            font-size: clamp(18px, 2.8vh, 26px);
            cursor: pointer;
        }

        .login-btn:hover {
            background: #064f91;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            line-height: 1.6;
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

        .footer {
            text-align: center;
            font-size: clamp(12px, 1.7vh, 16px);
            color: #333333;
            padding-top: 12px;
        }

        /* ===== Layar potret (HP / tablet berdiri): susun atas-bawah ===== */
        @media (max-aspect-ratio: 1/1) {
            .container {
                grid-template-columns: 1fr;
                grid-template-rows: 42% 58%;
            }

            .laras-logo {
                top: -1vh;
                width: min(66%, 23vh);
            }

            .kids-image {
                height: 22vh;
            }

            .right-panel {
                padding: 3vh 6% 2vh;
            }

            .school-logo {
                max-height: 12vh;
            }

            .login-box {
                padding: 0;
            }
        }

        /* Layar sangat pendek (HP landscape): izinkan scroll */
        @media (max-height: 480px) {
            .container {
                height: auto;
                min-height: 100vh;
            }

            .left-panel {
                min-height: 100vh;
            }

            .right-panel {
                height: auto;
                min-height: 100vh;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- Panel kiri: logo LARAS + foto -->
        <section class="left-panel">

            <img
                src="{{ asset('images/Laras.png') }}"
                alt="LARAS - Layanan Adminsitrasi Rekam Akademik Sekolah"
                class="laras-logo"
            >

            <img
                src="{{ asset('images/anak.png') }}"
                alt=""
                class="kids-image"
            >

        </section>

        <!-- Panel kanan: logo sekolah + form login -->
        <section class="right-panel">

            <img
                src="{{ asset('images/LOGO SDIT.png') }}"
                alt="SDIT Nur Hidayah Surakarta"
                class="school-logo"
            >

            <div class="login-area">
                <div class="login-box">

                    <h2>Login</h2>

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

                        <div class="form-group">
                            <label for="email">Email Pengguna</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                autocomplete="username"
                                required
                                autofocus
                            >
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <button type="submit" class="login-btn">Masuk</button>
                    </form>

                </div>
            </div>

            <div class="footer">PUSKOMDATIN{{ date('Y') }}</div>

        </section>

    </div>

</body>

</html>