# FixIT - Aplikasi Manajemen Fasilitas

<p align="center">
  <img alt="Laravel" src="https://img.shields.io/badge/Laravel-10-FF2D20.svg"/>
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.1+-777BB4.svg"/>
  <img alt="License" src="https://img.shields.io/badge/License-MIT-green.svg"/>
  <img alt="Status" src="https://img.shields.io/badge/Status-Active-brightgreen.svg"/>
</p>

## 📋 Deskripsi Proyek

**FixIT** adalah aplikasi web berbasis web yang dirancang untuk mengelola fasilitas dan maintenance secara terpadu. Aplikasi ini membantu organisasi dalam:

- **Pelaporan Kerusakan Fasilitas** - Memudahkan pengguna melaporkan kerusakan atau masalah pada fasilitas
- **Manajemen Prioritas** - Menggunakan algoritma TOPSIS untuk menentukan prioritas perbaikan berdasarkan berbagai kriteria
- **Pelacakan Perbaikan** - Mengelola status dan progres perbaikan fasilitas
- **Manajemen Aset** - Mencatat dan mengelola aset fasilitas (barang, ruangan, gedung, lantai)
- **Pelaporan Terstruktur** - Menghasilkan laporan untuk analisis dan pengambilan keputusan
- **Sistem Role-Based** - Mendukung berbagai peran pengguna (Admin, Pelapor, Sarpras, Teknisi)

## ✨ Fitur Utama

### 1. **Dashboard Interaktif**
- Dashboard khusus untuk setiap role (Admin, Pelapor, Sarpras, Teknisi)
- Visualisasi data real-time
- Notifikasi untuk pembaruan penting

### 2. **Manajemen Pelaporan**
- Membuat laporan kerusakan fasilitas
- Lampirkan foto sebagai dokumentasi
- Tracking status laporan secara real-time
- Feedback dan komentar pada laporan

### 3. **Sistem Prioritas Berbasis TOPSIS**
- Menganalisis relevansi laporan menggunakan Multi-Criteria Decision Making
- Kriteria: frekuensi masalah, usia fasilitas, kondisi, bobot prioritas barang
- Rekomendasi otomatis untuk penanganan perbaikan

### 4. **Manajemen Perbaikan**
- Penugasan teknisi untuk perbaikan
- Pencatatan detail pekerjaan perbaikan
- Pelacakan biaya dan durasi perbaikan
- Status siklus lengkap perbaikan

### 5. **Master Data Fasilitas**
- Manajemen Gedung (Buildings)
- Manajemen Lantai (Floors)
- Manajemen Ruang (Rooms)
- Manajemen Barang/Aset (Items)
- Kategori dan Klasifikasi Fasilitas

### 6. **Laporan & Analitik**
- Export ke PDF dan Excel
- Analisis riwayat perbaikan
- Rekomendasi pengembangan fasilitas
- Laporan feedback pengguna

## 🛠️ Teknologi yang Digunakan

### Backend
- **Laravel 10** - PHP Web Framework
- **MySQL/PostgreSQL** - Database Management
- **Eloquent ORM** - Object Relational Mapping
- **Laravel Sanctum** - API Authentication

### Frontend
- **Vite** - Modern Build Tool
- **JavaScript/ES6+** - Client-side Logic
- **Blade Template** - Server-side Templating

### Libraries Tambahan
- **barryvdh/laravel-dompdf** - PDF Generation
- **phpoffice/phpspreadsheet** - Excel Export
- **yajra/laravel-datatables** - Data Table Management
- **guzzlehttp/guzzle** - HTTP Client

## 📦 Instalasi

### Prasyarat
- PHP 8.1 atau lebih tinggi
- Composer
- Node.js 16+ dan npm
- Database (MySQL/PostgreSQL)

### Langkah Instalasi

1. **Clone Repository**
```bash
git clone https://github.com/yourusername/FixIT-Aplikasi_Manajemen_Fasilitas.git
cd FixIT-Aplikasi_Manajemen_Fasilitas
```

2. **Install Dependencies PHP**
```bash
composer install
```

3. **Install Dependencies JavaScript**
```bash
npm install
```

4. **Setup Environment**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Konfigurasi Database**
Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fixIt
DB_USERNAME=root
DB_PASSWORD=
```

6. **Jalankan Migrasi Database**
```bash
php artisan migrate
```

7. **Seed Database (Opsional)**
```bash
php artisan db:seed
```

8. **Build Assets**
```bash
npm run build
```

9. **Jalankan Development Server**
```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

## 🚀 Penggunaan

### Mode Development
```bash
# Terminal 1 - PHP Server
php artisan serve

# Terminal 2 - Vite Dev Server
npm run dev
```

### Build untuk Production
```bash
npm run build
php artisan migrate --env=production
```

## 📁 Struktur Direktori

```
app/
├── Http/
│   ├── Controllers/          # Controller Applications
│   │   ├── Admin/
│   │   ├── Pelapor/
│   │   ├── Sarpras/
│   │   └── Teknisi/
│   └── Middleware/           # Custom Middleware
├── Models/                   # Database Models
│   ├── UserModel
│   ├── FasilitasModel
│   ├── LaporanModel
│   ├── PerbaikanModel
│   ├── KriteriaModel
│   ├── PairwiseKriteriaModel
│   └── ... (Models lainnya)
├── Services/                 # Business Logic
│   └── TopsisService        # TOPSIS Algorithm Implementation
└── Providers/               # Service Providers

database/
├── migrations/              # Database Schema
└── seeders/                 # Initial Data

resources/
├── css/                     # Stylesheets
├── js/                      # JavaScript Files
└── views/                   # Blade Templates

routes/
├── web.php                  # Web Routes
├── api.php                  # API Routes
└── console.php             # Console Commands

public/
├── images/                 # Public Images
└── landing-page/          # Landing Page Assets
```

## 🔐 Role & Permissions

Aplikasi mendukung 4 peran utama:

| Role | Deskripsi | Akses Utama |
|------|-----------|-------------|
| **Admin** | Pengelola sistem | Manage semua data, user, settings |
| **Pelapor** | Pelapor kerusakan | Buat laporan, view feedback |
| **Sarpras** | Manager infrastruktur | Assign perbaikan, manage fasilitas |
| **Teknisi** | Pelaksana perbaikan | View penugasan, catat progress |

## 📊 Database Schema

Beberapa tabel utama:

- `t_user` - Data pengguna
- `t_perbaikan` - Data perbaikan
- `t_laporan` - Data laporan kerusakan
- `t_fasilitas` - Data fasilitas
- `m_criteria` - Kriteria TOPSIS
- `m_pairwise_criteria` - Pairwise comparison untuk AHP
- `t_rekomendasi` - Rekomendasi sistem

## 🧪 Testing

Jalankan unit tests:
```bash
php artisan test
```

Jalankan test dengan coverage:
```bash
php artisan test --coverage
```

## 📝 API Documentation

API dapat diakses dengan Bearer Token authentication. Contoh:

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/laporan
```

## 🤝 Kontribusi

Kontribusi sangat diterima! Untuk berkontribusi:

1. Fork repository
2. Buat branch feature (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📜 Lisensi

Proyek ini dilisensikan di bawah lisensi MIT - lihat file [LICENSE](LICENSE) untuk detail.

## 👥 Tim Pengembang

Dikembangkan untuk membantu organisasi mengelola fasilitas mereka dengan lebih efisien.

## 📞 Dukungan & Feedback

Jika Anda menemukan bug atau memiliki saran, silakan [buka issue](https://github.com/yourusername/FixIT-Aplikasi_Manajemen_Fasilitas/issues).

## 📚 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [TOPSIS Method](https://en.wikipedia.org/wiki/TOPSIS)
- [PHP Official](https://www.php.net/)

---

**Dibuat dengan ❤️ untuk manajemen fasilitas yang lebih baik**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
