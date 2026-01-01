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
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ? AND status = 'active'";

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
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["admin_logged_in"] = true;
                            $_SESSION["admin_id"] = $id;
                            $_SESSION["admin_username"] = $username;

                            // Redirect user to admin dashboard
                            header("location: Admindashboard.php");
                            exit();
                        } else {
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username doesn't exist, display a generic error message
                    $login_err = "Invalid username or password.";
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
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 40px;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .admin-login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--tan-crayola);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: var(--fw-500);
            color: var(--smokey-black);
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            font-size: 1.6rem;
            transition: var(--transition-1);
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            outline: none;
            border-color: var(--tan-crayola);
            box-shadow: 0 0 0 2px rgba(210, 180, 140, 0.2);
        }

        .error-message {
            color: var(--red-orange-color-wheel);
            font-size: 1.4rem;
            margin-top: 5px;
        }

        .btn-admin-login {
            width: 100%;
            padding: 15px;
            background-color: var(--tan-crayola);
            color: var(--white);
            border: none;
            border-radius: 4px;
            font-size: 1.6rem;
            font-weight: var(--fw-500);
            cursor: pointer;
            transition: var(--transition-1);
        }

        .btn-admin-login:hover {
            background-color: var(--smokey-black);
        }

        .back-to-site {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-site a {
            color: var(--tan-crayola);
            text-decoration: none;
        }

        .back-to-site a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <h2>Admin Login</h2>

        <?php
        if (!empty($login_err)) {
            echo '<div class="error-message">' . $login_err . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="error-message"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="error-message"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn-admin-login" value="Login">
            </div>
        </form>

        <div class="back-to-site">
            <a href="../index.php">← Back to Site</a>
        </div>
    </div>
</body>
</html>
