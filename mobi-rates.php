<?php

// Mandatory claims
$mobicard_version = "2.0";
$mobicard_mode = "TEST"; // Use "LIVE" for production
$mobicard_merchant_id = "4";
$mobicard_api_key = "YmJkOGY0OTZhMTU2ZjVjYTIyYzFhZGQyOWRiMmZjMmE2ZWU3NGIxZWM3ZTBiZSJ9";
$mobicard_secret_key = "NjIwYzEyMDRjNjNjMTdkZTZkMjZhOWNiYjIxNzI2NDQwYzVmNWNiMzRhMzBjYSJ9";

$mobicard_token_id = abs(rand(1000000,1000000000));
$mobicard_token_id = "$mobicard_token_id";

$mobicard_txn_reference = abs(rand(1000000,1000000000)); // Your reference
$mobicard_txn_reference = "$mobicard_txn_reference";

$mobicard_service_id = "20000"; // Card Services ID
$mobicard_service_type = "FOREXRATES";

$mobicard_base_currency = "USD"; // Default USD base currency. Options: (USD, EUR, JPY, GBP, CNY).
$mobicard_query_currency = ""; // 3-letter ISO code or leave empty for all currencies

// Create JWT Header
$mobicard_jwt_header = [
    "typ" => "JWT",
    "alg" => "HS256"
];
$mobicard_jwt_header = rtrim(strtr(base64_encode(json_encode($mobicard_jwt_header)), '+/', '-_'), '=');

// Create JWT Payload
$mobicard_jwt_payload = array(
    "mobicard_version" => "$mobicard_version",
    "mobicard_mode" => "$mobicard_mode",
    "mobicard_merchant_id" => "$mobicard_merchant_id",
    "mobicard_api_key" => "$mobicard_api_key",
    "mobicard_service_id" => "$mobicard_service_id",
    "mobicard_service_type" => "$mobicard_service_type",
    "mobicard_token_id" => "$mobicard_token_id",
    "mobicard_txn_reference" => "$mobicard_txn_reference",
    "mobicard_base_currency" => "$mobicard_base_currency",
    "mobicard_query_currency" => "$mobicard_query_currency"
);

$mobicard_jwt_payload = rtrim(strtr(base64_encode(json_encode($mobicard_jwt_payload)), '+/', '-_'), '=');

// Generate Signature
$header_payload = $mobicard_jwt_header . '.' . $mobicard_jwt_payload;
$mobicard_jwt_signature = rtrim(strtr(base64_encode(hash_hmac('sha256', $header_payload, $mobicard_secret_key, true)), '+/', '-_'), '=');

// Create Final JWT
$mobicard_auth_jwt = "$mobicard_jwt_header.$mobicard_jwt_payload.$mobicard_jwt_signature";

// Make API Call
$mobicard_request_access_token_url = "https://mobicardsystems.com/api/v1/forex_rates";

$mobicard_curl_post_data = array('mobicard_auth_jwt' => $mobicard_auth_jwt);

$curl_mobicard = curl_init();
curl_setopt($curl_mobicard, CURLOPT_URL, $mobicard_request_access_token_url);
curl_setopt($curl_mobicard, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_mobicard, CURLOPT_POST, true);
curl_setopt($curl_mobicard, CURLOPT_POSTFIELDS, json_encode($mobicard_curl_post_data));
curl_setopt($curl_mobicard, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl_mobicard, CURLOPT_SSL_VERIFYPEER, false);
$mobicard_curl_response = curl_exec($curl_mobicard);
curl_close($curl_mobicard);

// Parse Response
$response_data = json_decode($mobicard_curl_response, true);

if(isset($response_data) && is_array($response_data)) {
    
    if($response_data['status'] === 'SUCCESS') {
        
        // Access forex rate information
        $base_currency = $response_data['base_currency'];
        $timestamp = $response_data['timestamp'];
        $forex_rates = $response_data['forex_rates'];
        
        echo "Forex Rates Retrieved Successfully!
";
        echo "Base Currency: " . $base_currency . "
";
        echo "Timestamp: " . $timestamp . "
";
        
        // Display all rates or specific rate
        if(!empty($mobicard_query_currency)) {
            $currency_pair = $base_currency . $mobicard_query_currency;
            if(isset($forex_rates[$currency_pair])) {
                echo "Exchange Rate (" . $base_currency . "/" . $mobicard_query_currency . "): " . $forex_rates[$currency_pair] . "
";
            }
        } else {
            echo "Total Currencies Available: " . count($forex_rates) . "
";
            // Display first 5 rates as example
            $counter = 0;
            foreach($forex_rates as $pair => $rate) {
                if($counter < 5) {
                    echo $pair . ": " . $rate . "
";
                    $counter++;
                }
            }
        }
        
    } else {
        echo "Error: " . $response_data['status_message'] . " (Code: " . $response_data['status_code'] . ")";
    }
} else {
    echo "Error: Invalid API response";
}
