<?php

namespace App\Http\Controllers\Sarpras;

use App\Http\Controllers\Controller;
use App\Models\BarangModel;
use App\Models\LaporanModel;
use App\Models\PeriodeModel;
use App\Models\FasilitasModel;
use App\Models\BobotPrioritasModel;
use App\Models\KriteriaModel;
use App\Models\NotifikasiModel;
use App\Models\PerbaikanModel;
use App\Models\RekomendasiModel;
use App\Models\RiwayatPenugasanModel;
use App\Models\UserModel;
use App\Services\TopsisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LaporanSarprasController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Kelola Laporan',
            'list' => ['Home', 'Laporan']
        ];

        $page = (object) [
            'title' => 'Daftar laporan kerusakan yang perlu ditangani'
        ];

        $activeMenu = 'laporan';
        $periodes = PeriodeModel::all();
        $fasilitas = FasilitasModel::all();
        $prioritas = BobotPrioritasModel::all();
        $barangs = BarangModel::all();

        return view('sarpras.laporan.index', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'periodes' => $periodes,
            'fasilitas' => $fasilitas,
            'prioritas' => $prioritas,
            'barangs' => $barangs,
            'activeMenu' => $activeMenu

        ]);
    }

    public function list(Request $request)
    {
        $laporan = LaporanModel::with(['user', 'fasilitas', 'bobotPrioritas', 'user'])
            ->select('t_laporan.*');

        // Filter berdasarkan periode
        if ($request->user_Id) {
            $laporan->where('user_id', $request->user_id);
        }

        // Filter berdasarkan fasilitas
        if ($request->fasilitas_id) {
            $laporan->where('fasilitas_id', $request->fasilitas_id);
        }

        // Filter berdasarkan status
        if ($request->status) {
            $laporan->where('status', $request->status);
        }

        // Filter berdasarkan prioritas
        if ($request->bobot_id) {
            $laporan->where('bobot_id', $request->bobot_id);
        }
        $laporan->where('status', '!=', 'diterima');

        //filter berdasarkan barang
        if ($request->barang_id) {
            $laporan->whereHas('fasilitas.barang', function ($query) use ($request) {
                $query->where('barang_id', $request->barang_id);
            });
        }
        return DataTables::of($laporan)
            ->addIndexColumn()
            ->addColumn('aksi', function ($laporan) {
                $btn = '<button onclick="modalAction(\'' . url('/sarpras/laporan/' . $laporan->laporan_id . '/show_ajax') . '\')" class="btn btn-info btn-sm me-1"><i class="fa fa-eye"></i></button>';



                // Tombol Ubah Status (jika status bukan 'selesai' atau 'ditolak')
                if (!in_array($laporan->status, ['diterima', 'ditolak', 'selesai'])) {
                    $btn .= '<button onclick="modalAction(\'' . url('/sarpras/laporan/' . $laporan->laporan_id . '/change_status_ajax') . '\')" class="btn btn-primary btn-sm">
                    <i class="fa fa-edit"></i>
                </button>';
                }

                return $btn;
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show_ajax($id)
    {
        $laporan = LaporanModel::with([
            'periode',
            'fasilitas',
            'fasilitas.ruang',
            'fasilitas.ruang.lantai',
            'fasilitas.ruang.lantai.gedung',
            'fasilitas.barang',
            'bobotPrioritas',
            'user',
            'perbaikans.teknisi',
            'perbaikans.details'
        ])->find($id);

        if (!$laporan) {
            return view('sarpras.laporan.show_ajax', ['laporan' => null]);
        }

        return view('sarpras.laporan.show_ajax', compact('laporan'));
    }


    public function export_excel()
    {
        $laporan = LaporanModel::with(['periode', 'fasilitas', 'bobotPrioritas', 'user'])
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Judul');
        $sheet->setCellValue('C1', 'Deskripsi');
        $sheet->setCellValue('D1', 'Pelapor');
        $sheet->setCellValue('E1', 'Periode');
        $sheet->setCellValue('F1', 'Fasilitas');
        $sheet->setCellValue('G1', 'Prioritas');
        $sheet->setCellValue('H1', 'Status');
        $sheet->setCellValue('I1', 'Tanggal Lapor');

        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        $no = 1;
        $baris = 2;
        foreach ($laporan as $item) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $item->judul);
            $sheet->setCellValue('C' . $baris, $item->deskripsi);
            $sheet->setCellValue('D' . $baris, $item->user->nama ?? '-');
            $sheet->setCellValue('E' . $baris, $item->periode->periode_nama ?? '-');
            $sheet->setCellValue('F' . $baris, $item->fasilitas->fasilitas_nama ?? '-');
            $sheet->setCellValue('G' . $baris, $item->bobotPrioritas->bobot_nama ?? '-');
            $sheet->setCellValue('H' . $baris, $item->status);
            $sheet->setCellValue('I' . $baris, $item->created_at->format('d-m-Y'));
            $no++;
            $baris++;
        }

        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data Laporan');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data_Laporan_' . date('Y-m-d_H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        $laporan = LaporanModel::with(['periode', 'fasilitas', 'bobotPrioritas', 'user'])
            ->get();

        $data = [
            'laporan' => $laporan,
            'title' => 'Laporan Data Kerusakan'
        ];

        $pdf = Pdf::loadView('sarpras.laporan.export_pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption("isRemoteEnabled", true);
        $pdf->render();

        return $pdf->stream('Data Laporan ' . date('Y-m-d H-i-s') . '.pdf');
    }

    public function change_status_ajax($id)
    {
        $laporan = LaporanModel::find($id);
        if (!$laporan) {
            return response()->json(['error' => 'Laporan tidak ditemukan.'], 404);
        }
        return view('sarpras.laporan.change_status_ajax', compact('laporan'));
    }


    // /**
    //  * Memproses perubahan status laporan.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  int  $id laporan_id
    //  * @return \Illuminate\Http\JsonResponse
    //  */

    public function changeStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:diterima,ditolak,selesai',
            'alasan_penolakan' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'msgField' => $validator->errors()
            ]);
        }

        try {
            $laporan = LaporanModel::findOrFail($id);
            $laporan->update([
                'status' => $request->status,
                'alasan_penolakan' => $request->alasan_penolakan
            ]);

            // Notify the pelapor
            NotifikasiModel::create([
                'judul' => 'Perubahan Status Laporan',
                'pesan' => "Status laporan '{$laporan->judul}' telah diubah menjadi '{$request->status}'." .
                    ($request->alasan_penolakan ? " Alasan: {$request->alasan_penolakan}" : ""),
                'user_id' => $laporan->user_id,
                'laporan_id' => $laporan->laporan_id,
                'tipe' => 'status_laporan',
                'dibaca' => false,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Status laporan berhasil diubah',
                'laporan_id' => $laporan->laporan_id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengubah status laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function tentukanBobotPrioritas($skor)
    {
        if ($skor >= 0.8) return 1; // Tinggi
        if ($skor >= 0.6) return 2; // Sedang
        if ($skor >= 0.4) return 3; // Sedang
        if ($skor >= 0.2) return 4; // Sedang
        if ($skor >= 0.0) return 5; // Sedang
        return 3; // Rendah
    }


    private function hitungSkorPrioritas($laporan)
    {
        return TopsisService::hitungSkorPrioritas($laporan);
    }

    public function recalculateRecommendations(Request $request, $level_id = null)
    {
        try {
            $laporans = LaporanModel::with(['fasilitas', 'fasilitas.barang', 'fasilitas.barang.klasifikasi'])
                ->where('status', 'diterima')
                ->when($level_id, function ($query, $level_id) {
                    $query->whereHas('user', function ($q) use ($level_id) {
                        $q->where('level_id', $level_id);
                    });
                })
                ->get();

            if ($laporans->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada laporan dengan status diterima untuk dihitung ulang'
                ], 404);
            }

            $results = [];
            foreach ($laporans as $laporan) {
                $skor = $this->hitungSkorPrioritas($laporan);
                $bobotId = $this->tentukanBobotPrioritas($skor['skor_total']);

                $nilaiKriteria = $skor['nilai_kriteria'] ?? [
                    'frekuensi' => 0,
                    'usia' => 0,
                    'kondisi' => 0,
                    'barang' => 0,
                    'klasifikasi' => 0
                ];
                $skorTotal = $skor['skor_total'] ?? 0;

                $jsonKriteria = json_encode($nilaiKriteria);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON encoding failed for nilai_kriteria', [
                        'nilai_kriteria' => $nilaiKriteria,
                        'error' => json_last_error_msg()
                    ]);
                    throw new \Exception('Gagal mengencode nilai_kriteria: ' . json_last_error_msg());
                }

                Log::info('Data to be saved for laporan_id: ' . $laporan->laporan_id, [
                    'nilai_kriteria' => $nilaiKriteria,
                    'skor_total' => $skorTotal,
                    'bobot_id' => $bobotId
                ]);

                $rekomendasi = RekomendasiModel::updateOrCreate(
                    ['laporan_id' => $laporan->laporan_id],
                    [
                        'nilai_kriteria' => $jsonKriteria,
                        'skor_total' => $skorTotal,
                        'bobot_id' => $bobotId
                    ]
                );

                Log::info('Rekomendasi saved for laporan_id: ' . $laporan->laporan_id, [
                    'rekomendasi_id' => $rekomendasi->rekomendasi_id,
                    'nilai_kriteria' => $rekomendasi->nilai_kriteria,
                    'skor_total' => $rekomendasi->skor_total,
                    'bobot_id' => $rekomendasi->bobot_id
                ]);

                $laporan->update(['bobot_id' => $bobotId]);

                $results[] = [
                    'laporan_id' => $laporan->laporan_id,
                    'skor_total' => $skorTotal,
                    'prioritas' => BobotPrioritasModel::find($bobotId)->bobot_nama ?? 'Tidak diketahui'
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Rekomendasi berhasil dihitung ulang',
                'data' => $results
            ], 200);
        } catch (\Exception $e) {
            Log::error('Gagal menghitung ulang rekomendasi', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghitung ulang rekomendasi: ' . $e->getMessage()
            ], 500);
        }
    }
    // Fungsi-fungsi helper sama seperti di RekomendasiController
    public function assign_ajax($id)
    {
        $laporan = LaporanModel::find($id);
        $teknisi = UserModel::whereHas('level', function ($query) {
            $query->where('level_kode', 'TKN'); // Atau pakai where('level_id', 3) jika pakai angka
        })->get();
        return view('sarpras.laporan.assign_ajax', compact('laporan', 'teknisi'));
    }

    public function assign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'teknisi_id' => 'required|exists:m_user,user_id',
            'catatan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'msgField' => $validator->errors()
            ]);
        }

        try {
            // Buat perbaikan baru
            $perbaikan = PerbaikanModel::create([
                'laporan_id' => $id,
                'teknisi_id' => $request->teknisi_id,
                'tanggal_mulai' => now(),
                'status' => 'dalam_antrian',
                'catatan' => $request->catatan
            ]);

            // Update status laporan
            $laporan = LaporanModel::find($id);
            $laporan->update(['status' => 'diproses']);

            // Buat riwayat penugasan
            RiwayatPenugasanModel::create([
                'laporan_id' => $id,
                'teknisi_id' => $request->teknisi_id,
                'sarpras_id' => auth()->user()->user_id, // Assuming authenticated user is sarpras
                'tanggal_penugasan' => now(),
                'status_penugasan' => 'ditugaskan',
                'catatan' => $request->catatan
            ]);

            // Notify the technician
            NotifikasiModel::create([
                'judul' => 'Penugasan Perbaikan',
                'pesan' => "Anda telah ditugaskan untuk memperbaiki laporan '{$laporan->judul}'.",
                'user_id' => $request->teknisi_id,
                'laporan_id' => $id,
                'tipe' => 'penugasan',
                'dibaca' => false,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Teknisi berhasil ditugaskan',
                'id' => $perbaikan->perbaikan_id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menugaskan teknisi: ' . $e->getMessage()
            ]);
        }
    }
}
