<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$con = new mysqli("localhost", "root", "", "medsave");

// Check if the database connection was successful
if ($con->connect_error) {
    die("Failed to connect: " . $con->connect_error);
}

// Get the logged-in user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch product details from the database for the logged-in user
$stmt = $con->prepare("SELECT * FROM products WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if products are found
if ($result->num_rows > 0) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $products = [];
}

// Handle the delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Prepare SQL to delete the product
    $delete_stmt = $con->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $delete_id, $user_id);
    if ($delete_stmt->execute()) {
        echo "<p class='success-message'>Product deleted successfully.</p>";
    } else {
        echo "<p class='error-message'>Error deleting product. Please try again.</p>";
    }
    $delete_stmt->close();
}

// Close the prepared statement and the database connection
$stmt->close();
$con->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MedSave</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="dashboard-container">
        <header class="header">
            <h1>Welcome, <?php echo $_SESSION['first_name']; ?>!</h1>
            <nav>
                <a href="logout.php" class="logout-btn">Logout</a>
            </nav>
        </header>

        <h2>Your Products</h2>
        <?php if (empty($products)) : ?>
            <p>No products found. Add some products to get started.</p>
        <?php else : ?>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Image</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Usage Duration</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="100"></td>
                            <td><?php echo $product['price'] ? '$' . number_format($product['price'], 2) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                            <td><?php
                                $con = new mysqli("localhost", "root", "", "medsave");
                                $cat_stmt = $con->prepare("SELECT name FROM categories WHERE cat_id = ?");
                                $cat_stmt->bind_param("i", $product['cat_id']);
                                $cat_stmt->execute();
                                $cat_result = $cat_stmt->get_result();
                                $category = $cat_result->fetch_assoc();
                                echo htmlspecialchars($category['name']);
                                $cat_stmt->close();
                                $con->close();
                            ?></td>
                            <td><?php echo htmlspecialchars($product['usage_duration']); ?></td>
                            <td>
                                <a href="?delete_id=<?php echo $product['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <style>
        .logout-btn {
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .logout-btn:hover {
            background-color: #2980b9;
        }

        .logout-btn:active {
            background-color: #3498db;
        }
    </style>
</body>

</html>
