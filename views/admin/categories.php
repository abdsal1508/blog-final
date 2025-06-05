<?php
require_once '../../config/config.php';
require_admin();

$categoryService = new CategoryService();
$postService = new PostService();

if (isset($_POST['create_category'])) {
    $name = $_POST['category_name'] ?? '';
    $description = $_POST['category_description'] ?? '';
    
    if (empty($name)) {
        set_flash_message('error', 'Category name is required');
    } else {
        $result = $categoryService->createCategory($name, $description);
        
        if ($result['success']) {
            set_flash_message('success', $result['message']);
        } else {
            set_flash_message('error', $result['message']);
        }
    }
    
    redirect($_SERVER['PHP_SELF']);
}

if (isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'] ?? 0;
    $name = $_POST['category_name'] ?? '';
    $description = $_POST['category_description'] ?? '';
    
    if (empty($name)) {
        set_flash_message('error', 'Category name is required');
    } else {
        $result = $categoryService->updateCategory($category_id, $name, $description);
        
        if ($result['success']) {
            set_flash_message('success', $result['message']);
        } else {
            set_flash_message('error', $result['message']);
        }
    }
    
    redirect($_SERVER['PHP_SELF']);
}

if (isset($_POST['delete_item']) && $_POST['delete_type'] === 'categories') {
    $category_id = $_POST['delete_id'];
    $result = $categoryService->deleteCategory($category_id);
    
    if ($result['success']) {
        set_flash_message('success', $result['message']);
    } else {
        set_flash_message('error', $result['message']);
    }
    
    redirect($_SERVER['PHP_SELF']);
}

$edit_category = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_category = $categoryService->getCategory($_GET['edit']);
}

$categories = $categoryService->getAllCategories();

$page_title = "Manage Categories";

include('../components/header.php');
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-tags me-2"></i> Manage Categories</h2>
        <p class="text-muted">Create and manage categories for your blog posts.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0">
                    <i class="fas fa-<?php echo $edit_category ? 'edit' : 'plus'; ?> me-2"></i> 
                    <?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <?php if ($edit_category): ?>
                        <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required
                            value="<?php echo $edit_category ? html_escape($edit_category['category_name']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_description" class="form-label">Description</label>
                        <textarea class="form-control" id="category_description" name="category_description" rows="3"><?php echo $edit_category ? html_escape($edit_category['category_description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="<?php echo $edit_category ? 'update_category' : 'create_category'; ?>" class="btn manual-button">
                            <i class="fas fa-<?php echo $edit_category ? 'save' : 'plus-circle'; ?> me-1"></i> 
                            <?php echo $edit_category ? 'Update Category' : 'Add Category'; ?>
                        </button>
                        
                        <?php if ($edit_category): ?>
                            <a href="categories.php" class="btn manual-button cancel">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header heading-for text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Categories</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Name</th>
                                <th width="40%">Description</th>
                                <th width="15%">Posts</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $index => $category): 
                                    $cat_posts = $postService->getAllPosts(array('category_id' => $category['category_id']));
                                    $post_count = count($cat_posts['posts']);
                                ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo html_escape($category['category_name']); ?></td>
                                        <td><?php echo html_escape($category['category_description'] ?? ''); ?></td>
                                        <td><?php echo $post_count; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="categories.php?edit=<?php echo $category['category_id']; ?>" class="btn btn-sm manual-button">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <?php
                                                $item = array(
                                                    'id' => $category['category_id'], 
                                                    'name' => $category['category_name']
                                                );
                                                
                                                $table = 'categories';
                                                $item_type = 'Category';
                                                
                                                include('../components/delete_modal.php');
                                                ?>
                                                
                                                <button type="button" class="btn btn-sm manual-button" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#<?php echo $modal_id; ?>"
                                                    <?php echo $post_count > 0 ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-3">No categories found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../components/footer.php'); ?>
