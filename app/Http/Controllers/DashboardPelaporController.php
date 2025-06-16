<?php

namespace App\Http\Controllers;

use App\Models\LaporanModel;
use App\Models\FasilitasModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardPelaporController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Dashboard Pelapor',
            'list' => ['Home', 'Dashboard']
        ];

        $page = (object) [
            'title' => 'Dashboard Pelapor Sistem Pelaporan Fasilitas'
        ];

        $activeMenu = 'dashboard';

        // Get statistics for the reporter's dashboard
        $reportStats = [
            'total' => LaporanModel::where('user_id', Auth::id())->count(),
            'menunggu' => LaporanModel::where('user_id', Auth::id())->where('status', 'menunggu')->count(),
            'diterima' => LaporanModel::where('user_id', Auth::id())->where('status', 'diterima')->count(),
            'diproses' => LaporanModel::where('user_id', Auth::id())->where('status', 'diproses')->count(),
            'selesai' => LaporanModel::where('user_id', Auth::id())->where('status', 'selesai')->count(),
            'ditolak' => LaporanModel::where('user_id', Auth::id())->where('status', 'ditolak')->count(),
        ];

        // Get available facilities
        $facilities = FasilitasModel::with(['ruang', 'barang'])
            ->orderBy('fasilitas_nama', 'asc')
            ->get();

        // Get reports with feedback for the logged-in user
        $reportsWithFeedback = LaporanModel::with(['feedback'])
            ->where('user_id', Auth::id())
            ->orderBy('status', 'desc')
            ->get();

        return view('pelapor.dashboard', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'activeMenu' => $activeMenu,
            'reportStats' => $reportStats,
            'facilities' => $facilities,
            'reportsWithFeedback' => $reportsWithFeedback
        ]);
    }
}