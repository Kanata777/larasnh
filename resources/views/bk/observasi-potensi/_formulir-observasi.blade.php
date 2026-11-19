{{--
Formulir catatan observasi (dipakai halaman buat-observasi dan ubah-observasi).
Variabel: $siswa, $observasi (null saat buat), $daftarKategori, $daftarTingkat,
$pengamatBawaan, $aksi, $metode
--}}
@php
    // Nilai awal: isian lama (jika validasi gagal) > data catatan (saat ubah) > nilai bawaan
    $isi = fn ($nama, $bawaan = null) => old($nama, $observasi?->getRawOriginal($nama) ?? $bawaan);
@endphp

<form method="POST" action="{{ $aksi }}">
    @csrf
    @if($metode !== 'POST') @method($metode) @endif

    <div class="panel">
        <div class="panel-head"><h3>Data siswa</h3></div>
        <dl>
            <dt>Nama</dt><dd><strong>{{ $siswa->nama_lengkap }}</strong></dd>
            <dt>Kelas saat ini</dt><dd>{{ $siswa->riwayatAktif?->kelas?->nama_kelas ?? '-' }}</dd>
        </dl>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Catatan observasi</h3>
                <p>Tulis apa yang benar-benar terlihat (perilaku, hasil karya, ucapan), bukan label atau dugaan.</p>
            </div>
        </div>

        <div class="form-grid">
            <div class="field">
                <label for="tanggal">Tanggal pengamatan <span class="req">*</span></label>
                <input type="date" id="tanggal" name="tanggal" max="{{ now()->toDateString() }}"
                    value="{{ $isi('tanggal', now()->toDateString()) }}" class="{{ $errors->has('tanggal') ? 'invalid' : '' }}" required>
                @error('tanggal')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="pengamat">Pengamat <span class="req">*</span></label>
                <input type="text" id="pengamat" name="pengamat" maxlength="150" value="{{ $isi('pengamat', $pengamatBawaan) }}"
                    class="{{ $errors->has('pengamat') ? 'invalid' : '' }}" required>
                <span class="muted">Nama guru yang mengamati.</span>
                @error('pengamat')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="kategori">Bidang potensi <span class="req">*</span></label>
                <select id="kategori" name="kategori" class="{{ $errors->has('kategori') ? 'invalid' : '' }}" required>
                    <option value="">Pilih bidang</option>
                    @foreach($daftarKategori as $k)
                        <option value="{{ $k->value }}" @selected($isi('kategori') === $k->value)>{{ $k->ikon() }} {{ $k->label() }}</option>
                    @endforeach
                </select>
                @error('kategori')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="tingkat_potensi">Tingkat potensi <span class="req">*</span></label>
                <select id="tingkat_potensi" name="tingkat_potensi" class="{{ $errors->has('tingkat_potensi') ? 'invalid' : '' }}" required>
                    <option value="">Pilih tingkat</option>
                    @foreach($daftarTingkat as $t)
                        <option value="{{ $t->value }}" @selected($isi('tingkat_potensi') === $t->value)>{{ $t->label() }}</option>
                    @endforeach
                </select>
                @error('tingkat_potensi')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field full">
                <label for="bakat_minat">Bakat / minat yang terlihat</label>
                <input type="text" id="bakat_minat" name="bakat_minat" maxlength="150" placeholder="Contoh: menggambar, bermain piano"
                    value="{{ $isi('bakat_minat') }}" class="{{ $errors->has('bakat_minat') ? 'invalid' : '' }}">
                <span class="muted">Pisahkan dengan koma jika lebih dari satu.</span>
                @error('bakat_minat')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field full">
                <label for="temuan">Hasil pengamatan <span class="req">*</span></label>
                <textarea id="temuan" name="temuan" rows="4" class="{{ $errors->has('temuan') ? 'invalid' : '' }}" required>{{ $isi('temuan') }}</textarea>
                @error('temuan')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field full">
                <label for="rekomendasi">Rekomendasi pengembangan</label>
                <textarea id="rekomendasi" name="rekomendasi" class="{{ $errors->has('rekomendasi') ? 'invalid' : '' }}">{{ $isi('rekomendasi') }}</textarea>
                @error('rekomendasi')<span class="err">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn btn-primary">Simpan catatan</button>
            <a class="btn btn-light" href="{{ route('bk.observasi-potensi.profil', $siswa->id) }}">Batal</a>
        </div>
    </div>
</form>
