<?php

namespace App\Http\Controllers\Pelapor;

use App\Http\Controllers\Controller;
use App\Models\BarangModel;
use App\Models\LaporanModel;
use App\Models\PeriodeModel;
use App\Models\FasilitasModel;
use App\Models\BobotPrioritasModel;
use App\Models\GedungModel;
use App\Models\LantaiModel;
use App\Models\NotifikasiModel;
use App\Models\RuangModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class LaporanPelaporController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Daftar Laporan',
            'list' => ['Home', 'Laporan']
        ];

        $page = (object) [
            'title' => 'Daftar laporan yang dibuat oleh pelapor'
        ];

        $activeMenu = 'laporan';

        return view('pelapor.laporan.index', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'activeMenu' => $activeMenu
        ]);
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $query = LaporanModel::where('user_id', Auth::id())
                ->with(['periode', 'fasilitas', 'bobotPrioritas'])
                ->select('t_laporan.*');

            // Filter status jika ada
            if ($request->has('status_filter') && $request->status_filter != '') {
                if ($request->status_filter == 'active') {
                    $query->whereNotIn('status', ['selesai', 'ditolak']);
                } else {
                    $query->where('status', $request->status_filter);
                }
            }

            // Filter berdasarkan tanggal lapor jika ada
            if ($request->has('tanggal_lapor') && $request->tanggal_lapor != '') {
                $query->whereDate('tanggal_lapor', $request->tanggal_lapor);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('tanggal_lapor', function ($laporan) {
                    return $laporan->tanggal_lapor->format('d-m-Y H:i:s');
                })
                ->addColumn('aksi', function ($laporan) {
                    $showUrl = url('/pelapor/laporan/' . $laporan->laporan_id . '/show_ajax');
                    $editUrl = url('/pelapor/laporan/' . $laporan->laporan_id . '/edit');
                    $deleteUrl = url('/pelapor/laporan/' . $laporan->laporan_id . '/confirm_ajax');

                    $buttons = '
                    <button onclick="modalAction(\'' . $showUrl . '\')" class="btn btn-info btn-sm">
                        <i class="fa fa-eye"></i>  
                    </button>
                ';

                    if ($laporan->status !== 'diproses' && $laporan->status !== 'diterima') {
                        $buttons .= '
                    <a href="' . $editUrl . '" class="btn btn-warning btn-sm">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button onclick="modalAction(\'' . $deleteUrl . '\')" class="btn btn-danger btn-sm">
                            <i class="fa fa-trash"></i>  
                        </button>
                    ';
                    }

                    return $buttons;
                })
                ->rawColumns(['aksi', 'status'])
                ->make(true);
        }
    }

    public function create()
    {
        $gedungs = GedungModel::all();

        return view('pelapor.laporan.create', compact('gedungs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|min:3',
            'deskripsi' => 'required|string|min:10',
            'fasilitas_id' => 'required|exists:m_fasilitas,fasilitas_id',
            'foto_path' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        try {
            $periode = PeriodeModel::where('is_aktif', true)->first();

            $data = [
                'user_id' => Auth::id(),
                'periode_id' => $periode->periode_id,
                'fasilitas_id' => $request->fasilitas_id,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'status' => 'Menunggu',
                'tanggal_lapor' => now(),
            ];

            if ($request->hasFile('foto_path')) {
                $data['foto_path'] = $request->file('foto_path')->store('laporan_photos', 'public');
            }

            $laporan = LaporanModel::create($data);
            // Notify all Sarpras (SPR) users
            $sarprasUsers = UserModel::whereHas('level', function ($query) {
                $query->where('level_kode', 'SPR');
            })->get();

            foreach ($sarprasUsers as $sarpras) {
                NotifikasiModel::create([
                    'judul' => 'Laporan Baru Dibuat',
                    'pesan' => "Laporan baru dengan judul '{$request->judul}' telah dibuat oleh pelapor.",
                    'user_id' => $sarpras->user_id,
                    'laporan_id' => $laporan->laporan_id,
                    'tipe' => 'laporan_baru',
                    'dibaca' => false,
                ]);
            }

            return redirect()->route('pelapor.laporan.index')->with('success', 'Laporan berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }
    public function edit($id)
    {
        $laporan = LaporanModel::with(['fasilitas.ruang.lantai.gedung'])->findOrFail($id);
        $gedungs = GedungModel::all();
        $lantais = LantaiModel::where('gedung_id', $laporan->fasilitas->ruang->lantai->gedung_id)->get();
        $ruangs = RuangModel::where('lantai_id', $laporan->fasilitas->ruang->lantai_id)->get();
        $barangs = BarangModel::whereHas('fasilitas', function ($query) use ($laporan) {
            $query->where('ruang_id', $laporan->fasilitas->ruang_id);
        })->get();
        $fasilitas = FasilitasModel::where('ruang_id', $laporan->fasilitas->ruang_id)
            ->where('barang_id', $laporan->fasilitas->barang_id)
            ->get();

        $current_gedung_id = $laporan->fasilitas->ruang->lantai->gedung_id;
        $current_lantai_id = $laporan->fasilitas->ruang->lantai_id;
        $current_ruang_id = $laporan->fasilitas->ruang_id;
        $current_barang_id = $laporan->fasilitas->barang_id;



        $page = (object) [
            'title' => 'Daftar laporan yang dibuat oleh pelapor'
        ];
        return view('pelapor.laporan.edit', compact(
            'laporan',
            'gedungs',
            'lantais',
            'ruangs',
            'barangs',
            'fasilitas',
            'current_gedung_id',
            'current_lantai_id',
            'current_ruang_id',
            'current_barang_id',
            'page',

        ));
    }

    public function update(Request $request, $id)
    {
        $laporan = LaporanModel::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|min:3',
            'deskripsi' => 'required|string|min:10',
            'fasilitas_id' => 'required|exists:m_fasilitas,fasilitas_id',
            'foto_path' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        try {

            $data = [
                'fasilitas_id' => $request->fasilitas_id,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
            ];

            if ($request->hasFile('foto_path')) {
                if ($laporan->foto_path) {
                    Storage::disk('public')->delete($laporan->foto_path);
                }
                $data['foto_path'] = $request->file('foto_path')->store('laporan_photos', 'public');
            }

            $laporan->update($data);

            return redirect()->route('pelapor.laporan.index')->with('success', 'Laporan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui laporan: ' . $e->getMessage());
        }
    }

    public function getLantai($gedung_id)
    {
        $lantais = LantaiModel::where('gedung_id', $gedung_id)->get(['lantai_id', 'lantai_nomor']);
        return response()->json($lantais);
    }

    public function getRuang($lantai_id)
    {
        $ruangs = RuangModel::where('lantai_id', $lantai_id)->get(['ruang_id', 'ruang_nama']);
        return response()->json($ruangs);
    }

    public function getBarang($ruang_id)
    {
        $barangs = BarangModel::whereHas('fasilitas', function ($query) use ($ruang_id) {
            $query->where('ruang_id', $ruang_id);
        })->get(['barang_id', 'barang_nama']);
        return response()->json($barangs);
    }

    public function getFasilitas($barang_id)
    {
        $fasilitas = FasilitasModel::where('barang_id', $barang_id)->get(['fasilitas_id', 'fasilitas_nama']);
        return response()->json($fasilitas);
    }

    public function confirm_ajax($id)
    {
        $laporan = LaporanModel::find($id);
        if (!$laporan) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
        return view('pelapor.laporan.confirm_ajax', compact('laporan'));
    }

    public function delete_ajax($id)
    {
        if (request()->ajax()) {
            $laporan = LaporanModel::find($id);
            if ($laporan) {
                if ($laporan->foto_path) {
                    Storage::disk('public')->delete($laporan->foto_path);
                }
                $laporan->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Laporan berhasil dihapus'
                ]);
            }
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
    }

    public function show_ajax($id)
    {
        $laporan = LaporanModel::with(['periode', 'fasilitas', 'user',])->find($id);
        if (!$laporan) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
        return view('pelapor.laporan.show_ajax', compact('laporan'));
    }

    public function import()
    {
        return view('pelapor.laporan.import');
    }

    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'file_laporan' => ['required', 'mimes:xlsx', 'max:1024']
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            $file = $request->file('file_laporan');
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, false, true, true);
            $insert = [];

            if (count($data) > 1) {
                foreach ($data as $baris => $value) {
                    if ($baris > 1) {
                        $insert[] = [
                            'user_id' => Auth::id(),
                            'periode_id' => trim($value['A']),
                            'fasilitas_id' => trim($value['B']),
                            'judul' => trim($value['C']),
                            'deskripsi' => trim($value['D']),
                            'bobot_id' => trim($value['E']) ?: null,
                            'status' => 'z',
                            'created_at' => now(),
                        ];
                    }
                }

                if (count($insert) > 0) {
                    LaporanModel::insertOrIgnore($insert);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Data laporan berhasil diimport'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Tidak ada data yang bisa diimport'
            ]);
        }

        return redirect('/');
    }

    public function export_excel()
    {
        $laporan = LaporanModel::where('user_id', Auth::id())
            ->with(['periode', 'fasilitas', 'bobotPrioritas'])
            ->select('t_laporan.*')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Judul');
        $sheet->setCellValue('C1', 'Deskripsi');
        $sheet->setCellValue('D1', 'Periode');
        $sheet->setCellValue('E1', 'Fasilitas');
        $sheet->setCellValue('F1', 'Prioritas');
        $sheet->setCellValue('G1', 'Status');
        $sheet->setCellValue('H1', 'Tanggal Lapor');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        $no = 1;
        $baris = 2;
        foreach ($laporan as $item) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $item->judul);
            $sheet->setCellValue('C' . $baris, $item->deskripsi);
            $sheet->setCellValue('D' . $baris, $item->periode->periode_nama ?? '-');
            $sheet->setCellValue('E' . $baris, $item->fasilitas->fasilitas_nama ?? '-');
            $sheet->setCellValue('F' . $baris, $item->bobotPrioritas->bobot_nama ?? '-');
            $sheet->setCellValue('G' . $baris, $item->status);
            $sheet->setCellValue('H' . $baris, $item->tanggal_lapor);
            $no++;
            $baris++;
        }

        foreach (range('A', 'H') as $columnID) {
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
        $laporan = LaporanModel::where('user_id', Auth::id())
            ->with(['periode', 'fasilitas', 'bobotPrioritas'])
            ->select('t_laporan.*')
            ->get();

        $data = [
            'laporan' => $laporan,
            'title' => 'Laporan Data Pelapor'
        ];

        $pdf = Pdf::loadView('pelapor.laporan.export_pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption("isRemoteEnabled", true);
        $pdf->render();

        return $pdf->stream('Data Laporan ' . date('Y-m-d H-i-s') . '.pdf');
    }
}
