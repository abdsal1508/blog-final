<?php
class CategoryService {
    private $categoryModel;
    
    public function __construct() {
        $this->categoryModel = new Category();
    }
    
    public function getAllCategories() {
        return $this->categoryModel->getAll();
    }
    
    public function getCategory($id) {
        return $this->categoryModel->findById($id);
    }
    
    public function createCategory($name, $description) {
        return $this->categoryModel->create($name, $description);
    }
    
    public function updateCategory($id, $name, $description) {
        return $this->categoryModel->update($id, $name, $description);
    }
    
    public function deleteCategory($id) {
        return $this->categoryModel->delete($id);
    }
    
    public function countCategories() {
        return $this->categoryModel->count();
    }
}
?>
