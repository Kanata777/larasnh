@php
    $badgeStatus = [
        'Aktif' => 'badge-green',
        'Pindah' => 'badge-blue',
        'Lulus' => 'badge-orange',
        'Nonaktif' => 'badge-gray',
    ];
@endphp

<div class="tab-panel" id="tab-status-siswa">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Status Siswa</h3>
                <p>Daftar status yang dipakai pada data siswa. Nilainya tetap dan dipilih saat mengisi atau mengubah data siswa.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Jumlah Siswa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statusSiswa as $i => $st)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><span class="badge {{ $badgeStatus[$st->nama] ?? 'badge-gray' }}">{{ $st->nama }}</span></td>
                            <td>{{ $st->keterangan }}</td>
                            <td>{{ $st->jumlah_siswa }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>