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
                    @forelse($kelas as $i => $k)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $k->nama_kelas }}</strong></td>
                            <td>{{ $k->tingkat }}</td>
                            <td>{{ $k->tahun_pelajaran_nama ?? '-' }}</td>
                            <td>
                                @php $waliKelasIni = $waliPerKelas->get($k->id, collect()); @endphp

                                @forelse($waliKelasIni as $w)
                                    <span class="chip">{{ $w->nama }}</span>
                                @empty
                                    <span class="text-muted">Belum diatur</span>
                                @endforelse
                            </td>
                            <td>{{ $k->jumlah_siswa ?? 0 }} / {{ $k->kapasitas ?? '-' }}</td>
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
                                        data-wali='@json($waliKelasIni->pluck('id')->values())'>
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