<?php

class Dbh
{

    private static $db;
    private $connection;

    private function __construct()
    {
        $this->connection = new PDO(
            'mysql:host=' . getenv('DB_HOST') . '; dbname=' . getenv('DB_NAME'),
            getenv('DB_USER'),
            getenv('DB_PASSWD')
        );
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    function __destruct()
    {
        //$this->connection->close();
    }

    public static function getConnection()
    {
        if (self::$db == null) {
            if (getenv('NODE') == 'localhost') {
                //error_log('DB Connected');
            }
            self::$db = new Dbh();
        }
        return self::$db->connection;
    }
}
