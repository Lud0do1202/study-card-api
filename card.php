<?php
/* --------------------------------- Require -------------------------------- */
require_once './lib/EZQuezy/EZQuery.php';

/* ----------------------------- Access-Control ----------------------------- */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT');
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

    /* ---------------------------- Get Topic ID ---------------------------- */
    $topicID = $_GET['topicID'] ?? null;

    /* ----------------------------- Bad Request ---------------------------- */
    if ($topicID === null) {
        http_response_code(400);
        exit;
    }

    /* ------------------------------- EZQuery ------------------------------ */
    $ez = new EZQuery();

    // Check topics own
    $own = $ez->executeSelect("SELECT id FROM topics WHERE id = ? AND id_user = ?", $topicID, $userID);
    if (empty($own)) {
        http_response_code(403); // Forbidden
        exit;
    }

    // Get all cards from id_topic
    $cards = $ez->executeSelect("SELECT id, id_topic, question, answer FROM cards WHERE id_topic = ? ORDER BY id ASC", $topicID);

    /* ------------------------------ Response ------------------------------ */
    http_response_code(200);  // OK
    header('Content-Type: application/json');
    echo json_encode($cards);
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
    $topicID = $data->card->id_topic ?? null;
    $question = $data->card->question ?? null;
    $answer = $data->card->answer ?? null;

    /* ----------------------------- Bad Request ---------------------------- */
    if (is_null($topicID) || is_null($question) || is_null($answer)) {
        http_response_code(400);
        exit;
    }

    /* ------------------------------- EZQuery ------------------------------ */
    $ez = new EZQuery();

    // Check topics own
    $own = $ez->executeSelect("SELECT id FROM topics WHERE id = ? AND id_user = ?", $topicID, $userID);
    if (empty($own)) {
        http_response_code(403); // Forbidden
        exit;
    }

    // Insert new cards
    $rowsAffected = $ez->executeEdit("INSERT INTO cards (id_topic, question, answer) VALUES (?, ?, ?)", $topicID, $question, $answer);

    /* ------------------------------ Response ------------------------------ */
    switch ($rowsAffected) {
        case 1: // SUCCESS
            // Get last id insert
            $lastID = $ez->executeSelect("SELECT LAST_INSERT_ID() as lastID");

            // Update the topic that was given
            $data->card->id = $lastID[0]['lastID'];

            // Response
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($data->card);
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
    $cardID = $data->card->id ?? null;
    $question = $data->card->question ?? null;
    $answer = $data->card->answer ?? null;

    /* ----------------------------- Bad Request ---------------------------- */
    if (is_null($cardID) || is_null($question) || is_null($answer)) {
        http_response_code(400);
        exit;
    }

    /* ------------------------------- EZQuery ------------------------------ */
    $ez = new EZQuery();

    // Check cards own
    $own = $ez->executeSelect("SELECT id FROM topics WHERE id = (SELECT id_topic FROM cards WHERE id = ?) AND id_user = ?", $cardID, $userID);
    if (empty($own)) {
        http_response_code(403); // Forbidden
        exit;
    }

    // Insert a new topics
    $rowsAffected = $ez->executeEdit("UPDATE cards SET question = ?, answer = ? WHERE id = ?", $question, $answer, $cardID);

    /* ------------------------------ Response ------------------------------ */
    switch ($rowsAffected) {
        case 0: // Nothing updated
        case 1: // SUCCESS
            http_response_code(200);
            exit;

        default: // Unknow error
            http_response_code(520);
            exit;
    }
}

/* ========================================================================== */
/*                                   DELETE                                   */
/* ========================================================================== */ //
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    /* ----------------------------- Get User ID ---------------------------- */
    $userID = $_SERVER['HTTP_X_USER_ID'] ?? null;

    /* ------------------------------ Forbidden ----------------------------- */
    if ($userID === null) {
        http_response_code(403);
        exit;
    }

    /* ------------------------------ Get Topic ----------------------------- */
    $cardID = $_GET['cardID'] ?? null;

    /* ----------------------------- Bad Request ---------------------------- */
    if ($cardID === null) {
        http_response_code(400);
        exit;
    }

    /* ------------------------------- EZQuery ------------------------------ */
    $ez = new EZQuery();

    // Check cards own
    $own = $ez->executeSelect("SELECT id FROM topics WHERE id = (SELECT id_topic FROM cards WHERE id = ?) AND id_user = ?", $cardID, $userID);
    if (empty($own)) {
        http_response_code(403); // Forbidden
        exit;
    }

    // Insert new user if it doesn't exist
    $rowsAffected = $ez->executeEdit("DELETE FROM cards WHERE id = ?", $cardID);

    /* ------------------------------ Response ------------------------------ */
    switch ($rowsAffected) {
        case 0: // Not Found
            http_response_code(404);
            exit;

        case 1: // SUCCESS
            http_response_code(200);
            exit;

        default: // Unknow error
            http_response_code(520);
            exit;
    }
}
