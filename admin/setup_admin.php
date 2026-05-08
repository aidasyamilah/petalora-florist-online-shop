<?php
include '../config.php' ;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Admin Setup / Debug Tool</h3>";

// 1. Attempt to create or update the admin account
$admin_name = 'admin';
$admin_email = 'admin@gmail.com';
$password_plain = 'admin123';
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

// Check if admin exists
$checkSql = "SELECT admin_id FROM admin WHERE admin_email = 'admin@gmail.com'";
$result = $conn->query($checkSql);

if ($result->num_rows > 0) {

    // UPDATE existing admin
    echo "Admin account found. Updating password...<br>";
    $update_admin = $conn->prepare("UPDATE admin SET admin_password = ?, admin_name = ? WHERE admin_email = 'admin@gmail.com'");
    $update_admin->bind_param("ss", $password_hash, $admin_name);
    if ($update_admin->execute()) {
        echo "<span style='color:green'>Success: Admin password reset to 'admin123'.</span><br>";
    } else {
        echo "<span style='color:red'>Error updating admin: " . $update_admin->error . "</span><br>";
    }
    
} else {
    
    // INSERT new admin
    echo "Admin account not found. Creating...<br>";
    $create_admin = $conn->prepare("INSERT INTO admin (admin_name, admin_email, admin_password) VALUES (?, ?, ?)");
    $create_admin->bind_param("sss", $admin_name, $admin_email, $password_hash);
    
    if ($create_admin->execute()) {
        echo "<span style='color:green'>Success: Admin account created.</span><br>";
    } else {
        echo "<span style='color:red'>Error creating admin: " . $create_admin->error . "</span><br>";
    }
}

echo "<hr>";

// 2. Verify what is in the database now
echo "<h4>Current Admin Records in Database:</h4>";
$sql = "SELECT admin_id, admin_name, admin_email, admin_password FROM admin";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Admin Name</th><th>Email</th><th>Password Hash (prefix)</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["admin_id"] . "</td>";
        echo "<td>" . $row["admin_name"] . "</td>";
        echo "<td>" . $row["admin_email"] . "</td>";
        echo "<td>" . substr($row["admin_password"], 0, 10) . "...</td>"; // Show valid hash start
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "0 results in admin table.";
}

$conn->close();

echo "<br><br><a href='login_admin.php' style='font-size:20px; font-weight:bold;'>Go to Admin Login</a>";
?>
