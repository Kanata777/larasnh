{{-- Modal Import Kelas dari Excel (2 langkah: unggah -> pratinjau) --}}
@php
    $imp = session('import_kelas');
    $jumlahBaru = $imp ? collect($imp['rows'])->where('status', 'baru')->count() : 0;
    $jumlahAda = $imp ? collect($imp['rows'])->where('status', 'ada')->count() : 0;
    $pesanModal = array_merge(
        $errors->get('file'),
        $errors->get('tahun_pelajaran_id'),
        $errors->get('rows')
    );
@endphp

<dialog id="modal-import-kelas" style="width:min(760px, calc(100% - 32px));">

    @if(! $imp)
        {{-- ============ Langkah 1: unggah ============ --}}
        <form method="POST" action="{{ route('super-admin.master-pendukung.kelas.import.preview') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-head">
                <h3>Import Kelas dari Excel</h3>
                <button type="button" class="modal-close" data-close aria-label="Tutup">&times;</button>
            </div>

            <div class="modal-body">
                @if($pesanModal)
                    <div class="alert alert-error" style="margin-bottom:16px;">
                        @foreach($pesanModal as $p)<div>{{ $p }}</div>@endforeach
                    </div>
                @endif

                <p class="muted" style="margin:0 0 16px;">
                    Unggah file <strong>Daftar Peserta Didik</strong> (Dapodik). Sistem membaca kolom
                    <strong>Rombel Saat Ini</strong> dan membuat satu kelas untuk tiap nama rombel,
                    misalnya "Kelas 1A". File lain juga bisa dipakai selama ada kolom <strong>Kelas</strong> atau <strong>Rombel</strong>.
                    Data siswa di file tidak diimpor.
                </p>

                <div class="field">
                    <label for="imp-tahun">Tahun pelajaran <span class="req">*</span></label>
                    <select id="imp-tahun" name="tahun_pelajaran_id" required>
                        <option value="">Pilih tahun pelajaran</option>
                        @foreach(($tahunPelajaran ?? []) as $t)
                            <option value="{{ $t->id }}" {{ (string) old('tahun_pelajaran_id', $tahunAktifId ?? '') === (string) $t->id ? 'selected' : '' }}>
                                {{ $t->nama }}{{ ($t->status ?? null) === 'aktif' ? ' (aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field" style="margin-bottom:0;">
                    <label for="imp-file">File Excel (.xlsx, .xls) atau CSV <span class="req">*</span></label>
                    <input type="file" id="imp-file" name="file" accept=".xlsx,.xls,.csv" required>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn btn-light" data-close>Batal</button>
                <button type="submit" class="btn btn-primary">Baca file</button>
            </div>
        </form>

    @else
        {{-- ============ Langkah 2: pratinjau ============ --}}
        <form method="POST" action="{{ route('super-admin.master-pendukung.kelas.import.process') }}">
            @csrf
            <div class="modal-head">
                <h3>Pratinjau Import Kelas</h3>
                <button type="button" class="modal-close" data-close aria-label="Tutup">&times;</button>
            </div>

            <div class="modal-body">
                @if($pesanModal)
                    <div class="alert alert-error" style="margin-bottom:16px;">
                        @foreach($pesanModal as $p)<div>{{ $p }}</div>@endforeach
                    </div>
                @endif

                <p class="muted" style="margin:0 0 14px;">
                    File <strong>{{ $imp['file'] }}</strong> &middot; Tahun pelajaran <strong>{{ $imp['tahun_nama'] }}</strong><br>
                    <span class="badge badge-green">{{ $jumlahBaru }} kelas baru</span>
                    @if($jumlahAda)<span class="badge badge-gray">{{ $jumlahAda }} sudah ada</span>@endif
                </p>

                <div class="table-wrap" style="max-height:340px;overflow-y:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:36px;">
                                    <input type="checkbox" id="imp-semua" aria-label="Pilih semua kelas" checked>
                                </th>
                                <th>Kelas / Rombel</th>
                                <th style="width:110px;">Tingkat</th>
                                <th>Siswa di file</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($imp['rows'] as $i => $row)
                                @php $bisa = $row['status'] === 'baru'; @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="imp-pilih" name="rows[{{ $i }}][pilih]" value="1"
                                               aria-label="Impor {{ $row['nama'] }}"
                                               {{ $bisa ? 'checked' : 'disabled' }}>
                                    </td>
                                    <td><strong>{{ $row['nama'] }}</strong></td>
                                    <td>
                                        <input type="number" name="rows[{{ $i }}][tingkat]" min="1" max="12"
                                               value="{{ old("rows.$i.tingkat", $row['tingkat']) }}"
                                               aria-label="Tingkat {{ $row['nama'] }}"
                                               {{ $bisa ? '' : 'disabled' }}>
                                    </td>
                                    <td>{{ $row['jumlah'] }}</td>
                                    <td>
                                        @if($row['status'] === 'baru')
                                            @if($row['tingkat'] === null)
                                                <span class="badge badge-orange">Isi tingkat</span>
                                            @else
                                                <span class="badge badge-green">Baru</span>
                                            @endif
                                        @elseif($row['status'] === 'ada')
                                            <span class="badge badge-gray">{{ $row['pesan'] }}</span>
                                        @else
                                            <span class="badge badge-red">{{ $row['pesan'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="muted" style="margin:12px 0 0;">
                    Tingkat diisi otomatis dari nama kelas. Periksa dan ubah jika kurang tepat.
                    Kapasitas dan wali kelas bisa diatur setelah diimpor.
                </p>
            </div>

            <div class="modal-foot">
                <button type="submit" class="btn btn-light"
                        formaction="{{ route('super-admin.master-pendukung.kelas.import.cancel') }}" formnovalidate>
                    Unggah ulang
                </button>
                <button type="submit" class="btn btn-primary" {{ $jumlahBaru ? '' : 'disabled' }}>Impor kelas terpilih</button>
            </div>
        </form>
    @endif
</dialog>

@push('scripts')
<script>
    (function () {
        const dialog = document.getElementById('modal-import-kelas');
        if (!dialog) return;

        document.querySelectorAll('[data-open-import-kelas]').forEach(btn => {
            btn.addEventListener('click', () => dialog.showModal());
        });

        dialog.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });

        const semua = document.getElementById('imp-semua');
        if (semua) {
            semua.addEventListener('change', () => {
                dialog.querySelectorAll('.imp-pilih:not(:disabled)').forEach(c => c.checked = semua.checked);
            });
        }

        @if(session('open_import_kelas'))
            dialog.showModal();
        @endif
    })();
</script>
@endpush