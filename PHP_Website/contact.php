<!DOCTYPE html>
<html lang="en">
    <?php
    $page_name = "Contact";
    include 'includes/header.php';
    ?>
    <body>
        <div class="basic-form-container center-both-axis">
            <h1>ToKa Fitness Contact Form</h1>
            <form action="" method="POST">
                <input type="email" name="email" minlength="12" maxlength="50" id="email" placeholder="Email" required>
                <input type="text" name="first_name" minlength="3" maxlength="20" id="first_name" placeholder="First Name" required>
                <input type="text" name="last_name" minlength="4" maxlength="30" id="last_name" placeholder="Last Name" required>
                <textarea type="text" name="message" minlength="50" maxlength="255" id="message" placeholder="Message" required></textarea>
                <button type="submit" name="submit">Submit</button>
            </form>
        </div>
    </body>
</html>