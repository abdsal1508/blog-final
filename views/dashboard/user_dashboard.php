<?php
require_once '../../config/config.php';
require_author();

$postService = new PostService();

if (isset($_POST['submit_post'])) {
    if (isset($_POST['post_id'])) {
        $result = $postService->updatePost($_POST, $_FILES);
    } else {
        $result = $postService->createPost($_POST, $_FILES);
    }

    if ($result['success']) {
        set_flash_message('success', $result['message']);
    } else {
        set_flash_message('error', $result['message']);
    }

    redirect($_SERVER['PHP_SELF']);
}

$user_id = $_SESSION['user_id'];
$userModel = new User();
$user = $userModel->findById($user_id);

$recent_posts_options = array(
    'author_id' => $user_id,
    'limit' => 5,
    'offset' => 0
);
$recent_posts = $postService->getAllPosts($recent_posts_options);

$edit_post = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_post = $postService->getPost($_GET['edit']);

    if (!$edit_post || $edit_post['r_author_id'] != $user_id) {
        set_flash_message('error', 'You do not have permission to edit this post');
        redirect('user_dashboard.php');
    }
}

$page_title = "Author Dashboard";

include('../components/header.php');
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-tachometer-alt me-2"></i> Author Dashboard</h2>
        <p class="text-muted">Welcome back, <?php echo html_escape($_SESSION['username']); ?>! Manage your blog posts and view your statistics.</p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="?create_post=1" class="btn manual-button">
            <i class="fas fa-plus-circle me-1"></i> Create New Post
        </a>
    </div>
</div>

<div class="row mb-4">
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
            'link' => 'my_posts.php'
        ),
        array(
            'title' => 'Published Posts',
            'count' => $published_posts,
            'icon' => 'check-circle',
            'color' => 'success',
            'link' => 'my_posts.php?status=published'
        ),
        array(
            'title' => 'Draft Posts',
            'count' => $draft_posts,
            'icon' => 'edit',
            'color' => 'warning',
            'link' => 'my_posts.php?status=draft'
        )
    );

    foreach ($stats as $stat):
        ?>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100 stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2"><?php echo $stat['title']; ?></h6>
                            <h3 class="mb-0 stats-number"><?php echo $stat['count']; ?></h3>
                        </div>
                        <div class="icon-circle bg-<?php echo $stat['color']; ?> stats-icon">
                            <i class="fas fa-<?php echo $stat['icon']; ?>"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="<?php echo $stat['link']; ?>" class="text-<?php echo $stat['color']; ?> text-decoration-none">
                        View Details <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Posts</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark sticky-thead">
                            <tr>
                                <th width="40%">Title</th>
                                <th width="20%">Category</th>
                                <th width="15%">Status</th>
                                <th width="15%">Date</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_posts['posts'])): ?>
                                <?php foreach ($recent_posts['posts'] as $post): ?>
                                    <tr>
                                        <td>
                                            <a href="../public/view_post.php?id=<?php echo $post['post_id']; ?>" class="text-decoration-none fw-bold">
                                                <?php echo html_escape($post['title']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo html_escape($post['category_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $post['post_status'] == 'published' ? 'success' : 'warning'; ?> post-status status-<?php echo $post['post_status']; ?>">
                                                <?php echo ucfirst($post['post_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_date($post['create_time']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="?edit=<?php echo $post['post_id']; ?>" class="btn btn-sm manual-button action-btn edit-btn">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-3">No posts found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <a href="my_posts.php" class="btn manual-button">
                    <i class="fas fa-list me-1"></i> View All Posts
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-link me-2"></i> Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="?create_post=1" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-plus-circle me-2"></i> Create New Post
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="my_posts.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-file-alt me-2"></i> Manage My Posts
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="profile.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-user-circle me-2"></i> Edit Profile
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="../public/index.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-eye me-2"></i> View Blog
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Writing Tips</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Use clear, concise titles
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Break content into paragraphs
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Include relevant images
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Choose the right category
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-check-circle text-success me-2"></i> Proofread before publishing
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$show_post_modal = isset($_GET['create_post']) || isset($edit_post);
include('../components/post_modal.php');
include('../components/footer.php');
?>
