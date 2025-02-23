<?php
// Database connection
$conn = mysqli_connect("localhost:3307", "root", "", "products");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Decode the JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate the input data
if (isset($data['name'], $data['phone'], $data['address'], $data['payment_option'], $data['total'], $data['products'])) {
    $name = $conn->real_escape_string($data['name']);
    $phone = $conn->real_escape_string($data['phone']);
    $address = $conn->real_escape_string($data['address']);
    $payment_option = $conn->real_escape_string($data['payment_option']);
    $total = floatval($data['total']);  // Ensure it's a float
    $products = $conn->real_escape_string(implode(", ", $data['products']));  // Join product names with commas

    // Insert customer details into the database, including product names
    $sql = "INSERT INTO `customer` (`name`, `phone`, `address`, `payment_option`, `total`, `products_names`) 
    VALUES ('$name', '$phone', '$address', '$payment_option', '$total', '$products')";

    if ($conn->query($sql) === TRUE) {
        // Return success response
        echo json_encode(['success' => true, 'message' => 'Customer details saved successfully']);
    } else {
        // Return error response
        echo json_encode(['success' => false, 'message' => 'Error saving customer details: ' . $conn->error]);
    }
} else {
    // Return validation error response
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
}

// Close the connection
$conn->close();
?>
