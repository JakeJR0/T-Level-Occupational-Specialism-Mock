<?php
class User {
    public $ID;
    public $logged_in = false;
    public $first_name;
    public $last_name;
    public $email;
    public $dob;
    private $password;
    public $user_type;
    public $membership_type;
    public $created_on;

    public static function create($first_name, $last_name, $email, $membership_type, $dob, $password, $confirm_password, $user_type="user") {
        $valid = true;
        $errors = array();
        require_once '../storage.php';
        $first_name = trim($first_name);
        $last_name = trim($last_name);
        $email = trim($email);
        $dob = trim($dob);
        $membership_type = trim($membership_type);
        $user_type = trim($user_type);

        $first_name = strip_tags($first_name);
        $last_name = strip_tags($last_name);
        $email = strip_tags($email);
        $dob = strip_tags($dob);
        $membership_type = strip_tags($membership_type);
        $user_type = strip_tags($user_type);

        $first_name = mysqli_real_escape_string($connection, $first_name);
        $last_name = mysqli_real_escape_string($connection, $last_name);
        $email = mysqli_real_escape_string($connection, $email);
        $dob = mysqli_real_escape_string($connection, $dob);
        $membership_type = mysqli_real_escape_string($connection, $membership_type);
        $user_type = mysqli_real_escape_string($connection, $user_type);

        if ($user_type == "") {
            $user_type = "user";
        }

        switch ($user_type) {
            case "user":
                break;
            case "admin":
                break;
            default:
                $valid = false;
                $errors[] = "Invalid user type";
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

        if ($valid == false) {
            return $errors;
        }

        $user = new User();

        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->dob = $dob;
        $user->email = $email;
        $user->user_type = $user_type;
        $user->membership_type = $membership_type;
        $result = $user->set_password($password, $confirm_password);

        if ($result !== true) {
            $errors[] = $result;
            return $errors;
        }

        return $user;
    }



    public static function login($ID, $password) {
        $user = new User();
        require_once '.../storage.php';

        $ID = trim($ID);
        $password = trim($password);

        $ID = strip_tags($ID);
        $password = strip_tags($password);

        $ID = mysqli_real_escape_string($connection, $ID);
        $password = mysqli_real_escape_string($connection, $password);

        $user->ID = $ID;

        $sql = "
        SELECT first_name, last_name, email, dob, user_type, membership_type, created_on
        FROM users
        WHERE ID = $ID;
        ";

        $result = mysqli_query($connection, $sql);

        if ($result) {
            $user_data = mysqli_fetch_assoc($result);

            if ($user_data) {
                if (password_verify($password, $user_data['password'])) {
                    $user->logged_in = true;
                    $user->first_name = $user_data['first_name'];
                    $user->last_name = $user_data['last_name'];
                    $user->email = $user_data['email'];
                    $user->user_type = $user_data['user_type'];
                    $user->dob = $user_data['dob'];
                    $user->membership_type = $user_data['membership_type'];
                    $user->created_on = $user_data['created_on'];

                    return $user;
                }
            } else {
                return false;
            }
        }
    }

    public function set_password($password, $confirm_password) {
        if ($password != $confirm_password) {
            return "Passwords do not match";
        }

        if (strlen($password) < 8 || strlen($password) > 50) {
            return "Password must be between 8 and 50 characters";
        }

        $this->password = password_hash($password, PASSWORD_DEFAULT);
        return true;
    }

    public function save() {
        if ($this->password == null || $this->password == "") {
            return false;
        } elseif ($this->first_name == null || $this->last_name == null || $this->email == null || $this->dob == null || $this->user_type == null || $this->membership_type == null) {
            return false;
        }

        require_once '.../storage.php';

        if ($this->logged_in == false) {
            $sql = "
                INSERT INTO users (first_name, last_name, email, dob, user_type, membership_type, password)
                VALUES ('$this->first_name', '$this->last_name', '$this->email', '$this->dob', '$this->user_type', '$this->membership_type', '$this->password');
            ";
            
            $result = mysqli_query($connection, $sql);

            if ($result) {
                $this->ID = mysqli_insert_id($connection);
                $this->logged_in = true;
                return true;
            } else {
                return false;
            }
        } else {
            $sql = "
                UPDATE users
                SET first_name = '$this->first_name', last_name = '$this->last_name', email = '$this->email', dob = '$this->dob', user_type = '$this->user_type', membership_type = '$this->membership_type'
                WHERE ID = $this->ID;
            ";

            $result = mysqli_query($connection, $sql);

            if ($result) {
                return true;
            } else {
                return false;
            }
        }
    }
}
?>