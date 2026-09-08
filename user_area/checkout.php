<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . '/../includes/connect.php');
include_once(__DIR__ . '/../function/common_function.php');
include_once(__DIR__ . '/../function/csrf.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!verify_csrf_token()) {
        $message = "<div class='alert alert-danger text-center'>Invalid request. Please try again.</div>";
    } else {
        $login_input = trim($_POST['login_input'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($login_input === '' || $password === '') {
            $message = "<div class='alert alert-danger text-center'>Please enter your username or email and password.</div>";
        } else {
            $login_stmt = mysqli_prepare(
                $con,
                "SELECT user_name, user_password
                 FROM `user_table`
                 WHERE user_name = ? OR user_email = ?
                 LIMIT 1"
            );
            mysqli_stmt_bind_param($login_stmt, 'ss', $login_input, $login_input);
            mysqli_stmt_execute($login_stmt);
            $login_result = mysqli_stmt_get_result($login_stmt);
            $user = mysqli_fetch_assoc($login_result);
            mysqli_stmt_close($login_stmt);

            if ($user && password_verify($password, $user['user_password'])) {
                $_SESSION['username'] = $user['user_name'];
                header('Location: checkout.php');
                exit();
            }

            $message = "<div class='alert alert-danger text-center'>Invalid username/email or password.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../sty.css">
</head>
<body>
<div class="container-fluid p-0">
    <nav class="navbar navbar-expand-lg bg-info">
        <div class="container-fluid">
            <img src="../images/icon.png" alt="Logo" class="logo">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#checkoutNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="checkoutNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../display_all.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="user_registration.php">Register</a></li>
                    <li class="nav-item"><a class="nav-link" href="../cart.php"><i class="fa-solid fa-cart-shopping"></i><sup><?php cart_item(); ?></sup></a></li>
                    <li class="nav-item"><a class="nav-link active" href="checkout.php">Total: <?php total_cart_price(); ?>/-</a></li>
                </ul>
                <form class="d-flex" action="../search_product.php" method="get">
                    <input class="form-control me-2" type="search" placeholder="Search" name="search_data">
                    <input type="submit" value="Search" class="btn btn-outline-light" name="search_data_product">
                </form>
            </div>
        </div>
    </nav>

    <nav class="navbar navbar-expand-lg bg-secondary">
        <ul class="navbar nav me-auto">
            <?php if (!isset($_SESSION['username'])): ?>
                <li class="nav-item"><a class="nav-link text-white" href="profile.php"><i class="fa-solid fa-user me-1"></i> Welcome</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="checkout.php">Login</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link text-white" href="profile.php"><i class="fa-solid fa-user me-1"></i> Welcome <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></a></li>
                <li class="nav-item"><a class="nav-link text-white" href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="bg-light text-center p-3">
        <h3>Checkout</h3>
        <p>Please login or proceed with payment to complete your order</p>
    </div>

    <div class="row px-1">
        <div class="col-12">
            <div class="row">
                <?php
                if (!isset($_SESSION['username'])) {
                    $_SESSION['return_url'] = 'checkout.php';
                    include(__DIR__ . '/_login_fragment.php');
                } else {
                    include(__DIR__ . '/payment.php');
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include(__DIR__ . '/../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
