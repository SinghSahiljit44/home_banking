<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Banca Italiana')</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- AOS CSS -->
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
</head>
<body class="gradient-bg text-white">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top px-3 transparent-navbar">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="{{ url('/') }}">Banca Italiana</a>
      <div class="d-flex">
        <a href="{{ url('/login') }}" class="btn btn-outline-light btn-sm btn-login">Login</a>
      </div>
    </div>
  </nav>

  <main class="pt-5 mt-5">
    @yield('content')
  </main>

  <!-- Footer -->
  <footer class="text-center py-4 mt-5">
    <p class="text-white-50">&copy; 2025 Banca Italiana. Tutti i diritti riservati.</p>
  </footer>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init({ duration: 1000 });
  </script>
</body>
</html>
