{{-- resources/views/super-admin/master-pendukung/kelas/_modal-wali.blade.php --}}
@php
    $waliKelasList = $waliKelasList
        ?? \Illuminate\Support\Facades\DB::table('wali_kelas')
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nama', 'jenis_kelamin']);
@endphp

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css" rel="stylesheet">

<dialog id="modal-wali">
    <form method="POST" class="modal-form" id="form-wali">
        @csrf
        <input type="hidden" name="_method" value="PATCH">
        {{-- id wali (asisten) yang sedang menggantikan utama sementara; kosong = tidak ada --}}
        <input type="hidden" name="pengganti_id" id="wk-pengganti" value="">

        <div class="modal-head">
            <h3 class="modal-title">Atur Wali Kelas</h3>
            <button type="button" class="modal-close" data-close aria-label="Tutup">&times;</button>
        </div>

        <div class="modal-body">

            {{-- Keterangan warna --}}
            <div class="jk-legend">
                <span><i class="wk-avatar jk-l">L</i> Laki-laki</span>
                <span><i class="wk-avatar jk-p">P</i> Perempuan</span>
                <span><i class="wk-avatar jk-x">?</i> Belum diisi</span>
            </div>

            {{-- Wali kelas utama --}}
            <div class="field">
                <label for="wk-utama">Wali kelas utama</label>
                <select id="wk-utama" name="wali_utama_id">
                    <option value=""></option>
                    @foreach($waliKelasList as $wali)
                        <option value="{{ $wali->id }}"
                                data-data="{{ json_encode(['jk' => $wali->jenis_kelamin]) }}">{{ $wali->nama }}</option>
                    @endforeach
                </select>
                @error('wali_utama_id')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- Asisten 1 --}}
            <div class="field">
                <label for="wk-asisten-1">Asisten wali kelas 1</label>
                <div class="role-row">
                    <select id="wk-asisten-1" name="asisten_ids[]">
                        <option value=""></option>
                        @foreach($waliKelasList as $wali)
                            <option value="{{ $wali->id }}"
                                    data-data="{{ json_encode(['jk' => $wali->jenis_kelamin]) }}">{{ $wali->nama }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn-promote" data-ganti="asisten1"
                            title="Gantikan wali kelas utama untuk sementara">Gantikan sementara</button>
                </div>
            </div>

            {{-- Asisten 2 --}}
            <div class="field">
                <label for="wk-asisten-2">Asisten wali kelas 2</label>
                <div class="role-row">
                    <select id="wk-asisten-2" name="asisten_ids[]">
                        <option value=""></option>
                        @foreach($waliKelasList as $wali)
                            <option value="{{ $wali->id }}"
                                    data-data="{{ json_encode(['jk' => $wali->jenis_kelamin]) }}">{{ $wali->nama }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn-promote" data-ganti="asisten2"
                            title="Gantikan wali kelas utama untuk sementara">Gantikan sementara</button>
                </div>
            </div>

            @error('asisten_ids.*')
                <small class="field-error">{{ $message }}</small>
            @enderror

            <div class="ganti-info" id="wk-ganti-info" hidden></div>

            <small class="field-hint">
                Jika wali kelas utama berhalangan, klik <strong>Gantikan sementara</strong> pada asisten, lalu Simpan.
                Wali kelas utama tetap tercatat sebagai utama. Klik <strong>Kembalikan</strong> dan Simpan saat beliau bertugas lagi.
            </small>
        </div>

        <div class="modal-foot">
            <button type="button" class="btn btn-light" data-close>Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</dialog>

<style>
    [hidden] { display: none !important; }

    /* ===== Kerangka modal ===== */
    #modal-wali {
        width: min(540px, 92vw);
        padding: 0;
        border: 0;
        border-radius: 12px;
        overflow: visible;              /* dropdown boleh keluar dari modal */
        box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
    }
    #modal-wali::backdrop { background: rgba(15, 23, 42, .45); }
    #modal-wali .modal-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb;
    }
    #modal-wali .modal-title { margin: 0; font-size: 1.05rem; }
    #modal-wali .modal-close {
        border: 0; background: none; font-size: 1.5rem; line-height: 1; cursor: pointer; color: #6b7280;
    }
    #modal-wali .modal-body { padding: 1.25rem; overflow: visible; }
    #modal-wali .modal-foot {
        display: flex; justify-content: flex-end; gap: .5rem;
        padding: .9rem 1.25rem; border-top: 1px solid #e5e7eb;
    }
    #modal-wali .field { margin-bottom: 1rem; }
    #modal-wali .field label { display: block; margin-bottom: .4rem; font-weight: 600; }
    #modal-wali .field-hint  { display: block; margin-top: .25rem; color: #6b7280; line-height: 1.45; }
    #modal-wali .field-error { display: block; margin-top: .4rem; color: #dc2626; }

    /* ===== Lingkaran jenis kelamin ===== */
    #modal-wali .wk-avatar {
        display: inline-flex; align-items: center; justify-content: center;
        flex: 0 0 auto; width: 22px; height: 22px; border-radius: 50%;
        font-size: .7rem; font-weight: 700; font-style: normal; color: #fff;
    }
    #modal-wali .jk-l { background: #2563eb; }   /* laki-laki: biru */
    #modal-wali .jk-p { background: #ec4899; }   /* perempuan: pink */
    #modal-wali .jk-x { background: #9ca3af; }   /* belum diisi: abu-abu */

    #modal-wali .wk-opt { display: flex; align-items: center; gap: .5rem; }

    #modal-wali .jk-legend {
        display: flex; flex-wrap: wrap; gap: .5rem 1rem; margin-bottom: 1rem;
        padding: .55rem .8rem; font-size: .85rem; color: #4b5563;
        background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;
    }
    #modal-wali .jk-legend span { display: inline-flex; align-items: center; gap: .4rem; }
    #modal-wali .jk-legend .wk-avatar { width: 18px; height: 18px; font-size: .6rem; }

    /* ===== Baris asisten: select + tombol ===== */
    #modal-wali .role-row { display: flex; align-items: center; gap: .5rem; }
    #modal-wali .role-row .ts-wrapper { flex: 1; min-width: 0; }
    #modal-wali .btn-promote {
        flex: 0 0 auto; padding: .5rem .75rem; font-size: .85rem; white-space: nowrap;
        color: #1a56db; background: #fff; border: 1px solid #bfdbfe; border-radius: 8px; cursor: pointer;
    }
    #modal-wali .btn-promote:hover:not(:disabled) { background: #eff6ff; }
    #modal-wali .btn-promote:disabled { color: #9ca3af; border-color: #e5e7eb; cursor: not-allowed; }
    #modal-wali .btn-promote.is-active { color: #92400e; background: #fef3c7; border-color: #f59e0b; }
    #modal-wali .btn-promote.is-active:hover { background: #fde68a; }

    /* baris asisten yang sedang menggantikan */
    #modal-wali .role-row.is-ganti .ts-control { border-color: #f59e0b; background: #fffbeb; }

    #modal-wali .ganti-info {
        margin: 0 0 .75rem; padding: .6rem .8rem; font-size: .88rem; line-height: 1.4;
        color: #92400e; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;
    }

    /* ===== Tom Select: kotak ===== */
    #modal-wali .ts-wrapper { width: 100%; }
    #modal-wali .ts-control {
        min-height: 42px; padding: .3rem .6rem; gap: .3rem;
        border: 1px solid #d1d5db; border-radius: 8px; box-shadow: none;
    }
    #modal-wali .ts-wrapper.focus .ts-control {
        border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15);
    }
    /* reset style input bawaan aplikasi agar tidak bentrok */
    #modal-wali .ts-control > input {
        width: auto; min-height: 0; margin: 0; padding: 0;
        border: 0; background: transparent; box-shadow: none;
    }

    /* ===== Tom Select: dropdown ===== */
    #modal-wali .ts-dropdown {
        margin-top: 4px; z-index: 1100;
        border: 1px solid #e5e7eb; border-radius: 8px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .12);
    }
    #modal-wali .ts-dropdown .ts-dropdown-content { max-height: 200px; }
    #modal-wali .ts-dropdown .option,
    #modal-wali .ts-dropdown .no-results { padding: .5rem .75rem; }
    #modal-wali .ts-dropdown .active { background: #eff6ff; color: #1e40af; }
    /* wali yang sudah dipilih di peran lain */
    #modal-wali .ts-dropdown .option.is-taken { opacity: .45; pointer-events: none; }
</style>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('modal-wali');
    const form   = document.getElementById('form-wali');
    const hidden = document.getElementById('wk-pengganti');
    const info   = document.getElementById('wk-ganti-info');

    // warna lingkaran menurut jenis kelamin
    const kelasJk = jk => (jk === 'L' ? 'jk-l' : jk === 'P' ? 'jk-p' : 'jk-x');

    const tampil = (d, esc) =>
        `<span class="wk-opt"><i class="wk-avatar ${kelasJk(d.jk)}">${esc((d.text || '?').trim().charAt(0).toUpperCase())}</i>` +
        `<span>${esc(d.text)}</span></span>`;

    const base = {
        plugins: ['clear_button'],
        maxItems: 1,
        maxOptions: null,
        openOnFocus: true,
        render: {
            no_results: () => '<div class="no-results">Wali kelas tidak ditemukan</div>',
            option: (d, esc) =>
                `<div class="${d.disabled ? 'is-taken' : ''}">${tampil(d, esc)}${d.disabled ? ' <small>(sudah dipilih)</small>' : ''}</div>`,
            item: (d, esc) => `<div>${tampil(d, esc)}</div>`
        }
    };

    const selects = {
        utama:    new TomSelect('#wk-utama',     { ...base, placeholder: 'Pilih wali kelas utama...' }),
        asisten1: new TomSelect('#wk-asisten-1', { ...base, placeholder: 'Pilih asisten wali kelas 1...' }),
        asisten2: new TomSelect('#wk-asisten-2', { ...base, placeholder: 'Pilih asisten wali kelas 2...' })
    };
    const all = Object.values(selects);

    // asisten yang sedang menggantikan utama sementara: 'asisten1' | 'asisten2' | null
    let pengganti = null;

    const put  = (ts, v) => (v ? ts.setValue(String(v), true) : ts.clear(true));
    const nama = ts => { const v = ts.getValue(); return v && ts.options[v] ? ts.options[v].text : ''; };

    function refresh() {
        // satu orang tidak boleh punya dua peran: opsi yang dipakai peran lain dinonaktifkan
        const chosen = all.map(ts => ts.getValue()).filter(Boolean);
        all.forEach(ts => {
            const own = ts.getValue();
            Object.values(ts.options).forEach(o => {
                const taken = chosen.includes(String(o.value)) && String(o.value) !== own;
                if (!!o.disabled !== taken) ts.updateOption(o.value, { ...o, disabled: taken });
            });
        });

        // penggantian hanya valid jika utama dan asisten yang menggantikan sama-sama terisi
        if (pengganti && (!selects.utama.getValue() || !selects[pengganti].getValue())) {
            pengganti = null;
        }
        hidden.value = pengganti ? selects[pengganti].getValue() : '';

        document.querySelectorAll('[data-ganti]').forEach(btn => {
            const slot  = btn.dataset.ganti;
            const aktif = pengganti === slot;
            btn.disabled = !aktif && (!selects[slot].getValue() || !selects.utama.getValue());
            btn.textContent = aktif ? '↩ Kembalikan' : 'Gantikan sementara';
            btn.classList.toggle('is-active', aktif);
            btn.closest('.role-row').classList.toggle('is-ganti', aktif);
        });

        if (pengganti) {
            info.textContent = `${nama(selects[pengganti])} menggantikan ${nama(selects.utama)} untuk sementara. ` +
                               `${nama(selects.utama)} tetap tercatat sebagai wali kelas utama.`;
            info.hidden = false;
        } else {
            info.hidden = true;
        }
    }

    function enableAll() {
        all.forEach(ts => Object.values(ts.options).forEach(o => {
            if (o.disabled) ts.updateOption(o.value, { ...o, disabled: false });
        }));
    }

    all.forEach(ts => ts.on('change', refresh));

    // tombol "Gantikan sementara" / "Kembalikan"
    document.querySelectorAll('[data-ganti]').forEach(btn => {
        btn.addEventListener('click', () => {
            const slot = btn.dataset.ganti;
            pengganti = (pengganti === slot) ? null : slot;
            refresh();
        });
    });

    // buka modal
    document.querySelectorAll('[data-open-wali]').forEach(btn => {
        btn.addEventListener('click', () => {
            form.action = btn.dataset.action;
            const asisten = JSON.parse(btn.dataset.asisten || '[]');
            enableAll();
            put(selects.utama,    btn.dataset.utama || '');
            put(selects.asisten1, asisten[0] || '');
            put(selects.asisten2, asisten[1] || '');

            const penggantiId = String(btn.dataset.pengganti || '');
            pengganti = null;
            if (penggantiId) {
                if (String(asisten[0] || '') === penggantiId) pengganti = 'asisten1';
                else if (String(asisten[1] || '') === penggantiId) pengganti = 'asisten2';
            }

            refresh();
            dialog.showModal();
        });
    });

    // tutup modal: tombol, dan klik area gelap di luar modal
    dialog.querySelectorAll('[data-close]').forEach(el => {
        el.addEventListener('click', () => dialog.close());
    });
    dialog.addEventListener('mousedown', e => {
        if (e.target === dialog) dialog.close();
    });

    // reset saat tertutup
    dialog.addEventListener('close', () => {
        all.forEach(ts => { ts.clear(true); ts.close(); });
        pengganti = null;
        enableAll();
        refresh();
    });
});
</script>