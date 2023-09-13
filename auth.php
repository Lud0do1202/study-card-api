<?php
/* --------------------------------- Require -------------------------------- */
require_once './lib/EZQuezy/EZQuery.php';

/* ----------------------------- Access-Control ----------------------------- */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

/* ========================================================================== */
/*                                    POST                                    */
/* ========================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* ------------------------------ Get Data ------------------------------ */
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id;

    /* ------------------------------- EZQuery ------------------------------ */
    $ez = new EZQuery();

    // Insert new user if it doesn't exist
    $rowAffected = $ez->executeEdit("INSERT INTO users (id) VALUES ('$id') ON DUPLICATE KEY UPDATE id = '$id'");

    /* ------------------------------ Response ------------------------------ */
    switch ($rowAffected) {
        case 0: // Already exists
            http_response_code(200);  // OK
            break;

        case 1: // New User
            http_response_code(201); // CREATED
            break;

        default:
            http_response_code(520); // Unknown error
            break;
    }
}
