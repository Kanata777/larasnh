{{-- resources/views/super-admin/master-pendukung/kelas/_modal-wali.blade.php --}}
@php
    $waliKelasList = $waliKelasList
        ?? \Illuminate\Support\Facades\DB::table('wali_kelas')
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nama']);
@endphp

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css" rel="stylesheet">

<dialog id="modal-wali">
    <form method="POST" class="modal-form" id="form-wali">
        @csrf
        <input type="hidden" name="_method" value="PATCH">

        <div class="modal-head">
            <h3 class="modal-title">Atur Wali Kelas <span id="wk-nama-kelas"></span></h3>
            <button type="button" class="modal-close" data-close aria-label="Tutup">&times;</button>
        </div>

        <div class="modal-body">
            <div class="field">
                <label for="wk-select">Wali kelas</label>
                <select id="wk-select" name="wali_kelas_ids[]" multiple>
                    @foreach($waliKelasList as $wali)
                        <option value="{{ $wali->id }}">{{ $wali->nama }}</option>
                    @endforeach
                </select>
                <small class="field-hint">Satu kelas dapat memiliki lebih dari satu wali kelas.</small>
                @error('wali_kelas_ids.*')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="modal-foot">
            <button type="button" class="btn btn-light" data-close>Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</dialog>

<style>
    /* ===== Kerangka modal ===== */
    #modal-wali {
        width: min(480px, 92vw);
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
    #modal-wali .modal-title span { color: #6b7280; font-weight: 400; }
    #modal-wali .modal-close {
        border: 0; background: none; font-size: 1.5rem; line-height: 1; cursor: pointer; color: #6b7280;
    }
    #modal-wali .modal-body { padding: 1.25rem; overflow: visible; }
    #modal-wali .modal-foot {
        display: flex; justify-content: flex-end; gap: .5rem;
        padding: .9rem 1.25rem; border-top: 1px solid #e5e7eb;
    }
    #modal-wali .field label { display: block; margin-bottom: .4rem; font-weight: 600; }
    #modal-wali .field-hint  { display: block; margin-top: .4rem; color: #6b7280; }
    #modal-wali .field-error { display: block; margin-top: .4rem; color: #dc2626; }

    /* ===== Tom Select: kotak ===== */
    #modal-wali .ts-wrapper { width: 100%; }
    #modal-wali .ts-control {
        min-height: 42px; padding: .3rem .5rem; gap: .3rem;
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

    /* ===== Tom Select: chip wali kelas terpilih ===== */
    #modal-wali .ts-wrapper.multi .ts-control > .item {
        margin: 0; padding: .2rem .35rem .2rem .7rem;
        background: #e8f0fe; color: #1a56db; border: 0; border-radius: 999px; font-size: .88rem;
    }
    #modal-wali .ts-wrapper.multi .ts-control > .item .remove {
        border-left: 0; padding: 0 .3rem; color: inherit; border-radius: 50%;
    }
    #modal-wali .ts-wrapper.multi .ts-control > .item .remove:hover { background: rgba(26, 86, 219, .15); }

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

    /* ===== Chip di tabel kelas ===== */
    .chip {
        display: inline-block; padding: .15rem .6rem; margin: 0 .25rem .25rem 0;
        border-radius: 999px; background: #e8f0fe; color: #1a56db; font-size: .85rem;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('modal-wali');
    const form   = document.getElementById('form-wali');
    const judul  = document.getElementById('wk-nama-kelas');

    const waliSelect = new TomSelect('#wk-select', {
        plugins: ['remove_button'],
        maxItems: null,
        maxOptions: null,
        hideSelected: true,
        closeAfterSelect: false,
        openOnFocus: true,
        placeholder: 'Klik untuk memilih atau ketik untuk mencari wali kelas...',
        render: {
            no_results: () => '<div class="no-results">Wali kelas tidak ditemukan</div>'
        }
    });

    // buka modal
    document.querySelectorAll('[data-open-wali]').forEach(btn => {
        btn.addEventListener('click', () => {
            form.action = btn.dataset.action;
            judul.textContent = btn.dataset.kelas ? '– ' + btn.dataset.kelas : '';
            waliSelect.setValue(JSON.parse(btn.dataset.wali || '[]'));
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
        waliSelect.clear(true);
        waliSelect.close();
    });
});
</script>