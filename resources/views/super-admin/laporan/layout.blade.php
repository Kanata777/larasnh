{{-- SESUAIKAN: ganti 'layouts.super-admin' dan nama section 'content' dengan yang dipakai
     di resources/views/super-admin/siswa/index.blade.php. Hanya file ini yang perlu disesuaikan. --}}
@extends('super-admin.layout')

@section('content')
<style>
    .lap-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:16px}
    .lap-head h2{margin:0 0 4px;font-size:22px}
    .lap-muted{color:#64748b;font-size:14px;margin:0}
    .lap-actions{display:flex;gap:8px;flex-wrap:wrap}
    .lap-btn{display:inline-block;padding:9px 16px;border-radius:8px;border:1px solid #cbd5e1;background:#f1f5f9;color:#0f172a;font-size:14px;font-weight:600;text-decoration:none;cursor:pointer}
    .lap-btn-primary{background:#1a73e8;border-color:#1a73e8;color:#fff}
    .lap-filter{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
    .lap-filter input,.lap-filter select,.lap-card input,.lap-card select{padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;background:#fff}
    .lap-filter input[name=q]{flex:1;min-width:220px}
    .lap-chips{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
    .lap-chip{background:#eef4ff;border-radius:10px;padding:10px 16px}
    .lap-chip b{display:block;font-size:20px}
    .lap-chip span{font-size:12px;color:#475569}
    .lap-scroll{overflow:auto;max-height:70vh;border:1px solid #e2e8f0;border-radius:10px}
    .lap-table{width:100%;border-collapse:collapse;font-size:14px}
    .lap-table th{position:sticky;top:0;background:#f8fafc;text-align:left;padding:10px 12px;border-bottom:1px solid #e2e8f0;white-space:nowrap}
    .lap-table td{padding:9px 12px;border-bottom:1px solid #f1f5f9}
    .lap-total td{font-weight:700;background:#f8fafc}
    .lap-empty{text-align:center;color:#94a3b8;padding:24px!important}
    .lap-bar{margin-bottom:18px;max-width:640px}
    .lap-bar-row{display:grid;grid-template-columns:90px 1fr 120px;align-items:center;gap:10px;margin-bottom:8px;font-size:14px}
    .lap-bar-track{background:#e2e8f0;border-radius:99px;height:14px;overflow:hidden}
    .lap-bar-fill{background:#1a73e8;height:100%}
    .lap-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:14px}
    .lap-tile{display:block;border:1px solid #e2e8f0;border-radius:12px;padding:18px;text-decoration:none;color:#0f172a;background:#fff}
    .lap-tile:hover{border-color:#1a73e8;box-shadow:0 2px 10px rgba(26,115,232,.12)}
    .lap-tile .ic{font-size:26px}
    .lap-tile b{display:block;margin:8px 0 4px}
    .lap-tile small{color:#64748b}
    .lap-card{border:1px solid #e2e8f0;border-radius:12px;padding:18px;margin-bottom:16px;background:#fff}
    .lap-card h3{margin:0 0 4px}
    .lap-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;align-items:center}
</style>
@yield('laporan')
@endsection
