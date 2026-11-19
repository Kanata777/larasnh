<div class="tab-panel active" id="tab-tahun-pelajaran">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Tahun Pelajaran</h3>
                <p>Hanya satu tahun pelajaran yang boleh aktif dalam satu waktu.</p>
            </div>
            <button type="button" class="btn btn-primary"
                    data-modal="modal-tahun"
                    data-title="Tambah Tahun Pelajaran"
                    data-action="{{ route('super-admin.master-pendukung.tahun-pelajaran.store') }}"
                    data-method="POST"
                    data-values="{{ json_encode(['nama' => '', 'tanggal_mulai' => '', 'tanggal_selesai' => '', 'keterangan' => '']) }}">
                + Tambah Tahun Pelajaran
            </button>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tahun Pelajaran</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tahunPelajaran as $i => $t)
                        @php $aktif = $t->status === 'aktif'; @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $t->nama }}</strong></td>
                            <td>{{ $t->tanggal_mulai ? \Carbon\Carbon::parse($t->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $t->tanggal_selesai ? \Carbon\Carbon::parse($t->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $t->keterangan ?: '-' }}</td>
                            <td>
                                <span class="badge {{ $aktif ? 'badge-green' : 'badge-gray' }}">
                                    {{ $aktif ? 'Aktif' : 'Tidak aktif' }}
                                </span>
                            </td>
                            <td class="actions">
                                @unless($aktif)
                                    <form class="inline-form" method="POST"
                                          action="{{ route('super-admin.master-pendukung.tahun-pelajaran.activate', $t->id) }}"
                                          onsubmit="return confirm('Aktifkan tahun pelajaran {{ $t->nama }}? Tahun pelajaran aktif lainnya akan dinonaktifkan.')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-light btn-sm">Aktifkan</button>
                                    </form>
                                @endunless
                                <button type="button" class="btn btn-light btn-sm"
                                        data-modal="modal-tahun"
                                        data-title="Edit Tahun Pelajaran"
                                        data-action="{{ route('super-admin.master-pendukung.tahun-pelajaran.update', $t->id) }}"
                                        data-method="PUT"
                                        data-values="{{ json_encode(['nama' => $t->nama, 'tanggal_mulai' => $t->tanggal_mulai, 'tanggal_selesai' => $t->tanggal_selesai, 'keterangan' => $t->keterangan]) }}">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Belum ada tahun pelajaran. Klik "Tambah Tahun Pelajaran" untuk memulai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>