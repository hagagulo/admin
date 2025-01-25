<?php
require_once 'config/session.php';
require_once 'db_config.php';
checkLogin(); // Menggunakan fungsi checkLogin dari session.php Anda
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="height-100 bg-light">
    <div class="container-fluid">
        <!-- Welcome message dengan nama admin dari session -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <h5>Selamat Datang, <?php echo htmlspecialchars($_SESSION['admin_nama']); ?></h5>
        </div>

        <div class="row mt-4">
            <!-- Statistik Cards -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Personil</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-personil">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="bx bx-user fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hadir Hari Ini -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Hadir Hari Ini</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-hadir">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="bx bx-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terlambat -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Terlambat</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-terlambat">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="bx bx-time fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Belum Absen -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Belum Absen</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-belum-absen">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="bx bx-x-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik dan Daftar -->
        <div class="row">
            <!-- Grafik Kehadiran -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Grafik Kehadiran Mingguan</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="kehadiranChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Daftar Belum Absen -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Personil Belum Absen</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group" id="belum-absen-list">
                            <!-- List akan diisi oleh JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadDashboardData();
    setInterval(loadDashboardData, 300000);
});

function initChart(data) {
    const ctx = document.getElementById('kehadiranChart').getContext('2d');
    
    // Hapus chart lama jika ada
    if (window.myChart instanceof Chart) {
        window.myChart.destroy();
    }
    
    window.myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.tanggal),
            datasets: [
                {
                    label: 'Hadir',
                    data: data.map(d => d.hadir),
                    backgroundColor: 'rgb(75, 192, 192)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
                },
                {
                    label: 'Terlambat',
                    data: data.map(d => d.terlambat),
                    backgroundColor: 'rgb(255, 159, 64)',
                    borderColor: 'rgb(255, 159, 64)',
                    borderWidth: 1
                },
                {
                    label: 'Pulang Cepat',
                    data: data.map(d => d.pulang_cepat),
                    backgroundColor: 'rgb(255, 99, 132)',
                    borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Statistik Kehadiran 7 Hari Terakhir'
                }
            }
        }
    });
}


function loadDashboardData() {
    $.ajax({
        url: 'api/dashboard_data.php?action=overview',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data && !data.error) {
                // Update cards
                $('#total-personil').text(data.total_personil || 0);
                $('#total-hadir').text((data.absensi_hari_ini?.total_hadir) || 0);
                $('#total-terlambat').text((data.absensi_hari_ini?.total_terlambat) || 0);
                $('#total-belum-absen').text((data.belum_absen?.length) || 0);
                
                // Update grafik
                if (data.kehadiran_mingguan) {
                    initChart(data.kehadiran_mingguan);
                }
                
                // Update list belum absen
                const list = $('#belum-absen-list');
                list.empty();
                
                if (Array.isArray(data.belum_absen)) {
                    if (data.belum_absen.length > 0) {
                        data.belum_absen.forEach(personil => {
                            list.append(`
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">${personil.nama || 'Nama Tidak Tersedia'}</h6>
                                        <small>${personil.nip_nrp || 'NIP/NRP Tidak Tersedia'}</small>
                                    </div>
                                    <small class="text-muted">${personil.jabatan || 'Jabatan Tidak Tersedia'}</small>
                                </a>
                            `);
                        });
                    } else {
                        list.append('<div class="text-center p-3">Semua personil sudah absen</div>');
                    }
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("Error:", status, error);
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>