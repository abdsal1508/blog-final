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

$search_query = '';
$category_id = '';
$status_filter = '';
$options = array('author_id' => $_SESSION['user_id']);

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $options['search'] = $search_query;
}

if (isset($_GET['category']) && !empty($_GET['category']) && $_GET['category'] != 'all') {
    $category_id = $_GET['category'];
    $options['category_id'] = $category_id;
}

if (isset($_GET['status']) && !empty($_GET['status']) && $_GET['status'] != 'all') {
    $status_filter = $_GET['status'];
    $options['status'] = $status_filter;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$options['limit'] = $per_page;
$options['offset'] = ($page - 1) * $per_page;

$posts_result = $postService->getAllPosts($options);
$posts = $posts_result['posts'];
$total_posts = $posts_result['total'];
$total_pages = ceil($total_posts / $per_page);

$categoryService = new CategoryService();
$categories = $categoryService->getAllCategories();

$edit_post = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_post = $postService->getPost($_GET['edit']);
    
    if (!$edit_post || $edit_post['r_author_id'] != $_SESSION['user_id']) {
        set_flash_message('error', 'You do not have permission to edit this post');
        redirect('my_posts.php');
    }
}

if (isset($_POST['delete_item']) && $_POST['delete_type'] === 'posts') {
    $post_id = $_POST['delete_id'];
    
    $post = $postService->getPost($post_id);
    if (!$post || $post['r_author_id'] != $_SESSION['user_id']) {
        set_flash_message('error', 'You do not have permission to delete this post');
        redirect('my_posts.php');
    }
    
    $result = $postService->deletePost($post_id);
    
    if ($result['success']) {
        set_flash_message('success', $result['message']);
    } else {
        set_flash_message('error', $result['message']);
    }
    
    redirect('my_posts.php');
}

$page_title = "My Posts";

include('../components/header.php');
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="fas fa-file-alt me-2"></i> My Posts</h2>
        <p class="text-muted">Manage your blog posts.</p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="?create_post=1" class="btn manual-button">
            <i class="fas fa-plus-circle me-1"></i> Create New Post
        </a>
    </div>
</div>

<div class="row mb-4">
    <?php
    $total_published = $postService->countPosts(array('status' => 'published', 'author_id' => $_SESSION['user_id']));
    $total_drafts = $postService->countPosts(array('status' => 'draft', 'author_id' => $_SESSION['user_id']));
    
    $stats = array(
        array(
            'title' => 'Published Posts',
            'count' => $total_published,
            'icon' => 'file-alt',
            'color' => 'primary',
            'link' => '?status=published'
        ),
        array(
            'title' => 'Draft Posts',
            'count' => $total_drafts,
            'icon' => 'file',
            'color' => 'warning',
            'link' => '?status=draft'
        )
    );
    
    foreach ($stats as $stat):
    ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2"><?php echo $stat['title']; ?></h6>
                            <h3 class="mb-0"><?php echo $stat['count']; ?></h3>
                        </div>
                        <div class="icon-circle bg-<?php echo $stat['color']; ?>">
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

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search posts..." name="search" value="<?php echo html_escape($search_query); ?>">
                    <button class="btn manual-button" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="col-md-3">
                <select class="form-select" name="category" onchange="this.form.submit()">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>" <?php echo $category_id == $category['category_id'] ? 'selected' : ''; ?>>
                            <?php echo html_escape($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="all">All Statuses</option>
                    <option value="published" <?php echo $status_filter == 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn manual-button w-100">
                    <i class="fas fa-sync-alt me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header heading-for text-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i> My Posts</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">#</th>
                        <th width="40%">Title</th>
                        <th width="15%">Category</th>
                        <th width="15%">Status</th>
                        <th width="15%">Date</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $index => $post): ?>
                            <tr>
                                <td><?php echo ($page - 1) * $per_page + $index + 1; ?></td>
                                <td>
                                    <a href="../public/view_post.php?id=<?php echo $post['post_id']; ?>" class="text-decoration-none fw-bold">
                                        <?php echo html_escape($post['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo html_escape($post['category_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $post['post_status'] == 'published' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($post['post_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo format_date($post['create_time']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?php echo $post['post_id']; ?>" class="btn btn-sm manual-button">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php
                                        $item = array(
                                            'id' => $post['post_id'], 
                                            'name' => $post['title']
                                        );
                                        
                                        $table = 'posts';
                                        $item_type = 'Post';
                                        
                                        include('../components/delete_modal.php');
                                        ?>
                                        
                                        <button type="button" class="btn btn-sm manual-button" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#<?php echo $modal_id; ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-3">No posts found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($total_pages > 1): ?>
        <div class="card-footer bg-white">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo $category_id; ?>&status=<?php echo $status_filter; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo $category_id; ?>&status=<?php echo $status_filter; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo $category_id; ?>&status=<?php echo $status_filter; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php
$show_post_modal = isset($_GET['create_post']) || isset($edit_post);
include('../components/post_modal.php');
include('../components/footer.php');
?>
