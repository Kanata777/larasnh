@extends('super-admin.laporan.layout')

@section('laporan')
<div class="lap-head">
    <div>
        <h2>{{ $judul }}</h2>
        <p class="lap-muted">{{ $tahunNama ? 'Tahun pelajaran ' . $tahunNama . ' · ' : '' }}{{ count($rows) }} baris</p>
    </div>
    <div class="lap-actions">
        <a class="lap-btn" href="{{ route('super-admin.laporan.index') }}">← Menu laporan</a>
        <a class="lap-btn" target="_blank" href="{{ request()->fullUrlWithQuery(['cetak' => 1, 'export' => null]) }}">Cetak / PDF</a>
        <a class="lap-btn lap-btn-primary" href="{{ request()->fullUrlWithQuery(['export' => 1, 'cetak' => null]) }}">Export Excel</a>
    </div>
</div>

@include('super-admin.laporan._filter')

@isset($ringkasan)
    <div class="lap-chips">
        @foreach ($ringkasan as $label => $nilai)
            <div class="lap-chip"><b>{{ $nilai }}</b><span>{{ $label }}</span></div>
        @endforeach
    </div>
@endisset

@isset($bar)
    <div class="lap-bar">
        @foreach ($bar as $b)
            <div class="lap-bar-row">
                <span>{{ $b['label'] }}</span>
                <div class="lap-bar-track"><div class="lap-bar-fill" style="width: {{ $b['pct'] }}%"></div></div>
                <strong>{{ $b['n'] }} ({{ $b['pct'] }}%)</strong>
            </div>
        @endforeach
    </div>
@endisset

<div class="lap-scroll">
    <table class="lap-table">
        <thead><tr>@foreach ($head as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="{{ ! empty($total) && $loop->last ? 'lap-total' : '' }}">
                    @foreach ($row as $c)<td>{{ $c }}</td>@endforeach
                </tr>
            @empty
                <tr><td class="lap-empty" colspan="{{ count($head) }}">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
