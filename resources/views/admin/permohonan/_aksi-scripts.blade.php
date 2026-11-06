{{-- Dipakai index.blade.php dan show.blade.php bersama _aksi-modals.blade.php. --}}
<script>
    function bukaModalAksi(btn) {
        const acc = btn.dataset.aksiModal === 'acc';
        const form = document.getElementById(acc ? 'formACC' : 'formTolak');
        if (!form) return;

        document.getElementById(acc ? 'accNama' : 'tolakNama').textContent = btn.dataset.nama || '-';
        document.getElementById(acc ? 'accBarang' : 'tolakBarang').textContent = btn.dataset.barang || '-';

        form.action = btn.dataset.action;
        form.querySelector('[name="catatan_admin"]').value = '';

        AdminUI.openModal(form.closest('.admin-modal').id);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-aksi-modal]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                bukaModalAksi(btn);
            });
        });
    });
</script>
