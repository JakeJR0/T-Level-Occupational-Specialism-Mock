<?php
class User {
    public $ID;
    public $logged_in = false;
    public $first_name;
    public $last_name;
    public $email;
    public $user_type;
    public $membership_type;
    public $created_on;

    public static function create($first_name, $last_name, $email, $user_type, $membership_type) {
        $user = new User();

        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->email = $email;
        $user->user_type = $user_type;
        $user->membership_type = $membership_type;
        return $user;
    }


    public static function login($ID, $password) {
        $user = new User();
        $user->ID = $ID;

        require_once '.../storage.php';

        $sql = "
            SELECT first_name, last_name, email, user_type, membership_type, created_on
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
                    $user->membership_type = $user_data['membership_type'];
                    $user->created_on = $user_data['created_on'];

                    return $user;
                }
            } else {

                return false;
            }
        }
    }
}
?>