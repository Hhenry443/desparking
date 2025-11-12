<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Carparks.php';

class ReadCarparks extends Carparks
{
    private PDO $db;

    public function getCarparks()
    {

        $carparks = array();

        $carparks = $this->selectAllCarparks();

        return $carparks;
    } // function getUsers

}// class ReadUsers