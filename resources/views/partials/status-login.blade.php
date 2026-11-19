{{--
Penanda "Login sebagai ..." sesuai role user yang sedang login.
Pemakaian di view/layout mana pun:  @include('partials.status-login')
Teks diambil dari App\Enums\Role::label(), misalnya:
  Super Administrator | Staff Tata Usaha | Kepala Tata Usaha | Guru BK | ...
--}}
@php
    $uLogin = auth()->user();
    $labelRole = $uLogin?->role instanceof \App\Enums\Role
        ? $uLogin->role->label()
        : (string) ($uLogin?->role ?? '');
    $namaLogin = $uLogin?->nama ?? $uLogin?->name ?? '';
@endphp

@if($uLogin)
    @once
        <style>
            .status-login {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 5px 12px;
                border-radius: 999px;
                background: #eef4ff;
                border: 1px solid #c7dbff;
                color: #1e40af;
                font-size: 13px;
                line-height: 1.2;
                white-space: nowrap;
            }
            .status-login strong { font-weight: 700; }
        </style>
    @endonce

    <span class="status-login" title="{{ $namaLogin }}">
        🔐 Login sebagai <strong>{{ $labelRole }}</strong>
    </span>
@endif