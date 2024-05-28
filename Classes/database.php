<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

class database{ 

    function opencon(){
        return new PDO('mysql:host=localhost; dbname=phpcrud', 'root', '');
    }

    // function check($username, $password){
    //     $con = $this->opencon();
    //     $query = "Select * from users WHERE user_name='".$username."'&&user_pass='".$password."'";
    //     return $con->query($query)->fetch();
    // }

    function check($username, $password) {
        // Open database connection
        $con = $this->opencon();
    
        // Prepare the SQL query
        $query = $con->prepare("SELECT * FROM users WHERE user_name = ?");
        $query->execute([$username]);
    
        // Fetch the user data as an associative array
        $user = $query->fetch(PDO::FETCH_ASSOC);
    
        // If a user is found, verify the password
        if ($user && password_verify($password, $user['user_pass'])) {
            return $user;
        }
    
        // If no user is found or password is incorrect, return false
        return false;
    }

    function signup($firstname, $lastname, $birthday, $sex, $username, $password, $confirm){
    $con = $this->opencon();
    // Check if the username already exists
    $query = $con->prepare("SELECT user_name FROM users WHERE user_name = ?");
    $query->execute([$username]);
    $existingUser = $query->fetch();
    // If the username already exists, return false
    if ($existingUser) {
        return false;
    }
    // Insert the new username and password into the database
    return $con->prepare("INSERT INTO users (user_firstname, user_lastname, user_birthday, user_sex, user_name, user_pass) VALUES (?, ?, ?, ?, ?, ?)")->execute([$firstname, $lastname, $birthday, $sex, $username, $password]);
    }

    function signupUser($firstname, $lastname, $birthday, $sex, $email, $username, $password, $profilePicture)
    {
        $con = $this->opencon();
        // Save user data along with profile picture path to the database
        $con->prepare("INSERT INTO users (user_firstname, user_lastname, user_birthday, user_sex, user_email, user_name, user_pass, user_profile_picture) VALUES (?,?,?,?,?,?,?,?)")->execute([$firstname, $lastname, $birthday, $sex, $email, $username, $password, $profilePicture]);
        return $con->lastInsertId();
        }
    
    function insertAddress($user_id, $street, $barangay, $city, $province)
    {
        try
    {
        $con = $this->opencon();
        $con->beginTransaction();
        $con->prepare("INSERT INTO user_address (user_id, street, barangay, city, province) VALUES (?,?,?,?,?)")->execute([$user_id, $street, $barangay,  $city, $province]);
        $con->commit();
        return true;
    }
        catch (PDOException $e) {
            $con->rollBack();
            return false;
        }  
          
    }

    function view()
        {
            $con = $this->opencon();
            return $con->query("SELECT users.user_id, users.user_firstname, users.user_lastname, users.user_birthday, users.user_sex, users.user_name, users.user_profile_picture, CONCAT(user_address.city,', ', user_address.province) AS address from users INNER JOIN user_address ON users.user_id = user_address.user_id")->fetchAll();
        }

        function delete($id) 
        {
        try {
        $con = $this->opencon();
        $con->beginTransaction();

        // Delete user address
        $query = $con->prepare("DELETE FROM user_address WHERE user_id = ?");
        $query->execute([$id]);

        // Delete user
        $query2 = $con->prepare("DELETE FROM users WHERE user_id = ?");
        $query2->execute([$id]);

        $con->commit();
        return true; // Deletion successful
    } catch (PDOException $e) {
        $con->rollBack();
        return false; 
    }
}
}