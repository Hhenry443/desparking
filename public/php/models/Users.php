<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class Users extends Dbh
{
    private PDO $db;

    function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    protected function insertUser($name, $email, $passwordHash)
    {
        $sql = "
    INSERT INTO users
                (user_name, user_email, user_password_hash)
                VALUES (:name, :email, :password)
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);
        $stmt->bindValue(":password", $passwordHash, PDO::PARAM_STR);

        $stmt->execute();

        return $this->db->lastInsertId();
    } //insertUser

    protected function usernameInUse($name)
    {
        $sql = "
    SELECT * FROM users WHERE user_name = :user_name
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(":user_name", $name, PDO::PARAM_STR);
        
        $stmt->execute();

        $results = $stmt->fetch();

        if (!empty($results)) {
            return true;
        } else {
            return false;
        }
    } //usernameInUse

    protected function emailInUse($email)
    {
        $sql = "
    SELECT * FROM users WHERE user_email = :user_email
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(":user_email", $email, PDO::PARAM_STR);
        
        $stmt->execute();

        $results = $stmt->fetch();

        if (!empty($results)) {
            return true;
        } else {
            return false;
        }
    } //emailInUse

    protected function getUserById(int $id)
    {
        $sql = "
    SELECT user_id, user_name, user_email, user_password_hash
    FROM users
    WHERE user_id = :user_id
    LIMIT 1
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":user_id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    } // getUserById

    protected function updateUserNameEmail(int $id, string $name, string $email)
    {
        $sql = "
    UPDATE users
    SET user_name = :name, user_email = :email
    WHERE user_id = :id
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":name",  $name,  PDO::PARAM_STR);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);
        $stmt->bindValue(":id",    $id,    PDO::PARAM_INT);

        return $stmt->execute();
    } // updateUserNameEmail

    protected function updateUserPassword(int $id, string $passwordHash)
    {
        $sql = "
    UPDATE users
    SET user_password_hash = :hash
    WHERE user_id = :id
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":hash", $passwordHash, PDO::PARAM_STR);
        $stmt->bindValue(":id",   $id,           PDO::PARAM_INT);

        return $stmt->execute();
    } // updateUserPassword

    protected function login(string $email, string $password)
    {
    $sql = "
        SELECT user_id, user_name, user_email, user_password_hash, user_is_admin
        FROM users
        WHERE user_email = :user_email
        LIMIT 1
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(":user_email", $email, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // No user found
    if (!$user) {
        return false;
    }

    // Check password
    if (!password_verify($password, $user['user_password_hash'])) {
        return false;
    }

    return $user;
    } // login
}// class Users