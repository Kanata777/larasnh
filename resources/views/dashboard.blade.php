
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Data Siswa</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f0f5fb;
            color: #183153;
        }

        .navbar {
            background: #0755a5;
            colofr: white;
            padding: 20px 6%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .navbar h2 {
            font-size: 18px;
            margin: 0;
        }

        .logout-btn {
            border: 1px solid white;
            background: transparent;
            color: white;
            padding: 9px 15px;
            border-radius: 8px;
            cursor: pointer;
        }

        .content {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 25px #1831530d;
        }

        .welcome h1 {
            font-size: 26px;
        }

        .role {
            display: inline-block;
            background: #e2efff;
            color: #0755a5;
            padding: 8px 13px;
            border-radius: 20px;
            font-size: 13px;
            margin-top: 8px;
        }
    </style>
</head>
<body>

<header class="navbar">
    <h2>🎓 Sistem Informasi Data Siswa</h2>

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</header>

<main class="content">
    <section class="welcome">
        <h1>Login Berhasil!</h1>

        <p>
            Selamat datang,
            <strong>{{ auth()->user()->name }}</strong>.
        </p>

        <p>
            Kamu berhasil masuk ke dalam sistem.
            Halaman ini merupakan halaman sementara
            untuk tahap Akses Sistem.
        </p>

        <span class="role">
            Hak akses: {{ auth()->user()->role }}
        </span>
    </section>
</main>

</body>
</html>
