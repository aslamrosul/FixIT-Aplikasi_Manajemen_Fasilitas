@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">{{ $page->title }}</h4>
            <div class="card-tools">
                <a href="{{ url('/pelapor/laporan/create') }}" class="btn btn-success">
                    <i class="fa fa-plus"></i> Tambah Laporan
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <!-- Form filter yang disederhanakan -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="status_filter">Filter Status</label>
                    <select class="form-control" id="status_filter">
                        <option value="">Semua Status</option>
                        <option value="menunggu">Menunggu</option>
                        <option value="diterima">Diterima</option>
                        <option value="diproses">Diproses</option>
                        <option value="selesai">Selesai</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tanggal_lapor">Tanggal Laporan</label>
                    <input type="date" class="form-control" id="tanggal_lapor" name="tanggal_lapor">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary mr-2" id="filter_button">Filter</button>
                    <button type="button" class="btn btn-secondary" id="reset_button">Reset</button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="table_laporan">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Periode</th>
                            <th>Fasilitas</th>
                            <th>Tanggal Lapor</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <div id="myModal" class="modal fade" tabindex="-1" aria-hidden="true"></div>
@endsection

@push('js')
    <script>
        jQuery(document).ready(function () {
            // Inisialisasi DataTable
            window.dataLaporan = jQuery('#table_laporan').DataTable({
                serverSide: true,
                ajax: {
                    url: "{{ url('pelapor/laporan/list') }}",
                    type: "POST",
                    data: function (d) {
                        // Tambahkan parameter filter
                        d.status_filter = jQuery('#status_filter').val();
                        d.tanggal_lapor = jQuery('#tanggal_lapor').val();
                    },
                    headers: {
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                    }
                },
                columns: [
                    { data: "DT_RowIndex", className: "text-center", orderable: false, searchable: false },
                    { data: "judul", className: "", orderable: true, searchable: true },
                    { data: "periode.periode_nama", className: "", orderable: true, searchable: true },
                    { data: "fasilitas.fasilitas_nama", className: "", orderable: true, searchable: true },
                    { data: "tanggal_lapor", className: "", orderable: true, searchable: true },
                    {
                        data: "status", 
                        render: function (data) {
                            return renderStatusBadge(data);
                        },
                        className: "", 
                        orderable: true, 
                        searchable: true
                    },
                    { data: "aksi", className: "text-center", orderable: false, searchable: false }
                ]
            });

            // Fungsi untuk filter
            jQuery('#filter_button').click(function() {
                window.dataLaporan.ajax.reload();
            });

            // Fungsi untuk reset filter
            jQuery('#reset_button').click(function() {
                jQuery('#status_filter').val('');
                jQuery('#tanggal_lapor').val('');
                window.dataLaporan.ajax.reload();
            });
        });

        // Fungsi untuk menampilkan badge status
        function renderStatusBadge(status) {
            let badgeClass = '';
            switch(status) {
                case 'menunggu':
                    badgeClass = 'bg-secondary';
                    break;
                case 'diterima':
                    badgeClass = 'bg-primary';
                    break;
                case 'diproses':
                    badgeClass = 'bg-info';
                    break;
                case 'selesai':
                    badgeClass = 'bg-success';
                    break;
                case 'ditolak':
                    badgeClass = 'bg-danger';
                    break;
                default:
                    badgeClass = 'bg-secondary';
            }
            return `<span class="badge ${badgeClass}">${status}</span>`;
        }
    </script>
@endpush