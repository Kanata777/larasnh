@extends('super-admin.layout')

@section('title', 'Import Excel')
@section('subtitle', 'Unggah data siswa dari file Excel, cocokkan kolomnya, lalu simpan')

@section('content')

    @php
        $report = session('report');
        $fields = \App\Support\SiswaData::IMPORT;
    @endphp

    @if($step === 'upload')

        {{-- ===== Hasil import terakhir ===== --}}
        @if($report)
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3>Hasil Import</h3>
                        <p>
                            <span class="badge badge-green">{{ $report['imported'] }} berhasil</span>
                            <span class="badge {{ $report['skipped'] ? 'badge-red' : 'badge-gray' }}">{{ $report['skipped'] }}
                                dilewati</span>
                        </p>
                    </div>
                    <a href="{{ route('super-admin.siswa.index') }}" class="btn btn-primary">Lihat daftar siswa</a>
                </div>

                @if($report['skipped'])
                    <p class="muted" style="margin-bottom:12px;">
                        Baris berikut tidak disimpan. Perbaiki di file Excel, lalu unggah ulang hanya baris yang bermasalah.
                    </p>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Baris</th>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th>Masalah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report['errors'] as $e)
                                    <tr>
                                        <td>{{ $e['baris'] }}</td>
                                        <td>{{ $e['nis'] }}</td>
                                        <td>{{ $e['nama'] }}</td>
                                        <td>
                                            @foreach($e['pesan'] as $p)
                                                <div style="color:#b91c1c;">{{ $p }}</div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($report['hidden'] > 0)
                        <p class="muted" style="margin-top:12px;">Dan {{ $report['hidden'] }} baris bermasalah lainnya tidak ditampilkan.
                        </p>
                    @endif
                @endif
            </div>
        @endif

        {{-- ===== Form unggah ===== --}}
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3>1. Unggah file</h3>
                    <p>Baris pertama berisi judul kolom, baris berikutnya data siswa. Maksimal 3.000 baris dan 5 MB.</p>
                </div>
                <a href="{{ route('super-admin.siswa.import.template') }}" class="btn btn-light">⬇ Unduh template</a>
            </div>

            <form method="POST" action="{{ route('super-admin.siswa.import.preview') }}" enctype="multipart/form-data">
                @csrf
                <div class="field">
                    <label for="file">File Excel (.xlsx, .xls) atau CSV</label>
                    <input type="file" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                </div>
                <button type="submit" class="btn btn-primary">Lanjut ke pemetaan kolom</button>
            </form>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>Pemeriksaan otomatis</h3>
            </div>
            <p class="muted" style="margin:0;">Setiap baris diperiksa sebelum disimpan. Baris yang bermasalah dilewati dan
                dilaporkan, baris lainnya tetap masuk.</p>
            <ul class="muted" style="margin:10px 0 0 18px;">
                <li>NIS dan NISN harus unik, baik terhadap data yang sudah ada maupun di dalam file.</li>
                <li>NIS, nama lengkap, dan jenis kelamin wajib terisi. NISN harus 10 digit.</li>
                <li>Tanggal lahir dan tanggal masuk harus tanggal yang valid (contoh 2010-05-17 atau 17/05/2010).</li>
                <li>Nomor telepon dan HP harus valid. Angka 0 di depan yang hilang dari Excel dipulihkan otomatis.</li>
                <li>Sel yang berisi "-", "0", atau kosong dianggap tidak diisi.</li>
                <li>Nama kelas harus sama dengan kelas pada tahun pelajaran yang aktif.</li>
            </ul>
        </div>

    @else

        {{-- ===== Pemetaan kolom ===== --}}
        <form method="POST" action="{{ route('super-admin.siswa.import.process') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="header_row" value="{{ $headerRow }}">

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3>2. Cocokkan kolom</h3>
                        <p>{{ number_format($totalRows, 0, ',', '.') }} baris data ditemukan. Pilih kolom file untuk tiap isian
                            siswa. Kolom bertanda <span class="req">*</span> wajib dipetakan.</p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Isian siswa</th>
                                <th>Kolom di file</th>
                                <th>Contoh isi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fields as $field => [$label, $wajib])
                                @php $dipilih = old("map.$field", $mapping[$field]); @endphp
                                <tr>
                                    <td>{{ $label }}@if($wajib) <span class="req">*</span>@endif</td>
                                    <td style="min-width:220px;">
                                        <select name="map[{{ $field }}]" data-field="{{ $field }}"
                                            aria-label="Kolom untuk {{ $label }}">
                                            <option value="">{{ $wajib ? 'Pilih kolom' : 'Tidak diimpor' }}</option>
                                            @foreach($headers as $i => $h)
                                                <option value="{{ $i }}" {{ (string) $dipilih === (string) $i ? 'selected' : '' }}>
                                                    {{ $h !== '' ? $h : 'Kolom ' . ($i + 1) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="muted contoh" data-field="{{ $field }}">-</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h3>Pratinjau file</h3>
                        <p>5 baris pertama.</p>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>@foreach($headers as $h)
                            <th>{{ $h }}</th>@endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sample as $row)
                                <tr>@foreach($headers as $i => $h)
                                <td>{{ $row[$i] ?? '' }}</td>@endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn btn-primary">Validasi &amp; simpan {{ number_format($totalRows, 0, ',', '.') }}
                    baris</button>
                <a href="{{ route('super-admin.siswa.import') }}" class="btn btn-light">Batal</a>
            </div>
        </form>

        @push('scripts')
            <script>
                const sample = @json(array_values($sample));

                function perbarui(select) {
                    const cell = document.querySelector('td.contoh[data-field="' + select.dataset.field + '"]');
                    if (select.value === '') { cell.textContent = '-'; return; }
                    const nilai = sample.map(r => r[select.value]).filter(v => v !== null && v !== '').slice(0, 2);
                    cell.textContent = nilai.length ? nilai.join(', ') : '(kosong)';
                }

                document.querySelectorAll('select[data-field]').forEach(s => {
                    perbarui(s);
                    s.addEventListener('change', () => perbarui(s));
                });
            </script>
        @endpush

    @endif

@endsection