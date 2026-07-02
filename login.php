<?php
session_start();
include 'koneksi.php';

// Jika sudah login
if (isset($_SESSION['id_user'])) {

    if (
        isset($_SESSION['role']) &&
        $_SESSION['role'] === 'admin'
    ) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: home.php");
    }

    exit();
}

$error_message = "";
$email_input = "";

// Proses Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $email_input = $email;

    $query = "
        SELECT
            id_user,
            nama,
            username,
            email,
            password,
            role
        FROM users
        WHERE email = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($koneksi, $query);

    if (!$stmt) {

        $error_message = "Terjadi kesalahan pada sistem.";

    } else {

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) == 1) {

            $user_data = mysqli_fetch_assoc($result);

            if (
                password_verify(
                    $password,
                    $user_data['password']
                )
            ) {

                session_regenerate_id(true);

                $_SESSION['id_user'] = $user_data['id_user'];
                $_SESSION['nama'] = $user_data['nama'];
                $_SESSION['username'] = $user_data['username'];
                $_SESSION['role'] = $user_data['role'];

                if ($user_data['role'] === 'admin') {

                    header("Location: admin/dashboard.php");

                } else {

                    header("Location: home.php");

                }

                exit();

            } else {

                $error_message = "Password salah!";

            }

        } else {

            $error_message = "Email tidak ditemukan!";

        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookLens - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
    * {
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        min-height: 100vh;
        margin: 0;
        background-color: #f4f7f9;
        color: #0f172a;
    }

    .login-page {
        min-height: 100vh;
        padding: 70px 20px 40px;
    }

    .back-link {
        position: absolute;
        top: 40px;
        left: 40px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #1e293b;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
    }

    .back-link:hover {
        color: #0f172a;
    }

    .header-container {
        margin-top: 20px;
        margin-bottom: 24px;
        text-align: center;
    }

    .login-custom-logo {
        width: 56px;
        height: 56px;
        object-fit: contain;
        margin-bottom: 6px;
    }

    .header-container h1 {
        margin-bottom: 5px;
        color: #0f172a;
        font-size: 2.2rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .header-container p {
        margin-bottom: 0;
        color: #64748b;
        font-size: 0.9rem;
    }

    .login-card {
        width: 100%;
        max-width: 460px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }

    .login-card .card-body {
        padding: 40px;
    }

    .custom-label {
        margin-bottom: 8px;
        color: #0f172a;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .custom-input-group .input-group-text {
        height: 46px;
        border-color: #cbd5e1;
        border-radius: 6px 0 0 6px;
        background-color: #fbfbfb;
        color: #94a3b8;
    }

    .custom-input-group .form-control {
        height: 46px;
        border-color: #cbd5e1;
        border-left: 0;
        border-radius: 0 6px 6px 0;
        background-color: #fbfbfb;
        color: #334155;
        font-size: 0.9rem;
        box-shadow: none;
    }

    .custom-input-group.password-group .form-control {
        border-radius: 0;
    }

    .custom-input-group .form-control:focus {
        border-color: #94a3b8;
        background-color: #ffffff;
        box-shadow: none;
    }

    .custom-input-group:focus-within .input-group-text {
        border-color: #94a3b8;
        background-color: #ffffff;
    }

    .custom-input-group .form-control::placeholder {
        color: #94a3b8;
    }

    .password-toggle {
        height: 46px;
        border-color: #cbd5e1 !important;
        border-left: 0 !important;
        border-radius: 0 6px 6px 0 !important;
        background-color: #fbfbfb !important;
        color: #64748b !important;
        cursor: pointer;
    }

    .password-toggle:hover {
        color: #1e293b !important;
    }

    .forgot-link {
        color: #334155;
        font-size: 0.75rem;
        font-weight: 500;
        text-decoration: none;
    }

    .forgot-link:hover {
        color: #0f172a;
        text-decoration: underline;
    }

    .remember-check .form-check-input {
        border-color: #94a3b8;
        cursor: pointer;
        box-shadow: none;
    }

    .remember-check .form-check-input:checked {
        border-color: #2b3a4a;
        background-color: #2b3a4a;
    }

    .remember-check .form-check-label {
        color: #475569;
        font-size: 0.8rem;
        cursor: pointer;
    }

    .btn-login {
        height: 46px;
        border: 1px solid #2b3a4a;
        border-radius: 6px;
        background-color: #2b3a4a;
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .btn-login:hover,
    .btn-login:focus {
        border-color: #1e2936;
        background-color: #1e2936;
        color: #ffffff;
    }

    .or-divider {
        display: flex;
        align-items: center;
        margin: 25px 0;
        color: #94a3b8;
        font-size: 0.75rem;
        text-align: center;
    }

    .or-divider::before,
    .or-divider::after {
        content: "";
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }

    .or-divider::before {
        margin-right: 12px;
    }

    .or-divider::after {
        margin-left: 12px;
    }

    .social-button {
        width: 100%;
        height: 44px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background-color: #ffffff;
        color: #334155;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .social-button:hover {
        border-color: #94a3b8;
        background-color: #f8fafc;
        color: #1e293b;
    }

    .social-button img {
        width: 17px;
        height: 17px;
        object-fit: contain;
    }

    .register-text {
        margin-top: 34px;
        color: #475569;
        font-size: 0.85rem;
        text-align: center;
    }

    .register-text a {
        color: #2b3a4a;
        font-weight: 600;
        text-decoration: none;
    }

    .register-text a:hover {
        text-decoration: underline;
    }

    .custom-alert {
        margin-bottom: 20px;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 0.8rem;
        text-align: center;
    }

    @media (max-width: 576px) {
        .login-page {
            justify-content: flex-start !important;
            padding-top: 90px;
        }

        .back-link {
            top: 25px;
            left: 20px;
        }

        .login-card .card-body {
            padding: 28px 22px;
        }

        .header-container h1 {
            font-size: 1.8rem;
        }
    }
</style>
</head>

<body>
    <a href="index.php" class="back-link">
        <i class="bi bi-chevron-left"></i>
        <span>Back</span>
    </a>

<main class="login-page d-flex flex-column align-items-center justify-content-center">
    <section class="header-container">
        <img
            src="assets/images/ui/boxicons_book.png"
            alt="BookLens Logo"
            class="login-custom-logo"
        >

        <h1>Welcome Back</h1>
        <p>Please Log in to your BookLens account</p>
    </section>

    <section class="card login-card">
        <div class="card-body">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger custom-alert" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-4">
                    <label for="email" class="form-label custom-label">
                        Email
                    </label>

                    <div class="input-group custom-input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            placeholder="yourname@example.com"
                            value="<?php echo htmlspecialchars($email_input); ?>"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

                <div class="mb-2">
                    <label for="inputPass" class="form-label custom-label">
                        Password
                    </label>

                    <div class="input-group custom-input-group password-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock-fill"></i>
                        </span>

                        <input
                            type="password"
                            class="form-control"
                            id="inputPass"
                            name="password"
                            placeholder="********"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="input-group-text password-toggle"
                            onclick="viewPasswordToggle()"
                            aria-label="Show or hide password"
                        >
                            <i id="eyeIcon" class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="text-end mb-3">
                    <a href="#" class="forgot-link">
                        Forgot Password?
                    </a>
                </div>

                <div class="form-check remember-check mb-4">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        id="remCheck"
                        name="remember"
                    >

                    <label class="form-check-label" for="remCheck">
                        Remember me
                    </label>
                </div>

                <button
                    type="submit"
                    name="login"
                    class="btn btn-login w-100 d-flex align-items-center justify-content-center gap-2"
                >
                    <span>Login</span>
                    <i class="bi bi-arrow-right"></i>
                </button>

                <div class="or-divider">
                    Or continue with
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <button
                            type="button"
                            class="btn social-button d-flex align-items-center justify-content-center gap-2"
                        >
                            <img
                                src="https://www.google.com/images/branding/googleg/1x/googleg_standard_color_128dp.png"
                                alt="Google Logo"
                            >
                            <span>Google</span>
                        </button>
                    </div>

                    <div class="col-6">
                        <button
                            type="button"
                            class="btn social-button d-flex align-items-center justify-content-center gap-2"
                        >
                            <img
                                src="https://upload.wikimedia.org/wikipedia/commons/b/b9/2023_Facebook_icon.svg"
                                alt="Facebook Logo"
                            >
                            <span>Facebook</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <div class="register-text">
        Don't have an account?
        <a href="register.php">Register</a>
    </div>
</main>

<script>
    function viewPasswordToggle() {
        const inputPass = document.getElementById("inputPass");
        const eyeIcon = document.getElementById("eyeIcon");

        if (inputPass.type === "password") {
            inputPass.type = "text";
            eyeIcon.classList.remove("bi-eye");
            eyeIcon.classList.add("bi-eye-slash");
        } else {
            inputPass.type = "password";
            eyeIcon.classList.remove("bi-eye-slash");
            eyeIcon.classList.add("bi-eye");
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
