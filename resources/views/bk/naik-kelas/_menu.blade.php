@php
    $peran        = auth()->user()->role->value;
    $bk           = $peran === 'guru_bk';
    $onPenempatan = request()->routeIs('bk.naik-kelas.penempatan');
    $onNaik       = request()->routeIs('bk.naik-kelas.otomatis');
    $onLulus      = request()->routeIs('bk.naik-kelas.kelulusan');
    $tk           = (int) request('tingkat', $bk ? 3 : 1);
@endphp

{{--
    Pembagian tugas:
      Guru BK  : Penempatan kelas 1 (dari PMB) dan Naik kelas 3 -> 4
      Staff TU : Naik kelas 1->2, 2->3, 4->5, 5->6 dan Kelulusan
--}}
<div class="panel" style="padding:14px 22px">
    <div class="btn-row" style="align-items:center">
        @if($bk)
            <a class="btn {{ $onPenempatan ? 'btn-primary' : 'btn-light' }}"
                href="{{ route('bk.naik-kelas.penempatan') }}">Penempatan kelas 1</a>
            <a class="btn {{ $onNaik ? 'btn-primary' : 'btn-light' }}"
                href="{{ route('bk.naik-kelas.otomatis', ['tingkat' => 3]) }}">Pembagian kelas 3 → 4</a>
        @else
            <select aria-label="Naik kelas" style="width:auto;padding:8px 12px;font-weight:600;{{ $onNaik ? 'border-color:var(--blue);background:var(--blue);color:#fff' : '' }}"
                onchange="if (this.value) window.location = this.value">
                @unless($onNaik)<option value="">Naik kelas…</option>@endunless
                @foreach([1, 2, 4, 5] as $t)
                    <option value="{{ route('bk.naik-kelas.otomatis', ['tingkat' => $t]) }}" @selected($onNaik && $tk === $t)>
                        Naik kelas {{ $t }} → {{ $t + 1 }}
                    </option>
                @endforeach
            </select>

            <a class="btn {{ $onLulus ? 'btn-primary' : 'btn-light' }}"
                href="{{ route('bk.naik-kelas.kelulusan') }}">Kelulusan kelas 6</a>
        @endif

        <a class="btn btn-light" style="margin-left:auto" href="{{ route('bk.pindah-kelas.index') }}">← Riwayat</a>
    </div>
</div>