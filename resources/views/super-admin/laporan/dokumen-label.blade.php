<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Label {{ $kelas->nama_kelas }}</title>
<style>
    @page { size: A4 portrait; margin: 10mm; }
    body { font-family: Arial, sans-serif; color: #000; margin: 0; }
    .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4mm; }
    .label { border: 1px dashed #666; border-radius: 4px; padding: 5mm; height: 24mm; box-sizing: border-box; page-break-inside: avoid; }
    .label small { font-size: 9px; color: #333; }
    .label b { display: block; font-size: 14px; margin: 3px 0; text-transform: uppercase; }
    .label span { font-size: 11px; }
</style>
</head>
<body onload="window.print()">
    <div class="grid">
        @foreach ($siswa as $s)
            <div class="label">
                <small>{{ $sekolah }}</small>
                <b>{{ $s->nama_lengkap }}</b>
                <span>NIS {{ $s->nis }} · Kelas {{ $kelas->nama_kelas }}</span>
            </div>
        @endforeach
    </div>
</body>
</html>
