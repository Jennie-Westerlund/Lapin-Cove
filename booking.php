<?php

declare(strict_types=1);

require(__DIR__ . '/index.html');

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

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
        /* Calculate price of the booking*/
        $roomPriceQuery = "SELECT Prize FROM Rooms WHERE id = :roomId";
        $roomPriceStmt = $database->prepare($roomPriceQuery);
        $roomPriceStmt->execute([
            ':roomId' => $roomId,
        ]);
        $roomPrice = $roomPriceStmt->fetchColumn();
        $dateDuration = amountOfDays($startDate, $endDate);
        $roomTotalPrice = $roomPrice * $dateDuration;

        /* Calculate price of features */
        $featureTotalPrice = 0; /* Initialize total feature price */
        $featurePriceQuery = "SELECT Prize FROM Features WHERE id = :featureId";
        $featurePriceStmt = $database->prepare($featurePriceQuery);

        foreach ($features as $featureId) {
            $featurePriceStmt->execute([
                ':featureId' => (int)$featureId,
            ]);
            $featurePrice = $featurePriceStmt->fetchColumn(); /* Get price for features */
            $featureTotalPrice += $featurePrice; // Add this feature's price to total
        }

        $bookingTotalPrice = $roomTotalPrice + $featureTotalPrice;

        /* Checking validity of transfer code and amount vs cost against the API */
        if (isset($_POST['transferCode'])) {
            $client = new Client(); /* Starting new guzzle client */

            /* Sanitize the transfer code */
            $transferCode = htmlspecialchars(trim($_POST['transferCode']));

            try {
                $response = $client->post('https://www.yrgopelago.se/centralbank/transferCode', [
                    'form_params' => [
                        'transferCode' => $transferCode,
                        'totalCost' => $bookingTotalPrice
                    ]
                ]);

                $transferCodeResult = json_decode($response->getBody()->getContents(), true);

                // Check if the transfer is valid and has sufficient funds
                if ($$transferCodeResult['transferCode'] === 'valid' && $$transferCodeResult['amount'] >= $bookingTotalPrice) {
                    // Transfer is valid and amount is sufficient
                    // Proceed with booking

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


                    echo "Your booking was successful! You have booked $dateDuration days for a total of $roomTotalPrice$, features for a total of $featureTotalPrice$ which brings your total cost up to $bookingTotalPrice$";
                } else {
                    // Transfer is invalid or insufficient funds
                    echo "Invalid transfer code or insufficient funds.";
                }
            } catch (RequestException $e) {
                // Handle any errors that occurred during the request
                echo "Error validating transfer code: " . $e->getMessage();
            }
        }



        /* Jag ska ta fram pris för features med en foreach loop och sen ta totalCost med transferCode mot API med Guzzle !!!!!!!! */
    }
}
