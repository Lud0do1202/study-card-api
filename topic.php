<?php
/* --------------------------------- Require -------------------------------- */
require_once './lib/EZQuezy/EZQuery.php';

/* ----------------------------- Access-Control ----------------------------- */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT');
header('Access-Control-Allow-Headers: X-User-ID, Content-Type');

/* ========================================================================== */
/*                                     GET                                    */
/* ========================================================================== */ //
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    /* ----------------------------- Get User ID ---------------------------- */
    $userID = $_SERVER['HTTP_X_USER_ID'] ?? null;

    /* ------------------------------ Forbidden ----------------------------- */
    if ($userID === null) {
        http_response_code(403);
        exit;
    }

    /* ------------------------------- EZQuery ------------------------------ */
    $ez = new EZQuery();

    // Get all topics from user
    $topics = $ez->executeSelect("SELECT id, topic, color FROM topics WHERE id_user = '$userID'");

    /* ------------------------------ Response ------------------------------ */
    http_response_code(200);  // OK
    header('Content-Type: application/json');
    echo json_encode($topics);
    exit;
}

/* ========================================================================== */
/*                                     PUT                                    */
/* ========================================================================== */ //
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    /* ----------------------------- Get User ID ---------------------------- */
    $userID = $_SERVER['HTTP_X_USER_ID'] ?? null;

    /* ------------------------------ Forbidden ----------------------------- */
    if ($userID === null) {
        http_response_code(403);
        exit;
    }

    /* ------------------------------ Get Topic ----------------------------- */
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->topic->id;
    $topic = $data->topic->topic;
    $color = $data->topic->color;

    /* ------------------------------- EZQuery ------------------------------ */
    $ez = new EZQuery();

    // Insert new user if it doesn't exist
    $rowsAffected = $ez->executeEdit("UPDATE topics SET topic = '$topic', color = '$color' WHERE id = '$id'");

    /* ------------------------------ Response ------------------------------ */
    switch ($rowsAffected) {
        case 0: // Nothing updated
        case 1: // SUCCESS
            http_response_code(204);
            break;

        default: // Unknow error
            http_response_code(520);
            break;
    }
}
