<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="al-aqsa-mosque.png">
    <title>@yield('title')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style/master.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="bg-dark text-white sidebar" id="sidebar">
            <div class="d-flex flex-column align-items-start py-4 px-3">
                <!-- Close Button -->
                <button class="btn btn-light text-dark d-md-none" id="closeSidebar" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
                <h4 class="text-center w-100 mb-4">لوحة التحكم</h4>
                <a href="{{ route('dashboard') }}" class="btn btn-dark w-100 text-end py-2 active">
                    <i class="fas fa-tachometer-alt"></i> الصفحة الرئيسية
                </a>
                <a href="#" class="btn btn-dark w-100 text-end py-2" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>

        </div>

        <!-- Main Content -->
        <div class="col content">
            <!-- Burger Menu Button -->
            <button class="btn btn-dark d-md-none mb-3 mt-2" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            @yield('content')
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const closeSidebar = document.getElementById('closeSidebar');

    sidebarToggle.addEventListener('click', function () {
        sidebar.classList.toggle('active');
        if (sidebar.classList.contains('active')) {
            closeSidebar.style.display = 'block';
        } else {
            closeSidebar.style.display = 'none';
        }
    });

    closeSidebar.addEventListener('click', function () {
        sidebar.classList.remove('active');
        closeSidebar.style.display = 'none';
    });
</script>
</body>
</html>
