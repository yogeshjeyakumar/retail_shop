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

// Handle DELETE request
if (isset($_POST['delete'])) {
    $id_to_delete = $_POST['product_id'];
    $delete_query = "DELETE FROM $category WHERE id = $id_to_delete";
    mysqli_query($conn, $delete_query);
}

// Handle the form submission (image upload and stock)
if (isset($_POST['submit'])) {
    $code = $_POST['code'];
    $name = $_POST['name'];
    $category = $_POST['category'];  // The selected category
    $color = $_POST['color'];
    $price = $_POST['pprice'];
    $quantity = $_POST['quantity'];
    $product_id = $_POST['product_id'] ?? null;

    // Handle the uploaded file (image upload)
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $upload_dir = "uploads/";
    $image_path = "";

    if (!empty($image_name)) {
        $image_path = $upload_dir . basename($image_name);
        move_uploaded_file($image_tmp, $image_path);
    } else if ($product_id) {
        $select_query = "SELECT image FROM $category WHERE id = $product_id";
        $result = mysqli_query($conn, $select_query);
        $row = mysqli_fetch_assoc($result);
        $image_path = $row['image'];
    }

    // Update or insert the product
    if ($product_id) {
        $query = "UPDATE $category SET code='$code', name='$name', category='$category', color='$color', price='$price', image='$image_path', quantity='$quantity' WHERE id=$product_id";
    } else {
        $query = "INSERT INTO $category (code, name, category, color, price, image, quantity) VALUES ('$code','$name', '$category', '$color', $price, '$image_path', $quantity)";
    }

    mysqli_query($conn, $query);
    header("Location: products.php?category=$category");
    exit();
}

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
        echo "<tr><td>Product ID</td><td>:</td><td>" . $select['id'] . "</td></tr>";
        echo "<tr><td>Product Code</td><td>:</td><td>" . $select['code'] . "</td></tr>";
        echo "<tr><td>Product Name</td><td>:</td><td>" . $select['name'] . "</td></tr>";
        echo "<tr><td>Product Category</td><td>:</td><td>" . $select['category'] . "</td></tr>";
        echo "<tr><td>Product Color</td><td>:</td><td>" . $select['color'] . "</td></tr>";
        echo "<tr><td>Product Price</td><td>: ₹</td><td>" . $select['price'] . "</td></tr>";
        echo "<tr><td>Product Available</td><td>:</td><td>" . $select['quantity'] . "</td></tr>";
        echo "</table>";
        echo "<form method='POST' style='display:inline-block;' onsubmit='return confirmDelete()'>";
        echo "<input type='hidden' name='product_id' value='" . $select['id'] . "'>";
        echo "<button type='submit' class='dlt' name='delete'>Delete</button>";
        echo "</form>";
        echo "<button class='edit' onclick='toggleEditForm(" . $select['id'] . ", \"" . $select['code'] . "\", \"" . $select['name'] . "\", \"" . $select['category'] . "\", \"" . $select['color'] . "\", \"" . $select['price'] . "\", \"" . $select['quantity'] . "\", \"" . $select['image'] . "\", this)'>Edit</button>";
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
            <a href="index.php">Home</a>
            <a href="billing.php">Billing</a>
            <a href="stocks.php">Stocks</a>
            <a href="customer.php">Customers</a>

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

    <center>
        <form action="products.php?category=<?php echo $category; ?>" method="post" id="form" enctype="multipart/form-data" style="display: none;">
            <div class="container">
                <input type="text" name="code" id="code" class="input" placeholder="Product code" required><br>
                <input type="text" name="name" id="name" class="input" placeholder="Product Name" required><br>
                <input type="text" name="category" id="category" class="input" placeholder="Product Category" required value="<?php echo $category; ?>" readonly><br>
                <input type="text" name="color" id="color" class="input" placeholder="Product Color" required><br>
                <input type="number" name="pprice" id="price" class="input" placeholder="Product Price" required><br>
                <input type="number" name="quantity" id="quantity" class="input" placeholder="Stock Quantity" required><br>
                <input type="file" name="image" id="image" class="input" placeholder="Image URL"><br>
                <input type="hidden" name="product_id" id="product_id"> <!-- Hidden field for product ID when editing -->
                <input type="submit" name="submit" class="submit"><br>
            </div>
        </form>
        <input type="button" name="add" id="add" value="ADD">
    </center>

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
        // Toggle Add Product Form
        document.getElementById('add').addEventListener('click', function() {
            const form = document.getElementById('form');
            const addButton = document.getElementById('add');
            const editButtons = document.querySelectorAll('.edit');

            // Reset all edit buttons to "Edit" when Add button is clicked
            editButtons.forEach((btn) => (btn.textContent = 'Edit'));

            if (form.dataset.mode !== 'add') {
                // If form is not in "add" mode, reset it
                document.getElementById('form').reset();
            }

            form.dataset.mode = 'add'; // Set mode to "add"
            form.classList.toggle('show');
            addButton.value = form.classList.contains('show') ? "CLOSE" : "ADD";
        });

        // Open/Close Edit Form
        function toggleEditForm(productId, code, name, category, color, price, quantity, image, button) {
            const form = document.getElementById('form');
            const addButton = document.getElementById('add');
            const editButtons = document.querySelectorAll('.edit');

            if (button.textContent === 'Edit') {
                // Close the Add button if it's open
                if (form.dataset.mode === 'add') {
                    addButton.value = 'ADD';
                    form.classList.remove('show');
                }

                // Reset all other edit buttons to "Edit"
                editButtons.forEach((btn) => (btn.textContent = 'Edit'));

                // Open Edit Form
                form.style.display = 'block';
                form.dataset.mode = 'edit'; // Set mode to "edit"
                button.textContent = 'Edit Close';
                document.getElementById('product_id').value = productId;
                document.getElementById('code').value = code;
                document.getElementById('name').value = name;
                document.getElementById('category').value = category;
                document.getElementById('color').value = color;
                document.getElementById('price').value = price;
                document.getElementById('quantity').value = quantity;
                document.getElementById('image').value = image;
            } else {
                // Close Edit Form
                form.style.display = 'none';
                form.dataset.mode = ''; // Reset mode
                button.textContent = 'Edit';
                document.getElementById('form').reset();
            }
        }

        function confirmDelete() {
            return confirm("Are you sure you want to delete this product?");
        }

        function b() {
            window.history.back();
        }

        function f() {
            window.history.forward();
        }
    </script>
</body>

</html>