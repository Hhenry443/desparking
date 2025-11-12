<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class Carparks extends Dbh
{
    private PDO $db;

    function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    protected function selectAllCarparks()
    {
        $sql = "
    SELECT *
    FROM carparks
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        if (!empty($results)) {
            return $results;
        } else {
            return false;
        }
    } //selectAllUsers

}// class Users