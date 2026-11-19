{{-- Pesan sukses / galat / error validasi. Hapus @include('bk.partials.pesan') jika layouts.app sudah menampilkannya. --}}
@if(session('ok'))
    <div role="status" style="background:#dcfce7;color:#166534;padding:10px 14px;border-radius:6px;margin-bottom:14px">{{ session('ok') }}</div>
@endif
@if(session('galat'))
    <div role="alert" style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:6px;margin-bottom:14px">{{ session('galat') }}</div>
@endif
@if($errors->any())
    <div role="alert" style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:6px;margin-bottom:14px">
        <strong>Data belum bisa disimpan:</strong>
        <ul style="margin:6px 0 0 18px">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif
