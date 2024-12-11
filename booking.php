<?php

declare(strict_types=1);

header(__DIR__ . '/index.html');

if (isset($_POST['startDate']) && ($_POST['endDate'])) {
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $room = $_POST['Room'];
    echo "$startDate and $endDate in the $room room";
}
