<?php
require_once '../../config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$userModel = new User();
$postService = new PostService();

$user = $userModel->findById($user_id);

if (!$user) {
    set_flash_message('error', 'User not found');
    redirect('../../api/endpoints/logout.php');
}

if (isset($_POST['update_profile'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';

    if (empty($username) || empty($email)) {
        set_flash_message('error', 'Username and email are required');
    } else {
        $db = new Database();
        $result = $db->query(
            "UPDATE user_details 
             SET user_name = ?, 
                 user_email = ?
             WHERE user_id = ?",
            array($username, $email, $user_id)
        );

        if ($result) {
            $_SESSION['username'] = $username;
            set_flash_message('success', 'Profile updated successfully');
            redirect($_SERVER['PHP_SELF']);
        } else {
            set_flash_message('error', 'Failed to update profile');
        }
    }
}

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        set_flash_message('error', 'All password fields are required');
    } elseif ($new_password !== $confirm_password) {
        set_flash_message('error', 'New passwords do not match');
    } elseif (strlen($new_password) < 6) {
        set_flash_message('error', 'New password must be at least 6 characters long');
    } else {
        if ($current_password !== $user['user_password'] && !password_verify($current_password, $user['user_password'])) {
            set_flash_message('error', 'Current password is incorrect');
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $db = new Database();
            $result = $db->query(
                "UPDATE user_details 
                 SET user_password = ?
                 WHERE user_id = ?",
                array($hashed_password, $user_id)
            );

            if ($result) {
                set_flash_message('success', 'Password changed successfully');
                redirect($_SERVER['PHP_SELF']);
            } else {
                set_flash_message('error', 'Failed to change password');
            }
        }
    }
}

$page_title = "My Profile";

include('../components/header.php');
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-user-circle me-2"></i> My Profile</h2>
        <p class="text-muted">Manage your account settings and change your password.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i> Profile Information</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required
                            value="<?php echo html_escape($user['user_name']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required
                            value="<?php echo html_escape($user['user_email']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <input type="text" class="form-control" id="role"
                            value="<?php echo ucfirst($user['user_role']); ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="joined" class="form-label">Joined</label>
                        <input type="text" class="form-control" id="joined"
                            value="<?php echo format_date($user['create_time'], 'F j, Y'); ?>" readonly>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="update_profile" class="btn manual-button">
                            <i class="fas fa-save me-1"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i> Change Password</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text">Password must be at least 6 characters long</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="change_password" class="btn manual-button">
                            <i class="fas fa-key me-1"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Account Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $total_posts = $postService->countPosts(array('author_id' => $user_id));
                    $published_posts = $postService->countPosts(array('author_id' => $user_id, 'status' => 'published'));
                    $draft_posts = $postService->countPosts(array('author_id' => $user_id, 'status' => 'draft'));

                    $stats = array(
                        array(
                            'title' => 'Total Posts',
                            'count' => $total_posts,
                            'icon' => 'file-alt',
                            'color' => 'primary',
                            'link' => is_admin() ? '../admin/admin.php?author=' . $user_id : 'my_posts.php'
                        ),
                        array(
                            'title' => 'Published Posts',
                            'count' => $published_posts,
                            'icon' => 'check-circle',
                            'color' => 'success',
                            'link' => is_admin() ? '../admin/admin.php?author=' . $user_id . '&status=published' : 'my_posts.php?status=published'
                        ),
                        array(
                            'title' => 'Draft Posts',
                            'count' => $draft_posts,
                            'icon' => 'edit',
                            'color' => 'warning',
                            'link' => is_admin() ? '../admin/admin.php?author=' . $user_id . '&status=draft' : 'my_posts.php?status=draft'
                        ),
                        array(
                            'title' => 'Account Age',
                            'count' => format_date($user['create_time'], 'M Y'),
                            'icon' => 'calendar-alt',
                            'color' => 'info',
                            'link' => '#'
                        )
                    );

                    foreach ($stats as $stat):
                        ?>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="icon-circle bg-<?php echo $stat['color']; ?> mx-auto mb-3">
                                        <i class="fas fa-<?php echo $stat['icon']; ?>"></i>
                                    </div>
                                    <h5 class="card-title"><?php echo $stat['title']; ?></h5>
                                    <p class="card-text display-6"><?php echo $stat['count']; ?></p>
                                    <?php if ($stat['link'] != '#'): ?>
                                        <a href="<?php echo $stat['link']; ?>" class="btn btn-sm manual-button">
                                            View Details
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../components/footer.php'); ?>
