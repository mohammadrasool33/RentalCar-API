<?php

/**
 * API Testing Script for Rental Car API
 * Tests the new guarantor and passport functionality
 */

// Base URL for the API
$baseUrl = 'http://localhost:8000/api';

// Store tokens and IDs for later use
$data = [
    'tokens' => [],
    'rental_ids' => [],
    'car_ids' => []
];

// Helper function to make API requests
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $curl = curl_init();
    
    $headers = ['Accept: application/json', 'Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ];
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($curl, $options);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        echo "cURL Error: " . $err . "\n";
        return null;
    }
    
    return [
        'status' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

// Function to run a test and display results
function runTest($name, $callback) {
    echo "\n========== TEST: $name ==========\n";
    $result = $callback();
    echo "Status: " . ($result ? "PASS" : "FAIL") . "\n";
    echo "==================================\n\n";
    return $result;
}

// 1. Register and get token (if authentication is required)
function register() {
    global $baseUrl, $data;
    
    $userData = [
        'name' => 'Test User',
        'email' => 'test_' . time() . '@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ];
    
    $response = makeRequest("$baseUrl/register", 'POST', $userData);
    
    if ($response && isset($response['body']['token'])) {
        $data['tokens']['user'] = $response['body']['token'];
        echo "User registered and token acquired.\n";
        return true;
    }
    
    echo "Failed to register user or get token.\n";
    return false;
}

// 2. Get available cars
function getAvailableCars() {
    global $baseUrl, $data;
    
    $response = makeRequest("$baseUrl/cars?available=true", 'GET', null, $data['tokens']['user'] ?? null);
    
    if ($response && $response['status'] == 200 && !empty($response['body']['data'])) {
        // Save car IDs for later use
        foreach ($response['body']['data'] as $car) {
            $data['car_ids'][] = $car['id'];
        }
        echo "Found " . count($data['car_ids']) . " available cars.\n";
        return true;
    }
    
    echo "Failed to get available cars or no cars available.\n";
    return false;
}

// 3. Create rental with primary guarantor and passport
function createRentalWithPassport() {
    global $baseUrl, $data;
    
    if (empty($data['car_ids'])) {
        echo "No car IDs available for testing.\n";
        return false;
    }
    
    $rentalData = [
        'car_id' => $data['car_ids'][0],
        'duration_type' => 'daily',
        'duration_count' => 3,
        'primary_guarantor_name' => 'John Smith',
        'primary_guarantor_phone' => '+123456789',
        'passport' => 'AB123456',
        'secondary_guarantor_name' => 'Jane Doe',
        'secondary_guarantor_phone' => '+987654321',
        'secondary_guarantor_id_type' => 'driver_license',
        'secondary_guarantor_id_number' => 'DL987654',
        'pickup_location' => 'Airport Terminal 1',
        'mileage_at_rental' => 12500,
        'is_paid' => true
    ];
    
    $response = makeRequest("$baseUrl/rentals", 'POST', $rentalData, $data['tokens']['user'] ?? null);
    
    if ($response && $response['status'] == 201 && isset($response['body']['data']['id'])) {
        $data['rental_ids']['with_passport'] = $response['body']['data']['id'];
        echo "Created rental with passport, ID: " . $data['rental_ids']['with_passport'] . "\n";
        
        // Check that the passport was stored correctly
        if ($response['body']['data']['passport'] === 'AB123456') {
            echo "Passport was stored correctly.\n";
            return true;
        } else {
            echo "Passport was not stored correctly.\n";
            return false;
        }
    }
    
    echo "Failed to create rental with passport.\n";
    return false;
}

// 4. Create rental with legacy fields
function createRentalWithLegacyFields() {
    global $baseUrl, $data;
    
    if (count($data['car_ids']) < 2) {
        echo "Not enough car IDs available for testing.\n";
        return false;
    }
    
    $rentalData = [
        'car_id' => $data['car_ids'][1],
        'duration_type' => 'weekly',
        'duration_count' => 2,
        'renter_name' => 'Mike Johnson', 
        'renter_phone' => '+1122334455',
        'passport_number' => 'CD654321',
        'pickup_location' => 'Downtown Office',
        'mileage_at_rental' => 8700,
        'is_paid' => false
    ];
    
    $response = makeRequest("$baseUrl/rentals", 'POST', $rentalData, $data['tokens']['user'] ?? null);
    
    if ($response && $response['status'] == 201 && isset($response['body']['data']['id'])) {
        $data['rental_ids']['legacy'] = $response['body']['data']['id'];
        echo "Created rental with legacy fields, ID: " . $data['rental_ids']['legacy'] . "\n";
        
        // Check that the legacy fields were mapped correctly
        if ($response['body']['data']['passport'] === 'CD654321') {
            echo "Legacy passport was mapped correctly.\n";
            return true;
        } else {
            echo "Legacy passport was not mapped correctly.\n";
            return false;
        }
    }
    
    echo "Failed to create rental with legacy fields.\n";
    return false;
}

// 5. Update rental with new passport
function updateRentalPassport() {
    global $baseUrl, $data;
    
    if (!isset($data['rental_ids']['with_passport'])) {
        echo "No rental ID with passport available for testing.\n";
        return false;
    }
    
    $updateData = [
        'passport' => 'XY789012',
        'primary_guarantor_phone' => '+9988776655'
    ];
    
    $response = makeRequest(
        "$baseUrl/rentals/" . $data['rental_ids']['with_passport'], 
        'PUT', 
        $updateData, 
        $data['tokens']['user'] ?? null
    );
    
    if ($response && in_array($response['status'], [200, 202])) {
        // Check if the passport was updated correctly
        if ($response['body']['data']['passport'] === 'XY789012') {
            echo "Passport was updated correctly.\n";
            return true;
        } else {
            echo "Passport was not updated correctly.\n";
            return false;
        }
    }
    
    echo "Failed to update rental passport.\n";
    return false;
}

// 6. Update rental with secondary guarantor
function updateSecondaryGuarantor() {
    global $baseUrl, $data;
    
    if (!isset($data['rental_ids']['legacy'])) {
        echo "No legacy rental ID available for testing.\n";
        return false;
    }
    
    $updateData = [
        'secondary_guarantor_name' => 'Robert Brown',
        'secondary_guarantor_phone' => '+5544332211',
        'secondary_guarantor_id_type' => 'national_id',
        'secondary_guarantor_id_number' => 'ID12345678'
    ];
    
    $response = makeRequest(
        "$baseUrl/rentals/" . $data['rental_ids']['legacy'], 
        'PUT', 
        $updateData, 
        $data['tokens']['user'] ?? null
    );
    
    if ($response && in_array($response['status'], [200, 202])) {
        // Check if the secondary guarantor was updated correctly
        if ($response['body']['data']['secondary_guarantor_name'] === 'Robert Brown') {
            echo "Secondary guarantor was updated correctly.\n";
            return true;
        } else {
            echo "Secondary guarantor was not updated correctly.\n";
            return false;
        }
    }
    
    echo "Failed to update secondary guarantor.\n";
    return false;
}

// 7. Return a car
function returnCar() {
    global $baseUrl, $data;
    
    if (!isset($data['rental_ids']['with_passport'])) {
        echo "No rental ID with passport available for testing.\n";
        return false;
    }
    
    $returnData = [
        'id' => $data['rental_ids']['with_passport'],
        'mileage_at_return' => 12890,
        'additional_charges' => 25.50,
        'return_location' => 'Airport Terminal 2',
        'comments' => 'Minor scratch on rear bumper',
        'return_service_check' => [
            'fuel_level' => '3/4',
            'exterior_condition' => 'good',
            'interior_condition' => 'good'
        ]
    ];
    
    $response = makeRequest("$baseUrl/rentals/return", 'POST', $returnData, $data['tokens']['user'] ?? null);
    
    if ($response && in_array($response['status'], [200, 202])) {
        echo "Car returned successfully.\n";
        return true;
    }
    
    echo "Failed to return car.\n";
    return false;
}

// 8. Get rental details to verify
function verifyRentalDetails() {
    global $baseUrl, $data;
    
    if (!isset($data['rental_ids']['with_passport'])) {
        echo "No rental ID with passport available for testing.\n";
        return false;
    }
    
    $response = makeRequest(
        "$baseUrl/rentals/" . $data['rental_ids']['with_passport'], 
        'GET', 
        null, 
        $data['tokens']['user'] ?? null
    );
    
    if ($response && $response['status'] == 200) {
        $rental = $response['body']['data'];
        echo "Rental details retrieved. Checking fields...\n";
        
        $valid = true;
        $checks = [
            'Passport present' => isset($rental['passport']),
            'Is active is false after return' => $rental['is_active'] === false,
            'Has mileage after return' => isset($rental['mileage_at_return']) && $rental['mileage_at_return'] > 0,
            'Return date is set' => isset($rental['return_date']),
            'Final total includes additional charges' => 
                $rental['final_total'] === $rental['final_price'] + $rental['additional_charges']
        ];
        
        foreach ($checks as $check => $result) {
            echo "- $check: " . ($result ? "✅" : "❌") . "\n";
            $valid = $valid && $result;
        }
        
        return $valid;
    }
    
    echo "Failed to get rental details.\n";
    return false;
}

// Run all tests
echo "STARTING API TESTS FOR RENTAL CAR API\n";
echo "Testing guarantor and passport functionality\n";
echo "----------------------------------------\n";

// If authentication is required, uncomment this
// runTest("Register and get token", 'register');

runTest("Get available cars", 'getAvailableCars');
runTest("Create rental with passport", 'createRentalWithPassport');
runTest("Create rental with legacy fields", 'createRentalWithLegacyFields');
runTest("Update rental with new passport", 'updateRentalPassport');
runTest("Update rental with secondary guarantor", 'updateSecondaryGuarantor');
runTest("Return a car", 'returnCar');
runTest("Verify rental details", 'verifyRentalDetails');

echo "\nAPI TESTING COMPLETE\n"; 