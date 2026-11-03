<div class="tabs" role="tablist">
    <button type="button" class="tab active" role="tab" data-tab="tahun-pelajaran">
        <span class="icon">📅</span>
        <strong>Tahun Pelajaran</strong>
        <span class="desc">Tambah, edit, aktifkan</span>
    </button>
    <button type="button" class="tab" role="tab" data-tab="kelas">
        <span class="icon">🏫</span>
        <strong>Kelas / Rombel</strong>
        <span class="desc">Tambah, edit, atur wali kelas</span>
    </button>
    <button type="button" class="tab" role="tab" data-tab="status-siswa">
        <span class="icon">📋</span>
        <strong>Status Siswa</strong>
        <span class="desc">Aktif, pindah, lulus, nonaktif</span>
    </button>
</div>

<style>
    /* Paksa tab memenuhi lebar penuh, menimpa CSS lama yang bentrok */
    div.tabs[role="tablist"] {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: .75rem;
        width: 100% !important;
        max-width: none !important;
        min-width: 0;
        box-sizing: border-box;
        align-self: stretch;     /* kalau induknya flex */
        flex: 1 1 100%;          /* kalau induknya flex */
        margin-left: 0;
        margin-right: 0;
    }

    div.tabs[role="tablist"] > .tab {
        display: flex !important;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .2rem;
        width: 100% !important;
        max-width: none !important;
        min-width: 0;
        flex: none;
        box-sizing: border-box;
        padding: .9rem 1rem;
        text-align: center;
        font: inherit;
        cursor: pointer;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }

    div.tabs[role="tablist"] > .tab .icon { font-size: 1.4rem; }
    div.tabs[role="tablist"] > .tab .desc { font-size: .8rem; color: #6b7280; }

    div.tabs[role="tablist"] > .tab.active {
        background: #eff6ff;
        border-color: #2563eb;
    }

    @media (max-width: 700px) {
        div.tabs[role="tablist"] { grid-template-columns: 1fr !important; }
    }
</style>