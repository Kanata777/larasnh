@php
    $adaFilter = collect(['q', 'kelas_id', 'jk', 'status'])->contains(fn ($k) => filled(request($k)));
@endphp

<form method="GET" action="{{ url()->current() }}" class="lap-filter">
    @if (in_array('q', $fields))
        <input type="search" name="q" value="{{ request('q') }}"
               placeholder="Cari nama, NIS, atau NISN" aria-label="Cari siswa">
    @endif

    @if (in_array('tahun', $fields))
        <select name="tahun_id" id="lap-f-tahun" aria-label="Filter tahun pelajaran">
            @foreach ($tahunOptions as $t)
                <option value="{{ $t->id }}" @selected($tahunId == $t->id)>
                    {{ $t->nama }}{{ ($t->status ?? '') === 'aktif' ? ' (aktif)' : '' }}
                </option>
            @endforeach
        </select>
    @endif

    @if (in_array('kelas', $fields))
        <select name="kelas_id" id="lap-f-kelas" aria-label="Filter kelas">
            <option value="">Semua kelas</option>
            @foreach ($kelasOptions as $k)
                <option value="{{ $k->id }}"
                        data-tahun="{{ $k->tahun_pelajaran_id ?? '' }}"
                        @selected(request('kelas_id') == $k->id)>
                    {{ $k->nama_kelas }}
                </option>
            @endforeach
        </select>
    @endif

    @if (in_array('jk', $fields))
        <select name="jk" aria-label="Filter jenis kelamin">
            <option value="">Semua jenis kelamin</option>
            <option value="L" @selected(request('jk') === 'L')>Laki-laki</option>
            <option value="P" @selected(request('jk') === 'P')>Perempuan</option>
        </select>
    @endif

    @if (in_array('status', $fields))
        <select name="status" aria-label="Filter status siswa">
            <option value="">Semua status</option>
            @foreach ($statusOptions as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
            @endforeach
        </select>
    @endif

    <div class="btn-row">
        <button type="submit" class="btn btn-primary">Cari</button>
        @if ($adaFilter)
            <a href="{{ url()->current() }}" class="btn btn-light">Reset</a>
        @endif
    </div>
</form>

<style>
    /* Sama dengan filter di Daftar Siswa; jumlah kolom menyesuaikan filter yang aktif */
    .lap-filter {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 10px;
        margin-bottom: 18px;
        align-items: center;
    }
    .lap-filter input[type="search"] { grid-column: span 2; }
    @media (max-width: 900px) {
        .lap-filter { grid-template-columns: 1fr; }
        .lap-filter input[type="search"] { grid-column: auto; }
    }
</style>

<script>
    /* Kelas mengikuti tahun pelajaran (hanya jika opsi kelas punya data-tahun) */
    (function () {
        const tahun = document.getElementById('lap-f-tahun');
        const kelas = document.getElementById('lap-f-kelas');
        if (!tahun || !kelas) return;

        function saring(reset) {
            const t = tahun.value;
            Array.from(kelas.options).forEach(function (opt) {
                if (!opt.value || !opt.dataset.tahun) return;
                const cocok = !t || opt.dataset.tahun === t;
                opt.hidden = !cocok;
                opt.disabled = !cocok;
            });
            if (reset) kelas.value = '';
        }

        tahun.addEventListener('change', function () { saring(true); });
        saring(false);
    })();
</script>