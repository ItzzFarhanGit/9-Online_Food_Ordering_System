<?php
require_once 'db.php';
include 'admin-header.php';

$msg = '';
$err = '';

// Handle Food Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $food_id = (int)($_GET['id'] ?? 0);
    if ($food_id > 0) {
        try {
            // First check if product exists
            $stmt = $pdo->prepare("SELECT name, image FROM products WHERE id = ?");
            $stmt->execute([$food_id]);
            $product = $stmt->fetch();
            
            if ($product) {
                // Delete product
                $stmt_del = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt_del->execute([$food_id]);
                
                // Optionally delete physical file if it exists and is not a default seed image
                $default_images = ['Italian-pizza.jpg', 'Double-burger.jpg', 'Fried-Chicken.jpg', 'Sea food pasta.jpg', 'Fried-chicken-bucket.jpg', 'Caramel-Topped Ice Cream Dessert.jpg', 'Cheese-pizza.jpg', 'Chicken Burger.png', 'Chicken.jpg', 'Pasta.jpg', 'Drink.jpg', 'Dessert .jpg'];
                if (!in_array($product['image'], $default_images) && file_exists($product['image'])) {
                    unlink($product['image']);
                }

                $msg = "Food item '" . htmlspecialchars($product['name']) . "' deleted successfully!";
            } else {
                $err = "Food item not found.";
            }
        } catch (PDOException $e) {
            // Check for foreign key constraint violation
            if ($e->getCode() == '23000') {
                $err = "Cannot delete this food item because it is part of existing orders. You can modify its description to indicate it is unavailable instead.";
            } else {
                $err = "Database error: Failed to delete item.";
            }
        }
    }
}

// Handle Adding Food Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_food'])) {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $discount = trim($_POST['discount'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // File upload validation
    $image_file = $_FILES['image'] ?? null;
    $image_name = '';

    if (empty($name) || empty($category) || $price <= 0 || empty($description)) {
        $err = "Please fill in all required fields.";
    } elseif (!$image_file || $image_file['error'] !== UPLOAD_ERR_OK) {
        $err = "Please upload a valid food image.";
    } else {
        // Validate image file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $file_type = mime_content_type($image_file['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            $err = "Invalid image file type. Allowed formats: JPEG, JPG, PNG, WEBP.";
        } else {
            // Create a unique name to avoid naming collisions
            $file_ext = pathinfo($image_file['name'], PATHINFO_EXTENSION);
            $new_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($image_file['name'], PATHINFO_FILENAME)) . '.' . $file_ext;
            
            // Move file to project root folder
            $upload_path = $new_filename;
            
            if (move_uploaded_file($image_file['tmp_name'], $upload_path)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO products (name, category, price, discount, description, image, rating) 
                        VALUES (?, ?, ?, ?, ?, ?, 5.0)
                    ");
                    $stmt->execute([
                        $name,
                        $category,
                        $price,
                        !empty($discount) ? $discount : null,
                        $description,
                        $new_filename
                    ]);
                    $msg = "Food item '" . htmlspecialchars($name) . "' added successfully to catalog!";
                } catch (PDOException $e) {
                    $err = "Database error: Failed to save food details. " . $e->getMessage();
                    // Cleanup uploaded file
                    unlink($upload_path);
                }
            } else {
                $err = "Failed to upload image file to server.";
            }
        }
    }
}

// Fetch all products
$products = [];
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle error
}
?>

<div class="admin-dashboard-section">
    <div class="admin-panel-container">
        
        <div class="admin-page-header">
            <h1>Manage Food Catalog</h1>
            <p>Add new meals, upload photos, set pricing, or remove dishes from the shop.</p>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="admin-alert success"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <?php if (!empty($err)): ?>
            <div class="admin-alert error"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($err); ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;">
            
            <!-- LEFT COLUMN: ADD FOOD FORM -->
            <div class="admin-card">
                <h2><i class="fa-solid fa-circle-plus"></i> Add New Food</h2>
                <form action="admin-foods.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_food" value="1">
                    
                    <div class="form-group">
                        <label>Food Item Name *</label>
                        <input type="text" name="name" placeholder="e.g. Hawaiian Supreme Pizza" required>
                    </div>

                    <div class="admin-form-grid" style="grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category" required>
                                <option value="Pizza">Pizza</option>
                                <option value="Burger">Burger</option>
                                <option value="Chicken">Chicken</option>
                                <option value="Pasta">Pasta</option>
                                <option value="Drinks">Drinks</option>
                                <option value="Desserts">Desserts</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Price (Rs.) *</label>
                            <input type="number" name="price" min="1" step="0.01" placeholder="2450" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Discount Badge (Optional)</label>
                        <input type="text" name="discount" placeholder="e.g. -20% or NEW">
                    </div>

                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" rows="4" placeholder="Describe the flavors, ingredients, serving sizes..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Food Image Upload *</label>
                        <input type="file" name="image" accept="image/*" required>
                        <small style="color: #888; font-size: 11px; display: block; margin-top: 5px;">Allowed formats: JPG, JPEG, PNG, WEBP.</small>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">
                        <i class="fa-solid fa-upload"></i> Upload & Add Food
                    </button>
                </form>
            </div>

            <!-- RIGHT COLUMN: PRODUCTS LIST -->
            <div class="admin-card">
                <h2><i class="fa-solid fa-table-list"></i> Food Catalog List</h2>
                
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Badge</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $prod): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($prod['image']); ?>" alt="food" class="admin-img-preview">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($prod['name']); ?></strong>
                                            <div style="font-size: 11px; color: #888; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <?php echo htmlspecialchars($prod['description']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($prod['category']); ?></td>
                                        <td><strong>Rs. <?php echo number_format($prod['price']); ?></strong></td>
                                        <td>
                                            <?php if (!empty($prod['discount'])): ?>
                                                <span style="background: #ff3b30; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600;">
                                                    <?php echo htmlspecialchars($prod['discount']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: #ccc;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="admin-foods.php?action=delete&id=<?php echo $prod['id']; ?>" class="btn-action delete" onclick="return confirm('Are you sure you want to delete \'<?php echo htmlspecialchars(addslashes($prod['name'])); ?>\' from the menu?');">
                                                <i class="fa-solid fa-trash-can"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #888;">No food items in catalog.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

<?php include 'admin-footer.php'; ?>
