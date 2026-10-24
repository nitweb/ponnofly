<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
<script>
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('form[data-swal]');
        if (!form || form.dataset.swalDone === '1') return;
        e.preventDefault();
        Swal.fire({
            title: form.dataset.swalTitle || 'Are you sure?',
            text: form.dataset.swalText || '',
            icon: form.dataset.swalIcon || 'warning',
            showCancelButton: true,
            confirmButtonText: form.dataset.swalConfirm || 'Yes',
            cancelButtonText: 'Cancel',
            confirmButtonColor: form.dataset.swalDanger === '1' ? '#DC2626' : '#0B3A8C',
            reverseButtons: true,
            focusCancel: true
        }).then(function (result) {
            if (!result.isConfirmed) return;
            form.dataset.swalDone = '1';
            form.submit();
        });
    });
</script>
