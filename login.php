<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'ccshop');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General */
        body {
            background: linear-gradient(135deg, #000428, #004e92);
            font-family: 'Poppins', sans-serif;
            color: white;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Kezzy Shop Title */
        .logo-title {
            text-align: center;
            font-size: 3rem;
            color: #ff6a00;
            font-weight: bold;
            text-shadow: 0px 0px 20px #ff4500, 0px 0px 30px #ff6a00;
            margin-bottom: 30px;
            animation: glowEffect 1.5s infinite alternate;
        }

        @keyframes glowEffect {
            0% { text-shadow: 0px 0px 10px #ff4500, 0px 0px 20px #ff6a00; }
            100% { text-shadow: 0px 0px 20px #ff6a00, 0px 0px 30px #ff4500; }
        }

        /* Form Container */
        .form-container {
            background: linear-gradient(135deg, #232323, #191919);
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.5);
            width: 400px;
            text-align: center;
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: #4ef3c6;
            font-weight: bold;
        }

        .form-container .form-label {
            color: #bbbbbb;
            font-weight: bold;
        }

        .form-container input {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            border-radius: 10px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .form-container input:focus {
            background: rgba(255, 255, 255, 0.2);
            outline: none;
            box-shadow: 0 0 10px #4ef3c6, 0 0 20px #38a3a5;
        }

        /* Submit Button */
        .btn-primary {
            background: linear-gradient(135deg, #4ef3c6, #38a3a5);
            border: none;
            color: black;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 10px;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0px 0px 10px rgba(78, 243, 198, 0.8);
        }

        /* Register Link */
        .register-link {
            color: #f0ad4e;
            margin-top: 20px;
            display: inline-block;
            text-decoration: none;
            font-weight: bold;
        }

        .register-link:hover {
            text-decoration: underline;
            color: #ffc107;
        }

        /* Error Message */
        .alert {
            margin-bottom: 20px;
            border-radius: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="logo-title">Kezzy Shop</div>
        <h2>Welcome Back!</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <a href="register.php" class="register-link">Don't have an account? Register now</a>
    </div>
</body>
</html>
