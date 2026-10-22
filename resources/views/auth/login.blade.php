
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem Data Siswa</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f3ff, #f4f7fb);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #183153;
        }

        .container {
            width: 100%;
            max-width: 980px;
            min-height: 560px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: white;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 18px 55px rgba(26, 69, 120, .13);
        }

        .left-panel {
            background: linear-gradient(150deg, #0755a5, #0c82ce);
            color: white;
            padding: 45px 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 42px;
        }

        .brand-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, .17);
            font-size: 29px;
        }

        .brand h2 {
            font-size: 20px;
            line-height: 1.4;
        }

        .brand p {
            font-size: 12px;
            opacity: .8;
            margin-top: 4px;
        }

        .illustration {
            font-size: 95px;
            text-align: center;
            margin: 8px 0 24px;
        }

        .left-panel h1 {
            font-size: 28px;
            line-height: 1.35;
            margin-bottom: 14px;
        }

        .left-panel > p {
            font-size: 14px;
            line-height: 1.8;
            color: #e0efff;
        }

        .features {
            list-style: none;
            margin-top: 28px;
        }

        .features li {
            margin: 13px 0;
            font-size: 13px;
        }

        .right-panel {
            padding: 46px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right-panel h2 {
            font-size: 27px;
            margin-bottom: 9px;
            color: #16365c;
        }

        .subtitle {
            color: #7b8ca3;
            font-size: 13px;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 19px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
            color: #344a65;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #dbe4ef;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            background: #fbfdff;
            color: #243b53;
        }

        input:focus {
            border-color: #1675ce;
            box-shadow: 0 0 0 3px rgba(22, 117, 206, .1);
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap input {
            padding-right: 90px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #1265ae;
            cursor: pointer;
            font-size: 12px;
        }

        .login-btn {
            width: 100%;
            border: none;
            border-radius: 10px;
            padding: 14px;
            background: #0868ba;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 7px;
            transition: .2s;
        }

        .login-btn:hover {
            background: #064f91;
        }

        .alert {
            padding: 12px;
            border-radius: 9px;
            font-size: 13px;
            margin-bottom: 18px;
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
            font-size: 11px;
            color: #93a1b3;
            margin-top: 26px;
            line-height: 1.8;
        }

        @media (max-width: 700px) {
            body {
                padding: 14px;
            }

            .container {
                grid-template-columns: 1fr;
            }

            .left-panel {
                padding: 25px;
            }

            .brand {
                margin-bottom: 18px;
            }

            .illustration {
                font-size: 58px;
                margin: 5px 0 15px;
            }

            .left-panel h1 {
                font-size: 23px;
            }

            .features {
                margin-top: 12px;
            }

            .right-panel {
                padding: 30px 24px;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- Panel informasi -->
        <section class="left-panel">

            <div class="brand">
                <div class="brand-icon">🎓</div>

                <div>
                    <h2>SISTEM DATA SISWA</h2>
                    <p>Administrasi Sekolah Terintegrasi</p>
                </div>
            </div>

            <div class="illustration">🖥️</div>

            <h1>Selamat Datang di Sistem Informasi Sekolah</h1>

            <p>
                Kelola data siswa dengan lebih mudah, aman,
                dan terorganisir melalui satu sistem terpadu.
            </p>

            <ul class="features">
                <li>✓ Akses sesuai hak pengguna</li>
                <li>✓ Data tersimpan dalam database</li>
                <li>✓ Pengelolaan administrasi sekolah</li>
                <li>✓ Sistem login yang aman</li>
            </ul>

        </section>

        <!-- Panel login -->
        <section class="right-panel">

            <h2>Login Sistem</h2>

            <p class="subtitle">
                Silakan masuk menggunakan email dan password
                akun yang sudah terdaftar pada sistem.
            </p>

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

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Pengguna</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Masukkan email"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>

                    <div class="password-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword()"
                        >
                            Lihat
                        </button>
                    </div>
                </div>

                <!-- Tombol login -->
                <button type="submit" class="login-btn">
                    Masuk ke Sistem →
                </button>

            </form>

            <div class="footer">
                Sistem Informasi Data Siswa &copy; {{ date('Y') }}
                <br>
                Akses hanya untuk pengguna yang berwenang.
            </div>

        </section>

    </div>

    <script>
        // Menampilkan atau menyembunyikan password
        function togglePassword() {
            const password = document.getElementById('password');
            const button = document.querySelector('.toggle-password');

            if (password.type === 'password') {
                password.type = 'text';
                button.textContent = 'Sembunyikan';
            } else {
                password.type = 'password';
                button.textContent = 'Lihat';
            }
        }
    </script>

</body>
</html>
