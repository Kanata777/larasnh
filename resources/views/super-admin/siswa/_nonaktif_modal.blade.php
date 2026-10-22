<dialog id="modal-nonaktif">
    <form method="POST" id="form-nonaktif">
        @csrf
        @method('PATCH')
        <div class="modal-head">
            <h3>Nonaktifkan Siswa</h3>
            <button type="button" class="modal-close" data-close aria-label="Tutup">&times;</button>
        </div>
        <div class="modal-body">
            <p class="muted" style="margin:0 0 16px;">Siswa: <strong id="nonaktif-nama"></strong></p>
            <div class="field">
                <label for="na-status">Alasan</label>
                <select id="na-status" name="status_siswa" required>
                    <option value="Pindah">Pindah sekolah</option>
                    <option value="Lulus">Lulus</option>
                    <option value="Nonaktif">Keluar / nonaktif</option>
                </select>
            </div>
            <div class="field">
                <label for="na-catatan">Catatan</label>
                <textarea id="na-catatan" name="catatan" maxlength="255" placeholder="Opsional, mis. nama sekolah tujuan"></textarea>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-light" data-close>Batal</button>
            <button type="submit" class="btn btn-danger">Nonaktifkan</button>
        </div>
    </form>
</dialog>

@push('scripts')
<script>
    (function () {
        const dialog = document.getElementById('modal-nonaktif');
        const form = document.getElementById('form-nonaktif');

        document.querySelectorAll('[data-nonaktif]').forEach(btn => {
            btn.addEventListener('click', () => {
                form.action = btn.dataset.action;
                document.getElementById('nonaktif-nama').textContent = btn.dataset.nama;
                dialog.showModal();
            });
        });

        dialog.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
    })();
</script>
@endpush