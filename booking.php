<?php

declare(strict_types=1);

header(__DIR__ . '/index.html');

$database = new PDO('sqlite:' . __DIR__ . '/Backend/test.db');

/* Collecting the input from user and insert it into the database */

if (isset($_POST['startDate'], $_POST['endDate'], $_POST['Room'])) {
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $roomId = (int)$_POST['Room'];

    /* Check for overlapping bookings */
    $checkQuery = "
        SELECT COUNT(*) FROM Bookings
        WHERE Room_id = :roomId
        AND (
            (Start_date <= :endDate AND End_date >= :startDate)
        )
    ";
    $checkStmt = $database->prepare($checkQuery);
    $checkStmt->execute([
        ':roomId' => $roomId,
        ':startDate' => $startDate,
        ':endDate' => $endDate,
    ]);

    $existingBookings = $checkStmt->fetchColumn();

    if ($existingBookings > 0) {
        echo "Sorry, the selected room is already booked for the specified dates.";
    } else {
        /* Using a prepared statement to safely insert the data, if not already booked */
        $bookingQuery = "INSERT INTO Bookings (Room_id, Start_date, End_date) VALUES (:roomId, :startDate, :endDate)";
        $statement = $database->prepare($bookingQuery);
        $statement->execute([
            ':roomId' => $roomId,
            ':startDate' => $startDate,
            ':endDate' => $endDate,
        ]);

        echo "Your booking successful!";
    }
}
