<?php
// Get current page for active nav link
$current_page = basename($_SERVER['PHP_SELF']);

// Default page title
$page_title = isset($page_title) ? $page_title : SITE_NAME;
$header_title = isset($header_title) ? $header_title : SITE_NAME;
$container_class = isset($container_class) ? $container_class : '';
$hide_header_nav = isset($hide_header_nav) ? $hide_header_nav : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/main.css">
</head>

<body>
    <?php if (!$hide_header_nav): ?>
        <div class="header">
            <nav class="navbar navbar-expand-lg manual-navbar fixed-top shadow">
                <div class="container-fluid px-3">
                    <a class="navbar-brand" href="<?php echo is_logged_in() ? (is_admin() ? '../admin/admin.php' : (is_author() ? '../dashboard/user_dashboard.php' : '../public/index.php')) : '../public/index.php'; ?>">
                        <img src="../../assets/images/imga.svg" alt="Logo" class="img-fluid navbar-logo">
                    </a>

                    <h1 class="navbar-title-centered"><?php echo html_escape($header_title); ?></h1>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarText">
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link " href="../public/index.php">Home</a>
                            </li>

                            <?php if (is_logged_in()): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="../dashboard/profile.php">Profile</a>
                                </li>

                                <?php if (is_admin()): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="../admin/admin.php">Dashboard</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="../admin/categories.php">Categories</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="../admin/users.php">Users</a>
                                    </li>
                                <?php elseif (is_author()): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="../dashboard/user_dashboard.php">Dashboard</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="../dashboard/my_posts.php">My Posts</a>
                                    </li>
                                <?php endif; ?>

                                <li class="nav-item">
                                    <a href="../../api/endpoints/logout.php" class="btn btn-danger btn-sm ms-2">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a href="../auth/login.php" class="btn manual-button btn-sm ms-2">
                                        <i class="fas fa-sign-in-alt"></i> Login
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="../auth/signup.php" class="btn manual-button btn-sm ms-2">
                                        <i class="fas fa-user-plus"></i> Sign Up
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    <?php endif; ?>

    <div class="container my-4 <?php echo $container_class; ?>">
        <?php
        $flash_message = get_flash_message();
        if ($flash_message):
            $toast_class = '';
            $icon_class = '';

            switch ($flash_message['type']) {
                case 'success':
                    $toast_class = 'bg-success text-dark';
                    $icon_class = 'fas fa-check-circle text-success';
                    break;
                case 'error':
                case 'danger':
                    $toast_class = 'bg-danger text-dark';
                    $icon_class = 'fas fa-exclamation-circle text-danger';
                    break;
                case 'warning':
                    $toast_class = 'bg-warning text-dark';
                    $icon_class = 'fas fa-exclamation-triangle text-warning';
                    break;
                case 'info':
                    $toast_class = 'bg-info text-dark';
                    $icon_class = 'fas fa-info-circle text-info';
                    break;
                default:
                    $toast_class = 'bg-light text-dark';
                    $icon_class = 'fas fa-info-circle';
            }
            ?>
            <div class="alert <?php echo str_replace('bg-', 'alert-', explode(' ', $toast_class)[0]); ?> alert-dismissible fade show">
                <i class="<?php echo $icon_class; ?> me-2"></i>
                <?php echo $flash_message['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
