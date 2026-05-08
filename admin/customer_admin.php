<?php
require_once 'includes/admin_auth.php';
admin_auth();
include 'config.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $customerId = intval($_GET['delete']);
    $delete_customer = $conn->prepare("DELETE FROM customers WHERE customer_id=?");
    $delete_customer->bind_param("i", $customerId);
    $delete_customer->execute();
    $delete_customer->close();
    redirect_with_admin_message("customer_admin.php", "Customer deleted.");
}

// Handle Add/Edit Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name        = trim($_POST['full_name'] ?? '');
    $customer_email       = trim($_POST['email'] ?? '');
    $customer_phone_number = trim($_POST['phone_number'] ?? '');
    $customer_address     = trim($_POST['address'] ?? '');
    $customer_password    = $_POST['customer_password'] ?? '';
    $update_id            = intval($_POST['update_id'] ?? 0);

    if (empty($customer_name) || empty($customer_email) || empty($phone_number) || empty($customer_address)) {
        set_admin_message("Customer name, email, phone number, and address are required.", "error");
    } else {
        if ($update_id > 0) {
            // Update existing customer
            if (!empty($customer_password)) {
                $hashedCustomerPassword = password_hash($customer_password, PASSWORD_DEFAULT);
                $update_customer = $conn->prepare("UPDATE customers SET customer_name=?, customer_email=?, customer_password=?, phone_number=?, address=? WHERE customer_id=?");
                $update_customer->bind_param("sssssi", $customer_name, $customer_email, $hashedCustomerPassword, $customer_phone_number, $customer_address, $update_id);
            } else {
                $update_customer = $conn->prepare("UPDATE customers SET customer_name=?, customer_email=?, phone_number=?, address=? WHERE customer_id=?");
                $update_customer->bind_param("ssssi", $customer_name, $customer_email, $customer_phone_number, $customer_address, $update_id);
            }
            $update_customer->execute();
            $update_customer->close();
            redirect_with_admin_message("customer_admin.php", "Customer updated.");
        } else {
            // Add new customer
            if (empty($customer_password)) {
                set_admin_message("Password is required for new customers.", "error");
            } else {
                $check_customer_email = $conn->prepare("SELECT customer_id FROM customers WHERE customer_email=?");
                $check_customer_email->bind_param("s", $customer_email);
                $check_customer_email->execute();
                $check_customer_email->store_result();
                if ($check_customer_email->num_rows > 0) {
                    $check_customer_email->close();
                    set_admin_message("Customer email already exists.", "error");
                } else {
                    $check_customer_email->close();
                    $hashedCustomerPassword = password_hash($customer_password, PASSWORD_DEFAULT);
                    $create_customer = $conn->prepare("INSERT INTO customers (customer_name, customer_email, customer_password, phone_number, address) VALUES (?, ?, ?, ?, ?)");
                    $create_customer->bind_param("sssss", $customer_name, $customer_email, $hashedCustomerPassword, $customer_phone_number, $customer_address);
                    $create_customer->execute();
                    $create_customer->close();
                    redirect_with_admin_message("customer_admin.php", "Customer saved.");
                }
            }
        }
    }
}

$page_title = "Customers";
$search_keyword = trim($_GET['search'] ?? '');
include 'includes/header.php';
?>

<div class="list-toolbar">
    <form class="search-form" method="get">
        <input class="form-control" type="text" name="search" placeholder="Search customer name, email, or phone" value="<?php echo htmlspecialchars($search_keyword); ?>">
        <button class="btn btn-ghost" type="submit">Search</button>
    </form>
    <button class="btn btn-primary" data-open-modal="customerModal"><i class='fa fa-plus'></i> New Customer</button>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Address</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $customerSql = "SELECT * FROM customers";
        if ($search_keyword !== '') {
            $undo_search = $conn->real_escape_string($search_keyword);
            $customerSql .= " WHERE customer_name LIKE '%{$undo_search}%' OR email LIKE '%{$undo_search}%' OR phone_number LIKE '%{$undo_search}%'";
        }
        $customerSql .= " ORDER BY customer_id DESC";
        $result = $conn->query($customerSql);
        if ($result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
        ?>
            <tr>
                <td><?php echo (int) $row['customer_id']; ?></td>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                <td><?php echo htmlspecialchars($row['address']); ?></td>
                <td>
                    <button class="icon-btn"
                        data-open-modal="customerModal"
                        data-customer-edit="true"
                        data-id="<?php echo (int) $row['customer_id']; ?>"
                        data-name="<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>"
                        data-email="<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>"
                        data-phone="<?php echo htmlspecialchars($row['phone_number'], ENT_QUOTES); ?>"
                        data-address="<?php echo htmlspecialchars($row['address'], ENT_QUOTES); ?>">
                        <i class='fa fa-pencil'></i>
                    </button>
                    <a class="icon-btn delete" data-confirm-delete="true" href="customer_admin.php?delete=<?php echo (int) $row['customer_id']; ?>">
                        <i class='fa fa-trash'></i>
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center;">No customers found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="modal" id="customerModal">
    <div class="modal-card" style="max-width:700px;">
        <div class="modal-header">
            <h3 style="margin:0;" id="customerModalTitle">Add Customer</h3>
            <button type="button" class="icon-btn" data-close-modal><i class='bx bx-x'></i></button>
        </div>

        <div class="modal-body">
            <form method="post" data-custom-validate="true" id="customerForm">
                <input type="hidden" name="update_id" id="customer_update_id" value="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Name</label>
                        <input class="form-control" type="text" name="customer_name" id="customer_name" data-required="true">
                        <div class="field-error" data-error-for="customer_name"></div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input class="form-control" type="email" name="email" id="email" data-required="true">
                        <div class="field-error" data-error-for="email"></div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input class="form-control" type="password" name="customer_password" id="customer_password" placeholder="Customer Password">
                        <div class="field-error" data-error-for="customer_password"></div>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input class="form-control" type="text" name="phone_number" id="phone_number" data-required="true">
                        <div class="field-error" data-error-for="phone_number"></div>
                    </div>

                    <div class="form-group full">
                        <label>Address</label>
                        <textarea class="form-control" name="address" id="address" rows="3"></textarea>
                        <div class="field-error" data-error-for="address"></div>
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
// Populate edit values in floating customer modal.
document.querySelectorAll("[data-customer-edit='true']").forEach(function (btn) {
    btn.addEventListener("click", function () {
        document.getElementById("customerModalTitle").textContent = "Edit Customer";
        document.getElementById("customer_update_id").value = btn.dataset.id || "";
        document.getElementById("customer_name").value = btn.dataset.name || "";
        document.getElementById("email").value = btn.dataset.email || "";
        document.getElementById("phone_number").value = btn.dataset.phone || "";
        document.getElementById("address").value = btn.dataset.address || "";
        document.getElementById("customer_password").placeholder = "New Password (Optional)";
    });
});

document.querySelector("[data-open-modal='customerModal']").addEventListener("click", function () {
    document.getElementById("customerModalTitle").textContent = "Add Customer";
    document.getElementById("customerForm").reset();
    document.getElementById("customer_update_id").value = "";
    document.getElementById("customer_password").placeholder = "Customer Password";
});
</script>

<?php
$conn->close();
include 'includes/footer.php';
?>
