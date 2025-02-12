<?php
// functions.php

function generate_jwt($user_id) {
    $secret_key = "your_secret_key_here"; // Palitan ng secret key mo
    $issued_at = time();
    $expiration_time = $issued_at + 3600; // 1 hour expiration
    $payload = json_encode([
        "iat" => $issued_at,
        "exp" => $expiration_time,
        "user_id" => $user_id
    ]);

    // Encode the header, payload, and signature
    $header = base64_encode(json_encode(["alg" => "HS256", "typ" => "JWT"]));
    $payload = base64_encode($payload);
    $signature = hash_hmac('sha256', "$header.$payload", $secret_key, true);
    $signature = base64_encode($signature);

    return "$header.$payload.$signature";
}
?>
