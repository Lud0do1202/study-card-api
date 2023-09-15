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
    $topics = $ez->executeSelect("SELECT id, topic, theme FROM topics WHERE id_user = '$userID'");

    /* ------------------------------ Response ------------------------------ */
    http_response_code(200);  // OK
    header('Content-Type: application/json');
    echo json_encode($topics);
    exit;
}


/* ========================================================================== */
/*                                    POST                                    */
/* ========================================================================== */ //
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* ----------------------------- Get User ID ---------------------------- */
    $userID = $_SERVER['HTTP_X_USER_ID'] ?? null;

    /* ------------------------------ Forbidden ----------------------------- */
    if ($userID === null) {
        http_response_code(403);
        exit;
    }

    /* ------------------------------ Get Topic ----------------------------- */
    $data = json_decode(file_get_contents("php://input"));
    $topic = $data->topic->topic;
    $theme = $data->topic->theme;

    /* ------------------------------- EZQuery ------------------------------ */
    $ez = new EZQuery();

    // Insert new user if it doesn't exist
    $rowsAffected = $ez->executeEdit("INSERT INTO topics (id_user, topic, theme) VALUES ('$userID', '$topic', '$theme')");

    /* ------------------------------ Response ------------------------------ */
    switch ($rowsAffected) {
        case 1: // SUCCESS
            // Get last id insert
            $lastID = $ez->executeSelect("SELECT LAST_INSERT_ID() as lastID");

            // Update the topic that was given
            $data->topic->id = $lastID[0]['lastID'];

            // Response
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($data->topic);
            exit;

        default: // Unknow error
            http_response_code(520);
            exit;
    }
}

/* ========================================================================== */
/*                                     PUT                                    */
/* ========================================================================== */ //
else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
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
    $theme = $data->topic->theme;

    /* ------------------------------- EZQuery ------------------------------ */
    $ez = new EZQuery();

    // Insert new user if it doesn't exist
    $rowsAffected = $ez->executeEdit("UPDATE topics SET topic = '$topic', theme = '$theme' WHERE id = '$id'");

    /* ------------------------------ Response ------------------------------ */
    switch ($rowsAffected) {
        case 0: // Nothing updated
        case 1: // SUCCESS
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($data->topic);
            exit;

        default: // Unknow error
            http_response_code(520);
            exit;
    }
}
