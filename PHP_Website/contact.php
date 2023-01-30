<?php
$errors = array();
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../storage.php';

    $email = $_POST["email"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $message = $_POST["message"];

    $email = trim($email);
    $first_name = trim($first_name);
    $last_name = trim($last_name);
    $message = trim($message);

    $email = strip_tags($email);
    $first_name = strip_tags($first_name);
    $last_name = strip_tags($last_name);
    $message = strip_tags($message);

    $email = mysqli_real_escape_string($connection, $email);
    $first_name = mysqli_real_escape_string($connection, $first_name);
    $last_name = mysqli_real_escape_string($connection, $last_name);
    $message = mysqli_real_escape_string($connection, $message);

    $valid = true;

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

    if (strlen($message) < 50 || strlen($message) > 255) {
        $valid = false;
        $errors[] = "Message must be between 50 and 255 characters";
    }

    if ($valid) {
        $sql = "
            INSERT INTO contact (email, first_name, last_name, message)
            VALUES ('$email', '$first_name', '$last_name', '$message')
        ";

        if (mysqli_query($connection, $sql)) {
            $success = true;
        } else {
            error_log($connection->error);
            $errors[] = "Failed to submit contact form";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$page_name = "Contact";
include 'includes/header.php';
?>

<body>
    <div class="centred-form-container">
        <div class="basic-form">
            <h1 aria-label="ToKa Fitness Contact Form">ToKa Fitness Contact Form</h1>
            <?php
            if ($success) {
                echo "<p aria-label='Thank you for your message' class='success'>Thank you for your message, we will get back to you as soon as possible.</p>";
            } else {
                echo '<div class="errors">';

                foreach ($errors as $error) {
                    echo "<p aria-label='$error'>$error</p>";
                }

                echo '</div>';
            }
            ?>

            <form action="" method="POST">
                <input aria-label="Email" type="email" name="email" minlength="12" maxlength="50" id="email" placeholder="Email" required>
                <input type="text" aria-label="First Name" name="first_name" minlength="3" maxlength="20" id="first_name" placeholder="First Name" required>
                <input type="text" aria-label="Last Name" name="last_name" minlength="4" maxlength="30" id="last_name" placeholder="Last Name" required>
                <textarea type="text" aria-label="Message" name="message" minlength="50" maxlength="255" id="message" placeholder="Message" required></textarea>
                <button type="submit" name="submit" aria-label="Submit Button">Submit</button>
            </form>
        </div>
    </div>
</body>

</html>