@extends('layouts.template')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Daftar Notifikasi</h4>
                <a href="{{ url()->previous() }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                @if ($notifications->isEmpty())
                    <p class="text-center">Tidak ada notifikasi.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Pesan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($notifications as $notif)
                                    <tr>
                                        <td>{{ $notif->judul }}</td>
                                        <td>{{ $notif->pesan }}</td>
                                        <td>{{ $notif->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <span class="badge {{ $notif->dibaca ? 'bg-success' : 'bg-warning' }}">
                                                {{ $notif->dibaca ? 'Dibaca' : 'Belum Dibaca' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $notifications->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
