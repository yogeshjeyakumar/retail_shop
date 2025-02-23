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

// Fetch stock levels for all products along with their categories
$stock_query = "SELECT category, id, name, quantity, price FROM (
                SELECT 'sofa' AS category, id, name, quantity, price FROM sofa 
                UNION
                SELECT 'beds' AS category, id, name, quantity, price FROM beds
                UNION
                SELECT 'center_table' AS category, id, name, quantity, price FROM center_table
                UNION
                SELECT 'chairs' AS category, id, name, quantity, price FROM chairs
                UNION
                SELECT 'dining_table' AS category, id, name, quantity, price FROM dining_table
                UNION
                SELECT 'dressing_table' AS category, id, name, quantity, price FROM dressing_table
                UNION
                SELECT 'showcase' AS category, id, name, quantity, price FROM showcase
                UNION
                SELECT 'wardrobe' AS category, id, name, quantity, price FROM wardrobe
                UNION
                SELECT 'writing_table' AS category, id, name, quantity, price FROM writing_table
                ) AS all_products ORDER BY category";

$result = $conn->query($stock_query);
$products = [];
$low_stock_alerts = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
        if ($row['quantity'] < 3) {
            $low_stock_alerts[] = $row; // Add low stock products to the alert list
        }
    }
}






// Get the selected period from the form, default to 'today'
$period = isset($_GET['period']) ? $_GET['period'] : 'today';

// Modify the query based on the selected period
switch ($period) {
    case 'current_week':
        $date_condition = "AND WEEK(customer.generated_at) = WEEK(CURDATE())"; // Current week
        $title = "Current Week Report";
        break;
    case 'current_month':
        $date_condition = "AND MONTH(customer.generated_at) = MONTH(CURDATE()) AND YEAR(customer.generated_at) = YEAR(CURDATE())"; // Current month
        $title = "Current Month Report";
        break;
    case 'current_year':
        $date_condition = "AND YEAR(customer.generated_at) = YEAR(CURDATE())"; // Current year
        $title = "Current Year Report";
        break;
    case 'today':
    default:
        $date_condition = "AND DATE(customer.generated_at) = CURDATE()"; // Today
        $title = "Today's Report";
        break;
}

// Fetch sales data based on the selected period
$daily_sales_query = "
    SELECT 
        customer.name AS customer_name,
        customer.total AS total_amount,
        customer.generated_at AS generated_at,
        customer.products_names AS products_names
    FROM customer
    WHERE 1 $date_condition
    ORDER BY customer.generated_at DESC
";

$daily_sales_result = $conn->query($daily_sales_query);
$daily_sales = [];
if ($daily_sales_result && $daily_sales_result->num_rows > 0) {
    while ($row = $daily_sales_result->fetch_assoc()) {
        $daily_sales[] = $row;
    }
}

// Initialize an array to hold the total sold quantities for the selected period
$sold_quantities = [];
foreach ($daily_sales as $sale) {
    if (!empty($sale['products_names'])) {
        $sold_products = explode(',', $sale['products_names']);
        foreach ($sold_products as $product_name) {
            foreach ($products as &$product) {
                if (trim($product['name']) == trim($product_name)) {
                    if (!isset($sold_quantities[$product['category']])) {
                        $sold_quantities[$product['category']] = 0;
                    }
                    $sold_quantities[$product['category']]++;
                }
            }
        }
    }
}

// Calculate total sales per category
$category_sales = [];
$category_product_count = []; // To store the count of products per category
foreach ($products as &$product) {
    $category = $product['category'];

    // Count the number of products in each category
    if (!isset($category_product_count[$category])) {
        $category_product_count[$category] = 0;
    }
    $category_product_count[$category]++;

    // Calculate total sales for each category (based on sold quantities)
    $sold_quantity = isset($sold_quantities[$category]) ? $sold_quantities[$category] : 0;
    $category_sales[$category]['total_sales'] = isset($category_sales[$category]['total_sales']) 
        ? $category_sales[$category]['total_sales'] + ($sold_quantity * $product['price']) 
        : ($sold_quantity * $product['price']);
        
    // Store total quantity sold per category
    $category_sales[$category]['total_quantity_sold'] = isset($category_sales[$category]['total_quantity_sold']) 
        ? $category_sales[$category]['total_quantity_sold'] + $sold_quantity 
        : $sold_quantity;
    
    // Store total quantity of product in the category (without time period filter)
    $category_sales[$category]['total_quantity'] = isset($category_sales[$category]['total_quantity']) 
        ? $category_sales[$category]['total_quantity'] + $product['quantity'] 
        : $product['quantity'];
}

// Calculate total stock quantity
$total_quantity = 0;
foreach ($products as $product) {
    $total_quantity += $product['quantity'];
}

// Database connection
$conn = new mysqli("localhost:3307", "root", "", "products");

// Fetching the sum of total amounts for each month
$sql = "
    SELECT 
        months.month AS month, 
        COALESCE(SUM(c.total), 0) AS total_sales
    FROM 
        (SELECT 'January' AS month, 1 AS month_num UNION ALL
         SELECT 'February', 2 UNION ALL
         SELECT 'March', 3 UNION ALL
         SELECT 'April', 4 UNION ALL
         SELECT 'May', 5 UNION ALL
         SELECT 'June', 6 UNION ALL
         SELECT 'July', 7 UNION ALL
         SELECT 'August', 8 UNION ALL
         SELECT 'September', 9 UNION ALL
         SELECT 'October', 10 UNION ALL
         SELECT 'November', 11 UNION ALL
         SELECT 'December', 12) AS months
    LEFT JOIN customer c ON MONTH(c.generated_at) = months.month_num
    GROUP BY months.month, months.month_num
    ORDER BY months.month_num;
";

$result = $conn->query($sql);

// Prepare data for the chart
$months = [];
$totals = [];

while($row = $result->fetch_assoc()) {
    $months[] = $row['month'];
    $totals[] = $row['total_sales'];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    

   
</head>
<body>


<header>
    <div class="logo">WOOD FURNITURE</div>
    <nav>
        <a href="index.php">Home</a>
        <a href="billing.php">Billing</a>
        <a href="customer.php">Customers</a>
        <a href="" id="low_stock">Low Stock</a>
        <a href="" id="stock-report-page">Stock Report</a>
    
    </nav>
</header>

<center>
<h2>Sales Data Visualization</h2>
<canvas id="monthlySalesChart"></canvas>
</center>
<!-- Time Period Dropdown -->
<div class="a1" style="margin-top: 100px;">
<form method="GET" action="stocks.php" class="filter-form">
    <label for="period" style="color: var(--hero-text-color)">Select Time Period: </label>
    <select name="period" id="period">
        <option value="today" <?php echo ($period == 'today') ? 'selected' : ''; ?>>Today</option>
        <option value="current_week" <?php echo ($period == 'current_week') ? 'selected' : ''; ?>>Current Week</option>
        <option value="current_month" <?php echo ($period == 'current_month') ? 'selected' : ''; ?>>Current Month</option>
        <option value="current_year" <?php echo ($period == 'current_year') ? 'selected' : ''; ?>>Current Year</option>
    </select>
    <button type="submit">Filter</button>
</form>
    </div>

<!-- Low Stock Alert -->
<?php if (count($low_stock_alerts) > 0): ?>
    <script>
        var low=document.getElementById('low_stock');
        low.addEventListener('click',function() {
            var lowStockMessage = "Low Stock Products:\n";
            <?php foreach ($low_stock_alerts as $alert): ?>
                lowStockMessage += "<?php echo $alert['name']; ?> (<?php echo $alert['category']; ?>) - Quantity: <?php echo $alert['quantity']; ?>\n";
            <?php endforeach; ?>
            alert(lowStockMessage);
        });
    </script>
<?php endif; ?>

<!-- Content Above the Stock Report -->

<section class="daily-sales-report">
    <h2>Sales Report - <?php echo $title; ?></h2>
    <!-- Add Print Button -->
    <button onclick="printSalesReport()" id="print-btn" >Print Sales Report</button>
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Total Amount</th>
                <th>Generated At</th>
                <th>Products Purchased</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($daily_sales as $sale): ?>
                <tr>
                    <td><?php echo $sale['customer_name']; ?></td>
                    <td>₹<?php echo $sale['total_amount']; ?></td>
                    <td><?php echo $sale['generated_at']; ?></td>
                    <td><?php echo $sale['products_names']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<!-- Stock Report at the Bottom - Grouped by Category -->
<section class="stock-report" id="sales-report">
    <h2>Stock Report</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Number of Products</th>
                <th>Total Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($category_product_count as $category => $count): ?>
                <tr>
                    <td><?php echo $category; ?></td>
                    <td><?php echo $count; ?></td>
                    <td><?php echo isset($category_sales[$category]['total_quantity']) ? $category_sales[$category]['total_quantity'] : 0; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<footer>
    <p>&copy; 2025 WOOD FURNITURE. All Rights Reserved.</p>
</footer>

</body>
<script>
        document.querySelector('#stock-report-page').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default anchor link behavior

            // Scroll to the products section with smooth behavior
            document.querySelector('stock-report').scrollIntoView({
                behavior: 'smooth'
            });
        });

        // Function to print the sales report
        function printSalesReport() {
            var printContents = document.querySelector(".daily-sales-report").innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            window.location.reload(); // Reload to restore the original page
        } 


  // Manually setting the PHP arrays as JavaScript arrays
  var months = <?php echo "['" . implode("','", $months) . "']"; ?>;
        var totals = <?php echo "[" . implode(",", $totals) . "]"; ?>;

        // Creating the Chart
        new Chart(document.getElementById("monthlySalesChart"), {
            type: 'bar', // Bar chart
            data: {
                labels: months,
                datasets: [{
                    label: 'Monthly Sales',
                    data: totals,
                    backgroundColor: '#2b7a78',
                    borderColor: '#2b7a78',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
       
    </script>

</html>