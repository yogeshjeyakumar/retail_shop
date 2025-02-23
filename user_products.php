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

// Get category from the URL, if available
$category = isset($_GET['category']) ? $_GET['category'] : 'sofa'; // Default category is 'sofa'


// Check for search query
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch and display products based on search query and category
$show = "SELECT * FROM $category";
if (!empty($search_query)) {
    $search_query = mysqli_real_escape_string($conn, $search_query);

    // If search query is numeric, use it for the code (exact match)
    if (is_numeric($search_query)) {
        $show .= " WHERE code = '$search_query'"; // Exact match for code
    } else {
        $show .= " WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%'"; // Partial match for name & code
    }
}
$swrite = mysqli_query($conn, $show);

echo "<div class='products'>";
if (mysqli_num_rows($swrite) > 0) {
    while ($select = mysqli_fetch_assoc($swrite)) {
        echo "<div class='product'>";
        $image_url = $select['image'];
        echo "<img src='" . $image_url . "' alt='Product Image' class='img'>";
        echo "<table class='items-table'>";
        echo "<tr><td>Product Name</td><td>:</td><td>" . $select['name'] . "</td></tr>";
        echo "<tr><td>Product Color</td><td>:</td><td>" . $select['color'] . "</td></tr>";
        echo "<tr><td>MRP</td><td>: ₹</td><td> <s>" . $select['price']+1500 ."</s> </td></tr>";
        echo "<tr><td>Product Price</td><td>: ₹</td><td>" . $select['price'] . "</td></tr>";
        echo "</table>";
        echo "</div>";
    }
} else {
    echo "<center><p>No products found for '$search_query'.</center></p>";
}
echo "</div>";

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="stylesheet" href="css/products.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <div class="logo">WOOD FURNITURE</div>
        <nav>
            <a href="user_index.php">Home</a>
            <a href="" id="about">About Us</a>
           
            <div class="search-container">
                <form action="products.php?category=<?php echo $category; ?>" method="get" id="searchform">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search..." 
                        class="search-box"
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                        onkeydown="if(event.key === 'Enter'){this.form.submit();}">
                </form>
            </div>
           
        </nav>
    </header>
    <footer>
<h1>About Us</h1>
    <h2>20+ Years of Excellence in the Furniture Business</h2>
    
    <div class="footer-container">
        <div class="footer-column">
            <h3>🏬 Shop Address</h3>
            <p>No. 123, Main Road,</p>
            <p>Chennai - 600001, India</p>
            <p><strong>🕒 Timings:</strong> 9:00 AM - 9:00 PM</p>
        </div>
        
        <div class="footer-divider"></div> <!-- Vertical Line -->

        <div class="footer-column">
            <h3>📞 Contact Us</h3>
            <p><strong>Phone:</strong> +91 98765 43210</p>
            <p><strong>WhatsApp:</strong> +91 98765 43211</p>
            <p><strong>Email:</strong> support@furnitureshop.com</p>
        </div>

        <div class="footer-divider"></div> <!-- Vertical Line -->

        <div class="footer-column">
            <h3>🛋️ Our Products</h3>
            <p>Sofas, Beds, Chairs, Dining Tables</p>
            <p>Wardrobes, Dressing Tables, Showcases</p>
            <p>Center Tables, Writing Tables, More...</p>
        </div>
    </div>

    <div class="social-media">
        <a href="https://www.facebook.com" class="social-icon" aria-label="Facebook">
            <img src="index_images/facebook.jpg" alt="Facebook">
        </a>
        <a href="https://www.instagram.com" class="social-icon" aria-label="Instagram">
            <img src="index_images/insta.jpg" alt="Instagram">
        </a>
        <a href="https://www.whatsapp.com" class="social-icon" aria-label="Whatsapp">
            <img src="index_images/whatsapp.jpg" alt="Whatsapp">
        </a>
        <a href="https://www.twitter.com" class="social-icon" aria-label="Twitter">
            <img src="index_images/x.jpg" alt="Twitter">
        </a>
    </div>
</footer>
  
</body>
<script>

     // Smooth scroll for "Products" link
     document.querySelector('#about').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default anchor link behavior

            // Scroll to the products section with smooth behavior
            document.querySelector('footer').scrollIntoView({
                behavior: 'smooth'
            });
        });

</script>
</html>
