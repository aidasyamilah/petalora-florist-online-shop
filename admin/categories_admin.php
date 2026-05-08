<?php
require_once 'includes/admin_auth.php';
admin_auth();
include 'config.php';

if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    $delete_category = $conn->prepare("DELETE FROM categories WHERE category_id=?");
    $delete_category->bind_param("i", $category_id);
    $delete_category->execute();
    $delete_category->close();
    redirect_with_admin_message("categories_admin.php", "Category deleted.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category_name = trim($_POST['category_name'] ?? '');
    $category_description = trim($_POST['category_description'] ?? '');
    $update_id = intval($_POST['update_id'] ?? 0);

    if ($category_name === '') {
        set_admin_message("Category name is required.", "error");
    } else {
        if ($update_id > 0) {
            $update_category = $conn->prepare("UPDATE categories SET category_name=?, category_description=? WHERE category_id=?");
            $update_category->bind_param("ssi", $category_name, $category_description, $update_id);
            $update_category->execute();
            $update_category->close();
            redirect_with_admin_message("categories_admin.php", "Category updated.");
        } else {
            $check_category_name = $conn->prepare("SELECT category_id FROM categories WHERE category_name=?");
            $check_category_name->bind_param("s", $category_name);
            $check_category_name->execute();
            $check_category_name->store_result();
            if ($check_category_name->num_rows > 0) {
                $check_category_name->close();
                set_admin_message("Category with this name already exists.", "error");
            } else {
                $check_category_name->close();
                $create_category = $conn->prepare("INSERT INTO categories (category_name, category_description) VALUES (?, ?)");
                $create_category->bind_param("ss", $category_name, $category_description);
                $create_category->execute();
                $create_category->close();
                redirect_with_admin_message("categories_admin.php", "Category saved.");
            }
        }
    }
}

$search_keyword = trim($_GET['search'] ?? '');
$category_sql = "SELECT * FROM categories";
if ($search_keyword !== '') {
    $undo_search = $conn->real_escape_string($search_keyword);
    $category_sql .= " WHERE category_name LIKE '%{$undo_search}%' OR category_description LIKE '%{$undo_search}%'";
}

$category_sql .= " ORDER BY category_id DESC";
$result = $conn->query($category_sql);
$page_title = "Categories";
include 'includes/header.php';
?>
<div class="list-toolbar">
    <form class="search-form" method="get">
        <input class="form-control" type="text" name="search" placeholder="Search category" value="<?php echo htmlspecialchars($search_keyword); ?>">
        <button class="btn btn-ghost" type="submit">Search</button>
    </form>
    <button class="btn btn-primary" data-open-modal="category_modal"><i class='fa fa-plus'></i> New Category</button>
</div>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Description</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                <td><?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $row['category_name']))); ?></td>
                <td><?php echo htmlspecialchars($row['category_description']); ?></td>
                <td>
                    <button class="icon-btn"
                        data-open-modal="category_modal"
                        data-category-edit="true"
                        data-id="<?php echo (int) $row['category_id']; ?>"
                        data-name="<?php echo htmlspecialchars($row['category_name'], ENT_QUOTES); ?>"
                        data-description="<?php echo htmlspecialchars($row['category_description'], ENT_QUOTES); ?>">
                        <i class='fa fa-pencil'></i>
                    </button>
                    <a class="icon-btn delete" data-confirm-delete="true" href="categories_admin.php?delete=<?php echo (int) $row['category_id']; ?>"><i class='fa fa-trash'></i></a>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
        <tr>
            <td colspan="4" style="text-align:center;">No categories found.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="modal" id="category_modal">
    <div class="modal-card" style="max-width:700px;">
        <div class="modal-header">
            <h3 style="margin:0;" id="category_modal_title">Add Category</h3>
            <button type="button" class="icon-btn" data-close-modal><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body">
            <form method="post" data-custom-validate="true" id="category_form">
                <input type="hidden" name="update_id" id="category_update_id" value="">
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Name</label>
                        <input class="form-control" type="text" name="category_name" id="category_name" data-required="true">
                        <div class="field-error" data-error-for="category_name"></div>
                    </div>
                    <div class="form-group full">
                        <label>Description</label>
                        <textarea class="form-control" name="category_description" id="category_description" rows="4"></textarea>
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
// Populate edit values in floating category modal.
document.querySelectorAll("[data-category-edit='true']").forEach(function (btn) {
    btn.addEventListener("click", function () {
        document.getElementById("category_modal_title").textContent = "Edit Category";
        document.getElementById("category_update_id").value = btn.dataset.id || "";
        document.getElementById("category_name").value = btn.dataset.name || "";
        document.getElementById("category_description").value = btn.dataset.description || "";
    });
});

document.querySelector("[data-open-modal='category_modal']").addEventListener("click", function () {
    document.getElementById("category_modal_title").textContent = "Add Category";
    document.getElementById("category_form").reset();
    document.getElementById("category_update_id").value = "";
});
</script>

<?php
$conn->close();
include 'includes/footer.php';
?>
