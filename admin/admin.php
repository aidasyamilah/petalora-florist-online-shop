<?php
require_once 'includes/admin_auth.php';
admin_auth();
include 'config.php';

$current_admin_id = (int) ($_SESSION['admin_id'] ?? 0);

if (isset($_GET['delete'])) {
    $admin_id_to_delete = (int) $_GET['delete'];

    if ($admin_id_to_delete !== $current_admin_id) {
        redirect_with_admin_message('admin.php', 'You can only delete your own account.', 'error');
    }

    $delete_admin_sql = $conn->prepare('DELETE FROM admin WHERE admin_id = ?');
    $delete_admin_sql->bind_param('i', $admin_id_to_delete);
    $delete_admin_sql->execute();
    $delete_admin_sql->close();

    session_unset();
    session_destroy();
    header('Location: login_admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_name = trim($_POST['admin_name'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_password = trim($_POST['admin_password'] ?? '');
    $edit_admin_id = (int) ($_POST['edit_admin_id'] ?? 0);

    if ($admin_name === '' || $admin_email === '') {
        set_admin_message('Admin name and email are required.', 'error');
    } elseif ($edit_admin_id > 0) {
        if ($admin_password !== '') {
            $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
            $update_admin_sql = $conn->prepare('UPDATE admin SET admin_name = ?, admin_email = ?, admin_password = ? WHERE admin_id = ?');
            $update_admin_sql->bind_param('sssi', $admin_name, $admin_email, $password_hash, $edit_admin_id);
        } else {
            $update_admin_sql = $conn->prepare('UPDATE admin SET admin_name = ?, admin_email = ? WHERE admin_id = ?');
            $update_admin_sql->bind_param('ssi', $admin_name, $admin_email, $edit_admin_id);
        }

        $update_admin_sql->execute();
        $update_admin_sql->close();
        redirect_with_admin_message('admin.php', 'Admin updated.');
    } else {
        $check_email_sql = $conn->prepare('SELECT admin_id FROM admin WHERE admin_email = ?');
        $check_email_sql->bind_param('s', $admin_email);
        $check_email_sql->execute();
        $check_email_sql->store_result();

        if ($check_email_sql->num_rows > 0) {
            $check_email_sql->close();
            set_admin_message('Admin email already exists.', 'error');
        } elseif ($admin_password === '') {
            $check_email_sql->close();
            set_admin_message('Password is required for a new admin.', 'error');
        } else {
            $check_email_sql->close();
            $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
            $create_admin_sql = $conn->prepare('INSERT INTO admin (admin_name, admin_email, admin_password) VALUES (?, ?, ?)');
            $create_admin_sql->bind_param('sss', $admin_name, $admin_email, $password_hash);
            $create_admin_sql->execute();
            $create_admin_sql->close();
            redirect_with_admin_message('admin.php', 'Admin added.');
        }
    }
}

$search_keyword = trim($_GET['search'] ?? '');
$admins_sql = 'SELECT admin_id, admin_name, admin_email FROM admin';
if ($search_keyword !== '') {
    $safe_search_keyword = $conn->real_escape_string($search_keyword);
    $admins_sql .= " WHERE admin_name LIKE '%{$safe_search_keyword}%' OR admin_email LIKE '%{$safe_search_keyword}%'";
}
$admins_sql .= ' ORDER BY admin_id DESC';
$admins_result = $conn->query($admins_sql);

$page_title = 'Admins';
include 'includes/header.php';
?>

<div class="list-toolbar">
    <form class="search-form" method="get">
        <input class="form-control" type="text" name="search" placeholder="Search admin name or email" value="<?php echo htmlspecialchars($search_keyword); ?>">
        <button class="btn btn-ghost" type="submit">Search</button>
    </form>
    <button class="btn btn-primary" data-open-panel="admin_form_panel"><i class="fa fa-plus"></i> New Admin</button>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($admins_result && $admins_result->num_rows > 0): ?>
            <?php while ($admin_row = $admins_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo (int) $admin_row['admin_id']; ?></td>
                    <td><?php echo htmlspecialchars($admin_row['admin_name']); ?></td>
                    <td><?php echo htmlspecialchars($admin_row['admin_email']); ?></td>
                    <td>
                        <button class="icon-btn"
                                data-open-panel="admin_form_panel"
                                data-admin-edit="true"
                                data-id="<?php echo (int) $admin_row['admin_id']; ?>"
                                data-name="<?php echo htmlspecialchars($admin_row['admin_name'], ENT_QUOTES); ?>"
                                data-email="<?php echo htmlspecialchars($admin_row['admin_email'], ENT_QUOTES); ?>">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <?php if ((int) $admin_row['admin_id'] === $current_admin_id): ?>
                            <a class="icon-btn delete" data-confirm-delete="true" href="admin.php?delete=<?php echo (int) $admin_row['admin_id']; ?>">
                                <i class="fa fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align:center;">No admins found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="popup-panel" id="admin_form_panel">
    <div class="popup-card" style="max-width:700px;">
        <div class="popup-header">
            <h3 style="margin:0;" id="admin_form_title">Add Admin</h3>
            <button type="button" class="icon-btn" data-close-panel><i class="fa fa-times"></i></button>
        </div>
        <div class="popup-body">
            <form method="post" data-custom-validate="true" id="admin_form">
                <input type="hidden" name="edit_admin_id" id="edit_admin_id" value="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Name</label>
                        <input class="form-control" type="text" name="admin_name" id="admin_name" data-required="true">
                        <div class="field-error" data-error-for="admin_name"></div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input class="form-control" type="email" name="admin_email" id="admin_email" data-required="true">
                        <div class="field-error" data-error-for="admin_email"></div>
                    </div>
                    <div class="form-group full">
                        <label>Password (leave empty to keep current password)</label>
                        <input class="form-control" type="password" name="admin_password" id="admin_password">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-ghost" data-close-panel>Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll("[data-admin-edit='true']").forEach(function (edit_button) {
    edit_button.addEventListener("click", function () {
        document.getElementById("admin_form_title").textContent = "Edit Admin";
        document.getElementById("edit_admin_id").value = edit_button.dataset.id || "";
        document.getElementById("admin_name").value = edit_button.dataset.name || "";
        document.getElementById("admin_email").value = edit_button.dataset.email || "";
        document.getElementById("admin_password").value = "";
    });
});

document.querySelector("[data-open-panel='admin_form_panel']").addEventListener("click", function () {
    document.getElementById("admin_form_title").textContent = "Add Admin";
    document.getElementById("admin_form").reset();
    document.getElementById("edit_admin_id").value = "";
});
</script>

<?php
$conn->close();
include 'includes/footer.php';
?>
