<dialog id="modal-tahun">
    <form method="POST" class="modal-form">
        @csrf
        <input type="hidden" name="_method" value="POST">
        <div class="modal-head">
            <h3 class="modal-title">Tahun Pelajaran</h3>
            <button type="button" class="modal-close" data-close aria-label="Tutup">&times;</button>
        </div>
        <div class="modal-body">
            <div class="field">
                <label for="tp-nama">Tahun pelajaran</label>
                <input type="text" id="tp-nama" name="nama" placeholder="Contoh: 2026/2027" required maxlength="20">
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="tp-mulai">Tanggal mulai</label>
                    <input type="date" id="tp-mulai" name="tanggal_mulai">
                </div>
                <div class="field">
                    <label for="tp-selesai">Tanggal selesai</label>
                    <input type="date" id="tp-selesai" name="tanggal_selesai">
                </div>
            </div>
            <div class="field">
                <label for="tp-ket">Keterangan</label>
                <textarea id="tp-ket" name="keterangan" maxlength="255" placeholder="Opsional"></textarea>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-light" data-close>Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</dialog>