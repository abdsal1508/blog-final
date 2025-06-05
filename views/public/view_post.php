<?php
require_once '../../config/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash_message('error', 'Invalid post ID');
    redirect('index.php');
}

$post_id = $_GET['id'];
$postService = new PostService();
$categoryService = new CategoryService();

$post = $postService->getPost($post_id);

if (!$post) {
    set_flash_message('error', 'Post not found');
    redirect('index.php');
}

if ($post['post_status'] !== 'published' && !$postService->canEdit($post_id, $_SESSION['user_id'] ?? 0)) {
    set_flash_message('error', 'You do not have permission to view this post');
    redirect('index.php');
}

$related_options = array(
    'status' => 'published',
    'category_id' => $post['r_category_id'],
    'limit' => 3
);
$related_posts_result = $postService->getAllPosts($related_options);
$related_posts = $related_posts_result['posts'];

$related_posts = array_filter($related_posts, function($related_post) use ($post_id) {
    return $related_post['post_id'] != $post_id;
});
$related_posts = array_slice($related_posts, 0, 3);

$categories = $categoryService->getAllCategories();

$page_title = $post['title'] . " - Blog";
$container_class = "article-container";

include('../components/header.php');
?>

<div class="row">
    <div class="col-lg-8">
        <a href="index.php" class="back-button mb-4 d-inline-block">
            <i class="fas fa-arrow-left me-1"></i> Back to Articles
        </a>
        
        <article class="card shadow-sm mb-4">
            <div class="card-body">
                <header class="article-header">
                    <h1 class="article-title"><?php echo html_escape($post['title']); ?></h1>
                    
                    <div class="article-meta">
                        <div class="author-info">
                            <div class="author-avatar">
                                <?php echo substr($post['author_name'], 0, 1); ?>
                            </div>
                            <div>
                                <div><?php echo html_escape($post['author_name']); ?></div>
                                <small>Author</small>
                            </div>
                        </div>
                        
                        <div>
                            <i class="fas fa-calendar-alt me-1"></i>
                            <?php echo format_date($post['create_time']); ?>
                        </div>
                        
                        <a href="index.php?category=<?php echo $post['r_category_id']; ?>" class="category-badge">
                            <i class="fas fa-tag me-1"></i> <?php echo html_escape($post['category_name']); ?>
                        </a>
                    </div>
                </header>
                
                <?php if (!empty($post['image_link'])): ?>
                    <img src="../../<?php echo $post['image_link']; ?>" class="article-image img-fluid rounded mb-4" alt="<?php echo html_escape($post['title']); ?>">
                <?php endif; ?>
                
                <div class="article-content">
                    <?php echo nl2br(html_escape($post['content'])); ?>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-4 pt-4 border-top">
                    <div>
                        <a href="index.php?category=<?php echo $post['r_category_id']; ?>" class="category-badge">
                            <i class="fas fa-tag me-1"></i> <?php echo html_escape($post['category_name']); ?>
                        </a>
                    </div>
                    
                    <div class="share-buttons">
                        <a href="#" class="share-button share-facebook" title="Share on Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="share-button share-twitter" title="Share on Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="share-button share-linkedin" title="Share on LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="share-button share-email" title="Share via Email">
                            <i class="fas fa-envelope"></i>
                        </a>
                    </div>
                </div>
            </div>
        </article>
        
        <?php if (!empty($related_posts)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header heading-for text-white">
                    <h5 class="mb-0"><i class="fas fa-bookmark me-2"></i> Related Articles</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($related_posts as $related_post): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <img src="../../<?php echo !empty($related_post['image_link']) ? $related_post['image_link'] : 'assets/images/placeholder.jpg'; ?>" 
                                         class="card-img-top" alt="<?php echo html_escape($related_post['title']); ?>"
                                         style="height: 150px; object-fit: cover;">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <a href="view_post.php?id=<?php echo $related_post['post_id']; ?>" class="text-decoration-none">
                                                <?php echo html_escape($related_post['title']); ?>
                                            </a>
                                        </h6>
                                        <div class="small text-muted">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <?php echo format_date($related_post['create_time'], 'M j, Y'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4 shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i> About the Author</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="author-avatar me-3">
                        <?php echo substr($post['author_name'], 0, 1); ?>
                    </div>
                    <div>
                        <h5 class="mb-0"><?php echo html_escape($post['author_name']); ?></h5>
                        <div class="text-muted">Author</div>
                    </div>
                </div>
                <p>This article was written by <?php echo html_escape($post['author_name']); ?>, who contributes to our blog.</p>
                
                <?php
                $author_options = array(
                    'status' => 'published',
                    'author_id' => $post['r_author_id'],
                    'limit' => 3
                );
                $author_posts_result = $postService->getAllPosts($author_options);
                $author_posts = $author_posts_result['posts'];
                
                $author_posts = array_filter($author_posts, function($author_post) use ($post_id) {
                    return $author_post['post_id'] != $post_id;
                });
                $author_posts = array_slice($author_posts, 0, 3);
                
                if (!empty($author_posts)):
                ?>
                    <h6 class="mt-3">More from this author:</h6>
                    <ul class="list-unstyled">
                        <?php foreach ($author_posts as $author_post): ?>
                            <li class="mb-2">
                                <a href="view_post.php?id=<?php echo $author_post['post_id']; ?>" class="text-decoration-none">
                                    <i class="fas fa-angle-right me-1"></i> <?php echo html_escape($author_post['title']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-4 shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-tags me-2"></i> Categories</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($categories as $category): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="index.php?category=<?php echo $category['category_id']; ?>" class="text-decoration-none">
                                <?php echo html_escape($category['category_name']); ?>
                            </a>
                            <span class="badge heading-for rounded-pill">
                                <?php 
                                $cat_posts = $postService->getAllPosts(array('category_id' => $category['category_id'], 'status' => 'published'));
                                echo count($cat_posts['posts']); 
                                ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="card mb-4 shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-envelope me-2"></i> Newsletter</h5>
            </div>
            <div class="card-body">
                <p>Subscribe to our newsletter to get the latest updates directly to your inbox.</p>
                <form>
                    <div class="mb-3">
                        <input type="email" class="form-control" placeholder="Your email address">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn manual-button">
                            <i class="fas fa-paper-plane me-1"></i> Subscribe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../components/footer.php'); ?>
