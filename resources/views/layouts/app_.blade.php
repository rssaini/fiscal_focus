<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('ledgers.index') }}">
                <i class="fas fa-book"></i> Ledger System
            </a>

            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('chart-of-accounts.index') }}">
                    <i class="fas fa-sitemap"></i> Chart of Accounts
                </a>
                <a class="nav-link" href="{{ route('ledgers.index') }}">
                    <i class="fas fa-book"></i> Ledgers
                </a>
                <a class="nav-link" href="{{ route('customers.index') }}">
                    <i class="fas fa-users"></i> Customers
                </a>
                <a class="nav-link" href="{{ route('vouchers.index') }}">
                    <i class="fas fa-file-invoice"></i> Vouchers
                </a>
                <a class="nav-link" href="{{ route('journal-entries.index') }}">
                    <i class="fas fa-journal-whills"></i> Journal Entries
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
