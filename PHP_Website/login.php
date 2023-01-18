<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION["user"] != null) {
    header("Location: index.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'includes/user.php';
    $gym_id = $_POST["gym_id"];
    $password = $_POST["password"];

    $user = User::login($gym_id, $password);
    if (is_array($user)) {
        $errors = $user;
    } else {
        $_SESSION["user"] = serialize($user);
        header("Location: index.php");
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
                    echo "<div class='error'>";
                        foreach ($errors as $error) {
                            echo "<p>".$error."</p>";
                        }
                    echo "</div>";
                    } ?>
                    <?php if (isset($success)) {
                        echo "<div class='success'>";
                            echo $success;
                        echo "</div>";
                    } ?>
                </div>
                <form>
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