@extends('layouts.template')

@section('content')
    <div class="row">
        <!-- Header Card -->
        <div class="col-12 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Dashboard Pelapor</h4>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Report Statistics -->
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="stat-box bg-primary text-white">
                                <div class="stat-content">
                                    <h3>{{ $reportStats['total'] }}</h3>
                                    <p>Total Laporan</p>
                                    <i class="fa fa-file-text-o stat-icon"></i>
                                </div>
                                <a href="{{ url('pelapor/laporan') }}" class="stat-footer">
                                    More Info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="stat-box bg-warning text-dark">
                                <div class="stat-content">
                                    <h3>{{ $reportStats['menunggu'] }}</h3>
                                    <p>Menunggu</p>
                                    <i class="fa fa-clock-o stat-icon"></i>
                                </div>
                                <a href="{{ url('pelapor/laporan?status=menunggu') }}" class="stat-footer">
                                    More Info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="stat-box bg-info text-white">
                                <div class="stat-content">
                                    <h3>{{ $reportStats['diterima'] }}</h3>
                                    <p>Diterima</p>
                                    <i class="fa fa-check stat-icon"></i>
                                </div>
                                <a href="{{ url('pelapor/laporan?status=diterima') }}" class="stat-footer">
                                    More Info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="stat-box bg-danger text-white">
                                <div class="stat-content">
                                    <h3>{{ $reportStats['ditolak'] }}</h3>
                                    <p>Ditolak</p>
                                    <i class="fa fa-times stat-icon"></i>
                                </div>
                                <a href="{{ url('pelapor/laporan?status=ditolak') }}" class="stat-footer">
                                    More Info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="stat-box bg-secondary text-white">
                                <div class="stat-content">
                                    <h3>{{ $reportStats['diproses'] }}</h3>
                                    <p>Diproses</p>
                                    <i class="fa fa-wrench stat-icon"></i>
                                </div>
                                <a href="{{ url('pelapor/laporan?status=diproses') }}" class="stat-footer">
                                    More Info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="stat-box bg-success text-white">
                                <div class="stat-content">
                                    <h3>{{ $reportStats['selesai'] }}</h3>
                                    <p>Selesai</p>
                                    <i class="fa fa-check-circle-o stat-icon"></i>
                                </div>
                                <a href="{{ url('pelapor/laporan?status=selesai') }}" class="stat-footer">
                                    More Info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Facilities List -->
    <div class="row">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Daftar Fasilitas Tersedia</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table_fasilitas">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Fasilitas</th>
                                    <th>Lokasi</th>
                                    <th>Barang</th>
                                    <th>Tahun Pengadaan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($facilities as $index => $facility)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $facility->fasilitas_kode }}</td>
                                        <td>{{ $facility->fasilitas_nama }}</td>
                                        <td>
                                            @if($facility->ruang)
                                                {{ $facility->ruang->lantai->gedung->gedung_nama ?? '-' }},
                                                {{ $facility->ruang->lantai->lantai_nomor ?? '-' }},
                                                Ruang {{ $facility->ruang->ruang_nama ?? '-' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $facility->barang->barang_nama ?? '-' }}</td>
                                        <td>{{ $facility->tahun_pengadaan }}</td>
                                        <td>
                                            <span class="badge bg-{{ $facility->status == 'aktif' ? 'success' : 'danger' }}">
                                                {{ strtoupper($facility->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback List -->
    <div class="row">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Feedback Laporan Anda</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table_feedback">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>ID Laporan</th>
                                    <th>Status Laporan</th>
                                    <th>Rating</th>
                                    <th>Komentar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportsWithFeedback as $index => $report)
                                    @if($report->feedback)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $report->laporan_id }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $report->status == 'selesai' ? 'success' : ($report->status == 'ditolak' ? 'danger' : 'warning') }}">
                                                    {{ strtoupper($report->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($report->feedback->rating)
                                                    {{ $report->feedback->rating }}/5
                                                    <span class="text-warning">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fa fa-star{{ $i <= $report->feedback->rating ? '' : '-o' }}"></i>
                                                        @endfor
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $report->feedback->komentar ?? '-' }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dashboard-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a1a1a;
        }

        .stat-box {
            border-radius: 8px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease;
        }

        .stat-box:hover {
            transform: scale(1.02);
        }

        .stat-content {
            position: relative;
            z-index: 1;
        }

        .stat-box h3 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-box p {
            margin: 0;
            font-size: 1rem;
            opacity: 0.9;
        }

        .stat-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 40px;
            opacity: 0.2;
        }

        .stat-footer {
            display: block;
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.05);
            color: inherit;
            text-align: center;
            border-radius: 0 0 8px 8px;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }

        .stat-footer:hover {
            background-color: rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }

        .badge {
            font-size: 0.85rem;
            padding: 5px 10px;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .stat-box h3 {
                font-size: 1.5rem;
            }

            .card-title {
                font-size: 1.1rem;
            }
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#table_fasilitas').DataTable({
                responsive: true,
                pageLength: 5, // Limit to 5 rows per page
                lengthChange: false, // Disable length change dropdown
                searching: true, // Enable search
                ordering: true, // Enable sorting
                info: true, // Show table information
                paging: true, // Enable pagination
                columnDefs: [
                    { responsivePriority: 1, targets: 0 }, // No
                    { responsivePriority: 2, targets: 2 }, // Nama Fasilitas
                    { responsivePriority: 3, targets: 3 }  // Lokasi
                ],
                language: {
                    paginate: {
                        previous: "«",
                        next: "»"
                    }
                }
            });
            $('#table_feedback').DataTable({
                responsive: true,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 1 },
                    { responsivePriority: 3, targets: 3 }
                ]
            });

        });
    </script>
@endpush