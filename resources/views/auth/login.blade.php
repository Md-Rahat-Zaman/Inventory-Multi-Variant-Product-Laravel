
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Login | Inventory Management System</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

</head>


<body>


<div class="login-wrapper">


    <div class="login-card">


        <!-- =================================
             LEFT INFORMATION PANEL
        ================================= -->

        <div class="login-info">


            <div class="brand">

                <div class="brand-icon">

                    <i class="bi bi-boxes"></i>

                </div>


                <div>

                    <div class="brand-name">
                        Inventory System
                    </div>

                    <span class="brand-subtitle">
                        MANAGEMENT PLATFORM
                    </span>

                </div>

            </div>


            <h1>

                Manage your inventory
                <span>smarter.</span>

            </h1>


            <p>

                Control your products, stock, purchases,
                sales and business operations from one
                powerful inventory management platform.

            </p>


            <div class="feature-list">


                <div class="feature-item">

                    <i class="bi bi-box-seam"></i>

                    <span>
                        Complete Product Management
                    </span>

                </div>


                <div class="feature-item">

                    <i class="bi bi-bar-chart-line"></i>

                    <span>
                        Real-time Stock Monitoring
                    </span>

                </div>


                <div class="feature-item">

                    <i class="bi bi-cart-check"></i>

                    <span>
                        Sales & Purchase Management
                    </span>

                </div>


                <div class="feature-item">

                    <i class="bi bi-graph-up-arrow"></i>

                    <span>
                        Powerful Business Reports
                    </span>

                </div>


            </div>


        </div>



        <!-- =================================
             RIGHT LOGIN PANEL
        ================================= -->

        <div class="login-form-area">


            <div class="login-form">


                <div class="form-heading">

                    <h2>
                        Welcome back
                    </h2>

                    <p>
                        Sign in to access your inventory dashboard.
                    </p>

                </div>



                <!-- Laravel Login Form -->

                <form method="POST"
                      action="{{ route('login') }}">

                    @csrf


                    <!-- Email -->

                    <div class="form-group">

                        <label for="email"
                               class="form-label">

                            Email Address

                        </label>


                        <div class="input-wrapper">

                            <input
                                id="email"
                                name="email"
                                type="email"

                                value="{{ old('email') }}"

                                required
                                autofocus
                                autocomplete="username"

                                class="form-input"

                                placeholder="Enter your email"
                            >


                            <i class="bi bi-envelope input-icon"></i>

                        </div>


                        @if ($errors->get('email'))

                            <div class="error-message">

                                {{ $errors->first('email') }}

                            </div>

                        @endif

                    </div>



                    <!-- Password -->

                    <div class="form-group">

                        <label for="password"
                               class="form-label">

                            Password

                        </label>


                        <div class="input-wrapper">

                            <input
                                id="password"
                                name="password"
                                type="password"

                                required

                                autocomplete="current-password"

                                class="form-input"

                                placeholder="Enter your password"
                            >


                            <i class="bi bi-lock input-icon"></i>


                            <button
                                type="button"
                                class="password-toggle"
                                onclick="togglePassword()"
                                aria-label="Show password"
                                aria-pressed="false">

                                <i class="bi bi-eye"
                                   id="passwordIcon"></i>

                            </button>

                        </div>


                        @if ($errors->get('password'))

                            <div class="error-message">

                                {{ $errors->first('password') }}

                            </div>

                        @endif

                    </div>



                    <!-- Remember / Forgot -->

                    <div class="form-options">


                        <label class="remember-label">

                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"

                                class="remember-checkbox"
                            >

                            <span>
                                Remember me
                            </span>

                        </label>


                        @if (Route::has('password.request'))

                            <a
                                href="{{ route('password.request') }}"
                                class="forgot-link">

                                Forgot password?

                            </a>

                        @endif


                    </div>



                    <!-- Login -->

                    <button
                        type="submit"
                        class="login-btn">

                        <i class="bi bi-box-arrow-in-right"></i>

                        Sign In

                    </button>


                </form>



                <!-- Divider -->

                <div class="divider">

                    OR CONTINUE WITH

                </div>



                <!-- Social Login -->

                <div class="social-buttons">


                    <a
                        href="{{ url('/auth/google') }}"
                        class="social-btn">

                        <i class="bi bi-google google-icon"></i>

                        Google

                    </a>


                    <a
                        href="{{ url('/auth/facebook') }}"
                        class="social-btn">

                        <i class="bi bi-facebook facebook-icon"></i>

                        Facebook

                    </a>


                </div>



                <!-- Register -->

                @if (Route::has('register'))

                    <div class="register-text">

                        Don't have an account?

                        <a href="{{ route('register') }}">

                            Create account

                        </a>

                    </div>

                @endif


            </div>

        </div>


    </div>

</div>



<!-- =================================
     PASSWORD SCRIPT
================================= -->

<script>

function togglePassword()
{
    const password =
        document.getElementById('password');

    const icon =
        document.getElementById('passwordIcon');

    const toggle =
        document.querySelector('.password-toggle');

    if (password.type === 'password') {

        password.type = 'text';

        icon.classList.remove('bi-eye');

        icon.classList.add('bi-eye-slash');

        toggle.setAttribute('aria-label', 'Hide password');

        toggle.setAttribute('aria-pressed', 'true');

    } else {

        password.type = 'password';

        icon.classList.remove('bi-eye-slash');

        icon.classList.add('bi-eye');

        toggle.setAttribute('aria-label', 'Show password');

        toggle.setAttribute('aria-pressed', 'false');

    }
}

</script>


</body>

</html>
