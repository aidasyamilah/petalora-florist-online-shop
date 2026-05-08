<?php
require_once 'includes/admin_auth.php';
include 'config.php';

if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) {
    header("Location: dashboard_admin.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_email = trim($_POST['admin_email']);
    $password = trim($_POST['password']);

    if (empty($admin_email) || empty($password)) {
        $error = "Please enter both admin email and password.";
    } else {
        $admin_lookup = $conn->prepare("SELECT admin_id, admin_name, admin_password FROM admin WHERE admin_email = ?");
        $admin_lookup->bind_param("s", $admin_email);
        $admin_lookup->execute();
        $admin_lookup->store_result();

        if ($admin_lookup->num_rows == 1) {
            $admin_lookup->bind_result($admin_id, $admin_name, $hashed_password);
            $admin_lookup->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['admin_login'] = true;
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_name'] = $admin_name;
                $_SESSION['admin_email'] = $admin_email;
                $redirectUrl = $_SESSION['admin_redirect_after_login'] ?? 'dashboard_admin.php';
                unset($_SESSION['admin_redirect_after_login']);
                redirect_with_admin_message($redirectUrl, "Welcome back, " . $admin_name . ".", "success");
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Invalid admin email.";
        }
        $admin_lookup->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Petalora</title>
    <link rel="stylesheet" href="includes/admin.css">

    <style>
        .login-shell { 
            min-height: 100vh; 
            display: grid; 
            grid-template-columns: 1fr 2fr; 
            background:#0f0f10; 
        }

        .login-visual { 
            background: url('includes/background_admin.jpg') center/cover no-repeat;
            width: 100%;
        }
        
        .login-panel { display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 30px; 
            background: #FDE8E8; 
            color: #121213;
        }

        .login-card { 
            width: min(500px, 100%); 
        }
        
        .brand { 
            font-family: "Times New Roman", serif; 
            font-size: 42px; 
            margin: 0 0 10px; 
            font-weight: 500; 
        }
        
        .small { 
            color: #9b9b9b; 
            text-transform: uppercase; 
            letter-spacing: .22em; 
            font-size:10px; 
        }
        
        .title { 
            margin: 8px 0 18px; 
            font-family: "Times New Roman", serif; 
            font-size: 44px; 
            font-weight: 500; 
        }
        
        .error { 
            color: #e37d7d; 
            background:#2a1518; 
            border:1px solid #583136; 
            padding:10px; 
            margin-bottom: 10px; 
            font-size:13px; 
        }
        
        .line-input { 
            width:100%; 
            padding:10px 10px; 
            background:transparent; 
            border:0; 
            border-bottom:1px solid #303033; 
            color:#fff; 
        }
        
        .line-input:focus { 
            outline:none; 
            border-bottom-color:#6f1d29; 
        }

        .login-actions { 
            margin-top: 10px; 
            display:flex; 
            justify-content:space-between; 
            align-items:center; 
        }

        .forgot-link { 
            align-items: right; 
            color:#bfbfbf; 
            font-size:13px; 
            text-decoration:none; 
        }

        .forgot-link:hover { 
            color:#fff; 
        }

        @media (max-width: 900px) { .login-shell { grid-template-columns: 1fr; } .login-visual { display:none; }}
    </style>
</head>


<body>
<div class="login-shell">
    <div class="login-visual"></div>
    <div class="login-panel">
        <div class="login-card">
            <h1 class="brand">Petalora Florist</h1>
            <p class="small">Staff Access</p>
            <h2 class="title">Sign in to the studio.</h2>
            <?php if (!empty($error)) echo '<div class="error">' . htmlspecialchars($error) . '</div>'; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" data-custom-validate="true" novalidate>

                <!-- Admin email field -->
                <div class="form-group">
                    <label class="small" style="letter-spacing:.2em;">Email</label>
                    <input class="line-input" type="email" name="admin_email" data-required="true">
                    <div class="field-error" data-error-for="admin_email"></div>
                </div>

                <!-- Admin password field -->
                <div class="form-group">
                    <label class="small" style="letter-spacing:.2em;">Password</label>
                    <input class="line-input" type="password" name="password" data-required="true">
                    <div class="field-error" data-error-for="password"></div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:4px;border-radius:20px;">Sign In</button>
                <div class="login-actions">
                    <a href="forgot_password_admin.php" class="forgot-link">Forgot password?</a>
                </div>
                
            </form>
        </div>
    </div>
</div>
<script src="includes/admin.js"></script>
</body>
</html>
