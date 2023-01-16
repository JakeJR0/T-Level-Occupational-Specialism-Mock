<!DOCTYPE html>
<html lang="en">
    <?php
    $page_name = "Login";
    include 'includes/header.php';
    ?>
    <body>
        <div class="basic-form-container center-both-axis">
            <div class="form-title">
                <h1>Login</h1>
                <p>Please type your details to login</p>
            </div>
            <form>
                <input type="text" alt="Email Address" name="gym_id" minlength="6" maxlength="6" id="gym_id" placeholder="Gym Membership ID / Staff ID" required>
                <input type="password" alt="Password" name="password" minlength="8" maxlength="20" id="password" placeholder="Password" required>
                <span class="separator"></span>
                <a href="signup.php">Need to create an account?</a>
                <button type="submit" name="submit">Login</button>
            </form>
        </div>
    </body>
</html>