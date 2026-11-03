@php
    // Kelas penampung siswa yang belum punya kelas tidak ditampilkan di tabel
    $kelasTampil = $kelas
        ->reject(fn ($k) => strcasecmp(trim($k->nama_kelas), 'Belum ditempatkan') === 0)
        ->values();

    // Inisial untuk avatar, mis. "Agus Prasetyo, M.Pd" -> "AP"
    $inisial = fn ($nama) => \Illuminate\Support\Str::of($nama)
        ->before(',')
        ->trim()
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
        ->implode('');
@endphp

<div class="tab-panel" id="tab-kelas">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Kelas / Rombel</h3>
                <p>Atur kelas, tingkat, kapasitas, tahun pelajaran, dan wali kelas.</p>
            </div>

            <div class="btn-row">
                <button type="button" class="btn btn-light" data-open-import-kelas>
                    📥 Import Excel
                </button>

                <button type="button" class="btn btn-primary" data-modal="modal-kelas"
                    data-title="Tambah Kelas / Rombel"
                    data-action="{{ route('super-admin.master-pendukung.kelas.store') }}" data-method="POST"
                    data-values="{{ json_encode(['nama_kelas' => '', 'tingkat' => '', 'tahun_pelajaran_id' => $tahunAktifId ?? '', 'kapasitas' => '', 'wali_kelas_id' => '', 'status' => 'aktif']) }}">
                    + Tambah Kelas
                </button>
            </div>
        </div>

        {{-- Keterangan warna wali kelas --}}
        <div class="wali-legend">
            <span class="wali-legend-item"><i class="dot dot-utama"></i> Wali kelas utama</span>
            <span class="wali-legend-item"><i class="dot dot-wakil"></i> Wakil wali kelas</span>
            <span class="wali-legend-item"><i class="dot dot-pengganti"></i> Menggantikan sementara</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kelas / Rombel</th>
                        <th>Tingkat</th>
                        <th>Tahun Pelajaran</th>
                        <th>Wali Kelas</th>
                        <th>Siswa / Kapasitas</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelasTampil as $i => $k)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $k->nama_kelas }}</strong></td>
                            <td><span class="tingkat-pill">{{ $k->tingkat }}</span></td>
                            <td>{{ $k->tahun_pelajaran_nama ?? '-' }}</td>

                            {{-- ===== Wali kelas ===== --}}
                            <td>
                                @php
                                    $waliKelasIni  = $waliPerKelas->get($k->id, collect());
                                    $waliUtama     = $waliKelasIni->firstWhere('peran', 'utama');
                                    $waliWakil     = $waliKelasIni->where('peran', 'asisten')->values();
                                    $waliPengganti = $waliWakil->first(fn ($w) => (bool) ($w->menggantikan ?? false));
                                @endphp

                                <div class="wali-list">
                                    @if($waliUtama)
                                        <div class="wali wali--utama {{ $waliPengganti ? 'is-berhalangan' : '' }}">
                                            <span class="wali-avatar">{{ $inisial($waliUtama->nama) }}</span>
                                            <span class="wali-info">
                                                <span class="wali-nama">{{ $waliUtama->nama }}</span>
                                                <span class="wali-peran">
                                                    {{ $waliPengganti ? 'Wali Kelas Utama · Berhalangan' : 'Wali Kelas Utama' }}
                                                </span>
                                            </span>
                                        </div>
                                    @endif

                                    @foreach($waliWakil as $w)
                                        @if($waliPengganti && (int) $w->id === (int) $waliPengganti->id)
                                            <div class="wali wali--pengganti">
                                                <span class="wali-avatar">{{ $inisial($w->nama) }}</span>
                                                <span class="wali-info">
                                                    <span class="wali-nama">{{ $w->nama }}</span>
                                                    <span class="wali-peran">⇄ Menggantikan sementara</span>
                                                </span>
                                            </div>
                                        @else
                                            <div class="wali wali--wakil">
                                                <span class="wali-avatar">{{ $inisial($w->nama) }}</span>
                                                <span class="wali-info">
                                                    <span class="wali-nama">{{ $w->nama }}</span>
                                                    <span class="wali-peran">Wakil Wali Kelas</span>
                                                </span>
                                            </div>
                                        @endif
                                    @endforeach

                                    @if(!$waliUtama && $waliWakil->isEmpty())
                                        <span class="wali-kosong">Belum diatur</span>
                                    @endif
                                </div>
                            </td>

                            {{-- ===== Siswa / kapasitas ===== --}}
                            <td>
                                @php
                                    $jml    = (int) ($k->jumlah_siswa ?? 0);
                                    $kap    = (int) ($k->kapasitas ?? 0);
                                    $persen = $kap > 0 ? min(100, (int) round($jml / $kap * 100)) : 0;
                                    $level  = $kap > 0 && $jml >= $kap ? 'is-penuh' : ($persen >= 85 ? 'is-hampir' : '');
                                @endphp
                                <div class="kap">
                                    <div class="kap-text"><strong>{{ $jml }}</strong> / {{ $k->kapasitas ?? '-' }}</div>
                                    @if($kap > 0)
                                        <div class="kap-bar"><span class="kap-fill {{ $level }}" style="width: {{ $persen }}%"></span></div>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <span
                                    class="badge {{ $k->status === 'aktif' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($k->status) }}</span>
                            </td>
                            <td class="actions">
                                @php
                                    $prefixRoute = \Illuminate\Support\Str::before(\Illuminate\Support\Facades\Route::currentRouteName(), 'index');
                                    $routeWali = $prefixRoute . 'kelas.wali';
                                @endphp

                                @if(\Illuminate\Support\Facades\Route::has($routeWali))
                                    <button type="button" class="btn btn-sm btn-primary" data-open-wali
                                        data-action="{{ route($routeWali, $k->id) }}" data-kelas="{{ $k->nama_kelas }}"
                                        data-utama="{{ $waliUtama->id ?? '' }}"
                                        data-asisten="{{ json_encode($waliWakil->pluck('id')->values()) }}"
                                        data-pengganti="{{ $waliPengganti->id ?? '' }}">
                                        Atur Wali
                                    </button>
                                @endif
                                <button type="button" class="btn btn-light btn-sm" data-modal="modal-kelas"
                                    data-title="Edit Kelas / Rombel"
                                    data-action="{{ route('super-admin.master-pendukung.kelas.update', $k->id) }}"
                                    data-method="PUT"
                                    data-values="{{ json_encode(['nama_kelas' => $k->nama_kelas, 'tingkat' => $k->tingkat, 'tahun_pelajaran_id' => $k->tahun_pelajaran_id, 'kapasitas' => $k->kapasitas ?? '', 'wali_kelas_id' => $k->wali_kelas_id ?? '', 'status' => $k->status]) }}">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty">Belum ada kelas. Klik "Tambah Kelas" atau "Import Excel" untuk
                                memulai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* ===== Umum tabel kelas ===== */
    #tab-kelas tbody td { vertical-align: middle; }
    #tab-kelas tbody tr:hover { background: #f9fafb; }
    #tab-kelas td.actions { white-space: nowrap; }

    /* pil tingkat */
    #tab-kelas .tingkat-pill {
        display: inline-grid; place-items: center;
        min-width: 28px; height: 28px; padding: 0 .4rem;
        font-size: .85rem; font-weight: 600;
        color: #374151; background: #f3f4f6; border-radius: 8px;
    }

    /* ===== Keterangan warna ===== */
    #tab-kelas .wali-legend {
        display: flex; flex-wrap: wrap; gap: .4rem 1.1rem;
        margin: 0 0 .9rem; font-size: .8rem; color: #6b7280;
    }
    #tab-kelas .wali-legend-item { display: inline-flex; align-items: center; gap: .4rem; }
    #tab-kelas .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    #tab-kelas .dot-utama    { background: #1a56db; }
    #tab-kelas .dot-wakil    { background: #fbbf24; }
    #tab-kelas .dot-pengganti { background: #ea580c; }

    /* ===== Kartu wali kelas ===== */
    #tab-kelas .wali-list { display: flex; flex-flow: row wrap; align-items: center; gap: .3rem .4rem; }

    #tab-kelas .wali {
        display: flex; align-items: center; gap: .4rem;
        width: fit-content; max-width: 100%;
        padding: .18rem .6rem .18rem .2rem;
        border: 1px solid transparent; border-radius: 999px;
    }
    #tab-kelas .wali-avatar {
        flex: 0 0 auto; display: grid; place-items: center;
        width: 22px; height: 22px; border-radius: 50%;
        font-size: .62rem; font-weight: 700; letter-spacing: .02em;
    }
    #tab-kelas .wali-info { display: flex; flex-direction: column; min-width: 0; line-height: 1.15; white-space: nowrap; }
    #tab-kelas .wali-nama { font-size: .78rem; font-weight: 600; }
    #tab-kelas .wali-peran { font-size: .62rem; margin-top: 1px; }

    /* utama: biru */
    #tab-kelas .wali--utama { background: #eff6ff; border-color: #bfdbfe; }
    #tab-kelas .wali--utama .wali-avatar { background: #1a56db; color: #fff; }
    #tab-kelas .wali--utama .wali-nama   { color: #1e3a8a; }
    #tab-kelas .wali--utama .wali-peran  { color: #3b5fc0; }

    /* utama yang sedang digantikan: dipucatkan, garis putus-putus */
    #tab-kelas .wali--utama.is-berhalangan { background: #f8fafc; border: 1px dashed #93b4f0; }
    #tab-kelas .wali--utama.is-berhalangan .wali-avatar { background: #93b4f0; }
    #tab-kelas .wali--utama.is-berhalangan .wali-nama   { color: #64748b; }
    #tab-kelas .wali--utama.is-berhalangan .wali-peran  { color: #94a3b8; }

    /* wakil: kuning */
    #tab-kelas .wali--wakil { background: #fffbeb; border-color: #fde68a; }
    #tab-kelas .wali--wakil .wali-avatar { background: #fbbf24; color: #78350f; }
    #tab-kelas .wali--wakil .wali-nama   { color: #78350f; }
    #tab-kelas .wali--wakil .wali-peran  { color: #a16207; }

    /* pengganti sementara: oranye */
    #tab-kelas .wali--pengganti {
        background: #fff7ed; border-color: #fb923c;
        box-shadow: 0 1px 4px rgba(234, 88, 12, .18);
    }
    #tab-kelas .wali--pengganti .wali-avatar { background: #ea580c; color: #fff; }
    #tab-kelas .wali--pengganti .wali-nama   { color: #7c2d12; }
    #tab-kelas .wali--pengganti .wali-peran  { color: #c2410c; font-weight: 600; }

    /* belum ada wali */
    #tab-kelas .wali-kosong {
        display: inline-block; padding: .2rem .65rem;
        font-size: .76rem; color: #9ca3af;
        border: 1px dashed #d1d5db; border-radius: 999px;
    }

    /* ===== Kapasitas ===== */
    #tab-kelas .kap { min-width: 96px; }
    #tab-kelas .kap-text { font-size: .88rem; color: #6b7280; }
    #tab-kelas .kap-text strong { color: #111827; }
    #tab-kelas .kap-bar {
        height: 5px; margin-top: .3rem; overflow: hidden;
        background: #e5e7eb; border-radius: 999px;
    }
    #tab-kelas .kap-fill { display: block; height: 100%; background: #10b981; border-radius: 999px; }
    #tab-kelas .kap-fill.is-hampir { background: #f59e0b; }
    #tab-kelas .kap-fill.is-penuh  { background: #ef4444; }
</style>