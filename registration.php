<?php
// Start session for storing messages
session_start();

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
$name = $email = $password = $confirm_password = "";
$name_err = $email_err = $password_err = $confirm_password_err = "";

// Process form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "This email is already registered.";
                } else {
                    $email = trim($_POST["email"]);
                }
            }
            $stmt->close();
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm your password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting in database
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sss", $param_name, $param_email, $param_password);

            // Set parameters
            $param_name = $name;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to login page
                header("location: login.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
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
    <title>Registration - Furnessence</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .registration-container {
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
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: var(--tan-crayola);
            font-weight: var(--fw-500);
        }
        .password-strength {
            margin-top: 5px;
            font-size: 1.2rem;
        }
        .weak { color: red; }
        .medium { color: orange; }
        .strong { color: green; }
    </style>
</head>
<body>
    <div class="registration-container">
        <h2 style="text-align: center; margin-bottom: 30px;">Create Account</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="registrationForm">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo $name; ?>" required onkeyup="validateName()">
                <span class="error" id="nameError"><?php echo $name_err; ?></span>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo $email; ?>" required onblur="validateEmail()">
                <span class="error" id="emailError"><?php echo $email_err; ?></span>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required onkeyup="checkPasswordStrength()">
                <div class="password-strength" id="passwordStrength"></div>
                <span class="error"><?php echo $password_err; ?></span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required onkeyup="validateConfirmPassword()">
                <span class="error" id="confirmPasswordError"><?php echo $confirm_password_err; ?></span>
            </div>

            <button type="submit" class="btn">Register</button>
        </form>

        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script>
        function validateName() {
            const name = document.getElementById('name').value;
            const nameError = document.getElementById('nameError');

            // Name validation: not empty, contains only letters and spaces, minimum 2 characters
            const nameRegex = /^[a-zA-Z\s]+$/;
            if (name === '') {
                nameError.textContent = 'Please enter your name.';
                nameError.style.color = 'var(--red-orange-color-wheel)';
            } else if (name.length < 2) {
                nameError.textContent = 'Name must be at least 2 characters long.';
                nameError.style.color = 'var(--red-orange-color-wheel)';
            } else if (!nameRegex.test(name)) {
                nameError.textContent = 'Name can only contain letters and spaces.';
                nameError.style.color = 'var(--red-orange-color-wheel)';
            } else {
                nameError.textContent = 'Valid name.';
                nameError.style.color = 'green';
            }
        }

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

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthIndicator = document.getElementById('passwordStrength');

            let strength = 0;
            let feedback = [];

            if (password.length >= 6) strength++;
            else feedback.push('At least 6 characters');

            if (/[a-z]/.test(password)) strength++;
            else feedback.push('Lowercase letter');

            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('Uppercase letter');

            if (/[0-9]/.test(password)) strength++;
            else feedback.push('Number');

            if (/[^A-Za-z0-9]/.test(password)) strength++;
            else feedback.push('Special character');

            switch(strength) {
                case 0:
                case 1:
                    strengthIndicator.innerHTML = '<span class="weak">Weak password</span>';
                    break;
                case 2:
                case 3:
                    strengthIndicator.innerHTML = '<span class="medium">Medium password</span>';
                    break;
                case 4:
                case 5:
                    strengthIndicator.innerHTML = '<span class="strong">Strong password</span>';
                    break;
            }

            if (feedback.length > 0) {
                strengthIndicator.innerHTML += '<br><small>Missing: ' + feedback.join(', ') + '</small>';
            }
        }

        function validateConfirmPassword() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const confirmError = document.getElementById('confirmPasswordError');

            if (confirmPassword === '') {
                confirmError.textContent = 'Please confirm your password.';
                confirmError.style.color = 'var(--red-orange-color-wheel)';
            } else if (password !== confirmPassword) {
                confirmError.textContent = 'Passwords do not match.';
                confirmError.style.color = 'var(--red-orange-color-wheel)';
            } else {
                confirmError.textContent = 'Passwords match.';
                confirmError.style.color = 'green';
            }
        }

        // Form submission validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            let isValid = true;

            // Name validation
            const nameRegex = /^[a-zA-Z\s]+$/;
            if (name === '' || name.length < 2 || !nameRegex.test(name)) {
                document.getElementById('nameError').textContent = 'Please enter a valid name (at least 2 characters, letters and spaces only).';
                isValid = false;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('emailError').textContent = 'Please enter a valid email address.';
                isValid = false;
            }

            // Password validation
            if (password.length < 6) {
                document.getElementById('password').nextElementSibling.nextElementSibling.textContent = 'Password must have at least 6 characters.';
                isValid = false;
            }

            // Confirm password validation
            if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'Passwords do not match.';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
