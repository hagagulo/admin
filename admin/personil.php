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
            <h1 class="h3 mb-0 text-gray-800">Manajemen Personil</h1>
        </div>

        <!-- Filter dan Search -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <select id="filterUnit" class="form-select">
                            <option value="">Semua Unit</option>
                            <!-- Unit akan diisi oleh AJAX -->
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <select id="filterLokasi" class="form-select">
                            <option value="">Semua Lokasi</option>
                            <!-- Lokasi akan diisi oleh AJAX -->
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari personil...">
                            <button class="btn btn-primary" type="button" id="searchBtn">
                                <i class='bx bx-search'></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="dropdown">
                            <button class="btn btn-success dropdown-toggle w-100" type="button" id="exportDropdown" data-bs-toggle="dropdown">
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

        <!-- Tabel Personil -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="personilTable">
                        <thead>
                            <tr>
                                <th>NIP/NRP</th>
                                <th>Nama</th>
                                <th>Unit</th>
                                <th>Lokasi Absen 1</th>
                                <th>Lokasi Absen 2</th>
                                <th>Status Device</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="personilTableBody">
                            <!-- Data akan diisi oleh AJAX -->
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-center" id="pagination">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Lokasi Absen -->
<div class="modal fade" id="lokasiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Lokasi Absen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="lokasiForm">
                <div class="modal-body">
                    <input type="hidden" id="personilId">
                    <div class="mb-3">
                        <label for="lokasi1">Lokasi Absen 1</label>
                        <select id="lokasi1" class="form-select" required>
                            <!-- Lokasi akan diisi oleh AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="lokasi2">Lokasi Absen 2</label>
                        <select id="lokasi2" class="form-select">
                            <option value="">Tidak Ada</option>
                            <!-- Lokasi akan diisi oleh AJAX -->
                        </select>
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

<!-- Modal Konfirmasi Reset -->
<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Reset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Anda yakin ingin mereset <span id="resetType"></span> untuk personil ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmReset">Reset</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load data saat halaman dimuat
    loadPersonilData();
    loadFilterOptions();
    
    // Event listeners untuk filter dan search
    $('#searchBtn').on('click', function() {
        loadPersonilData();
    });
    
    $('#filterUnit, #filterLokasi').on('change', function() {
        loadPersonilData();
    });

    // Event listener untuk enter pada search input
    $('#searchInput').on('keypress', function(e) {
        if(e.which == 13) {
            loadPersonilData();
        }
    });
});

// Tambahkan fungsi-fungsi lain di sini
function loadFilterOptions() {
    // Load lokasi options
    $.ajax({
        url: './api/get_lokasi.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const select = $('#filterLokasi');
                select.empty();
                select.append('<option value="">Semua Lokasi</option>');
                response.data.forEach(function(lokasi) {
                    select.append(`<option value="${lokasi.id}">${lokasi.nama_lokasi}</option>`);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading lokasi:', error);
        }
    });

    // Load unit options
    $.ajax({
        url: './api/get_units.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const select = $('#filterUnit');
                select.empty();
                select.append('<option value="">Semua Unit</option>');
                response.data.forEach(function(unit) {
                    select.append(`<option value="${unit}">${unit}</option>`);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading units:', error);
        }
    });
}

function loadPersonilData() {
    console.log('Loading personil data...'); // Debug
    console.log('Filter values:', { // Debug
        unit: $('#filterUnit').val(),
        lokasi: $('#filterLokasi').val(),
        search: $('#searchInput').val()
    });

    $.ajax({
        url: './api/get_personil.php',
        type: 'GET',
        data: {
            unit: $('#filterUnit').val(),
            lokasi: $('#filterLokasi').val(),
            search: $('#searchInput').val()
        },
        dataType: 'json',
        success: function(response) {
            console.log('Response:', response); // Debug
            if (response.success) {
                displayPersonilData(response.data);
            } else {
                alert('Gagal memuat data: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            console.log('Response Text:', xhr.responseText); // Debug
            alert('Terjadi kesalahan saat memuat data');
        }
    });
}

function displayPersonilData(data) {
    const tbody = $('#personilTableBody');
    tbody.empty();
    
    if(data.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="7" class="text-center">Tidak ada data yang ditemukan</td>
            </tr>
        `);
        return;
    }
    
    data.forEach(function(personil) {
        tbody.append(`
            <tr>
                <td>${personil.nip_nrp}</td>
                <td>${personil.nama}</td>
                <td>${personil.unit_kerja}</td>
                <td>${personil.lokasi_1 || '-'}</td>
                <td>${personil.lokasi_2 || '-'}</td>
                <td>${personil.device_id ? '<span class="badge bg-success">Terdaftar</span>' : 
                                         '<span class="badge bg-warning">Belum terdaftar</span>'}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editLokasi('${personil.nip_nrp}')" 
                            title="Edit Lokasi">
                        <i class='bx bx-map'></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="resetDevice('${personil.nip_nrp}')"
                            title="Reset Device ID">
                        <i class='bx bx-mobile'></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="resetPassword('${personil.nip_nrp}')"
                            title="Reset Password">
                        <i class='bx bx-key'></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function resetDevice(nip_nrp) {
    if(confirm('Apakah Anda yakin ingin mereset Device ID untuk personil ini?')) {
        $.ajax({
            url: './api/reset_device.php',
            type: 'POST',
            data: { nip_nrp: nip_nrp },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('Device ID berhasil direset');
                    loadPersonilData();
                } else {
                    alert('Gagal reset device ID: ' + response.message);
                }
            }
        });
    }
}

function resetPassword(nip_nrp) {
    if(confirm('Apakah Anda yakin ingin mereset password untuk personil ini?')) {
        $.ajax({
            url: './api/reset_password.php',
            type: 'POST',
            data: { nip_nrp: nip_nrp },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('Password berhasil direset');
                    loadPersonilData();
                } else {
                    alert('Gagal reset password: ' + response.message);
                }
            }
        });
    }
}

// Fungsi untuk mengisi opsi lokasi di modal
function loadLokasiOptions() {
    $.ajax({
        url: './api/get_lokasi.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const lokasi1 = $('#lokasi1');
                const lokasi2 = $('#lokasi2');
                
                // Reset opsi
                lokasi1.empty();
                lokasi2.empty();
                
                // Tambah opsi default untuk lokasi 2
                lokasi2.append('<option value="">Tidak Ada</option>');
                
                // Isi opsi dari response
                response.data.forEach(function(lok) {
                    lokasi1.append(`<option value="${lok.id}">${lok.nama_lokasi}</option>`);
                    lokasi2.append(`<option value="${lok.id}">${lok.nama_lokasi}</option>`);
                });
            }
        }
    });
}

//fungsi editLokasi
function editLokasi(nip_nrp) {
    $('#personilId').val(nip_nrp);
    
    // Load opsi lokasi jika belum diload
    loadLokasiOptions();
    
    // Tampilkan modal
    $('#lokasiModal').modal('show');
}

// Event handler untuk form submit
$('#lokasiForm').on('submit', function(e) {
    e.preventDefault();
    
    const lokasi2Value = $('#lokasi2').val();
    
    const data = {
        nip_nrp: $('#personilId').val(),
        lokasi1: $('#lokasi1').val(),
        lokasi2: lokasi2Value ? lokasi2Value : null // Pastikan null jika tidak dipilih
    };

    $.ajax({
        url: './api/update_lokasi.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Lokasi berhasil diupdate');
                $('#lokasiModal').modal('hide');
                loadPersonilData(); // Refresh tabel
            } else {
                alert('Gagal update lokasi: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengupdate lokasi');
        }
    });
});

$('#lokasiForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: './api/update_lokasi.php',
        type: 'POST',
        data: {
            nip_nrp: $('#personilId').val(),
            lokasi1: $('#lokasi1').val(),
            lokasi2: $('#lokasi2').val()
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#lokasiModal').modal('hide');
                loadPersonilData();
            } else {
                alert('Gagal update lokasi: ' + response.message);
            }
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>