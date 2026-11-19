{{--
Formulir laporan konseling (dipakai halaman buat-laporan dan ubah-laporan).
Variabel: $siswa, $laporan (null saat buat), $daftarKomponen, $daftarBidang,
$daftarKelas, $daftarSemester, $kelasBawaan, $semesterBawaan, $aksi, $metode
--}}
@php
    // Nilai awal: isian lama (jika validasi gagal) > data laporan (saat ubah) > nilai bawaan
    $isi = fn ($nama, $bawaan = null) => old($nama, $laporan?->getRawOriginal($nama) ?? $bawaan);
    $kelasTerpilih = $isi('kelas_id', $kelasBawaan);
    $kelasAktifSiswa = $siswa->riwayatAktif?->kelas?->nama_kelas ?? '-';
@endphp

<form method="POST" action="{{ $aksi }}">
    @csrf
    @if($metode !== 'POST') @method($metode) @endif

    <div class="panel">
        <div class="panel-head"><h3>Data siswa</h3></div>
        <dl>
            <dt>Nama</dt><dd><strong>{{ $siswa->nama_lengkap }}</strong></dd>
            <dt>NIS / NISN</dt><dd>{{ $siswa->nis ?: '-' }} / {{ $siswa->nisn ?: '-' }}</dd>
            <dt>Kelas saat ini</dt><dd>{{ $kelasAktifSiswa }}</dd>
        </dl>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Isi laporan</h3>
                <p>Semua kolom wajib diisi.</p>
            </div>
        </div>

        <div class="form-grid">
            <div class="field">
                <label for="komponen_layanan">Komponen layanan <span class="req">*</span></label>
                <select id="komponen_layanan" name="komponen_layanan" class="{{ $errors->has('komponen_layanan') ? 'invalid' : '' }}" required>
                    <option value="">Pilih komponen layanan</option>
                    @foreach($daftarKomponen as $k)
                        <option value="{{ $k->value }}" @selected($isi('komponen_layanan') === $k->value)>{{ $k->label() }}</option>
                    @endforeach
                </select>
                @error('komponen_layanan')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="bidang_id">Bidang layanan <span class="req">*</span></label>
                <select id="bidang_id" name="bidang_id" class="{{ $errors->has('bidang_id') ? 'invalid' : '' }}" required>
                    <option value="">Pilih bidang layanan</option>
                    @foreach($daftarBidang as $b)
                        <option value="{{ $b->id }}" @selected((string) $isi('bidang_id') === (string) $b->id)>{{ ucfirst($b->nama) }}</option>
                    @endforeach
                </select>
                @error('bidang_id')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="kelas_id">Kelas <span class="req">*</span></label>
                <select id="kelas_id" name="kelas_id" class="{{ $errors->has('kelas_id') ? 'invalid' : '' }}" required>
                    <option value="">Pilih kelas</option>
                    @foreach($daftarKelas as $kl)
                        <option value="{{ $kl->id }}" @selected((string) $kelasTerpilih === (string) $kl->id)>{{ $kl->nama_kelas }}</option>
                    @endforeach
                </select>
                <span class="muted">Hanya kelas yang sudah berisi siswa yang ditampilkan.</span>
                @error('kelas_id')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="semester">Semester <span class="req">*</span></label>
                <select id="semester" name="semester" class="{{ $errors->has('semester') ? 'invalid' : '' }}" required>
                    @foreach($daftarSemester as $s)
                        <option value="{{ $s->value }}" @selected($isi('semester', $semesterBawaan) === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
                @error('semester')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="tanggal">Hari/tanggal <span class="req">*</span></label>
                <input type="date" id="tanggal" name="tanggal" max="{{ now()->toDateString() }}"
                    value="{{ $isi('tanggal', now()->toDateString()) }}" class="{{ $errors->has('tanggal') ? 'invalid' : '' }}" required>
                <span class="muted" id="nama-hari"></span>
                @error('tanggal')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="durasi_menit">Durasi pertemuan (menit) <span class="req">*</span></label>
                <input type="number" id="durasi_menit" name="durasi_menit" min="1" max="480" placeholder="Contoh: 45"
                    value="{{ $isi('durasi_menit') }}" class="{{ $errors->has('durasi_menit') ? 'invalid' : '' }}" required>
                @error('durasi_menit')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field full">
                <label for="topik">Topik layanan <span class="req">*</span></label>
                <input type="text" id="topik" name="topik" maxlength="200" value="{{ $isi('topik') }}"
                    class="{{ $errors->has('topik') ? 'invalid' : '' }}" required>
                @error('topik')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field full">
                <label for="tujuan">Tujuan layanan <span class="req">*</span></label>
                <textarea id="tujuan" name="tujuan" class="{{ $errors->has('tujuan') ? 'invalid' : '' }}" required>{{ $isi('tujuan') }}</textarea>
                @error('tujuan')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field full">
                <label for="materi">Materi <span class="req">*</span></label>
                <textarea id="materi" name="materi" rows="4" class="{{ $errors->has('materi') ? 'invalid' : '' }}" required>{{ $isi('materi') }}</textarea>
                @error('materi')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field full">
                <label for="tindak_lanjut">Tindak lanjut <span class="req">*</span></label>
                <textarea id="tindak_lanjut" name="tindak_lanjut" class="{{ $errors->has('tindak_lanjut') ? 'invalid' : '' }}" required>{{ $isi('tindak_lanjut') }}</textarea>
                @error('tindak_lanjut')<span class="err">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn btn-primary">Simpan laporan</button>
            <a class="btn btn-light" href="{{ route('bk.laporan-konseling.index') }}">Batal</a>
        </div>
    </div>
</form>

@push('scripts')
<script>
    // Menampilkan nama hari di bawah kolom tanggal
    (function () {
        const tanggal = document.getElementById('tanggal');
        const label = document.getElementById('nama-hari');
        function tampil() {
            if (!tanggal.value) { label.textContent = ''; return; }
            const d = new Date(tanggal.value + 'T00:00:00');
            label.textContent = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        }
        tanggal.addEventListener('input', tampil);
        tampil();
    })();
</script>
@endpush
