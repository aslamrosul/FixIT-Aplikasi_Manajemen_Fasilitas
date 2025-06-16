<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotifikasiController;


//Admin Controllers
use App\Http\Controllers\DashboardAdminController;
// 
use App\Http\Controllers\Admin\LaporanAdminController;

//  manajemen data
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Controllers\Admin\GedungController;
use App\Http\Controllers\Admin\LantaiController;
use App\Http\Controllers\Admin\RuangController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\BarangController;
use App\Http\Controllers\Admin\FasilitasController;

use App\Http\Controllers\Admin\KlasifikasiController;



//Pelapor Controllers
use App\Http\Controllers\DashboardPelaporController;
use App\Http\Controllers\Pelapor\FeedbackController;
use App\Http\Controllers\Pelapor\LaporanPelaporController;
use App\Http\Controllers\Pelapor\RiwayatPelaporController;

//Sarpras Controllers
use App\Http\Controllers\DashboardSarprasController;
use App\Http\Controllers\Sarpras\RekomendasiController;
use App\Http\Controllers\Sarpras\RiwayatPenugasanController;
use App\Http\Controllers\Sarpras\LaporanSarprasController;
use App\Http\Controllers\Sarpras\BobotPrioritasController;
use App\Http\Controllers\Sarpras\KriteriaController;

//Teknisi Controller
use App\Http\Controllers\DashboardTeknisiController;
use App\Http\Controllers\Teknisi\PerbaikanController;
use App\Models\NotifikasiModel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Route::pattern('id', '[0-9]+'); //jika ada parameter id, maka harus berupa angka
// register
Route::get('register', [AuthController::class, 'register'])->name('register');
Route::post('register', [AuthController::class, 'postRegister']);
// login
Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'postlogin']);
//logout
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('landing', function () {
    return view('layouts.landing');
})->name('landing');


Route::middleware(['auth'])->group(function () { //artinya semua route di dalam goup ini harus login dulu
    // masukkan semua route yang perlu autentikasi di sini

    // Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile.index');
        Route::get('/show_ajax', [ProfileController::class, 'show_ajax'])->name('profile.show_ajax');
        Route::get('/edit_ajax', [ProfileController::class, 'edit_ajax'])->name('profile.edit_ajax');
        Route::post('/update_ajax', [ProfileController::class, 'update_ajax'])->name('profile.update_ajax');
        Route::get('/edit_password_ajax', [ProfileController::class, 'edit_password_ajax'])->name('profile.edit_password_ajax');
        Route::post('/update_password_ajax', [ProfileController::class, 'update_password_ajax'])->name('profile.update_password_ajax');
    });

    //route notifikasi
    Route::get('/notifications', function () {
        $notifications = NotifikasiModel::where('user_id', Auth::id())
            ->with('laporan')
            ->orderBy('created_at', 'desc')
            ->take(5) // Limit to 5 notifications
            ->get();

        $unreadCount = NotifikasiModel::where('user_id', Auth::id())
            ->where('dibaca', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    })->middleware('auth')->name('notifications.fetch');

    Route::post('/notifications/mark-as-read/{id}', function ($id) {
        $notification = NotifikasiModel::where('user_id', Auth::id())->findOrFail($id);
        $notification->update(['dibaca' => true]);

        return response()->json(['status' => true, 'message' => 'Notifikasi ditandai sebagai dibaca']);
    })->middleware('auth')->name('notifications.markAsRead');

    Route::get('/', function () {
        return redirect()->to(url('/beranda'));
    });

    Route::get('/notifications/index', function () {
        $breadcrumb = (object) [
            'title' => 'Daftar Notifikasi',
            'list' => ['Home', 'Notifikasi']
        ];

        $page = (object) [
            'title' => 'Daftar semua notifikasi'
        ];

        $activeMenu = 'notifications';

        $notifications = NotifikasiModel::where('user_id', Auth::id())
            ->with('laporan')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('notifications.index', [
            'breadcrumb' => $breadcrumb,
            'page' => $page,
            'activeMenu' => $activeMenu,
            'notifications' => $notifications
        ]);
    })->middleware('auth')->name('notifications.index');

    Route::get('/beranda', function () {
        $user = Auth::user();
        switch ($user->level->level_kode) {
            case 'ADM':
                return redirect()->route('admin.dashboard');
            case 'MHS':
                return redirect()->route('pelapor.dashboard');
            case 'DSN':
                return redirect()->route('pelapor.dashboard');
            case 'TNK':
                return redirect()->route('pelapor.dashboard');
            case 'SPR':
                return redirect()->route('sarpras.dashboard');
            case 'TKN':
                return redirect()->route('teknisi.dashboard');
            default:
                return redirect()->route('login');
        }
    })->name('beranda');




    Route::middleware(['authorize:ADM'])->group(function () {
        Route::prefix('admin')->middleware(['auth'])->group(function () {
            Route::get('/', [DashboardAdminController::class, 'index'])->name('admin.dashboard');
        });        //route user
        Route::group(['prefix' => 'user'], function () {
            Route::get('/', [UserController::class, 'index'])->name('admin.user.index');; // menampilkan halaman awal user
            Route::post('/list', [UserController::class, 'list']); // menampilkan data user dalam bentuk json untuk datables
            Route::get('/create_ajax', [UserController::class, 'create_ajax']); //Menampilkan halaman form tambah user ajax
            Route::post('/ajax', [UserController::class, 'store_ajax']); // Menyimpan data user baru Ajax
            Route::get('/{id}/show_ajax', [UserController::class, 'show_ajax']); // menampilkan detail user ajax
            Route::get('/{id}/edit_ajax', [UserController::class, 'edit_ajax']); //Menampilkan halaman form edit user ajax
            Route::put('/{id}/update_ajax', [UserController::class, 'update_ajax']); // menyimpan perubahan data user ajax
            Route::get('/{id}/delete_ajax', [UserController::class, 'confirm_ajax']); //untuk tampilkan form confirm delete user ajax
            Route::delete('/{id}/delete_ajax', [UserController::class, 'delete_ajax']);

            Route::get('/import', [UserController::class, 'import']); // ajax form upload excel
            Route::post('/import_ajax', [UserController::class, 'import_ajax']); // ajax import excel
            Route::get('/export_excel', [UserController::class, 'export_excel']); //export excel
            Route::get('/export_pdf', [UserController::class, 'export_pdf']);
        });
        Route::prefix('admin')->group(function () {
            Route::get('/dashboard/damage-categories', [DashboardAdminController::class, 'DamageCategories'])->name('admin.dashboard.damage-categories');
            Route::get('/dashboard/priority-stats', [DashboardAdminController::class, 'PriorityStats'])->name('admin.dashboard.priority-stats');
        });
        //route level


        //route gedung
        Route::group(['prefix' => 'gedung'], function (): void {
            Route::get('/', [GedungController::class, 'index'])->name('admin.gedung.index'); // menampilkan halaman awal user
            Route::post('/list', [GedungController::class, 'list']); // menampilkan data user dalam bentuk json untuk datables
            Route::get('/create_ajax', [GedungController::class, 'create_ajax']); //Menampilkan halaman form tambah user ajax
            Route::post('/ajax', [GedungController::class, 'store_ajax']); // Menyimpan data user baru Ajax
            Route::get('/{id}/show_ajax', [GedungController::class, 'show_ajax']); // menampilkan detail user ajax
            Route::get('/{id}/edit_ajax', [GedungController::class, 'edit_ajax']); //Menampilkan halaman form edit user ajax
            Route::put('/{id}/update_ajax', [GedungController::class, 'update_ajax']); // menyimpan perubahan data user ajax
            Route::get('/{id}/delete_ajax', [GedungController::class, 'confirm_ajax']); //untuk tampilkan form confirm delete user ajax
            Route::delete('/{id}/delete_ajax', [GedungController::class, 'delete_ajax']);

            Route::get('/import', [GedungController::class, 'import']); // ajax form upload excel
            Route::post('/import_ajax', [GedungController::class, 'import_ajax']); // ajax import excel
            Route::get('/export_excel', [GedungController::class, 'export_excel']); //export excel
            Route::get('/export_pdf', [GedungController::class, 'export_pdf']);
        });


        //route lantai
        Route::group(['prefix' => 'lantai'], function () {
            Route::get('/', [LantaiController::class, 'index'])->name('admin.lantai.index'); // menampilkan halaman awal user
            Route::post('/list', [LantaiController::class, 'list']); // menampilkan data user dalam bentuk json untuk datables
            Route::get('/create_ajax', [LantaiController::class, 'create_ajax']); //Menampilkan halaman form tambah user ajax
            Route::post('/store_ajax', [LantaiController::class, 'store_ajax']); // Menyimpan data user baru Ajax
            Route::get('/{id}/show_ajax', [LantaiController::class, 'show_ajax']); // menampilkan detail user ajax
            Route::get('/{id}/edit_ajax', [LantaiController::class, 'edit_ajax']); //Menampilkan halaman form edit user ajax
            Route::put('/{id}/update_ajax', [LantaiController::class, 'update_ajax']); // menyimpan perubahan data user ajax
            Route::get('/{id}/delete_ajax', [LantaiController::class, 'confirm_ajax']); //untuk tampilkan form confirm delete user ajax
            Route::delete('/{id}/delete_ajax', [LantaiController::class, 'delete_ajax']);

            Route::get('/import', [LantaiController::class, 'import']); // ajax form upload excel
            Route::post('/import_ajax', [LantaiController::class, 'import_ajax']); // ajax import excel
            Route::get('/export_excel', [LantaiController::class, 'export_excel']); //export excel
            Route::get('/export_pdf', [LantaiController::class, 'export_pdf']);
        });

        //route ruang
        Route::group(['prefix' => 'ruang'], function () {
            Route::get('/', [RuangController::class, 'index'])->name('admin.ruang.index');; // menampilkan halaman awal user
            Route::post('/list', [RuangController::class, 'list']); // menampilkan data user dalam bentuk json untuk datables
            Route::get('/create_ajax', [RuangController::class, 'create_ajax']); //Menampilkan halaman form tambah user ajax
            Route::post('/ajax', [RuangController::class, 'store_ajax']); // Menyimpan data user baru Ajax
            Route::get('/{id}/show_ajax', [RuangController::class, 'show_ajax']); // menampilkan detail user ajax
            Route::get('/{id}/edit_ajax', [RuangController::class, 'edit_ajax']); //Menampilkan halaman form edit user ajax
            Route::put('/{id}/update_ajax', [RuangController::class, 'update_ajax']); // menyimpan perubahan data user ajax
            Route::get('/{id}/delete_ajax', [RuangController::class, 'confirm_ajax']); //untuk tampilkan form confirm delete user ajax
            Route::delete('/{id}/delete_ajax', [RuangController::class, 'delete_ajax']);

            Route::get('/import', [RuangController::class, 'import']); // ajax form upload excel
            Route::post('/import_ajax', [RuangController::class, 'import_ajax']); // ajax import excel
            Route::get('/export_excel', [RuangController::class, 'export_excel']); //export excel
            Route::get('/export_pdf', [RuangController::class, 'export_pdf']);
        });


        //route periode
        Route::group(['prefix' => 'periode'], function () {
            Route::get('/', [PeriodeController::class, 'index'])->name('admin.periode.index');; // menampilkan halaman awal user
            Route::post('/list', [PeriodeController::class, 'list']); // menampilkan data user dalam bentuk json untuk datables
            Route::get('/create_ajax', [PeriodeController::class, 'create_ajax']); //Menampilkan halaman form tambah user ajax
            Route::post('/ajax', [PeriodeController::class, 'store_ajax']); // Menyimpan data user baru Ajax
            Route::get('/{id}/show_ajax', [PeriodeController::class, 'show_ajax']); // menampilkan detail user ajax
            Route::get('/{id}/edit_ajax', [PeriodeController::class, 'edit_ajax']); //Menampilkan halaman form edit user ajax
            Route::put('/{id}/update_ajax', [PeriodeController::class, 'update_ajax']); // menyimpan perubahan data user ajax
            Route::get('/{id}/delete_ajax', [PeriodeController::class, 'confirm_ajax']); //untuk tampilkan form confirm delete user ajax
            Route::delete('/{id}/delete_ajax', [PeriodeController::class, 'delete_ajax']);

            Route::get('/import', [PeriodeController::class, 'import']); // ajax form upload excel
            Route::post('/import_ajax', [PeriodeController::class, 'import_ajax']); // ajax import excel
            Route::get('/export_excel', [PeriodeController::class, 'export_excel']); //export excel
            Route::get('/export_pdf', [PeriodeController::class, 'export_pdf']);
        });

        //route kategori
        Route::group(['prefix' => 'kategori'], function () {
            Route::get('/', [KategoriController::class, 'index'])->name('admin.kategori.index');; // menampilkan halaman awal user
            Route::post('/list', [KategoriController::class, 'list']); // menampilkan data user dalam bentuk json untuk datables
            Route::get('/create_ajax', [KategoriController::class, 'create_ajax']); //Menampilkan halaman form tambah user ajax
            Route::post('/ajax', [KategoriController::class, 'store_ajax']); // Menyimpan data user baru Ajax
            Route::get('/{id}/show_ajax', [KategoriController::class, 'show_ajax']); // menampilkan detail user ajax
            Route::get('/{id}/edit_ajax', [KategoriController::class, 'edit_ajax']); //Menampilkan halaman form edit user ajax
            Route::put('/{id}/update_ajax', [KategoriController::class, 'update_ajax']); // menyimpan perubahan data user ajax
            Route::get('/{id}/delete_ajax', [KategoriController::class, 'confirm_ajax']); //untuk tampilkan form confirm delete user ajax
            Route::delete('/{id}/delete_ajax', [KategoriController::class, 'delete_ajax']);

            Route::get('/import', [KategoriController::class, 'import']); // ajax form upload excel
            Route::post('/import_ajax', [KategoriController::class, 'import_ajax']); // ajax import excel
            Route::get('/export_excel', [KategoriController::class, 'export_excel']); //export excel
            Route::get('/export_pdf', [KategoriController::class, 'export_pdf']);
        });

        //route periode
        Route::group(['prefix' => 'barang'], function () {
            Route::get('/', [BarangController::class, 'index'])->name('admin.barang.index');; // menampilkan halaman awal user
            Route::post('/list', [BarangController::class, 'list']); // menampilkan data user dalam bentuk json untuk datables
            Route::get('/create_ajax', [BarangController::class, 'create_ajax']); //Menampilkan halaman form tambah user ajax
            Route::post('/ajax', [BarangController::class, 'store_ajax']); // Menyimpan data user baru Ajax
            Route::get('/{id}/show_ajax', [BarangController::class, 'show_ajax']); // menampilkan detail user ajax
            Route::get('/{id}/edit_ajax', [BarangController::class, 'edit_ajax']); //Menampilkan halaman form edit user ajax
            Route::put('/{id}/update_ajax', [BarangController::class, 'update_ajax']); // menyimpan perubahan data user ajax
            Route::get('/{id}/delete_ajax', [BarangController::class, 'confirm_ajax']); //untuk tampilkan form confirm delete user ajax
            Route::delete('/{id}/delete_ajax', [BarangController::class, 'delete_ajax']);

            Route::get('/import', [BarangController::class, 'import']); // ajax form upload excel
            Route::post('/import_ajax', [BarangController::class, 'import_ajax']); // ajax import excel
            Route::get('/export_excel', [BarangController::class, 'export_excel']); //export excel
            Route::get('/export_pdf', [BarangController::class, 'export_pdf']);
        });

        //route fasilitas
        Route::group(['prefix' => 'fasilitas'], function () {
            Route::get('/', [FasilitasController::class, 'index'])->name('admin.fasilitas.index');; // menampilkan halaman awal user
            Route::post('/list', [FasilitasController::class, 'list']); // menampilkan data user dalam bentuk json untuk datables
            Route::get('/create_ajax', [FasilitasController::class, 'create_ajax']); //Menampilkan halaman form tambah user ajax
            Route::post('/ajax', [FasilitasController::class, 'store_ajax']); // Menyimpan data user baru Ajax
            Route::get('/{id}/show_ajax', [FasilitasController::class, 'show_ajax']); // menampilkan detail user ajax
            Route::get('/{id}/edit_ajax', [FasilitasController::class, 'edit_ajax']); //Menampilkan halaman form edit user ajax
            Route::put('/{id}/update_ajax', [FasilitasController::class, 'update_ajax']); // menyimpan perubahan data user ajax
            Route::get('/{id}/delete_ajax', [FasilitasController::class, 'confirm_ajax']); //untuk tampilkan form confirm delete user ajax
            Route::delete('/{id}/delete_ajax', [FasilitasController::class, 'delete_ajax']);

            Route::get('/import', [FasilitasController::class, 'import']); // ajax form upload excel
            Route::post('/import_ajax', [FasilitasController::class, 'import_ajax']); // ajax import excel
            Route::get('/export_excel', [FasilitasController::class, 'export_excel']); //export excel
            Route::get('/export_pdf', [FasilitasController::class, 'export_pdf']);
        });



        //route klasifikasi
        Route::group(['prefix' => 'klasifikasi'], function () {
            Route::get('/', [KlasifikasiController::class, 'index'])->name('admin.klasifikasi.index');; // menampilkan halaman awal user
            Route::post('/list', [KlasifikasiController::class, 'list']); // menampilkan data user dalam bentuk json untuk datables
            Route::get('/create_ajax', [KlasifikasiController::class, 'create_ajax']); //Menampilkan halaman form tambah user ajax
            Route::post('/ajax', [KlasifikasiController::class, 'store_ajax']); // Menyimpan data user baru Ajax
            Route::get('/{id}/show_ajax', [KlasifikasiController::class, 'show_ajax']); // menampilkan detail user ajax
            Route::get('/{id}/edit_ajax', [KlasifikasiController::class, 'edit_ajax']); //Menampilkan halaman form edit user ajax
            Route::put('/{id}/update_ajax', [KlasifikasiController::class, 'update_ajax']); // menyimpan perubahan data user ajax
            Route::get('/{id}/delete_ajax', [KlasifikasiController::class, 'confirm_ajax']); //untuk tampilkan form confirm delete user ajax
            Route::delete('/{id}/delete_ajax', [KlasifikasiController::class, 'delete_ajax']);
            Route::get('/import', [KlasifikasiController::class, 'import']); // ajax form upload excel
            Route::post('/import_ajax', [KlasifikasiController::class, 'import_ajax']); // ajax import excel
            Route::get('/export_excel', [KlasifikasiController::class, 'export_excel']); //export excel
            Route::get('/export_pdf', [KlasifikasiController::class, 'export_pdf']);
        });



        Route::group(['prefix' => 'laporan-admin'], function () {
            Route::get('/', [LaporanAdminController::class, 'index'])->name('admin.laporan.index');
            Route::post('/list', [LaporanAdminController::class, 'list'])->name('admin.laporan.list'); // datatables
            // Optional: AJAX modal actions
            Route::get('/{laporan_id}/show_ajax', [LaporanAdminController::class, 'show_ajax'])->name('laporan.show_ajax');
            Route::get('/{id}/edit_ajax', [LaporanAdminController::class, 'edit_ajax'])->name('admin.laporan.edit_ajax');
            Route::put('/{id}/update_status', [LaporanAdminController::class, 'update_status'])->name('laporan.update_status');
        });


        Route::group(['prefix' => 'statistik-admin'], function () {
            Route::get('/', [PeriodeController::class, 'index'])->name('admin.statistik.index');; // menampilkan halaman awal user

        });
    });

    // Pelapor Mahasiswa, Dosen, Tenga Kependidikan
    Route::middleware(['authorize:MHS,DSN,TNK'])->group(function () {
        Route::prefix('pelapor')->middleware(['auth'])->group(function () {
            Route::get('/', [DashboardPelaporController::class, 'index'])->name('pelapor.dashboard');
        });

        Route::prefix('pelapor/laporan')->group(function () {
            Route::get('/', [LaporanPelaporController::class, 'index'])->name('pelapor.laporan.index');
            Route::post('/list', [LaporanPelaporController::class, 'list'])->name('pelapor.laporan.list');
            Route::get('/{id}/show_ajax', [LaporanPelaporController::class, 'show_ajax'])->name('pelapor.laporan.show_ajax');
            Route::get('/get-lantai/{gedung_id}', [LaporanPelaporController::class, 'getLantai'])->name('pelapor.laporan.get_lantai');
            Route::get('/get-ruang/{lantai_id}', [LaporanPelaporController::class, 'getRuang'])->name('pelapor.laporan.get_ruang');
            Route::get('/get-barang/{ruang_id}', [LaporanPelaporController::class, 'getBarang'])->name('pelapor.laporan.get_barang');
            Route::get('/get-fasilitas/{barang_id}', [LaporanPelaporController::class, 'getFasilitas'])->name('pelapor.laporan.get_fasilitas');
            Route::get('/{id}/confirm_ajax', [LaporanPelaporController::class, 'confirm_ajax'])->name('pelapor.laporan.confirm_ajax'); //untuk tampilkan form confirm delete user ajax
            Route::delete('/{id}/delete_ajax', [LaporanPelaporController::class, 'delete_ajax'])->name('pelapor.laporan.delete_ajax');
            Route::get('/import', [LaporanPelaporController::class, 'import'])->name('pelapor.laporan.import');
            Route::post('/import_ajax', [LaporanPelaporController::class, 'import_ajax'])->name('pelapor.laporan.import_ajax');
            Route::get('/export_excel', [LaporanPelaporController::class, 'export_excel'])->name('pelapor.laporan.export_excel'); //export excel
            Route::get('/export_pdf', [LaporanPelaporController::class, 'export_pdf'])->name('pelapor.laporan.export_pdf');
            Route::get('/riwayat', [RiwayatPelaporController::class, 'index'])->name('pelapor.riwayat.index');
            Route::post('/riwayat/list', [RiwayatPelaporController::class, 'list'])->name('pelapor.riwayat.list');
            route::get('/create', [LaporanPelaporController::class, 'create'])->name('pelapor.laporan.create');
            Route::post('/store', [LaporanPelaporController::class, 'store'])->name('pelapor.laporan.store');
            Route::get('/{id}/edit', [LaporanPelaporController::class, 'edit'])->name('pelapor.laporan.edit');
            Route::put('/{id}/update', [LaporanPelaporController::class, 'update'])->name('pelapor.laporan.update');
        });
    });
    Route::prefix('pelapor/feedback')->group(function () {
        Route::get('/', [FeedbackController::class, 'index'])->name('pelapor.feedback.index');
        Route::post('/list', [FeedbackController::class, 'list'])->name('pelapor.feedback.list');
        Route::get('/{laporan_id}/create', [FeedbackController::class, 'create'])->name('pelapor.feedback.create');
        Route::post('/{laporan_id}/store', [FeedbackController::class, 'store'])->name('pelapor.feedback.store');
        Route::get('/{laporan_id}/show', [FeedbackController::class, 'show'])->name('pelapor.feedback.show');
        Route::get('/{laporan_id}/edit', [FeedbackController::class, 'edit'])->name('pelapor.feedback.edit');
        Route::put('/{laporan_id}/update', [FeedbackController::class, 'update'])->name('pelapor.feedback.update');
        Route::get('/{laporan_id}/confirm', [FeedbackController::class, 'confirm'])->name('pelapor.feedback.confirm');
        Route::delete('/{laporan_id}/delete', [FeedbackController::class, 'delete'])->name('pelapor.feedback.delete');
    });
});

// Sarana Prasarana
Route::middleware(['authorize:SPR'])->group(function () {
    Route::prefix('sarpras')->middleware(['auth'])->group(function () {
        Route::get('/', [DashboardSarprasController::class, 'index'])->name('sarpras.dashboard');
        Route::get('/rekomendasi', [RekomendasiController::class, 'index'])->name('sarpras.rekomendasi.index');
        Route::get('/riwayat', [RiwayatPenugasanController::class, 'index'])->name('sarpras.riwayat.index');
    });

    //route laporan kerusakan
    // Route untuk Sarana Prasarana (Sarpras)
    Route::prefix('sarpras')->group(function () {

        // Route untuk dashboard Sarpras
        Route::prefix('laporan')->group(function () {
            Route::get('/yearly-trend', [DashboardSarprasController::class, 'yearlyTrend'])->name('sarpras.dashboard.yearly-trend');
            Route::get('/priority-facilities', [DashboardSarprasController::class, 'priorityFacilities'])->name('sarpras.dashboard.priority-facilities');
        });

        // Route untuk manajemen laporan kerusakan
        Route::prefix('laporan')->group(function () {
            Route::get('/', [LaporanSarprasController::class, 'index'])->name('sarpras.laporan.index');
            Route::post('/list', [LaporanSarprasController::class, 'list'])->name('laporan.list');
            Route::get('/{id}/show_ajax', [LaporanSarprasController::class, 'show_ajax'])->name('laporan.show');
            Route::get('/{id}/assign_ajax', [LaporanSarprasController::class, 'assign_ajax'])->name('laporan.assign_ajax');
            Route::post('/{id}/assign', [LaporanSarprasController::class, 'assign'])->name('laporan.assign');
            Route::get('/{id}/change_status_ajax', [LaporanSarprasController::class, 'change_status_ajax'])->name('laporan.change_status_ajax');
            Route::put('/{id}/update_status', [LaporanSarprasController::class, 'changeStatus'])->name('laporan.update_status');
            Route::get('/export_excel', [LaporanSarprasController::class, 'export_excel'])->name('laporan.export_excel');
            Route::get('/export_pdf', [LaporanSarprasController::class, 'export_pdf'])->name('laporan.export_pdf');
        });

        Route::prefix('penugasan')->group(function () {
            Route::get('/', [RiwayatPenugasanController::class, 'index'])->name('sarpras.penugasan.index');
            Route::post('/list', [RiwayatPenugasanController::class, 'list'])->name('penugasan.list');
            Route::get('/{id}/show_ajax', [RiwayatPenugasanController::class, 'show_ajax'])->name('penugasan.show_ajax');
            Route::get('/export_excel', [RiwayatPenugasanController::class, 'export_excel'])->name('penugasan.export_excel');
            Route::get('/export_pdf', [RiwayatPenugasanController::class, 'export_pdf'])->name('penugasan.export_pdf');
            Route::get('/create_ajax', [RiwayatPenugasanController::class, 'create_ajax'])->name('penugasan.create_ajax');
            Route::post('/', [RiwayatPenugasanController::class, 'store'])->name('penugasan.store');
        });
    });

    Route::get('/sarpras/kriteria/pairwise', [KriteriaController::class, 'showPairwiseForm'])->name('kriteria.pairwise');
    Route::post('/sarpras/kriteria/pairwise', [KriteriaController::class, 'updatePairwise'])->name('kriteria.updatePairwise');

    // Route untuk rekomendasi
    // Route untuk rekomendasi
    Route::prefix('sarpras/rekomendasi')->group(function () {
        Route::get('/', [RekomendasiController::class, 'index'])->name('sarpras.rekomendasi.index');
        Route::post('/list', [RekomendasiController::class, 'list'])->name('sarpras.rekomendasi.list');
        Route::get('/{id}/show_ajax', [RekomendasiController::class, 'show'])->name('sarpras.rekomendasi.show');
        Route::get('/{id}/detail-perhitungan', [RekomendasiController::class, 'showDetailPerhitungan'])->name('sarpras.rekomendasi.detail-perhitungan');
        Route::get('/export_excel', [RekomendasiController::class, 'export_excel'])->name('sarpras.rekomendasi.export_excel');
        Route::get('/export_pdf', [RekomendasiController::class, 'export_pdf'])->name('sarpras.rekomendasi.export_pdf');
        Route::post('/recalculate', [LaporanSarprasController::class, 'recalculateRecommendations'])->name('sarpras.rekomendasi.recalculate');
    });

    // Route untuk rekomendasi mahasiswa
    Route::prefix('sarpras/rekomendasi-mahasiswa')->group(function () {
        Route::get('/', [RekomendasiController::class, 'indexMahasiswa'])->name('sarpras.rekomendasi-mahasiswa.index');
        Route::post('/list', [RekomendasiController::class, 'listMahasiswa'])->name('sarpras.rekomendasi-mahasiswa.list');
        Route::get('/{id}/show_ajax', [RekomendasiController::class, 'show'])->name('sarpras.rekomendasi-mahasiswa.show');
        Route::get('/export_excel', [RekomendasiController::class, 'export_excel_mahasiswa'])->name('sarpras.rekomendasi-mahasiswa.export_excel');
        Route::get('/export_pdf', [RekomendasiController::class, 'export_pdf_mahasiswa'])->name('sarpras.rekomendasi-mahasiswa.export_pdf');
        Route::post('/recalculate', [LaporanSarprasController::class, 'recalculateRecommendations'])->name('sarpras.rekomendasi-mahasiswa.recalculate');
    });

    // Route untuk rekomendasi dosen
    Route::prefix('sarpras/rekomendasi-dosen')->group(function () {
        Route::get('/', [RekomendasiController::class, 'indexDosen'])->name('sarpras.rekomendasi-dosen.index');
        Route::post('/list', [RekomendasiController::class, 'listDosen'])->name('sarpras.rekomendasi-dosen.list');
        Route::get('/{id}/show_ajax', [RekomendasiController::class, 'show'])->name('sarpras.rekomendasi-dosen.show');
        Route::get('/export_excel', [RekomendasiController::class, 'export_excel_dosen'])->name('sarpras.rekomendasi-dosen.export_excel');
        Route::get('/export_pdf', [RekomendasiController::class, 'export_pdf_dosen'])->name('sarpras.rekomendasi-dosen.export_pdf');
        Route::post('/recalculate', [LaporanSarprasController::class, 'recalculateRecommendations'])->name('sarpras.rekomendasi-dosen.recalculate');
    });

    // Route untuk rekomendasi tendik
    Route::prefix('sarpras/rekomendasi-tendik')->group(function () {
        Route::get('/', [RekomendasiController::class, 'indexTendik'])->name('sarpras.rekomendasi-tendik.index');
        Route::post('/list', [RekomendasiController::class, 'listTendik'])->name('sarpras.rekomendasi-tendik.list');
        Route::get('/{id}/show_ajax', [RekomendasiController::class, 'show'])->name('sarpras.rekomendasi-tendik.show');
        Route::get('/export_excel', [RekomendasiController::class, 'export_excel_tendik'])->name('sarpras.rekomendasi-tendik.export_excel');
        Route::get('/export_pdf', [RekomendasiController::class, 'export_pdf_tendik'])->name('sarpras.rekomendasi-tendik.export_pdf');
        Route::post('/recalculate', [LaporanSarprasController::class, 'recalculateRecommendations'])->name('sarpras.rekomendasi-tendik.recalculate');
    });

    //Bobot Prioritas edit skor
    Route::prefix('sarpras/bobot-prioritas')->group(function () {
        Route::get('/', [BobotPrioritasController::class, 'index'])->name('sarpras.bobot-prioritas.index');
        Route::post('/list', [BobotPrioritasController::class, 'list'])->name('sarpras.bobot-prioritas.list');
        Route::get('/{bobot_id}/edit_ajax', [BobotPrioritasController::class, 'edit_ajax'])->name('sarpras.bobot-prioritas.edit_ajax');
        Route::put('/{bobot_id}/update_ajax', [BobotPrioritasController::class, 'update_ajax'])->name('sarpras.bobot-prioritas.update_ajax');
    });

    //route kriteria
    Route::group(['prefix' => 'sarpras/kriteria'], function () {
        Route::get('/', [KriteriaController::class, 'index'])->name('sarpras.kriteria.index');; // menampilkan halaman awal user
        Route::post('/list', [KriteriaController::class, 'list'])->name('sarpras.kriteria.list'); // menampilkan data user dalam bentuk json untuk datables
        Route::get('/create_ajax', [KriteriaController::class, 'create_ajax']); //Menampilkan halaman form tambah user ajax
        Route::post('/ajax', [KriteriaController::class, 'store_ajax']); // Menyimpan data user baru Ajax
        Route::get('/{id}/show_ajax', [KriteriaController::class, 'show_ajax']); // menampilkan detail user ajax
        Route::get('/{id}/edit_ajax', [KriteriaController::class, 'edit_ajax']); //Menampilkan halaman form edit user ajax
        Route::put('/{id}/update_ajax', [KriteriaController::class, 'update_ajax']); // menyimpan perubahan data user ajax
        Route::get('/{id}/delete_ajax', [KriteriaController::class, 'confirm_ajax']); //untuk tampilkan form confirm delete user ajax
        Route::delete('/{id}/delete_ajax', [KriteriaController::class, 'delete_ajax']);
        Route::get('/import', [KriteriaController::class, 'import']); // ajax form upload excel
        Route::post('/import_ajax', [KriteriaController::class, 'import_ajax']); // ajax import excel
        Route::get('/export_excel', [KriteriaController::class, 'export_excel']); //export excel
        Route::get('/export_pdf', [KriteriaController::class, 'export_pdf']);
    });


    Route::prefix('sarpras')->group(function () {
        Route::get('/dashboard/yearly-trend', [DashboardSarprasController::class, 'YearlyTrend'])->name('sarpras.dashboard.yearly-trend');
        Route::get('/dashboard/priority-facilities', [DashboardSarprasController::class, 'PriorityFacilities'])->name('sarpras.dashboard.priority-facilities');
    });
});

// Teknisi
Route::middleware(['authorize:TKN'])->group(function () {
    Route::prefix('teknisi')->middleware(['auth'])->group(function () {
        Route::get('/', [DashboardTeknisiController::class, 'index'])->name('teknisi.dashboard');
    });
    Route::group(['prefix' => 'teknisi/perbaikan'], function () {
        Route::get('/', [PerbaikanController::class, 'index'])->name('teknisi.perbaikan.index');
        Route::get('/riwayat', [PerbaikanController::class, 'riwayat'])->name(name: 'teknisi.riwayat.index');
        Route::post('/list', [PerbaikanController::class, 'list']);
        Route::post('/list-riwayat', [PerbaikanController::class, 'listRiwayat']);
        Route::get('/{id}/show_ajax', [PerbaikanController::class, 'show_ajax']);
        Route::get('/{id}/edit_ajax', [PerbaikanController::class, 'edit_ajax']);
        Route::put('/{id}/update_ajax', [PerbaikanController::class, 'update_ajax']);

        // Tambahkan route untuk menampilkan foto
        Route::get('/images/perbaikan/{filename}', function ($filename) {
            $path = public_path('images/perbaikan/' . $filename);

            if (!File::exists($path)) {
                abort(404);
            }

            $file = File::get($path);
            $type = File::mimeType($path);

            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);

            return $response;
        })->where('filename', '.*')->name('perbaikan.foto');
    });
});
