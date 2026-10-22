{{-- Pakai di halaman utama: @include('super-admin.master-pendukung.tabs.orang-tua') --}}

@php
    $fTahun = (int) request('tahun_id', 0);
    $fKelas = (int) request('kelas_id', 0);
    $adaFilter = request('q') || $fTahun || $fKelas;

    // pagination tetap kembali ke tab Orang Tua
    $orangTua->fragment('tab-orang-tua');
@endphp

<div class="tab-panel" id="tab-orang-tua">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Data Orang Tua</h3>
                <p>{{ number_format($orangTua->total(), 0, ',', '.') }} data orang tua ditemukan.</p>
            </div>
            <div class="btn-row">
                <button type="button" class="btn btn-primary" data-ot-open
                    data-title="Tambah Orang Tua"
                    data-action="{{ route('super-admin.master-pendukung.orang-tua.store') }}"
                    data-method="POST"
                    data-values="{{ json_encode(new stdClass) }}">
                    + Tambah Data
                </button>
            </div>
        </div>

        <form method="GET" action="{{ url()->current() }}#tab-orang-tua" class="ot-filter">
            <input type="search" name="q" value="{{ request('q') }}"
                   placeholder="Kata kunci pencarian" aria-label="Cari orang tua">

            <select name="tahun_id" id="ot-f-tahun" aria-label="Filter tahun pelajaran">
                <option value="">Semua tahun pelajaran</option>
                @foreach($tahunPelajaran as $tp)
                    <option value="{{ $tp->id }}" {{ $fTahun === (int) $tp->id ? 'selected' : '' }}>
                        {{ $tp->nama }}{{ $tp->status === 'aktif' ? ' (aktif)' : '' }}
                    </option>
                @endforeach
            </select>

            <select name="kelas_id" id="ot-f-kelas" aria-label="Filter kelas">
                <option value="">Semua kelas</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}"
                            data-tahun="{{ $k->tahun_pelajaran_id }}"
                            data-nama="{{ $k->nama_kelas }}"
                            data-tahun-nama="{{ $k->tahun_pelajaran_nama }}"
                            {{ $fKelas === (int) $k->id ? 'selected' : '' }}>
                        {{ $k->nama_kelas }}
                    </option>
                @endforeach
            </select>

            <div class="btn-row">
                <button type="submit" class="btn btn-primary">Cari</button>
                @if($adaFilter)
                    <a href="{{ url()->current() }}#tab-orang-tua" class="btn btn-light">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Ayah</th>
                        <th>Ibu</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orangTua as $i => $o)
                        <tr>
                            <td>{{ $orangTua->firstItem() + $i }}</td>
                            <td>
                                <strong>{{ $o->siswa_nama ?? ('ID: ' . $o->siswa_id) }}</strong><br>
                                <span class="muted">{{ $o->siswa_nis ?: '-' }}</span>
                            </td>
                            <td>{{ $o->kelas_nama ?: '-' }}</td>
                            <td>
                                <strong>{{ $o->nama_ayah ?: '-' }}</strong><br>
                                <span class="muted">NIK: {{ $o->nik_ayah ?: '-' }}</span><br>
                                <span class="muted">{{ collect([$o->pekerjaan_ayah, $o->penghasilan_ayah])->filter()->implode(' • ') ?: '-' }}</span>
                            </td>
                            <td>
                                <strong>{{ $o->nama_ibu ?: '-' }}</strong><br>
                                <span class="muted">NIK: {{ $o->nik_ibu ?: '-' }}</span><br>
                                <span class="muted">{{ collect([$o->pekerjaan_ibu, $o->penghasilan_ibu])->filter()->implode(' • ') ?: '-' }}</span>
                            </td>
                            <td class="actions">
                                <button type="button" class="btn btn-light btn-sm" data-ot-open
                                    data-title="Edit Orang Tua"
                                    data-action="{{ route('super-admin.master-pendukung.orang-tua.update', $o->id) }}"
                                    data-method="PUT"
                                    data-values="{{ json_encode($o->only([
                                        'siswa_id',
                                        'nama_ayah', 'nik_ayah', 'pekerjaan_ayah', 'penghasilan_ayah',
                                        'nama_ibu', 'nik_ibu', 'pekerjaan_ibu', 'penghasilan_ibu',
                                    ])) }}">
                                    Edit
                                </button>
                                <form class="inline-form" method="POST"
                                      action="{{ route('super-admin.master-pendukung.orang-tua.destroy', $o->id) }}"
                                      onsubmit="return confirm('Hapus data orang tua {{ addslashes($o->siswa_nama ?? '') }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                {{ $adaFilter
                                    ? 'Tidak ada data orang tua yang cocok dengan pencarian atau filter.'
                                    : 'Belum ada data orang tua. Gunakan "Tambah Data" untuk menambahkan.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orangTua->hasPages())
            <div class="pager">
                <span>Halaman {{ $orangTua->currentPage() }} dari {{ $orangTua->lastPage() }}</span>
                <span class="btn-row">
                    @if($orangTua->previousPageUrl())
                        <a class="btn btn-light btn-sm" href="{{ $orangTua->previousPageUrl() }}">&laquo; Sebelumnya</a>
                    @endif
                    @if($orangTua->nextPageUrl())
                        <a class="btn btn-light btn-sm" href="{{ $orangTua->nextPageUrl() }}">Berikutnya &raquo;</a>
                    @endif
                </span>
            </div>
        @endif
    </div>
</div>

{{-- ============ MODAL TAMBAH / EDIT ============ --}}
<div class="ot-modal" id="modal-orang-tua" hidden>
    <div class="ot-modal-box" role="dialog" aria-modal="true" aria-labelledby="ot-modal-title">
        <h3 id="ot-modal-title">Orang Tua</h3>

        <form method="POST" id="ot-form">
            @csrf
            <input type="hidden" name="_method" id="ot-method" value="POST">

            <div class="ot-field">
                <label for="ot-siswa">Siswa <span class="req">*</span></label>
                <select name="siswa_id" id="ot-siswa" required>
                    <option value="">-- Pilih siswa --</option>
                    @foreach($siswaList as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ot-grid">
                {{-- AYAH --}}
                <fieldset>
                    <legend>Data Ayah</legend>

                    <div class="ot-field">
                        <label>Nama Ayah</label>
                        <input type="text" name="nama_ayah" maxlength="150">
                    </div>
                    <div class="ot-field">
                        <label>NIK Ayah</label>
                        <input type="text" name="nik_ayah" maxlength="20" inputmode="numeric">
                    </div>
                    <div class="ot-field">
                        <label>Pekerjaan Ayah</label>
                        <select name="pekerjaan_ayah">
                            <option value="">-- Pilih --</option>
                            @foreach(($referensi['pekerjaan'] ?? []) as $r)
                                <option value="{{ $r->nama }}">{{ $r->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ot-field">
                        <label>Penghasilan Ayah</label>
                        <select name="penghasilan_ayah">
                            <option value="">-- Pilih --</option>
                            @foreach(($referensi['penghasilan'] ?? []) as $r)
                                <option value="{{ $r->nama }}">{{ $r->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>

                {{-- IBU --}}
                <fieldset>
                    <legend>Data Ibu</legend>

                    <div class="ot-field">
                        <label>Nama Ibu</label>
                        <input type="text" name="nama_ibu" maxlength="150">
                    </div>
                    <div class="ot-field">
                        <label>NIK Ibu</label>
                        <input type="text" name="nik_ibu" maxlength="20" inputmode="numeric">
                    </div>
                    <div class="ot-field">
                        <label>Pekerjaan Ibu</label>
                        <select name="pekerjaan_ibu">
                            <option value="">-- Pilih --</option>
                            @foreach(($referensi['pekerjaan'] ?? []) as $r)
                                <option value="{{ $r->nama }}">{{ $r->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ot-field">
                        <label>Penghasilan Ibu</label>
                        <select name="penghasilan_ibu">
                            <option value="">-- Pilih --</option>
                            @foreach(($referensi['penghasilan'] ?? []) as $r)
                                <option value="{{ $r->nama }}">{{ $r->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </fieldset>
            </div>

            <div class="ot-actions">
                <button type="button" class="btn btn-light" data-ot-close>Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Form filter: sama dengan halaman Daftar Siswa */
    .ot-filter {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 10px;
        margin-bottom: 18px;
    }

    /* Modal */
    .ot-modal {
        position: fixed; inset: 0; background: rgba(16, 24, 40, .55);
        display: flex; align-items: center; justify-content: center;
        z-index: 1000; padding: 16px;
    }
    .ot-modal[hidden] { display: none; }
    .ot-modal-box {
        background: #fff; border-radius: 12px; padding: 22px; width: 100%;
        max-width: 760px; max-height: 92vh; overflow-y: auto;
    }
    .ot-modal-box h3 { margin: 0 0 14px; }
    .ot-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 6px; }
    .ot-grid fieldset { border: 1px solid #e4e7ec; border-radius: 10px; padding: 12px 14px; margin: 0; }
    .ot-grid legend { font-weight: 600; padding: 0 6px; }
    .ot-field { margin-bottom: 10px; display: flex; flex-direction: column; gap: 4px; }
    .ot-field label { font-size: 13px; font-weight: 500; }
    .ot-field input, .ot-field select {
        padding: 8px 10px; border: 1px solid #d0d5dd; border-radius: 8px;
        width: 100%; box-sizing: border-box;
    }
    .ot-field .req { color: #d92d20; }
    .ot-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; }

    @media (max-width: 900px) {
        .ot-filter { grid-template-columns: 1fr; }
    }
    @media (max-width: 720px) {
        .ot-grid { grid-template-columns: 1fr; }
    }
</style>

<script>
    /* ---------- Modal tambah / edit ---------- */
    (function () {
        const modal = document.getElementById('modal-orang-tua');
        const form = document.getElementById('ot-form');
        const method = document.getElementById('ot-method');
        const title = document.getElementById('ot-modal-title');

        function setValue(name, value) {
            const el = form.querySelector('[name="' + name + '"]');
            if (!el) return;
            value = value == null ? '' : String(value);

            // Jika nilai belum ada di opsi dropdown (mis. referensi sudah dihapus), tambahkan agar tidak hilang.
            if (el.tagName === 'SELECT' && value !== '' &&
                !Array.from(el.options).some(o => o.value === value)) {
                el.add(new Option(value, value));
            }
            el.value = value;
        }

        function openModal(btn) {
            form.reset();

            title.textContent = btn.dataset.title || 'Orang Tua';
            form.action = btn.dataset.action;
            method.value = (btn.dataset.method || 'POST').toUpperCase();

            let values = {};
            try { values = JSON.parse(btn.dataset.values || '{}'); } catch (e) { }
            Object.keys(values).forEach(k => setValue(k, values[k]));

            modal.hidden = false;
            const first = form.querySelector('select, input[type="text"]');
            if (first) first.focus();
        }

        function closeModal() { modal.hidden = true; }

        document.addEventListener('click', function (e) {
            const openBtn = e.target.closest('[data-ot-open]');
            if (openBtn) { openModal(openBtn); return; }

            if (e.target.closest('[data-ot-close]') || e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hidden) closeModal();
        });
    })();

    /* ---------- Filter: kelas mengikuti tahun pelajaran ---------- */
    (function () {
        const tahun = document.getElementById('ot-f-tahun');
        const kelas = document.getElementById('ot-f-kelas');
        if (!tahun || !kelas) return;

        function saring(reset) {
            const t = tahun.value;
            Array.from(kelas.options).forEach(function (opt) {
                if (!opt.value) return; // "Semua kelas"
                const cocok = !t || opt.dataset.tahun === t;
                opt.hidden = !cocok;
                opt.disabled = !cocok;
                // jika semua tahun, tampilkan tahun di samping nama kelas
                opt.textContent = t ? opt.dataset.nama
                                    : opt.dataset.nama + ' (' + opt.dataset.tahunNama + ')';
            });
            if (reset) kelas.value = '';
        }

        tahun.addEventListener('change', function () { saring(true); });
        saring(false);
    })();

    /* ---------- Buka tab Orang Tua otomatis setelah Cari / pindah halaman ---------- */
    window.addEventListener('load', function () {
        const p = new URLSearchParams(location.search);
        const pakaiTab = location.hash === '#tab-orang-tua' ||
            ['q', 'cari_di', 'tahun_id', 'kelas_id', 'page'].some(function (k) { return p.has(k); });

        if (!pakaiTab) return;
        const tombol = document.querySelector('.tabs .tab[data-tab="orang-tua"]');
        if (tombol) tombol.click();
    });
</script>