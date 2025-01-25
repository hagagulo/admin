<?php
require_once 'config/session.php';
require_once 'db_config.php';
checkLogin();
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="height-100 bg-light">
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Manajemen Absensi</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inputAbsensiModal">
                <i class='bx bx-plus'></i> Input Absensi Manual
            </button>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Periode</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="startDate">
                            <span class="input-group-text">s/d</span>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <select id="filterUnit" class="form-select">
                            <option value="">Semua Unit</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select id="filterStatus" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="tepat">Tepat Waktu</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="pulang_cepat">Pulang Cepat</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cari</label>
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari nama/NIP...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="dropdown">
                            <button class="btn btn-success dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                                Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" id="exportExcel">Excel</a></li>
                                <li><a class="dropdown-item" href="#" id="exportPDF">PDF</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Absensi -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="absensiTable">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>NIP/NRP</th>
                                <th>Nama</th>
                                <th>Unit</th>
                                <th>Jam Masuk</th>
                                <th>Status Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status Pulang</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan diisi oleh JavaScript -->
                        </tbody>
                    </table>
                </div>
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-center" id="pagination">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Absensi Manual -->
<div class="modal fade" id="inputAbsensiModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Absensi Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="absensiForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>NIP/NRP</label>
                        <select class="form-select" id="inputNipNrp" required>
                            <option value="">Pilih Personil</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" id="inputTanggal" required>
                    </div>
                    <div class="mb-3">
                        <label>Jam Masuk</label>
                        <input type="time" class="form-control" id="inputJamMasuk">
                    </div>
                    <div class="mb-3">
                        <label>Jam Pulang</label>
                        <input type="time" class="form-control" id="inputJamPulang">
                    </div>
                    <div class="mb-3">
                        <label>Keterangan</label>
                        <textarea class="form-control" id="inputKeterangan" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    
    $(document).ready(function() {
    // Set default date range to current month
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    $('#startDate').val(firstDay.toISOString().split('T')[0]);
    $('#endDate').val(today.toISOString().split('T')[0]);

    // Load initial data
    loadAbsensiData();
    loadUnitData();
    loadPersonilData();

    // Event listeners
    $('#startDate, #endDate, #filterUnit, #filterStatus').on('change', loadAbsensiData);
    $('#searchInput').on('keyup', _.debounce(function() {
        loadAbsensiData(1);
    }, 500));
    $('#filterUnit, #filterStatus').on('change', function() {
        loadAbsensiData(1);
    });
    $('#exportExcel').click(exportToExcel);
    $('#exportPDF').click(exportToPDF);
    $('#absensiForm').on('submit', handleAbsensiSubmit);
});

function loadAbsensiData(page = 1) {
    // Konversi filters menjadi objek biasa
    const filters = {
        start_date: $('#startDate').val() || '',
        end_date: $('#endDate').val() || '',
        unit: $('#filterUnit').val() || '',
        status: $('#filterStatus').val() || '',
        search: $('#searchInput').val() || '',
        page: page || 1
    };

    // Gunakan URLSearchParams untuk membuat query string
    const params = new URLSearchParams();
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            params.append(key, filters[key]);
        }
    });
    
    // Tambahkan action ke params
    params.append('action', 'list');

    $.ajax({
        url: 'api/absensi_data.php',
        method: 'GET',
        data: params.toString(),
        success: function(response) {
            console.log('Response:', response);
            if (response.success) {
                updateTable(response.data);
                updatePagination(response.totalPages, response.currentPage);
            } else {
                showAlert('error', 'Gagal memuat data: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            console.log('Response Text:', xhr.responseText);
            showAlert('error', 'Terjadi kesalahan saat memuat data');
        }
    });
}


function updateTable(data) {
    const tbody = $('#absensiTable tbody');
    tbody.empty();

    data.forEach(item => {
        const row = `
            <tr>
                <td>${formatDate(item.tanggal)}</td>
                <td>${item.nip_nrp}</td>
                <td>${item.nama}</td>
                <td>${item.unit_kerja}</td>
                <td>${formatTime(item.jam_masuk)}</td>
                <td>
                    <span class="badge ${getBadgeClass(item.keterangan_masuk)}">
                        ${item.keterangan_masuk || 'Tepat Waktu'}
                    </span>
                </td>
                <td>${formatTime(item.jam_pulang)}</td>
                <td>
                    <span class="badge ${getBadgeClass(item.keterangan_pulang)}">
                        ${item.keterangan_pulang || 'Tepat Waktu'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="editAbsensi(${JSON.stringify(item)})">
                        <i class='bx bx-edit'></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function updatePagination(totalPages, currentPage) {
    const pagination = $('#pagination');
    pagination.empty();

    if (totalPages > 1) {
        for (let i = 1; i <= totalPages; i++) {
            pagination.append(`
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadAbsensiData(${i})">${i}</a>
                </li>
            `);
        }
    }
}

function handleAbsensiSubmit(e) {
    e.preventDefault();

    // Validasi input
    const nipNrp = $('#inputNipNrp').val();
    const tanggal = $('#inputTanggal').val();

    if (!nipNrp || !tanggal) {
        showAlert('error', 'NIP/NRP dan Tanggal harus diisi');
        return;
    }

    const formData = {
        nip_nrp: nipNrp,
        tanggal: tanggal,
        jam_masuk: $('#inputJamMasuk').val() || null,
        jam_pulang: $('#inputJamPulang').val() || null,
        keterangan: $('#inputKeterangan').val() || ''
    };

    $.ajax({
        url: 'api/input_manual_absen.php',
        method: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                $('#inputAbsensiModal').modal('hide');
                $('#absensiForm')[0].reset();
                showAlert('success', 'Data absensi berhasil disimpan');
                loadAbsensiData(); // Refresh tabel
            } else {
                showAlert('error', response.message || 'Gagal menyimpan data');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            showAlert('error', 'Terjadi kesalahan saat menyimpan data');
        }
    });
}

function exportToExcel() {
    // Implement Excel export
    const filters = {
        start_date: $('#startDate').val(),
        end_date: $('#endDate').val(),
        unit: $('#filterUnit').val(),
        status: $('#filterStatus').val(),
        search: $('#searchInput').val(),
        export_type: 'excel'
    };

    window.location.href = `api/export_absensi.php?${$.param(filters)}`;
}

function exportToPDF() {
    // Implement PDF export
    const filters = {
        start_date: $('#startDate').val(),
        end_date: $('#endDate').val(),
        unit: $('#filterUnit').val(),
        status: $('#filterStatus').val(),
        search: $('#searchInput').val(),
        export_type: 'pdf'
    };

    window.location.href = `api/export_absensi.php?${$.param(filters)}`;
}

// Helper functions
function formatDate(date) {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(time) {
    return time ? time.substring(0, 5) : '-';
}

function getBadgeClass(status) {
    switch(status) {
        case 'Terlambat':
            return 'bg-warning';
        case 'Pulang Cepat':
            return 'bg-danger';
        default:
            return 'bg-success';
    }
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert alert before the first card
    $('.card:first').before(alert);

    // Auto close after 3 seconds
    setTimeout(() => {
        $('.alert').alert('close');
    }, 3000);
}

function loadUnitData() {
    $.ajax({
        url: 'api/get_units.php',  // Sesuaikan dengan API yang sudah ada
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const select = $('#filterUnit');
                select.empty();
                select.append('<option value="">Semua Unit</option>');
                
                response.data.forEach(unit => {
                    select.append(`<option value="${unit}">${unit}</option>`);
                });
            } else {
                console.error('Gagal memuat data unit:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading unit data:', error);
        }
    });
}

function loadPersonilData() {
    $.ajax({
        url: 'api/get_personil.php',  // Sesuaikan dengan API yang sudah ada
        method: 'POST',
        success: function(response) {
            if (response.success) {
                const select = $('#inputNipNrp');
                select.empty();
                select.append('<option value="">Pilih Personil</option>');
                
                response.data.forEach(personil => {
                    select.append(`<option value="${personil.nip_nrp}">${personil.nip_nrp} - ${personil.nama}</option>`);
                });
            } else {
                console.error('Gagal memuat data personil:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading personil data:', error);
        }
    });
}


</script>

<?php require_once 'includes/footer.php'; ?>