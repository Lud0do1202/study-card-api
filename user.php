<?php
/* --------------------------------- Require -------------------------------- */
require_once './lib/EZQuezy/EZQuery.php';

/* ----------------------------- Access-Control ----------------------------- */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: X-User-ID');

/* ========================================================================== */
/*                                   DELETE                                   */
/* ========================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    /* ----------------------------- Get User ID ---------------------------- */
    $userID = $_SERVER['HTTP_X_USER_ID'] ?? null;

    /* ------------------------------ Forbidden ----------------------------- */
    if ($userID === null) {
        http_response_code(403);
        exit;
    }

    /* ------------------------------- EZQuery ------------------------------ */
    $ez = new EZQuery();

    // Delete user
    $ez->executeEdit("DELETE FROM cards WHERE id_topic IN (SELECT id FROM topics WHERE id_user = ?)", $userID);
    $ez->executeEdit("DELETE FROM topics where id_user = ?", $userID);
    $rowsAffected = $ez->executeEdit("DELETE FROM users where id = ?", $userID);

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
