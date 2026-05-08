<?php
require_once 'includes/admin_auth.php';
admin_auth();
include 'config.php';

if (isset($_GET['delete'])) {
    $productId = intval($_GET['delete']);
    $delete_product = $conn->prepare("DELETE FROM products WHERE product_id=?");
    $delete_product->bind_param("i", $productId);
    $delete_product->execute();
    $delete_product->close();
    redirect_with_admin_message("products_admin.php", "Product deleted.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Product form processing using existing data dictionary columns
    $category_id = intval($_POST['category_id'] ?? 0);
    $product_name = trim($_POST['product_name'] ?? '');
    $product_description = trim($_POST['product_description'] ?? '');
    $product_price = floatval($_POST['product_price'] ?? 0);
    $product_image = trim($_POST['product_image'] ?? '');
    $occasion_type = trim($_POST['occasion_type'] ?? '');
    $availability_status = trim($_POST['availability_status'] ?? '');
    $update_id = intval($_POST['update_id'] ?? 0);

    if (isset($_FILES['product_image_file']) && is_uploaded_file($_FILES['product_image_file']['tmp_name'])) {
        $uploadDirectory = __DIR__ . '/uploads/';
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }
        $imageInformation = getimagesize($_FILES['product_image_file']['tmp_name']);
        if ($imageInformation === false) {
            set_admin_message("Uploaded file is not a valid image.", "error");
            redirect_with_admin_message("products_admin.php", "", "error");
        }
        $fileExtension = strtolower(pathinfo($_FILES['product_image_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            set_admin_message("Only JPG, JPEG, PNG, GIF, and WEBP images are allowed.", "error");
            redirect_with_admin_message("products_admin.php", "", "error");
        }
        $savedFileName = uniqid('product_', true) . '.' . $fileExtension;
        $savedFilePath = $uploadDirectory . $savedFileName;
        if (!move_uploaded_file($_FILES['product_image_file']['tmp_name'], $savedFilePath)) {
            set_admin_message("Image upload failed. Please try again.", "error");
            redirect_with_admin_message("products_admin.php", "", "error");
        }
        $product_image = 'uploads/' . $savedFileName;
    }

    if ($category_id <= 0 || $product_name === '' || $product_description === '' || $product_price <= 0 || $occasion_type === '' || $availability_status === '') {
        set_admin_message("Please fill all required fields correctly.", "error");
    } else {
        if ($update_id > 0) {
            $update_product = $conn->prepare("UPDATE products SET category_id=?, product_name=?, product_description=?, product_price=?, product_image=?, occasion_type=?, availability_status=? WHERE product_id=?");
            $update_product->bind_param("issdsssi", $category_id, $product_name, $product_description, $product_price, $product_image, $occasion_type, $availability_status, $update_id);
            $update_product->execute();
            $update_product->close();
            redirect_with_admin_message("products_admin.php", "Product updated.");
        } else {
            $create_product = $conn->prepare("INSERT INTO products (category_id, product_name, product_description, product_price, product_image, occasion_type, availability_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $create_product->bind_param("issdsss", $category_id, $product_name, $product_description, $product_price, $product_image, $occasion_type, $availability_status);
            $create_product->execute();
            $create_product->close();
            redirect_with_admin_message("products_admin.php", "Product saved.");
        }
    }
}

$search_keyword = trim($_GET['search'] ?? '');
$categoryResult = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
$productListSql = "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id";
if ($search_keyword !== '') {
    $undo_search = $conn->real_escape_string($search_keyword);
    $productListSql .= " WHERE p.product_name LIKE '%{$undo_search}%' OR c.category_name LIKE '%{$undo_search}%'";
}
$productListSql .= " ORDER BY p.product_id DESC";
$productListResult = $conn->query($productListSql);

$page_title = "Products";
include 'includes/header.php';
?>

<div class="list-toolbar">
    <form class="search-form" method="get">
        <input class="form-control" type="text" name="search" placeholder="Search product or category" value="<?php echo htmlspecialchars($search_keyword); ?>">
        <button class="btn btn-ghost" type="submit">Search</button>
    </form>
    <button class="btn btn-primary" data-open-modal="productModal"><i class='fa fa-plus'></i> New Product</button>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($productListResult && $productListResult->num_rows > 0): ?>
            <?php while ($row = $productListResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo (int) $row['product_id']; ?></td>
                    <td>
                        <?php if (!empty($row['product_image'])): ?>
                            <img src="<?php echo htmlspecialchars($row['product_image']); ?>" alt="Product Image" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['product_name']); ?><br>
                        <small style="color:#989898;"><?php echo htmlspecialchars($row['occasion_type']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($row['category_name'] ?? 'Unknown'); ?></td>
                    <td>RM <?php echo number_format($row['product_price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['availability_status']); ?></td>
                    <td>
                        <button class="icon-btn"
                            data-open-modal="productModal"
                            data-product-edit="true"
                            data-id="<?php echo (int) $row['product_id']; ?>"
                            data-category="<?php echo (int) $row['category_id']; ?>"
                            data-name="<?php echo htmlspecialchars($row['product_name'], ENT_QUOTES); ?>"
                            data-description="<?php echo htmlspecialchars($row['product_description'], ENT_QUOTES); ?>"
                            data-price="<?php echo htmlspecialchars($row['product_price'], ENT_QUOTES); ?>"
                            data-image="<?php echo htmlspecialchars($row['product_image'], ENT_QUOTES); ?>"
                            data-occasion="<?php echo htmlspecialchars($row['occasion_type'], ENT_QUOTES); ?>"
                            data-status="<?php echo htmlspecialchars($row['availability_status'], ENT_QUOTES); ?>">
                            <i class='fa fa-pencil'></i>
                        </button>
                        <a class="icon-btn delete" data-confirm-delete="true" href="products_admin.php?delete=<?php echo (int) $row['product_id']; ?>"><i class='fa fa-trash'></i></a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No products found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="modal" id="productModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 style="margin:0;" id="productModalTitle">Add Product</h3>
            <button type="button" class="icon-btn" data-close-modal><i class='bx bx-x'></i></button>
        </div>


        <div class="modal-body">
            <form method="post" enctype="multipart/form-data" data-custom-validate="true" id="productForm">
                <input type="hidden" name="update_id" id="product_update_id" value="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Category</label>
                        <select class="form-control" name="category_id" id="product_category_id" data-required="true">
                            <option value="">Select Category</option>
                            <?php if ($categoryResult): while ($cat = $categoryResult->fetch_assoc()): ?>
                                <option value="<?php echo (int) $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                            <?php endwhile; endif; ?>
                        </select>


                        <div class="field-error" data-error-for="category_id"></div>
                    </div>

                    <div class="form-group">
                        <label>Product Name</label>
                        <input class="form-control" type="text" name="product_name" id="product_name" data-required="true">
                        <div class="field-error" data-error-for="product_name"></div>
                    </div>

                    <div class="form-group full">
                        <label>Description</label>
                        <textarea class="form-control" name="product_description" id="product_description" rows="3" data-required="true"></textarea>
                        <div class="field-error" data-error-for="product_description"></div>
                    </div>

                    <div class="form-group">
                        <label>Price</label>
                        <input class="form-control" type="number" step="0.01" name="product_price" id="product_price" data-required="true">
                        <div class="field-error" data-error-for="product_price"></div>
                    </div>

                    <div class="form-group">
                        <label>Image</label>
                        <input class="form-control" type="text" name="product_image" id="product_image">
                    </div>
                    <div class="form-group">
                        <label>Upload Image</label>
                        <input class="form-control" type="file" name="product_image_file" id="product_image_file" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Occasion Type</label>
                        <input class="form-control" type="text" name="occasion_type" id="occasion_type" data-required="true">
                        <div class="field-error" data-error-for="occasion_type"></div>
                    </div>

                    <div class="form-group">
                        <label>Availability Status</label>
                        <select class="form-control" name="availability_status" id="availability_status" data-required="true">
                            <option value="available">Available</option>
                            <option value="out_of_stock">Out of stock</option>
                            <option value="disabled">Disabled</option>
                        </select>
                        <div class="field-error" data-error-for="availability_status"></div>
                    </div>
                </div>


                <div class="modal-actions">
                    <button type="button" class="btn btn-ghost" data-close-modal>Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Prefill product data into floating form when clicking edit icon.
document.querySelectorAll("[data-product-edit='true']").forEach(function (btn) {
    btn.addEventListener("click", function () {
        document.getElementById("productModalTitle").textContent = "Edit Product";
        document.getElementById("product_update_id").value = btn.dataset.id || "";
        document.getElementById("product_category_id").value = btn.dataset.category || "";
        document.getElementById("product_name").value = btn.dataset.name || "";
        document.getElementById("product_description").value = btn.dataset.description || "";
        document.getElementById("product_price").value = btn.dataset.price || "";
        document.getElementById("product_image").value = btn.dataset.image || "";
        document.getElementById("occasion_type").value = btn.dataset.occasion || "";
        document.getElementById("availability_status").value = btn.dataset.status || "available";
    });
});

document.querySelector("[data-open-modal='productModal']").addEventListener("click", function () {
    document.getElementById("productModalTitle").textContent = "Add Product";
    document.getElementById("productForm").reset();
    document.getElementById("product_update_id").value = "";
});
</script>

<?php
$conn->close();
include 'includes/footer.php';
?>
