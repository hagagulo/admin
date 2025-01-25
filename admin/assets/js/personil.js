$(document).ready(function() {
    // Inisialisasi DataTable
    const table = $('#absensiTable').DataTable({
        processing: true,
        serverSide: false,
        order: [[3, 'desc']],
        ajax: {
            url: 'api/get_absensi.php',
            type: 'POST',
            data: function(d) {
                d.tanggal_awal = $('#tanggal_awal').val();
                d.tanggal_akhir = $('#tanggal_akhir').val();
                d.unit = $('#unit').val();
                d.status = $('#status').val();
            }
        },
        columns: [
            { data: 'nip_nrp' },
            { data: 'nama' },
            { data: 'unit' },
            { data: 'tanggal' },
            { data: 'jam_masuk' },
            { data: 'jam_pulang' },
            { 
                data: 'keterangan_masuk',
                render: function(data) {
                    let color = 'success';
                    if(data === 'Terlambat') color = 'warning';
                    return `<span class="badge bg-${color}">${data || 'Tepat Waktu'}</span>`;
                }
            },
            { 
                data: 'keterangan_pulang',
                render: function(data) {
                    let color = 'success';
                    if(data === 'Pulang Cepat') color = 'warning';
                    return `<span class="badge bg-${color}">${data || 'Normal'}</span>`;
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-warning editBtn" data-id="${data.id}">
                            <i class="bx bx-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.id}">
                            <i class="bx bx-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    // Filter Form Submit
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    // Export Excel
    $('#exportExcel').click(function() {
        const filters = {
            tanggal_awal: $('#tanggal_awal').val(),
            tanggal_akhir: $('#tanggal_akhir').val(),
            unit: $('#unit').val(),
            status: $('#status').val()
        };

        $.post('api/get_absensi.php', filters, function(response) {
            const ws = XLSX.utils.json_to_sheet(response.data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Absensi");
            XLSX.writeFile(wb, "Laporan_Absensi.xlsx");
        });
    });

    // Submit Absensi Manual
    $('#submitAbsensiManual').click(function() {
        const formData = new FormData($('#formAbsensiManual')[0]);
        
        $.ajax({
            url: 'api/input_absensi_manual.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    $('#inputManualModal').modal('hide');
                    table.ajax.reload();
                    alert('Absensi berhasil disimpan');
                } else {
                    alert(response.message || 'Gagal menyimpan absensi');
                }
            },
            error: function() {
                alert('Terjadi kesalahan sistem');
            }
        });
    });
});