<?php

// Database connection
$conn = mysqli_connect("localhost:3307", "root", "", "products");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

session_start();


// If session not set, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Logout request handle
if (isset($_GET['logout'])) {
    session_destroy();
    session_unset(); // Clear session data
    echo "success"; // Response for JS
    exit();
}

// Initialize variables
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$search_results = [];


// Handle search
if (!empty($search_query)) {
    $search_query = trim($conn->real_escape_string($search_query));

    // Query all tables using UNION for search results
    $sql = "
        (SELECT 'sofa' AS table_name, id, code, name, category, color, price, image, quantity FROM sofa WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'chairs', id, code, name, category, color, price, image, quantity FROM chairs WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'center_table', id, code, name, category, color, price, image, quantity FROM center_table WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'dining_table', id, code, name, category, color, price, image, quantity FROM dining_table WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'beds', id, code, name, category, color, price, image, quantity FROM beds WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'dressing_table', id, code, name, category, color, price, image, quantity FROM dressing_table WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'writing_table', id, code, name, category, color, price, image, quantity FROM writing_table WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'wardrobe', id, code, name, category, color, price, image, quantity FROM wardrobe WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC )
        UNION
        (SELECT 'showcase', id, code, name, category, color, price, image, quantity FROM showcase WHERE name LIKE '%$search_query%' OR code LIKE '%$search_query%' ORDER BY id DESC );
    ";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
    }
}
$recent_products = [];
$recent_sql = "
    (SELECT 'sofa' AS table_name, id, code, name, category, color, price, image, quantity FROM sofa ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'chairs', id, code, name, category, color, price, image, quantity FROM chairs ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'center_table', id, code, name, category, color, price, image, quantity FROM center_table ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'dining_table', id, code, name, category, color, price, image, quantity FROM dining_table ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'beds', id, code, name, category, color, price, image, quantity FROM beds ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'dressing_table', id, code, name, category, color, price, image, quantity FROM dressing_table ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'writing_table', id, code, name, category, color, price, image, quantity FROM writing_table ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'wardrobe', id, code, name, category, color, price, image, quantity FROM wardrobe ORDER BY id DESC LIMIT 2)
    UNION ALL
    (SELECT 'showcase', id, code, name, category, color, price, image, quantity FROM showcase ORDER BY id DESC LIMIT 2)
    ORDER BY id DESC;
";

$recent_result = $conn->query($recent_sql);

if ($recent_result && $recent_result->num_rows > 0) {
    while ($row = $recent_result->fetch_assoc()) {
        $recent_products[] = $row;
    }
}


$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOOD FURNITURE</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">

</head>

<body>
    <header role="admin">
        <div class="logo" onclick="reload()">WOOD FURNITURE</div>
        <nav>
            <a href="#products">Products</a>
            <a href="billing.php">Billing</a>
            <a href="stocks.php">Stocks</a>
            <a href="customer.php">Customer</a>
            
            <div class="search-container">
            <form action="index.php" method="get" id="searchform">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search..."
                        class="search-box"
                        value="<?php echo htmlspecialchars($search_query); ?>"
                        onkeydown="if(event.key === 'Enter'){this.form.submit();
                        }">
                </form>
            </div>
        </nav>
    </header>
    <a href="login.php" id="logout" onclick="logoutUser()" style="color:#17252a">Logout</a>
    <!-- Display Search Results -->
    <?php if (!empty($search_results)): ?>
        <section class="search-results">
            <h2>Search Results</h2>
            <div class="products">
                <?php foreach ($search_results as $product): ?>
                    <a href="<?php echo $product['table_name']; ?>.php" class="product-category1">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        <p>
                            <strong><?php echo $product['name']; ?></strong><br>
                            Price: ₹<?php echo $product['price']; ?><br>
                            Category: <?php echo $product['category']; ?>
                        </p>
                    </a>
                    
                <?php endforeach; ?>
            </div>
        </section>
    <?php elseif (!empty($search_query)): ?>
        <section class="search-results">
            <h2>No Results Found</h2>
            <p id="notfound">No products found for "<?php echo htmlspecialchars($search_query); ?>"</p>
        </section>
    <?php endif; ?>


    <!-- Recently Added Products Section -->
    <section class="recent-products">
    <h2>Recently Added Products</h2>
    <div class="products">
        <?php foreach ($recent_products as $product): ?>
            <a href="products.php?category=<?php echo $product['table_name']; ?>" class="product-category1">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                <p>
                    <strong><?php echo $product['name']; ?></strong><br><br>
                    Price: ₹<?php echo $product['price']; ?><br><br>
                    Category: <?php echo $product['category']; ?>
                </p>
            </a>
        <?php endforeach; ?>
    </div>
</section>






    <section class="hero">
        <h1>Manage Your Furniture Store Effortlessly</h1>
        <p>Track products, stocks, and generate bills seamlessly!</p>
    </section>

    <section class="products" id="products">
    <h2>Furniture Categories</h2>
    <a href="products.php?category=sofa" class="product-category">
        <img src="index_images/sofa.jpg" alt="Sofa">
        Sofa
    </a>
    <a href="products.php?category=chairs" class="product-category">
        <img src="index_images/chair.jpg" alt="Chair">
        Chairs
    </a>
    <a href="products.php?category=center_table" class="product-category">
        <img src="index_images/center_table.jpg" alt="Center Table">
        Center Table
    </a>
    <a href="products.php?category=dining_table" class="product-category">
        <img src="index_images/dining _table.jpg" alt="Dining Table">
        Dining Table
    </a>
    <a href="products.php?category=beds" class="product-category">
        <img src="index_images/bed.jpg" alt="Bed">
        Beds
    </a>
    <a href="products.php?category=dressing_table" class="product-category">
        <img src="index_images/dressing_tables.jpg" alt="Dressing Table">
        Dressing Table
    </a>
    <a href="products.php?category=writing_table" class="product-category">
        <img src="index_images/writing table.jpg" alt="Writing Table">
        Writing Table
    </a>
    <a href="products.php?category=wardrobe" class="product-category">
        <img src="index_images/wardrobe.jpg" alt="Wardrobe">
        Wardrobe
    </a>
    <a href="products.php?category=showcase" class="product-category">
        <img src="index_images/showcase.jpg" alt="Showcase">
        Showcase
    </a>
</section>




    <footer>
        <h2>20+ Years of Excellence in the Furniture Business</h2>
        <div class="social-media">
            <a href="https://www.facebook.com" class="social-icon" aria-label="Facebook">
                <img src="index_images/facebook.jpg" alt="Facebook">
            </a>
            <a href="https://www.instagram.com" class="social-icon" aria-label="Instagram">
                <img src="index_images/insta.jpg" alt="Instagram">
            </a>
            <a href="https://www.linkedin.com" class="social-icon" aria-label="Whatsapp">
                <img src="index_images/whatsapp.jpg" alt="Whatsapp">
            </a>
            <a href="https://www.twitter.com" class="social-icon" aria-label="Twitter">
                <img src="index_images/x.jpg" alt="Twitter">
            </a>
        </div>
    </footer>


    <script>
        // Smooth scroll for "Products" link
        document.querySelector('nav a[href="#products"]').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default anchor link behavior

            // Scroll to the products section with smooth behavior
            document.querySelector('#products').scrollIntoView({
                behavior: 'smooth'
            });
        });


        function logoutUser() {
    let confirmLogout = confirm("Are you sure you want to logout?");
    if (confirmLogout) {
        fetch('user_index.php?logout=true')
            .then(response => response.text())
            .then(data => {
                if (data.trim() === "success") {
                    window.location.replace('login.php'); // Redirect to login page
                }
            })
            .catch(error => console.error('Logout failed:', error));
    }
    window.reload();
}


    function reload() {
        window.history.back();
    }

    // Disable back button after logout
    window.history.pushState(null, "", window.location.href);
    window.addEventListener("popstate", function () {
        window.location.replace("login.php");
    });
    </script>


</body>

</html>