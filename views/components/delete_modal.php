<?php
if (!isset($item_type)) {
    $item_type = 'Item';
}

$modal_id = 'deleteModal_' . $table . '_' . $item['id'];
?>

<div class="modal fade" id="<?php echo $modal_id; ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash-alt me-2"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this <?php echo strtolower($item_type); ?> titled "<strong><?php echo html_escape($item['name']); ?></strong>"?</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-exclamation-circle me-2"></i> This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn manual-button cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                    <input type="hidden" name="delete_type" value="<?php echo $table; ?>">
                    <button type="submit" name="delete_item" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-1"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
