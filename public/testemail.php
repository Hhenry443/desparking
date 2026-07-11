<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/notifications/Notifier.php';

$conn = Dbh::getConnection();

// Use a real booking ID and user ID from your database
(new Notifier($conn))->bookingConfirmed(88, 65);

echo "Done";
