<?php
// Start session for storing messages
session_start();

// CSRF Protection: Generate token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Rate Limiting: Simple implementation using session
$max_attempts = 5;
$lockout_time = 900; // 15 minutes

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = time();
}

$current_time = time();
if ($_SESSION['login_attempts'] >= $max_attempts) {
    if (($current_time - $_SESSION['last_attempt']) < $lockout_time) {
        $remaining_time = $lockout_time - ($current_time - $_SESSION['last_attempt']);
        $error = "Too many failed login attempts. Please try again in " . ceil($remaining_time / 60) . " minutes.";
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

// Database connection (adjust these credentials as needed)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "furnessence";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$email = $password = "";
$email_err = $password_err = $general_err = "";

// Process form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $general_err = "Invalid request. Please try again.";
    } else {
        // Validate email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter your email.";
        } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        } else {
            $email = trim($_POST["email"]);
        }

        // Validate password
        if (empty(trim($_POST["password"]))) {
            $password_err = "Please enter your password.";
        } else {
            $password = trim($_POST["password"]);
        }

        // Check input errors before querying database
        if (empty($email_err) && empty($password_err) && empty($general_err)) {
            // Prepare a select statement
            $sql = "SELECT id, name, email, password FROM users WHERE email = ?";

            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("s", $param_email);

                // Set parameters
                $param_email = $email;

                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Store result
                    $stmt->store_result();

                    // Check if email exists, if yes then verify password
                    if ($stmt->num_rows == 1) {
                        // Bind result variables
                        $stmt->bind_result($id, $name, $email_db, $hashed_password);
                        if ($stmt->fetch()) {
                            if (password_verify($password, $hashed_password)) {
                                // Password is correct, reset attempts and start a new session
                                $_SESSION['login_attempts'] = 0;

                                // Store data in session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["name"] = $name;
                                $_SESSION["email"] = $email_db;

                                // Remember Me functionality
                                if (isset($_POST['remember_me'])) {
                                    $token = bin2hex(random_bytes(32));
                                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true); // 30 days, secure, httponly

                                    // Store token in database (you'd need to add a remember_tokens table)
                                    // For simplicity, we'll skip database storage here, but in production, store it securely
                                }

                                // Redirect user to welcome page or index
                                header("location: index.php");
                                exit();
                            } else {
                                // Password is not valid
                                $_SESSION['login_attempts']++;
                                $_SESSION['last_attempt'] = time();
                                $general_err = "Invalid email or password.";
                            }
                        }
                    } else {
                        // Email doesn't exist
                        $_SESSION['login_attempts']++;
                        $_SESSION['last_attempt'] = time();
                        $general_err = "Invalid email or password.";
                    }
                } else {
                    $general_err = "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                $stmt->close();
            }
        }
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Furnessence</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: var(--fw-500);
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            font-size: 1.6rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--tan-crayola);
        }
        .error {
            color: var(--red-orange-color-wheel);
            font-size: 1.4rem;
            margin-top: 5px;
        }
        .success {
            color: green;
            font-size: 1.4rem;
            margin-top: 5px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background-color: var(--tan-crayola);
            color: var(--white);
            border: none;
            border-radius: 4px;
            font-size: 1.6rem;
            font-weight: var(--fw-500);
            cursor: pointer;
            transition: var(--transition-1);
        }
        .btn:hover {
            background-color: var(--smokey-black);
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: var(--tan-crayola);
            font-weight: var(--fw-500);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 style="text-align: center; margin-bottom: 30px;">Login to Furnessence</h2>

        <?php if (!empty($error)): ?>
            <div class="error" style="text-align: center; margin-bottom: 20px;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo $email; ?>" required onblur="validateEmail()">
                <span class="error" id="emailError"><?php echo $email_err; ?></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <span class="error"><?php echo $password_err; ?></span>
            </div>

            <div class="form-group" style="display: flex; align-items: center; margin-bottom: 20px;">
                <input type="checkbox" id="remember_me" name="remember_me" style="margin-right: 8px;">
                <label for="remember_me" style="margin-bottom: 0; font-weight: normal;">Remember Me</label>
            </div>

            <?php if (!empty($general_err)): ?>
                <div class="error" style="text-align: center; margin-bottom: 15px;"><?php echo $general_err; ?></div>
            <?php endif; ?>

            <button type="submit" class="btn">Login</button>
        </form>

        <div class="register-link">
            <p>Don't have an account? <a href="registration.php">Register here</a></p>
        </div>
    </div>

    <script>
        function validateEmail() {
            const email = document.getElementById('email').value;
            const emailError = document.getElementById('emailError');

            // Basic email format validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email === '') {
                emailError.textContent = 'Please enter your email.';
                emailError.style.color = 'var(--red-orange-color-wheel)';
            } else if (!emailRegex.test(email)) {
                emailError.textContent = 'Please enter a valid email address.';
                emailError.style.color = 'var(--red-orange-color-wheel)';
            } else {
                emailError.textContent = 'Valid email format.';
                emailError.style.color = 'green';
            }
        }

        // Form submission validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            let isValid = true;

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('emailError').textContent = 'Please enter a valid email address.';
                isValid = false;
            }

            // Password validation
            if (password.length < 6) {
                document.getElementById('password').nextElementSibling.textContent = 'Password must have at least 6 characters.';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
