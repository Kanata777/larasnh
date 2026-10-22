(function () {
    'use strict';

    /* ---------- Tab ---------- */
    const tabs = document.querySelectorAll('.mp .tab');
    const panels = document.querySelectorAll('.mp .tab-panel');

    function openTab(name) {
        if (!document.getElementById('tab-' + name)) return;

        tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === name));
        panels.forEach(p => p.classList.toggle('active', p.id === 'tab-' + name));
        history.replaceState(null, '', '#' + name);
    }

    tabs.forEach(t => t.addEventListener('click', () => openTab(t.dataset.tab)));

    // Dipanggil saat menu sidebar (#tahun-pelajaran, #kelas, dst.) diklik
    window.addEventListener('hashchange', () => openTab(location.hash.replace('#', '')));
    openTab(location.hash.replace('#', ''));

    /* ---------- Modal tambah / edit ---------- */
    document.querySelectorAll('.mp [data-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const dialog = document.getElementById(btn.dataset.modal);
            const form = dialog.querySelector('form');
            const values = JSON.parse(btn.dataset.values || '{}');

            dialog.querySelector('.modal-title').textContent = btn.dataset.title;
            form.action = btn.dataset.action;
            form.querySelector('input[name="_method"]').value = btn.dataset.method;

            Object.entries(values).forEach(([name, value]) => {
                const field = form.elements[name];
                if (field) field.value = value ?? '';
            });

            dialog.showModal();
        });
    });

    document.querySelectorAll('.mp dialog').forEach(dialog => {
        dialog.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
    });
})();