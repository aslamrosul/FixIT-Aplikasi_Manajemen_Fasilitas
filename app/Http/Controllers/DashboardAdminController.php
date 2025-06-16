<?php

namespace App\Http\Controllers;

use App\Models\LaporanModel;
use App\Models\FeedbackModel;
use App\Models\PerbaikanModel;
use App\Models\PeriodeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Dashboard Admin',
            'list' => ['Home', 'Dashboard']
        ];

        $page = (object) [
            'title' => 'Dashboard Admin Sistem Pelaporan Fasilitas'
        ];

        $activeMenu = 'dashboard';

        // Get statistics for the dashboard
        $currentPeriod = PeriodeModel::where('is_aktif', 1)->first();

        $reportStats = [
            'total' => LaporanModel::count(),
            'menunggu' => LaporanModel::where('status', 'menunggu')->count(),
            'diterima' => LaporanModel::where('status', 'diterima')->count(),
            'processed' => LaporanModel::where('status', 'diproses')->count(),
            'completed' => LaporanModel::where('status', 'selesai')->count(),
            'rejected' => LaporanModel::where('status', 'ditolak')->count(),
        ];

        // Facility damage trends by category (last 6 months)
        $damageTrends = LaporanModel::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // User satisfaction statistics
        $satisfactionStats = FeedbackModel::select(
            DB::raw('AVG(rating) as average_rating'),
            DB::raw('COUNT(*) as total_feedback')
        )->first();

        // Top 5 most reported facilities
        $topFacilities = LaporanModel::select(
            't_laporan.fasilitas_id', // Explicitly specify which table's column to use
            'm_fasilitas.fasilitas_nama',
            DB::raw('COUNT(*) as total_reports')
        )
            ->join('m_fasilitas', 'm_fasilitas.fasilitas_id', '=', 't_laporan.fasilitas_id')
            ->groupBy('t_laporan.fasilitas_id', 'm_fasilitas.fasilitas_nama')
            ->orderBy('total_reports', 'desc')
            ->limit(5)
            ->get();

             // Kueri untuk rata-rata biaya perbaikan per barang
        $budgetData = PerbaikanModel::select(
            'm_barang.barang_nama',
            DB::raw('COUNT(t_perbaikan.perbaikan_id) as jumlah_perbaikan'),
            DB::raw('AVG(t_perbaikan.total_biaya) as rata_rata_biaya')
        )
            ->join('t_laporan', 't_perbaikan.laporan_id', '=', 't_laporan.laporan_id')
            ->join('m_fasilitas', 't_laporan.fasilitas_id', '=', 'm_fasilitas.fasilitas_id')
            ->join('m_barang', 'm_fasilitas.barang_id', '=', 'm_barang.barang_id')
            ->where('t_perbaikan.status', 'selesai')
            ->groupBy('m_barang.barang_id', 'm_barang.barang_nama')
            ->get();

        return view('admin.dashboard', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'activeMenu' => $activeMenu,
            'currentPeriod' => $currentPeriod,
            'reportStats' => $reportStats,
            'damageTrends' => $damageTrends,
            'satisfactionStats' => $satisfactionStats,
            'topFacilities' => $topFacilities,
            'budgetData' => $budgetData
        ]);

       

    }

    

    public function priorityStats(Request $request)
    {
        $totalLaporan = LaporanModel::count();
        $priorities = LaporanModel::select(
            'm_bobot_prioritas.bobot_nama as priority_name',
            DB::raw('COUNT(t_laporan.laporan_id) as count'),
            DB::raw('ROUND((COUNT(t_laporan.laporan_id) / :total) * 100, 1) as percentage')
        )
            ->join('m_bobot_prioritas', 'm_bobot_prioritas.bobot_id', '=', 't_laporan.bobot_id')
            ->groupBy('m_bobot_prioritas.bobot_nama')
            ->setBindings(['total' => $totalLaporan > 0 ? $totalLaporan : 1]) // Avoid division by zero
            ->orderBy('count', 'desc')
            ->get();

        return response()->json($priorities);
    }
}