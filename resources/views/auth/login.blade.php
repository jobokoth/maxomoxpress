<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Sign In | Masomo School ERP</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('dashui/assets/images/favicon/favicon.ico') }}" />
    <script src="{{ asset('dashui/assets/js/vendors/color-modes.js') }}"></script>
    <link href="{{ asset('dashui/assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('dashui/assets/css/theme.min.css') }}">
</head>

<body class="bg-light">
    <main>
        <section class="container d-flex flex-column vh-100">
            <div class="row align-items-center justify-content-center g-0 h-lg-100 py-8">
                <div class="col-12 col-md-8 col-lg-6 col-xxl-4 py-8 py-xl-0">
                    <div class="card smooth-shadow-md">
                        <div class="card-body p-6">
                            <div class="mb-5 text-center">
                                <h1 class="mb-1 fw-bold">Sign In</h1>
                                <p class="mb-0">Use your school account to access the dashboard.</p>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login.attempt') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                                        <label class="form-check-label" for="remember">Remember me</label>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Sign In</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="{{ asset('dashui/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
