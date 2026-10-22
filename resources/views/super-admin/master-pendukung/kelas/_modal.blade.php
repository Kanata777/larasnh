<dialog id="modal-kelas">
    <form method="POST" class="modal-form">
        @csrf
        <input type="hidden" name="_method" value="POST">
        <div class="modal-head">
            <h3 class="modal-title">Kelas / Rombel</h3>
            <button type="button" class="modal-close" data-close aria-label="Tutup">&times;</button>
        </div>
        <div class="modal-body">
            <div class="field-row">
                <div class="field">
                    <label for="kl-nama">Nama kelas / rombel</label>
                    <input type="text" id="kl-nama" name="nama_kelas" placeholder="Contoh: 1C" required maxlength="20">
                </div>
                <div class="field">
                    <label for="kl-tingkat">Tingkat (angka)</label>
                    <input type="number" id="kl-tingkat" name="tingkat" placeholder="Contoh: 6" required min="1" max="6">
                </div>
            </div>
            <div class="field">
                <label for="kl-tp">Tahun pelajaran</label>
                <select id="kl-tp" name="tahun_pelajaran_id" required>
                    <option value="">Pilih tahun pelajaran</option>
                    @foreach($tahunPelajaran as $t)
                        <option value="{{ $t->id }}">{{ $t->nama }}{{ $t->status === 'aktif' ? ' (aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-row">
                <div class="field">
                    <label for="kl-kap">Kapasitas</label>
                    <input type="number" id="kl-kap" name="kapasitas" placeholder="Contoh: 36" min="1" max="200">
                </div>
                <div class="field">
                    <label for="kl-status">Status</label>
                    <select id="kl-status" name="status" required>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label for="kl-wali">Wali kelas</label>
                <select id="kl-wali" name="wali_kelas_id">
                    <option value="">Belum diatur</option>
                    @foreach($guru as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-light" data-close>Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</dialog>