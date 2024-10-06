<?php
session_start();

// Check if the seller is logged in
if (!isset($_SESSION['seller_username'])) {
    header("Location: seller_login.php");
    exit();
}

// Include database connection
include 'C:\xampp\htdocs\Fresh_Cart\db_connection.php'; // Ensure this path is correct

$seller_username = $_SESSION['seller_username'];

// Handle product removal
if (isset($_POST['remove_product'])) {
    $productId = $_POST['product_id'];

    // Debugging: Print product ID to check if it's correctly passed
    echo "Removing product with ID: $productId<br>";

    // Prepare the DELETE query using a prepared statement
    $removeProductQuery = "DELETE FROM product_table WHERE product_id = ?";
    $stmt = $conn->prepare($removeProductQuery);

    // Ensure the statement was prepared successfully
    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
    } else {
        // Bind the productId parameter to prevent SQL injection
        $stmt->bind_param('i', $productId);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to avoid resubmission on refresh
            header("Location: seller_products.php");
            exit();
        } else {
            echo "Error removing product: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }
}

// Handle product search
$searchTerm = '';
if (isset($_POST['search'])) {
    $searchTerm = $_POST['search_term'];
}

// Fetch products for the seller with optional search
$productsQuery = "SELECT * FROM product_table WHERE seller_id = '$seller_username'";
if (!empty($searchTerm)) {
    $searchTerm = $conn->real_escape_string($searchTerm); // Prevent SQL injection
    $productsQuery .= " AND name LIKE '%$searchTerm%'";
}

$productsResult = $conn->query($productsQuery);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fresh Cart - Seller Products</title>
    <link rel="stylesheet" href="/fresh_cart/css/seller_products.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <a href="seller_home.php">
                    <img src="/fresh_cart/images/logo-no-background.png" width="200px" height="auto" alt="Fresh Cart Logo">
                </a>
            </div>
            <div class="menu">
                <nav>
                    <ul>
                        <li><a href="seller_home.php">Dashboard</a></li>
                        <li><a href="seller_logout.php" class="login-btn">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Section for Add Product Button -->
        <section class="add-product-section" >
            <a href="add_product.php" class="add_btn">Add New Product</a>
        </section>

        <!-- Section for Search -->
        <section class="search-section">
            <h3>Search Products</h3>
            <form method="POST">
                <input type="text" name="search_term" placeholder="Search by product name..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit" name="search">Search</button>
            </form>
        </section>

        <!-- Section to List Seller's Products -->
        <section class="product-grid">
            <h3>Your Products</h3>
            <main class="grid">
                <?php if ($productsResult->num_rows > 0): ?>
                    <?php while ($product = $productsResult->fetch_assoc()): ?>
                        <article>
                            <img src="/Fresh_Cart/uploads/<?php echo htmlspecialchars($product['image_path']); ?>" alt="Product Image" class="product-image">
                            <div class="text">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p>Price: $<?php echo htmlspecialchars($product['price']); ?></p>
                                <p>Stock: <?php echo htmlspecialchars($product['stock_quantity']); ?></p>
                            
                                <!-- Edit Button -->
                                <form action="edit_products.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                                    <button type="submit" class="edit-button">Edit</button>
                                </form>

                                <!-- Remove Form -->
                                <form method="POST" style="margin-top: 10px;">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                                    <button type="submit" class="remove-button" name="remove_product" >Remove</button>
                                </form>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No products found.</p>
                <?php endif; ?>
            </main>
        </section>
    </div>
</body>
</html>
