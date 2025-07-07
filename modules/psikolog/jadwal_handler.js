document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    new simpleDatatables.DataTable('#tableJadwal');

    // Initialize Flatpickr for time inputs
    flatpickr(".flatpickr-time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 30
    });

    // Form submission handler
    const formJadwal = document.getElementById('formJadwal');
    if (formJadwal) {
        formJadwal.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'jadwal.php';
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message
                });
            });
        });
    }

    // Reset form when modal is closed
    document.getElementById('modalJadwal').addEventListener('hidden.bs.modal', function () {
        formJadwal.reset();
        document.getElementById('jadwal_id').value = '';
        document.getElementById('modalJadwalLabel').textContent = 'Tambah Jadwal Praktik';
        document.querySelector('input[name="action"]').value = 'add_jadwal';
    });
});

function editJadwal(id) {
    fetch(`get_jadwal.php?id=${id}`)
    .then(response => response.json())
    .then(data => {
        document.getElementById('jadwal_id').value = data.id;
        document.getElementById('psikolog_id').value = data.psikolog_id;
        document.getElementById('hari').value = data.hari;
        document.getElementById('jam_mulai').value = data.jam_mulai;
        document.getElementById('jam_selesai').value = data.jam_selesai;
        
        document.getElementById('modalJadwalLabel').textContent = 'Edit Jadwal Praktik';
        document.querySelector('input[name="action"]').value = 'edit_jadwal';
        
        new bootstrap.Modal(document.getElementById('modalJadwal')).show();
    })
    .catch(error => {
        Swal.fire('Error!', 'Gagal mengambil data jadwal', 'error');
    });
}

function toggleStatus(id, status) {
    const title = status === 'aktif' ? 'Aktifkan Jadwal' : 'Non-aktifkan Jadwal';
    const text = status === 'aktif' ? 
        'Apakah Anda yakin ingin mengaktifkan jadwal ini?' : 
        'Apakah Anda yakin ingin menonaktifkan jadwal ini?';
    
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: status === 'aktif' ? '#28a745' : '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: status === 'aktif' ? 'Ya, Aktifkan!' : 'Ya, Non-aktifkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=toggle_jadwal&id=${id}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire('Error!', error.message, 'error');
            });
        }
    });
}
