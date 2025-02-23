<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = mysqli_connect("localhost:3307", "root", "", "products");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get search query from input
$search_query = isset($_GET['search']) ? trim($_GET['search']) : "";

// Base SQL query to fetch customer details with purchase count
$customer_query = "
    SELECT 
        customer.name AS customer_name,
        customer.phone AS customer_phone,
        customer.address AS customer_address,
        customer.payment_option AS payment_option,
        customer.total AS total_amount,
        customer.generated_at AS generated_at,
        customer.products_names AS products_names,
        (SELECT COUNT(*) FROM customer AS c WHERE c.phone = customer.phone) AS purchase_count
    FROM customer
";

// Modify query if search is entered
if (!empty($search_query)) {
    $customer_query .= " WHERE 
        customer.name LIKE '%$search_query%' 
        OR customer.phone LIKE '%$search_query%' 
        OR customer.products_names LIKE '%$search_query%' 
    ";
}

$customer_query .= " ORDER BY customer.generated_at DESC";

$customer_result = $conn->query($customer_query);
$customer_details = [];

if ($customer_result && $customer_result->num_rows > 0) {
    while ($row = $customer_result->fetch_assoc()) {
        $customer_details[] = $row;
    }
}



// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stocks - WOOD FURNITURE</title>
    <link rel="stylesheet" href="css/stocks.css">
</head>
<body>

    <header>
        <div class="logo">WOOD FURNITURE</div>
        <nav>
            <a href="index.php">Home</a>
            <a href="billing.php">Billing</a>
            <a href="stocks.php">Stock</a>
            <a href="" id="login_user">Users</a>
           
            <form action="customer.php" method="get" id="searchform">
                <input
                    type="text"
                    name="search"
                    placeholder="Search..."
                    class="search-box"
                    value="<?php echo htmlspecialchars($search_query); ?>"
                    onkeydown="if(event.key === 'Enter'){this.form.submit();}">
            </form>
            
        </nav>
    </header>

    <!-- Customer Details Section with ID for anchor link -->
    <section class="customer-details" id="customer-details">
        <h2>Customer Purchase Details</h2>
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Payment Option</th>
                    <th>Total Amount</th>
                    <th>Generated At</th>
                    <th>Products Purchased</th>
                    <th>Customer Type</th> <!-- New Column -->
                </tr>
            </thead>
            <tbody>
                <?php if (count($customer_details) > 0): ?>
                    <?php foreach ($customer_details as $customer): ?>
                        <tr>
                            <td><?php echo $customer['customer_name']; ?></td>
                            <td><?php echo $customer['customer_phone']; ?></td>
                            <td><?php echo $customer['customer_address']; ?></td>
                            <td><?php echo $customer['payment_option']; ?></td>
                            <td>₹<?php echo $customer['total_amount']; ?></td>
                            <td><?php echo $customer['generated_at']; ?></td>
                            <td><?php echo $customer['products_names']; ?></td>
                            <td>
                                <?php
                                // Determine if the customer is new or regular
                                if ($customer['purchase_count'] == 1) {
                                    echo "New";
                                } else {
                                    echo "Regular";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No matching results found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>


    <?php
$conn = new mysqli("localhost:3307", "root", "", "products");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT COUNT(*) AS total_users FROM users";
$result = $conn->query($sql);

$total_users = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_users = $row['total_users']-1;
}

$conn->close();
?>
<div class="card1"></div>
    <div class="card">
       
        <div class="number" id="userCount">0</div>
        <div class="text">Number of Users Login Our Page</div>
    </div>
    
    <?php
$conn = new mysqli("localhost:3307", "root", "", "products");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT name, phone FROM users";
$result = $conn->query($sql);

$users_list = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users_list[] = $row;
    }
}

$conn->close();
?>

<!-- Users List Section -->
<section class="users-list">
    <h2>Registered Users</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone Number</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($users_list) > 0): ?>
                <?php foreach ($users_list as $user): ?>
                    <tr>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo $user['phone']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

    <footer>
        <p>&copy; 2025 WOOD FURNITURE. All Rights Reserved.</p>
    </footer>

</body>

<script>
     function animateCount(target, element) {
            let start = 0;
            let end = target;
            let duration = 500; // Animation time in milliseconds
            let stepTime = Math.abs(Math.floor(duration / end));
            let timer = setInterval(() => {
                start++;
                element.textContent = start;
                if (start >= end) {
                    clearInterval(timer);
                }
            }, stepTime);
        }

        let totalUsers = <?php echo $total_users; ?>;
        let countElement = document.getElementById("userCount");
        animateCount(totalUsers, countElement);



        document.querySelector('#login_user').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default anchor link behavior

            // Scroll to the products section with smooth behavior
            document.querySelector('.card1').scrollIntoView({
                behavior: 'smooth'
            });
        });

</script>
</html>