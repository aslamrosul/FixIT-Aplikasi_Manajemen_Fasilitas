@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">{{ $page->title }}</h4>
            <div class="card-tools">
                <a href="{{ url('/sarpras/laporan/export_excel') }}" class="btn btn-primary">
                    <i class="bi bi-file-excel"></i> Export Excel
                </a>
                <a href="{{ url('/sarpras/laporan/export_pdf') }}" class="btn btn-warning">
                    <i class="bi bi-file-pdf"></i> Export PDF
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
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">
                        <label class="col-1 control-label col-form-label">Filter:</label>
                        <div class="col-2">
                            <select class="form-control" id="periode_id" name="periode_id">
                                <option value="">- Semua Periode -</option>
                                @foreach($periodes as $item)
                                    <option value="{{ $item->periode_id }}">{{ $item->periode_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2">
                            <select class="form-control" id="barang_id" name="barang_id">
                                <option value="">- Semua Barang -</option>
                                @foreach($barangs as $item)
                                    <option value="{{ $item->barang_id }}">{{ $item->barang_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2">
                            <select class="form-control" id="bobot_id" name="bobot_id">
                                <option value="">- Semua Prioritas -</option>
                                @foreach($prioritas as $item)
                                    <option value="{{ $item->bobot_id }}">{{ $item->bobot_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2">
                            <select class="form-control" id="status" name="status">
                                <option value="">- Semua Status -</option>
                                <option value="menunggu">Menunggu</option>
                                <option value="diterima">Diterima</option>
                                <option value="diproses">Diproses</option>
                                <option value="selesai">Selesai</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="table_laporan">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Pelapor</th>
                            <th>Fasilitas</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Tanggal Lapor</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <div id="myModal" class="modal fade" tabindex="-1" aria-hidden="true">
    </div>
@endsection

@push('js')
    <script>
        function modalAction(url = '') {
            $('#myModal').load(url, function() {
                var myModal = new bootstrap.Modal(document.getElementById('myModal'), {
                    keyboard: false,
                    backdrop: 'static'
                });
                myModal.show();
            });
        }
        

        $(document).ready(function() {
            var dataLaporan = $('#table_laporan').DataTable({
                serverSide: true,
                ajax: {
                    url: "{{ url('sarpras/laporan/list') }}",
                    type: "POST",
                    data: function(d) {
                        d.periode_id = $('#periode_id').val();
                        d.fasilitas_id = $('#fasilitas_id').val();
                        d.bobot_id = $('#bobot_id').val();
                        d.status = $('#status').val();
                    }
                },
                columns: [
                    { data: "DT_RowIndex", className: "text-center", orderable: false, searchable: false },
                    { data: "judul", className: "", orderable: true, searchable: true },
                    { data: "user.nama", className: "", orderable: false, searchable: false },
                    { data: "fasilitas.fasilitas_nama", className: "", orderable: false, searchable: false },
                    { data: "bobot_prioritas.bobot_nama", className: "", orderable: false, searchable: false },
                     {
                        data: "status", render: function (data) {
                            return renderStatusBadge(data);
                        },
                        className: "", orderable: true, searchable: true
                    },
                    { data: "tanggal_lapor", className: "text-center", orderable: true, searchable: true },
                    { data: "aksi", className: "", orderable: false, searchable: false }
                ]
            });

            $('#periode_id, #barang_id, #bobot_id, #status').on('change', function() {
                dataLaporan.ajax.reload();
            });
        });
    </script>
@endpush