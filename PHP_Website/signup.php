<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'includes/user.php';
    $email = $_POST["email"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $dob = $_POST["dob"];
    $membership_type = $_POST["membership_type"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $valid = true;

    if ($valid) {
        $user = User::create($first_name, $last_name, $email, $membership_type, $dob, $password, $confirm_password);
        if (is_array($user)) {
            $errors = $user;
        } else {
            $success = "Account created successfully";
            $_SESSION["user"] = json_encode($user);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <?php
    $page_name = "Signup";
    include 'includes/header.php';
    ?>
    <body>
        <div class="centred-form-container">
            <div class="basic-form">
                <h1>Signup</h1>
                <p>Please type your details to create an account</p>
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
                <form action="" method="POST">
                    <input type="email" alt="Email Address" name="email" minlength="12" maxlength="50" id="email" placeholder="Email" required>
                    <input type="text" alt="First Name" name="first_name" minlength="3" maxlength="20" id="first_name" placeholder="First Name" required>
                    <input type="text" alt="Last Name" name="last_name" minlength="4" maxlength="30" id="last_name" placeholder="Last Name" required>
                    <input type="date" alt="Date of Birth" name="dob" id="dob" placeholder="Date of Birth" required>
                    <div class="radio-option">
                        <div>
                            <input type="radio" alt="Basic Membership" name="membership_type" id="basic" value="basic">
                            <label for="basic">Basic Membership</label>
                            <p>£8 Per Month</p>
                        </div>
                        <div>
                            <input type="radio" alt="Pro Membership" name="membership_type" id="pro" value="pro">
                            <label for="pro">Pro Membership</label>
                            <p>£12 Per Month</p>
                        </div>
                        <div>
                            <input type="radio" alt="Student Membership" name="membership_type" id="student" value="student">
                            <label for="student">Student Membership</label>
                            <p>£4 Per Month</p>
                        </div>
                    </div>
                    <input type="password" alt="Password" name="password" minlength="8" maxlength="20" id="password" placeholder="Password" required>
                    <input type="password" alt="Confirm Password" name="confirm_password" minlength="8" maxlength="20" id="confirm_password" placeholder="Confirm Password" required>
                    <span class="separator"></span>
                    <a href="login.php">Already have an account?</a>
                    <button type="submit" name="submit">Signup</button>
                </form>
            </div>
        </div>
    </body>
</html>