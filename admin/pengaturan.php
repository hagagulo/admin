<?php
require_once 'config/session.php';
require_once 'db_config.php';
checkLogin();
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="height-100 bg-light">
    <div class="container-fluid">
        <div class="d-sm-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Pengaturan Sistem</h1>
        </div>

        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#harilibur">Hari Libur</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#jamkerja">Jam Kerja</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#lokasi">Lokasi Absen</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#admin">User Admin</a>
            </li>
        </ul>

        <!-- Tab content -->
        <div class="tab-content">
            <!-- Hari Libur -->
            <div id="harilibur" class="tab-pane active">
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <h5 class="card-title">Daftar Hari Libur</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLiburModal">
                                <i class='bx bx-plus'></i> Tambah
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="liburTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jam Kerja -->
            <div id="jamkerja" class="tab-pane fade">
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Pengaturan Jam Kerja</h5>
                        <form id="jamKerjaForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Hari Biasa</h6>
                                    <div class="mb-3">
                                        <label>Jam Masuk</label>
                                        <input type="time" class="form-control" id="jamMasukNormal">
                                    </div>
                                    <div class="mb-3">
                                        <label>Jam Pulang</label>
                                        <input type="time" class="form-control" id="jamPulangNormal">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Hari Jumat</h6>
                                    <div class="mb-3">
                                        <label>Jam Masuk</label>
                                        <input type="time" class="form-control" id="jamMasukJumat">
                                    </div>
                                    <div class="mb-3">
                                        <label>Jam Pulang</label>
                                        <input type="time" class="form-control" id="jamPulangJumat">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Lokasi -->
            <div id="lokasi" class="tab-pane fade">
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <h5 class="card-title">Daftar Lokasi Absen</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLokasiModal">
                                <i class='bx bx-plus'></i> Tambah
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="lokasiTable">
                                <thead>
                                    <tr>
                                        <th>Nama Lokasi</th>
                                        <th>Latitude</th>
                                        <th>Longitude</th>
                                        <th>Radius (meter)</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Admin -->
            <div id="admin" class="tab-pane fade">
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <h5 class="card-title">Daftar User Admin</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                                <i class='bx bx-plus'></i> Tambah
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="adminTable">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Nama</th>
                                        <th>Level</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals dan JavaScript akan dilanjutkan -->
 <!-- Modal Tambah Hari Libur -->
<div class="modal fade" id="addLiburModal">
<div class="modal-dialog modal-dialog-centered">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Tambah Hari Libur</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
           </div>
           <form id="liburForm">
               <div class="modal-body">
               <div class="mb-3">
    <div class="input-group">
        <input type="text" class="form-control" id="searchLocation" placeholder="Cari lokasi...">
        <button class="btn btn-primary" type="button" onclick="searchLocation()">Cari</button>
    </div>
</div>
                   <div class="mb-3">
                       <label>Tanggal</label>
                       <input type="date" class="form-control" id="tanggalLibur" required>
                   </div>
                   <div class="mb-3">
                       <label>Keterangan</label>
                       <input type="text" class="form-control" id="keteranganLibur" required>
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

<!-- Modal Tambah Lokasi -->
<div class="modal fade" id="addLokasiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Tambah Lokasi Absen</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
           </div>
           <form id="lokasiForm">
               <div class="modal-body">
                   <div class="mb-3">
                       <label>Nama Lokasi</label>
                       <input type="text" class="form-control" id="namaLokasi" required>
                   </div>
                   <div class="mb-3">
                       <div id="map" style="height: 400px;"></div>
                   </div>
                   <div class="row">
                       <div class="col-md-4">
                           <div class="mb-3">
                               <label>Latitude</label>
                               <input type="text" class="form-control" id="latitude" readonly required>
                           </div>
                       </div>
                       <div class="col-md-4">
                           <div class="mb-3">
                               <label>Longitude</label>
                               <input type="text" class="form-control" id="longitude" readonly required>
                           </div>
                       </div>
                       <div class="col-md-4">
                           <div class="mb-3">
                               <label>Radius (meter)</label>
                               <input type="number" class="form-control" id="radius" required>
                           </div>
                       </div>
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

<!-- Modal Tambah Admin -->
<div class="modal fade" id="addAdminModal">
<div class="modal-dialog modal-dialog-centered">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title">Tambah User Admin</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
           </div>
           <form id="adminForm">
               <div class="modal-body">
                   <div class="mb-3">
                       <label>Username</label>
                       <input type="text" class="form-control" id="username" required>
                   </div>
                   <div class="mb-3">
                       <label>Nama Lengkap</label>
                       <input type="text" class="form-control" id="namaAdmin" required>
                   </div>
                   <div class="mb-3">
                       <label>Password</label>
                       <input type="password" class="form-control" id="password" required>
                   </div>
                   <div class="mb-3">
                       <label>Level</label>
                       <select class="form-select" id="level" required>
                           <option value="">Pilih Level</option>
                           <option value="super_admin">Super Admin</option>
                           <option value="admin_unit">Admin Unit</option>
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

<!-- Tambahkan di header.php -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>

<script>
$(document).ready(function() {
   initMap();
   loadHariLibur();
   loadJamKerja();
   loadLokasi();
   loadAdmin();
   
   // Event handlers
   $('#liburForm').on('submit', handleLiburSubmit);
   $('#jamKerjaForm').on('submit', handleJamKerjaSubmit);
   $('#lokasiForm').on('submit', handleLokasiSubmit);
   $('#adminForm').on('submit', handleAdminSubmit);
});

let map;
let marker;

function initMap() {
    map = L.map('map').setView([-6.2088, 106.8456], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    marker = L.marker([-6.2088, 106.8456], {
        draggable: true
    }).addTo(map);
    
    marker.on('dragend', function() {
        const position = marker.getLatLng();
        $('#latitude').val(position.lat);
        $('#longitude').val(position.lng);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        $('#latitude').val(e.latlng.lat);
        $('#longitude').val(e.latlng.lng);
    });
}

function searchLocation() {
    const query = $('#searchLocation').val();
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);
                map.setView([lat, lon], 16);
                marker.setLatLng([lat, lon]);
                $('#latitude').val(lat);
                $('#longitude').val(lon);
            } else {
                alert('Lokasi tidak ditemukan');
            }
        });
}
// Handling Hari Libur
function loadHariLibur() {
   $.ajax({
       url: 'api/hari_libur.php?action=list',
       method: 'GET',
       success: function(response) {
           if (response.success) {
               updateLiburTable(response.data);
           }
       }
   });
}

function updateLiburTable(data) {
   const tbody = $('#liburTable tbody');
   tbody.empty();
   
   data.forEach(item => {
       tbody.append(`
           <tr>
               <td>${formatDate(item.tanggal)}</td>
               <td>${item.keterangan}</td>
               <td>
                   <button class="btn btn-sm btn-danger" onclick="deleteLibur(${item.id})">
                       <i class='bx bx-trash'></i>
                   </button>
               </td>
           </tr>
       `);
   });
}

function handleLiburSubmit(e) {
   e.preventDefault();
   const data = {
       tanggal: $('#tanggalLibur').val(),
       keterangan: $('#keteranganLibur').val()
   };

   $.ajax({
       url: 'api/hari_libur.php?action=add',
       method: 'POST',
       data: data,
       success: function(response) {
           if (response.success) {
               $('#addLiburModal').modal('hide');
               loadHariLibur();
               showAlert('success', 'Hari libur berhasil ditambahkan');
           }
       }
   });
}

function deleteLibur(id) {
   if (confirm('Yakin ingin menghapus?')) {
       $.ajax({
           url: 'api/hari_libur.php?action=delete',
           method: 'POST',
           data: { id: id },
           success: function(response) {
               if (response.success) {
                   loadHariLibur();
                   showAlert('success', 'Hari libur berhasil dihapus');
               }
           }
       });
   }
}

// Handling Jam Kerja
function loadJamKerja() {
   $.ajax({
       url: 'api/jam_kerja.php?action=get',
       method: 'GET',
       success: function(response) {
           if (response.success) {
               $('#jamMasukNormal').val(response.data.jam_masuk_normal);
               $('#jamPulangNormal').val(response.data.jam_pulang_normal);
               $('#jamMasukJumat').val(response.data.jam_masuk_jumat);
               $('#jamPulangJumat').val(response.data.jam_pulang_jumat);
           }
       }
   });
}

function handleJamKerjaSubmit(e) {
   e.preventDefault();
   const data = {
       jam_masuk_normal: $('#jamMasukNormal').val(),
       jam_pulang_normal: $('#jamPulangNormal').val(),
       jam_masuk_jumat: $('#jamMasukJumat').val(),
       jam_pulang_jumat: $('#jamPulangJumat').val()
   };

   $.ajax({
       url: 'api/jam_kerja.php?action=update',
       method: 'POST',
       data: data,
       success: function(response) {
           if (response.success) {
               showAlert('success', 'Jam kerja berhasil diupdate');
           }
       }
   });
}

// Handling Lokasi
function loadLokasi() {
   $.ajax({
       url: 'api/lokasi.php?action=list',
       method: 'GET',
       success: function(response) {
           if (response.success) {
               updateLokasiTable(response.data);
           }
       }
   });
}

function updateLokasiTable(data) {
   const tbody = $('#lokasiTable tbody');
   tbody.empty();
   
   data.forEach(item => {
       tbody.append(`
           <tr>
               <td>${item.nama_lokasi}</td>
               <td>${item.latitude}</td>
               <td>${item.longitude}</td>
               <td>${item.radius}</td>
               <td>
                   <button class="btn btn-sm btn-info" onclick="editLokasi(${item.id})">
                       <i class='bx bx-edit'></i>
                   </button>
                   <button class="btn btn-sm btn-danger" onclick="deleteLokasi(${item.id})">
                       <i class='bx bx-trash'></i>
                   </button>
               </td>
           </tr>
       `);
   });
}

function handleLokasiSubmit(e) {
   e.preventDefault();
   const data = {
       nama_lokasi: $('#namaLokasi').val(),
       latitude: $('#latitude').val(),
       longitude: $('#longitude').val(),
       radius: $('#radius').val()
   };

   $.ajax({
       url: 'api/lokasi.php?action=add',
       method: 'POST',
       data: data,
       success: function(response) {
           if (response.success) {
               $('#addLokasiModal').modal('hide');
               loadLokasi();
               showAlert('success', 'Lokasi berhasil ditambahkan');
           }
       }
   });
}

// Handling Admin Users
function loadAdmin() {
   $.ajax({
       url: 'api/admin.php?action=list',
       method: 'GET',
       success: function(response) {
           if (response.success) {
               updateAdminTable(response.data);
           }
       }
   });
}

function updateAdminTable(data) {
   const tbody = $('#adminTable tbody');
   tbody.empty();
   
   data.forEach(item => {
       tbody.append(`
           <tr>
               <td>${item.username}</td>
               <td>${item.nama}</td>
               <td>${item.level}</td>
               <td>
                   <span class="badge ${item.status ? 'bg-success' : 'bg-danger'}">
                       ${item.status ? 'Aktif' : 'Non-Aktif'}
                   </span>
               </td>
               <td>
                   <button class="btn btn-sm btn-info" onclick="editAdmin(${item.id})">
                       <i class='bx bx-edit'></i>
                   </button>
                   <button class="btn btn-sm btn-warning" onclick="resetPassword(${item.id})">
                       <i class='bx bx-key'></i>
                   </button>
                   <button class="btn btn-sm ${item.status ? 'btn-danger' : 'btn-success'}" 
                           onclick="toggleStatus(${item.id})">
                       <i class='bx bx-power-off'></i>
                   </button>
               </td>
           </tr>
       `);
   });
}

function handleAdminSubmit(e) {
   e.preventDefault();
   const data = {
       username: $('#username').val(),
       nama: $('#namaAdmin').val(),
       password: $('#password').val(),
       level: $('#level').val()
   };

   $.ajax({
       url: 'api/admin.php?action=add',
       method: 'POST',
       data: data,
       success: function(response) {
           if (response.success) {
               $('#addAdminModal').modal('hide');
               loadAdmin();
               showAlert('success', 'Admin berhasil ditambahkan');
           }
       }
   });
}

function resetPassword(id) {
   if (confirm('Reset password ke default?')) {
       $.ajax({
           url: 'api/admin.php?action=reset_password',
           method: 'POST',
           data: { id: id },
           success: function(response) {
               if (response.success) {
                   showAlert('success', 'Password berhasil direset');
               }
           }
       });
   }
}

function toggleStatus(id) {
   $.ajax({
       url: 'api/admin.php?action=toggle_status',
       method: 'POST',
       data: { id: id },
       success: function(response) {
           if (response.success) {
               loadAdmin();
               showAlert('success', 'Status berhasil diubah');
           }
       }
   });
}

// Utility Functions
function formatDate(date) {
   return new Date(date).toLocaleDateString('id-ID', {
       weekday: 'long',
       year: 'numeric',
       month: 'long',
       day: 'numeric'
   });
}

function showAlert(type, message) {
   const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
   const alert = `
       <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
           ${message}
           <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
       </div>
   `;
   
   $('.card:first').before(alert);
   setTimeout(() => $('.alert').alert('close'), 3000);
}
</script>