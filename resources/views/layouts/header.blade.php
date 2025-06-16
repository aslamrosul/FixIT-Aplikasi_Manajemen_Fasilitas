<header>
    <nav class="navbar navbar-expand navbar-light navbar-top">
        <div class="container-fluid">
            <a href="#" class="burger-btn d-block">
                <i class="bi bi-justify fs-3"></i>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-lg-0">
                    <!-- Notification and message menus here -->
                    
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link active dropdown-toggle text-gray-600" href="#" data-bs-toggle="dropdown"
                            data-bs-display="static" aria-expanded="false">
                            <i class='bi bi-bell bi-sub fs-4'></i>
                            <span class="badge badge-notification bg-danger" id="unread-count">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown"
                            aria-labelledby="dropdownMenuButton">
                            <li class="dropdown-header">
                                <h6>Notifications</h6>
                            </li>
                            <div id="notification-list">
                                <!-- Notifications will be loaded here via AJAX -->
                            </div>
                            <li>
                                <p class="text-center py-2 mb-0"><a href="{{ route('notifications.index') }}">See all
                                        notifications</a></p>
                            </li>
                        </ul>
                    </li>
                </ul>
                <div class="dropdown">
                    <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-menu d-flex">
                            <div class="user-name text-end me-3">
                                <h6 class="mb-0 text-gray-600">{{ Auth::user()->nama ?? 'User' }}</h6>
                                <p class="mb-0 text-sm text-gray-600">{{ Auth::user()->level->level_nama ?? 'Guest' }}
                                </p>
                            </div>
                            <div class="user-img d-flex align-items-center">
                                <div class="avatar avatar-md">
                                    <img
                                        src="{{ asset(Auth::user()->profile_photo ? Auth::user()->profile_photo : 'dist/assets/compiled/jpg/1.jpg') }}">

                                </div>
                            </div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton"
                        style="min-width: 11rem;">
                        <li>
                            <h6 class="dropdown-header">Hello, {{ Auth::user()->nama ?? 'User' }}!</h6>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i
                                    class="icon-mid bi bi-person me-2"></i> My Profile</a></li>
                        {{-- <li><a class="dropdown-item"
                        {{-- <li><a class="dropdown-item"
                                href="javascript:modalAction('{{ route('profile.edit_ajax') }}')"><i
                                    class="icon-mid bi bi-pencil-square me-2"></i> Edit Profile</a></li>


                        <li> --}}

                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="icon-mid bi bi-box-arrow-left me-2"></i> Logout
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>
@push('css')
    <style>
        .notification-dropdown {
            max-height: 400px;
            overflow-y: auto;
            width: 300px;
        }

        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #f1f1f1;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-text p {
            margin: 0;
        }

        .notification-title {
            font-size: 14px;
        }

        .notification-subtitle {
            font-size: 12px;
            color: #6c757d;
        }

        .badge-notification {
            position: absolute;
            top: -5px;
            right: -10px;
            font-size: 10px;
            padding: 4px 6px;
        }
    </style>
@endpush
@push('js')
    <script>
        $(document).ready(function () {
            // Function to load notifications
            function loadNotifications() {
                $.ajax({
                    url: '{{ route('notifications.fetch') }}',
                    method: 'GET',
                    success: function (response) {
                        $('#unread-count').text(response.unread_count > 0 ? response.unread_count : '');
                        let notificationHtml = '';

                        if (response.notifications.length === 0) {
                            notificationHtml = '<li class="dropdown-item notification-item"><p class="text-center">Tidak ada notifikasi.</p></li>';
                        } else {
                            response.notifications.forEach(function (notif) {
                                let iconClass = '';
                                let icon = '';
                                switch (notif.tipe) {
                                    case 'laporan_baru':
                                        iconClass = 'bg-primary';
                                        icon = 'bi bi-cart-check';
                                        break;
                                    case 'status_laporan':
                                        iconClass = 'bg-success';
                                        icon = 'bi bi-file-earmark-check';
                                        break;
                                    case 'penugasan':
                                        iconClass = 'bg-info';
                                        icon = 'bi bi-person-check';
                                        break;
                                    case 'status_penugasan':
                                        iconClass = 'bg-warning';
                                        icon = 'bi bi-tools';
                                        break;
                                    default:
                                        iconClass = 'bg-secondary';
                                        icon = 'bi bi-bell';
                                }

                                notificationHtml += `
                                        <li class="dropdown-item notification-item">
                                            <a class="d-flex align-items-center notification-link" href="#" data-id="${notif.notifikasi_id}">
                                                <div class="notification-icon ${iconClass}">
                                                    <i class="${icon}"></i>
                                                </div>
                                                <div class="notification-text ms-4">
                                                    <p class="notification-title font-bold">${notif.judul}</p>
                                                    <p class="notification-subtitle font-thin text-sm">${notif.pesan}</p>
                                                </div>
                                            </a>
                                        </li>`;
                            });
                        }

                        $('#notification-list').html(notificationHtml);
                    },
                    error: function () {
                        $('#notification-list').html('<li class="dropdown-item notification-item"><p class="text-center">Gagal memuat notifikasi.</p></li>');
                    }
                });
            }

            // Load notifications on page load
            loadNotifications();

            // Mark notification as read when clicked
            $(document).on('click', '.notification-link', function (e) {
                e.preventDefault();
                let notificationId = $(this).data('id');

                $.ajax({
                    url: '{{ route('notifications.markAsRead', ':id') }}'.replace(':id', notificationId),
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function () {
                        loadNotifications(); // Reload notifications to update unread count
                    },
                    error: function () {
                        console.log('Gagal menandai notifikasi sebagai dibaca.');
                    }
                });
            });

            // Periodically check for new notifications (every 30 seconds)
            setInterval(loadNotifications, 30000);
        });
    </script>
@endpush