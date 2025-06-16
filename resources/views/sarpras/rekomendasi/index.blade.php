@extends('layouts.template')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">{{ $page->title }}</h4>
            <div class="card-tools">
                <button onclick="recalculateRecommendations('{{ url('/sarpras/rekomendasi/recalculate') }}')"
                    class="btn btn-success">
                    <i class="bi bi-arrow-repeat"></i> Hitung Ulang Rekomendasi
                </button>
                <a href="{{ url('/sarpras/rekomendasi/export_excel') }}" class="btn btn-primary">
                    <i class="bi bi-file-excel"></i> Export Excel
                </a>
                <a href="{{ url('/sarpras/rekomendasi/export_pdf') }}" class="btn btn-warning">
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

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="table_rekomendasi">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Pelapor</th>
                            <th>Fasilitas</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Skor Total</th>
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
            $('#myModal').load(url, function () {
                var myModal = new bootstrap.Modal(document.getElementById('myModal'), {
                    keyboard: false,
                    backdrop: 'static'
                });
                myModal.show();
            });
        }

        $(document).ready(function () {
            var dataRekomendasi = $('#table_rekomendasi').DataTable({
                serverSide: true,
                ajax: {
                    url: "{{ url('sarpras/rekomendasi/list') }}",
                    type: "POST"
                },
                columns: [
                    { data: "DT_RowIndex", className: "text-center", orderable: false, searchable: false },
                    { data: "judul", className: "", orderable: true, searchable: true },
                    { data: "user.nama", className: "", orderable: false, searchable: false },
                    { data: "fasilitas.fasilitas_nama", className: "", orderable: false, searchable: false },
                    { data: "bobot_prioritas.bobot_nama", className: "", orderable: false, searchable: false },
                    {
                        data: "status",
                        className: "",
                        orderable: true,
                        searchable: true,
                        render: function (data, type, row) {
                            return renderStatusBadge(data);
                        }
                    },
                    { data: "rekomendasi.skor_total", className: "", orderable: true, searchable: false },
                    { data: "tanggal_lapor", className: "text-center", orderable: true, searchable: true },
                    { data: "aksi", className: "", orderable: false, searchable: false }
                ]
            });
        });
    </script>
    <script>
        function recalculateRecommendations(url) {
            if (confirm('Apakah Anda yakin ingin menghitung ulang semua rekomendasi?')) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.status) {
                            alert(response.message);
                            $('#table_rekomendasi').DataTable().ajax.reload();
                        } else {
                            alert('Gagal: ' + response.message);
                        }
                    },
                    error: function (xhr) {
                        alert('Terjadi kesalahan: ' + xhr.responseJSON.message);
                    }
                });
            }
        }
    </script>
@endpush