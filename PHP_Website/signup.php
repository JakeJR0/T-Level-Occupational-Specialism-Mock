<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $dob = $_POST["dob"];
    $membership_type = $_POST["membership_type"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    $valid = true;

    $first_name = trim($first_name);
    $last_name = trim($last_name);
    $email = trim($email);
    $dob = trim($dob);
    $membership_type = trim($membership_type);
    $password = trim($password);
    $confirm_password = trim($confirm_password);

    $first_name = strip_tags($first_name);
    $last_name = strip_tags($last_name);
    $email = strip_tags($email);
    $dob = strip_tags($dob);
    $membership_type = strip_tags($membership_type);
    $password = strip_tags($password);
    $confirm_password = strip_tags($confirm_password);

    require_once '../storage.php';

    $first_name = mysqli_real_escape_string($connection, $first_name);
    $last_name = mysqli_real_escape_string($connection, $last_name);
    $email = mysqli_real_escape_string($connection, $email);
    $dob = mysqli_real_escape_string($connection, $dob);
    $membership_type = mysqli_real_escape_string($connection, $membership_type);
    $password = mysqli_real_escape_string($connection, $password);
    $confirm_password = mysqli_real_escape_string($connection, $confirm_password);

    if ($password != $confirm_password) {
        $errors['password'] = "Passwords do not match";
        $valid = false;
    }

    if (strlen($password) < 8 || strlen($password) > 50) {
        $errors['password'] = "Password must be between 8 and 50 characters";
        $valid = false;
    }

    if ($first_name == null || $last_name == null || $email == null || $dob == null || $membership_type == null) {
        $errors['required'] = "All fields are required";
        $valid = false;
    }
    if (strlen($email) < 12 || strlen($email) > 50) {
        $valid = false;
        $errors[] = "Email must be between 12 and 50 characters";
    }

    if (strlen($first_name) < 3 || strlen($first_name) > 20) {
        $valid = false;
        $errors[] = "First name must be between 3 and 20 characters";
    }

    if (strlen($last_name) < 4 || strlen($last_name) > 30) {
        $valid = false;
        $errors[] = "Last name must be between 4 and 30 characters";
    }

    if ($valid) {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $private_key = bin2hex(random_bytes(256));
        // Ensures that the private key is unique

        $private_key_sql = "
            SELECT private_key
            FROM users
            WHERE private_key = '$private_key';
        ";

        $private_key_result = mysqli_query($connection, $private_key_sql);

        while (mysqli_num_rows($private_key_result) > 0) {
            $private_key = bin2hex(random_bytes(256));
            $private_key_sql = "
                SELECT private_key
                FROM users
                WHERE private_key = $private_key;
            ";
            $private_key_result = mysqli_query($connection, $private_key_sql);
        }

        $sql = "
            INSERT INTO users (first_name, last_name, email, dob, membership_type, password, private_key)
            VALUES ('$first_name', '$last_name', '$email', '$dob', '$membership_type', '".$password_hash."', '$private_key');
        ";

        $result = mysqli_query($connection, $sql);

        if ($result) {
            $user = array();
            $user['ID'] = mysqli_insert_id($connection);
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['email'] = $email;
            $user['dob'] = $dob;
            $user['user_type'] = 'user';
            $user['membership_type'] = $membership_type;
            $user['private_key'] = $private_key;
            $user['created_on'] = date("Y-m-d H:i:s");

            $_SESSION['user'] = $user;
            $_SESSION["logged_in"] = true;
            $success = "Successfully created your account with Gym ID: ". $user['ID'];
        } else {
            $errors[] = "Signup failed, please try again later";
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
                    echo "<div class='errors'>";
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