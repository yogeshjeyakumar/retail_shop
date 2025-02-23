<?php
// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    // Extract the product data from the request
    $table_name = $data['table_name'];
    $code = $data['code'];
    $new_stock = $data['new_stock'];

    // Database connection
$conn = mysqli_connect("localhost:3307", "root", "", "products");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

    // Prepare the update query
    $stmt = $conn->prepare("UPDATE $table_name SET quantity = ? WHERE code = ?");
    $stmt->bind_param("is", $new_stock, $code);  // Assuming `quantity` is an integer and `code` is a string

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
?>
