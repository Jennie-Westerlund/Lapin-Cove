<?php

declare(strict_types=1);

require(__DIR__ . '/index.html');

$database = new PDO('sqlite:' . __DIR__ . '/Backend/test.db');

/* My functions  */
function amountOfDays($date1, $date2)
{

    /* Calculating the difference in timestamps */
    $diff = strtotime($date2) - strtotime($date1) + 1;

    /* 1 day = 24 hours */
    /* 24 * 60 * 60 = 86400 seconds */
    return abs(round($diff / 86400));
}

/* Collecting the input from user and insert it into the database */

if (isset($_POST['startDate'], $_POST['endDate'], $_POST['room'])) {
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $roomId = (int)$_POST['room'];
    $features = isset($_POST['features']) && is_array($_POST['features']) ? $_POST['features'] : []; /* Features as an array in order to handle booking more than one */
    $transferCode = $_POST['transferCode'];

    /* Check for overlapping bookings */
    $checkQuery = " SELECT COUNT(*) FROM Bookings WHERE Room_id = :roomId AND ((Start_date <= :endDate AND End_date >= :startDate))
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
        /* Calculate prize of the booking*/
        $roomPrizeQuery = "SELECT Prize FROM Rooms WHERE id = :roomId"; /* Get prize of room from database */
        $roomPrizeStmt = $database->prepare($roomPrizeQuery);
        $roomPrizeStmt->execute([
            ':roomId' => $roomId,
        ]);

        $roomPrize = $roomPrizeStmt->fetchColumn(); /* Get the actual prize value */
        $dateDuration = amountOfDays($startDate, $endDate); /* Get amount of days they wish to book */
        $roomTotalPrize = $roomPrize * $dateDuration;


        /* Using a prepared statement to safely insert the data, if not already booked */
        $bookingQuery = "INSERT INTO Bookings (Room_id, Start_date, End_date) VALUES (:roomId, :startDate, :endDate)";
        $statement = $database->prepare($bookingQuery);
        $statement->execute([
            ':roomId' => $roomId,
            ':startDate' => $startDate,
            ':endDate' => $endDate,
        ]);

        /* Get the ID of the created booking */
        $bookingId = (int)$database->lastInsertId();

        /* Insert features into the Booking_Features table if there is any features */
        if (!empty($features)) {
            $featureQuery = "INSERT INTO Booking_Features (booking_id, feature_id) VALUES (:bookingId, :featureId)";
            $featureStmt = $database->prepare($featureQuery);

            foreach ($features as $featureId) {
                $featureStmt->execute([
                    ':bookingId' => $bookingId,
                    ':featureId' => (int)$featureId,
                ]);
            }
        }

        $bookedDaysQuery = "SELECT CAST((julianday(end_date) - julianday(start_date) + 1) AS INTEGER) AS inclusive_days FROM bookings WHERE id = :bookingId";
        $daysStmt = $database->prepare($bookedDaysQuery);
        $daysStmt->execute([
            ':bookingId' => $bookingId,
        ]);
        $daysResult = $daysStmt->fetch(PDO::FETCH_ASSOC);

        echo "Your booking was successful! You have booked $dateDuration days for a total of $roomTotalPrize$";
    }
}
