<?php
function generate_jwt($user_id) {
    // Secret key for encoding the JWT (wag ibigay ang secret key publicly)
    $secret_key = "your_secret_key_here";
    
    // Set the JWT claims (payload)
    $issued_at = time();
    $expiration_time = $issued_at + 3600;  // 1 hour expiration
    $payload = array(
        "iat" => $issued_at,
        "exp" => $expiration_time,
        "user_id" => $user_id
    );

    // Encode the payload to base64url
    $base64UrlHeader = base64UrlEncode(json_encode(["alg" => "HS256", "typ" => "JWT"]));
    $base64UrlPayload = base64UrlEncode(json_encode($payload));

    // Create the signature
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret_key, true);
    $base64UrlSignature = base64UrlEncode($signature);

    // Combine all parts
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    return $jwt;
}

// Function to URL-safe base64 encode
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
?>
