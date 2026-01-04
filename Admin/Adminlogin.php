<?php
session_start();

// Include config file
require_once '../config.php';

// Establish database connection
$conn = getDBConnection();

// Check if admin is already logged in - redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("location: Admindashboard.php");
    exit();
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement - CHECK FOR ADMIN ROLE
        $sql = "SELECT id, username, password, role FROM users WHERE username = ? AND status = 'active' AND role = 'admin'";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if username exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, store data in session variables
                            $_SESSION["admin_logged_in"] = true;
                            $_SESSION["admin_id"] = $id;
                            $_SESSION["admin_username"] = $username;
                            $_SESSION["admin_role"] = $role;

                            // Redirect user to admin dashboard
                            header("location: Admindashboard.php");
                            exit();
                        } else {
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username doesn't exist or not an admin, display a generic error message
                    $login_err = "Invalid username or password. Admin access required.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Furnessence</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-login-container {
            max-width: 450px;
            width: 100%;
            margin: 40px 20px;
            padding: 50px 40px;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            border-top: 4px solid var(--tan-crayola);
        }

        .admin-login-container h2 {
            text-align: center;
            margin-bottom: 35px;
            color: var(--smokey-black);
            font-size: 2.8rem;
            font-weight: var(--fw-700);
        }

        .admin-login-container h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: var(--tan-crayola);
            margin: 15px auto 0;
        }

        .error-message {
            color: var(--red-orange-color-wheel);
            font-size: 1.3rem;
            margin-top: 8px;
            display: block;
        }

        .login-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 14px 18px;
            border-radius: 6px;
            margin-bottom: 25px;
            border-left: 4px solid #dc3545;
            font-size: 1.4rem;
        }

        .btn-admin-login {
            width: 100%;
            padding: 16px;
            background-color: var(--tan-crayola);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1.6rem;
            font-weight: var(--fw-600);
            cursor: pointer;
            transition: var(--transition-1);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-admin-login:hover {
            background-color: var(--smokey-black);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .back-to-site {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--cultured);
        }

        .back-to-site a {
            color: var(--granite-gray);
            text-decoration: none;
            font-size: 1.4rem;
            font-weight: var(--fw-500);
            transition: var(--transition-1);
        }

        .back-to-site a:hover {
            color: var(--tan-crayola);
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <h2>Admin Login</h2>

        <?php
        if (!empty($login_err)) {
            echo '<div class="login-error">' . $login_err . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" autofocus>
                <span class="error-message"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="error-message"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <button type="submit" class="btn-admin-login">Login</button>
            </div>
        </form>

        <div class="back-to-site">
            <a href="../index.php">← Back to Site</a>
        </div>
    </div>
</body>
</html>
