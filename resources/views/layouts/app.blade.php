<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('image/ActiveCore_icon.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link href="{{ asset('startbootstrap-sb-admin-2-master/vendor/fontawesome-free/css/all.min.css') }}"
        rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('startbootstrap-sb-admin-2-master/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased" id="page-top">
    <div id="wrapper">
        @include('components.sidebar')
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
                    @include('layouts.navigation')
                    <div class="container-fluid">


                        <!-- Page Content -->
                        <main>
                            {{ $slot }}
                        </main>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('startbootstrap-sb-admin-2-master/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('startbootstrap-sb-admin-2-master/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('startbootstrap-sb-admin-2-master/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('startbootstrap-sb-admin-2-master/js/sb-admin-2.min.js') }}"></script>

    <!-- Page level plugins -->
    <script src="{{ asset('startbootstrap-sb-admin-2-master/vendor/chart.js/chart.min.js') }}vendor/chart.js/Chart.min.js">
    </script>

    <!-- Page level custom scripts -->
    <script src="{{ asset('startbootstrap-sb-admin-2-master/js/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('startbootstrap-sb-admin-2-master/js/demo/chart-pie-demo.js') }}"></script>
</body>

</html>
