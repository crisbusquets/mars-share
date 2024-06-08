<?php
function sm_encrypt_data($data) {
    $secretKey = $_ENV['SM_SECRET_KEY'];
    $iv = $_ENV['SM_IV'];
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', base64_decode($secretKey), 0, base64_decode($iv));
    return $encrypted;
}

function sm_decrypt_data($data) {
    $secretKey = $_ENV['SM_SECRET_KEY'];
    $iv = $_ENV['SM_IV'];
    $decrypted = openssl_decrypt($data, 'aes-256-cbc', base64_decode($secretKey), 0, base64_decode($iv));
    return $decrypted;
}