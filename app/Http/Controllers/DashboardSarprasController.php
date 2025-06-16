<?php

namespace App\Http\Controllers;

use App\Models\BobotPrioritasModel;
use App\Models\LaporanModel;
use App\Models\PerbaikanModel;
use App\Models\FasilitasModel;
use App\Models\FeedbackModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardSarprasController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Dashboard Sarpras',
            'list' => ['Home', 'Dashboard']
        ];

        $page = (object) [
            'title' => 'Dashboard Sarana Prasarana'
        ];

        $activeMenu = 'dashboard';

        // Maintenance statistics
        $maintenanceStats = [
            'total' => PerbaikanModel::count(),
            'ongoing' => PerbaikanModel::where('status', 'diproses')->count(),
            'completed' => PerbaikanModel::where('status', 'selesai')->count(),
        ];

        // Facility condition statistics
        $facilityConditions = FasilitasModel::select(
            'status',
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('status')
            ->get();

        // Repair frequency by facility
        $repairFrequency = LaporanModel::select(
            'm_fasilitas.fasilitas_nama',
            DB::raw('COUNT(t_laporan.laporan_id) as report_count')
        )
            ->join('m_fasilitas', 'm_fasilitas.fasilitas_id', '=', 't_laporan.fasilitas_id')
            ->groupBy('m_fasilitas.fasilitas_nama')
            ->orderBy('report_count', 'desc')
            ->limit(5)
            ->get();

        // Average repair time
        $avgRepairTime = PerbaikanModel::select(
            DB::raw('AVG(TIMESTAMPDIFF(HOUR, tanggal_mulai, tanggal_selesai)) as avg_hours')
        )
            ->whereNotNull('tanggal_selesai')
            ->first();

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


        // User satisfaction by facility type
        $satisfactionByFacility = FeedbackModel::select(
            'm_fasilitas.fasilitas_nama',
            DB::raw('AVG(t_feedback.rating) as avg_rating'),
            DB::raw('COUNT(t_feedback.feedback_id) as feedback_count')
        )
            ->join('t_laporan', 't_laporan.laporan_id', '=', 't_feedback.laporan_id')
            ->join('m_fasilitas', 'm_fasilitas.fasilitas_id', '=', 't_laporan.fasilitas_id')
            ->groupBy('m_fasilitas.fasilitas_nama')
            ->orderBy('avg_rating', 'desc')
            ->get();



        return view('sarpras.dashboard', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'activeMenu' => $activeMenu,
            'maintenanceStats' => $maintenanceStats,
            'facilityConditions' => $facilityConditions,
            'repairFrequency' => $repairFrequency,
            'avgRepairTime' => $avgRepairTime,
            'damageTrends' => $damageTrends,
            'satisfactionByFacility' => $satisfactionByFacility
        ]);
    }

    

    public function priorityFacilities(Request $request)
    {
        $barang = LaporanModel::select(
            'm_barang.barang_nama as name',
            DB::raw('COUNT(t_laporan.laporan_id) as report_count'),
            DB::raw('AVG(t_feedback.rating) as avg_rating'),
            DB::raw('CONCAT(REPEAT("★ ", FLOOR(AVG(t_feedback.rating))), REPEAT("☆ ", 5 - FLOOR(AVG(t_feedback.rating)))) as rating_stars'),
            't_laporan.bobot_id'
        )
        ->join('m_fasilitas', 'm_fasilitas.fasilitas_id', '=', 't_laporan.fasilitas_id')
        ->join('m_barang', 'm_barang.barang_id', '=', 'm_fasilitas.barang_id')
        ->leftJoin('t_feedback', 't_feedback.laporan_id', '=', 't_laporan.laporan_id')
        ->groupBy('m_barang.barang_nama', 't_laporan.bobot_id')
        ->orderBy('report_count', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($barang) {
            $bobot = BobotPrioritasModel::find($barang->bobot_id);
            $priority = 'Rendah';
            if ($barang->report_count >= 10) {
                $priority = 'Tinggi';
            } elseif ($barang->report_count >= 5) {
                $priority = 'Sedang';
            }
            return [
                'name' => $barang->name,
                'report_count' => $barang->report_count,
                'avg_rating' => $barang->avg_rating ? number_format($barang->avg_rating, 1) : 'N/A',
                'rating_stars' => $barang->rating_stars ?? '☆☆☆☆☆',
                'priority' => $bobot ? $bobot->bobot_nama : $priority
            ];
        });

        return response()->json($barang);
    }
}