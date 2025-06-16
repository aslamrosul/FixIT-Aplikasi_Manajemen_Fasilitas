@extends('layouts.template')

@section('content')
    <div class="row">
        <!-- Header Card -->
        <div class="col-12 mb-4">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Dashboard Sarana Prasarana</h4>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Maintenance Statistics -->
                        <div class="col-lg-4 col-md-6">
                            <div class="stat-box bg-primary text-white">
                                <div class="stat-content">
                                    <h3>{{ $maintenanceStats['total'] }}</h3>
                                    <p>Total Perbaikan</p>
                                    <i class="fa fa-wrench stat-icon"></i>
                                </div>
                                <a href="{{ route('sarpras.laporan.index') }}" class="stat-footer">
                                    More Info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="stat-box bg-warning text-dark">
                                <div class="stat-content">
                                    <h3>{{ $maintenanceStats['ongoing'] }}</h3>
                                    <p>Sedang Diproses</p>
                                    <i class="fa fa-clock-o stat-icon"></i>
                                </div>
                                <a href="{{ route('sarpras.laporan.index', ['status' => 'diproses']) }}"
                                    class="stat-footer">
                                    More Info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="stat-box bg-success text-white">
                                <div class="stat-content">
                                    <h3>{{ $maintenanceStats['completed'] }}</h3>
                                    <p>Selesai</p>
                                    <i class="fa fa-check-circle-o stat-icon"></i>
                                </div>
                                <a href="{{ route('sarpras.laporan.index', ['status' => 'selesai']) }}" class="stat-footer">
                                    More Info <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Facility Conditions Chart -->
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h4 class="card-title">Kondisi Fasilitas</h4>
                </div>
                <div class="card-body">
                    <canvas id="facilityConditionChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Repair Frequency -->
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h4 class="card-title">Frekuensi Perbaikan Fasilitas</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Fasilitas</th>
                                    <th>Jumlah Laporan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($repairFrequency as $facility)
                                    <tr>
                                        <td>{{ $facility->fasilitas_nama }}</td>
                                        <td>{{ $facility->report_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Average Repair Time -->
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h4 class="card-title">Waktu Perbaikan Rata-rata</h4>
                </div>
                <div class="card-body text-center">
                    @if($avgRepairTime->avg_hours)
                        <h1 class="display-4">{{ number_format($avgRepairTime->avg_hours, 1) }} <small>jam</small></h1>
                        <p class="text-muted">Waktu rata-rata dari mulai perbaikan hingga selesai</p>
                    @else
                        <p class="text-muted">Belum ada data waktu perbaikan</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Satisfaction by Facility -->
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h4 class="card-title">Kepuasan Pengguna per Fasilitas</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Fasilitas</th>
                                    <th>Rating Rata-rata</th>
                                    <th>Jumlah Ulasan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($satisfactionByFacility as $item)
                                    <tr>
                                        <td>{{ $item->fasilitas_nama }}</td>
                                        <td>
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= floor($item->avg_rating))
                                                    <i class="fa fa-star text-warning"></i>
                                                @elseif($i - 0.5 <= $item->avg_rating)
                                                    <i class="fa fa-star-half-o text-warning"></i>
                                                @else
                                                    <i class="fa fa-star-o text-warning"></i>
                                                @endif
                                            @endfor
                                            ({{ number_format($item->avg_rating, 1) }})
                                        </td>
                                        <td>{{ $item->feedback_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Long-term Maintenance Planning -->
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h4 class="card-title">Perencanaan Pemeliharaan Jangka Panjang</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h4 class="card-title">Tren Kerusakan Fasilitas (1 Tahun Terakhir)</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="damageTrendsChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card dashboard-card">

                                <div class="card-header">
                                    <h5>Fasilitas Prioritas</h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fasilitas</th>
                                                <th>Frekuensi Kerusakan</th>
                                                <th>Rating Kepuasan</th>
                                                <th>Prioritas</th>
                                            </tr>
                                        </thead>
                                        <tbody id="priorityTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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

        .table-light {
            background-color: #f1f3f5;
        }

        .table-hover tbody tr:hover {
            background-color: #e9ecef;
        }

        .stars i {
            font-size: 1.2rem;
            margin: 0 2px;
        }

        .display-4 small {
            font-size: 1.5rem;
            font-weight: normal;
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .stat-box h3 {
                font-size: 1.5rem;
            }

            .card-title {
                font-size: 1.1rem;
            }

            .display-4 {
                font-size: 2.5rem;
            }
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>

    <script>
        $(document).ready(function () {
            // Add fade-in animation
            $('.dashboard-card, .stat-box').addClass('fade-in');

            // Facility Condition Chart
            var conditionLabels = [];
            var conditionData = [];
            var conditionColors = [];

            @foreach($facilityConditions as $condition)
                conditionLabels.push('{{ $condition->status }}');
                conditionData.push({{ $condition->total }});
                @if($condition->status == 'baik')
                    conditionColors.push('#20c997');
                @elseif($condition->status == 'rusak_ringan')
                    conditionColors.push('#ffc107');
                @elseif($condition->status == 'rusak_berat')
                    conditionColors.push('#dc3545');
                @else
                    conditionColors.push('#6c757d');
                @endif
            @endforeach

                                                    var conditionCtx = document.getElementById('facilityConditionChart').getContext('2d');
            new Chart(conditionCtx, {
                type: 'pie',
                data: {
                    labels: conditionLabels,
                    datasets: [{
                        data: conditionData,
                        backgroundColor: conditionColors,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { size: 12, weight: 'bold' }, padding: 20 }
                        },
                        tooltip: { backgroundColor: 'rgba(0,0,0,0.8)' },
                        title: {
                            display: true,
                            text: 'Kondisi Fasilitas',
                            font: { size: 16 }
                        }
                    }
                }
            });

            // Damage Trends Chart
            var months = [];
            var counts = [];

            @foreach($damageTrends as $trend)
                months.push('{{ date("M Y", mktime(0, 0, 0, $trend->month, 1, $trend->year)) }}');
                counts.push({{ $trend->total }});
            @endforeach

                        var ctx = document.getElementById('damageTrendsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Jumlah Laporan Kerusakan',
                        data: counts,
                        backgroundColor: 'rgba(13, 110, 253, 0.2)',
                        borderColor: '#0d6efd',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0d6efd',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { size: 14, weight: 'bold' }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0,0,0,0.8)'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 },
                            grid: { color: 'rgba(0,0,0,0.1)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });

            // Priority Facilities
            $.get("{{ route('sarpras.dashboard.priority-facilities') }}", function (data) {
                var tableBody = $('#priorityTableBody');
                tableBody.empty();

                data.forEach(function (facility) {
                    // Determine priority badge
                    var priorityBadge = '';
                    if (facility.priority === 'Tinggi') {
                        priorityBadge = '<span class="badge bg-danger">Tinggi</span>';
                    } else if (facility.priority === 'Sedang') {
                        priorityBadge = '<span class="badge bg-warning text-dark">Sedang</span>';
                    } else {
                        priorityBadge = '<span class="badge bg-secondary">Rendah</span>';
                    }

                    tableBody.append(`
                        <tr>
                            <td>${facility.name}</td>
                            <td>${facility.report_count}</td>
                            <td>
                            ${facility.rating_stars}
                            (${facility.avg_rating})
                            </td>
                            <td>${priorityBadge}</td>
                        </tr>
                    `);
                });
            });
        });
    </script>
@endpush