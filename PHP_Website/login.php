<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION["user"] ?? null != null) {
    header("Location: index.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gym_id = $_POST["gym_id"] ?? "";
    $password = $_POST["password"] ?? "";

    $login_attempts = $_COOKIE["login_attempts"] ?? 0;

    if ($login_attempts >= 3) {
        $errors[] = "Too many login attempts. Please try again in 15 minutes.";
    } else {
        $user[] = array();
        require_once '../storage.php';

        $gym_id = trim($gym_id);
        $password = trim($password);

        $gym_id = strip_tags($gym_id);
        $password = strip_tags($password);

        $gym_id = mysqli_real_escape_string($connection, $gym_id);
        $password = mysqli_real_escape_string($connection, $password);

        $login_query = "
            SELECT
                user.ID,
                user.first_name,
                user.user_type,
                user.dob,
                password,
                user.membership_type,
                user.private_key
            FROM users AS user
            WHERE ID = '$gym_id'
        ";

        $login_result = mysqli_query($connection, $login_query);

        if (!$login_result) {
            die("Error: " . mysqli_error($connection));
        }

        $row = mysqli_fetch_assoc($login_result);
        $stored_password = $row['password'] ?? "";
        if (password_verify($password, $stored_password)) {
            $user['ID'] = $row['ID'];
            $user['first_name'] = $row['first_name'];
            $user['user_type'] = $row['user_type'];
            $user['dob'] = $row['dob'];
            $user['membership_type'] = $row['membership_type'];
            $user["private_key"] = $row['private_key'];
            $_SESSION['user'] = $user;
            $_SESSION["logged_in"] = true;


            $success = "Login successful";
        } else {
            $errors[] = "Invalid ID or password";
            $login_attempts++;
            setcookie("login_attempts", $login_attempts, time() + 900);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <?php
    $page_name = "Login";
    include 'includes/header.php';
    ?>
    <body>
        <div class="centred-form-container">
            <div class="basic-form">
                <div class="form-title">
                    <h1>Login</h1>
                    <p>Please type your details to login</p>
                    <?php if (isset($errors)) {
                    echo "<div class='errors'>";
                        foreach ($errors as $error) {
                            echo "<p>".$error."</p>";
                        }
                    echo "</div>";
                    } ?>
                    <?php if (isset($success)) {
                        echo "<div class='success'>";
                            echo $success;
                            echo "<script>setTimeout(function(){window.location.href = 'index.php';}, 2000);</script>";
                        echo "</div>";
                    } ?>
                </div>
                <form action="" method="POST">
                    <input type="text" alt="Email Address" name="gym_id" minlength="6" maxlength="6" id="gym_id" placeholder="Gym Membership ID / Staff ID" required>
                    <input type="password" alt="Password" name="password" minlength="8" maxlength="20" id="password" placeholder="Password" required>
                    <span class="separator"></span>
                    <a href="signup.php">Need to create an account?</a>
                    <button type="submit" name="submit">Login</button>
                </form>
            </div>
        </div>
    </body>
</html>