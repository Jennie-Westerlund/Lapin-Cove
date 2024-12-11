<?php

declare(strict_types=1);

header(__DIR__ . '/index.html');

$database = new PDO('sqlite:' . __DIR__ . '/Backend/test.db');

if (isset($_POST['startDate']) && ($_POST['endDate'])) {
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $room = $_POST['Room'];
    echo "$startDate and $endDate in the $room room";
}


$statement = $database->query('SELECT * FROM Rooms;');

$roomsData = $statement->fetch(PDO::FETCH_ASSOC);

echo $roomsData['Room'];
