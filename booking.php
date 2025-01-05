<?php

declare(strict_types=1);

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

function validateTransferCode(string $transferCode, int $totalCost): bool
{
    $client = new Client();

    try {
        $response = $client->post('https://www.yrgopelago.se/centralbank/transferCode', [
            'form_params' => [
                'transferCode' => $transferCode,
                'totalcost' => $totalCost
            ]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        return isset($data['status']) && $data['status'] === 'success';
    } catch (RequestException $e) {
        error_log('Error validating transfer code: ' . $e->getMessage());
        return false;
    }
}

function makeDeposit(string $transferCode): bool
{
    $client = new Client();

    try {
        $response = $client->post('https://www.yrgopelago.se/centralbank/deposit', [
            'form_params' => [
                'user' => 'Jennie',
                'transferCode' => $transferCode
            ]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        return isset($data['status']) && $data['status'] === 'success';
    } catch (RequestException $e) {
        error_log('Error validating transfer code: ' . $e->getMessage());
        return false;
    }
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
            $featureTotalPrice += $featurePrice; /* Add every feature's price to total */
        }

        $totalCost = $roomTotalPrice + $featureTotalPrice;
        $transferCode = htmlspecialchars(trim($_POST['transferCode'])); /* Washing transferCode from user */


        /* Validate transferCode */
        if (!validateTransferCode($transferCode, (int)$totalCost)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid transfer code or insufficient funds, please contact the Centralbank.'
            ]);
            exit;
        }

        /* Deposit to my account at the centralbank */
        if (!makeDeposit($transferCode)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Deposit failed. Please contact support.'
            ]);
            exit;
        }


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
        /* Respons/recipt for user */

        $featuresNames = [];

        foreach ($features as $featureId) {
            $featuresNameQuery = "SELECT feature FROM Features WHERE id = :featureId";
            $featuresNameStmt = $database->prepare($featuresNameQuery);
            $featuresNameStmt->execute([
                ':featureId' => (int)$featureId,
            ]);

            $featureName = $featuresNameStmt->fetchColumn();
            if ($featureName !== false) {
                $featuresNames[] = $featureName; /* Add the feature name to the array */
            }
        }

        $response = [
            "island" => "Corsica",
            "hotel" => "Lapin Cove",
            "arrival_date" => $startDate,
            "departure_date" => $endDate,
            "total_cost" => $totalCost . "$",
            "stars" => "3",
            "features" => $featuresNames,
            "additional_info" => [
                "greeting" => "Thank you for booking Lapin Cove",
                "imageUrl" => "https://cdn.britannica.com/32/177732-050-99BCA269/Bastia-Corsica-France.jpg"
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT); /* Return as JSON */
        exit;
    }
}
