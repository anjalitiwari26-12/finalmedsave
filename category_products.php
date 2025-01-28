<?php
// Database connection details
$host = 'localhost';
$db = 'medsave';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get category ID from URL
    $cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;

    // Fetch products for the specified category
    if ($cat_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE cat_id = :cat_id");
        $stmt->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $products = [];
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Products</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        header {
            background-color: #007BFF;
            color: white;
            padding: 15px;
            text-align: center;
        }

        main {
            padding: 20px;
        }

        .product-list {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* Display 3 columns in a row */
            gap: 20px;
        }

        .product {
            background-color: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .product img {
            width: 150px; /* Set a fixed width for all images */
            height: 150px; /* Set a fixed height for all images */
            object-fit: cover; /* Ensures the image covers the area without distortion */
            margin-bottom: 15px;
        }


        .product h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .product p {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .price {
            font-weight: bold;
            color: #007BFF;
        }

        button {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Popup styles */
        #buy-now-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
        }

        .popup-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .popup-content button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .popup-content button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function openBuyNowPopup(userId) {
            // Make a request to fetch the seller's details by user_id
            fetch(`fetch_user.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    const userDetails = document.getElementById('user-details');
                    if (data.success) {
                        // If the user data is found, display it in the popup
                        userDetails.innerHTML = `
                            <p><strong>First Name:</strong> ${data.data.firstName}</p>
                            <p><strong>Last Name:</strong> ${data.data.lastName}</p>
                            <p><strong>Email:</strong> ${data.data.email}</p>
                            <p><strong>Contact:</strong> ${data.data.contact}</p>
                            <p><strong>Address:</strong> ${data.data.address}</p>
                        `;
                    } else {
                        // If no user data is found, show an error message
                        userDetails.innerHTML = `<p>Error: ${data.message}</p>`;
                    }
                    // Show the popup
                    document.getElementById('buy-now-popup').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching user details:', error);
                    // In case of an error, display a generic error message
                    document.getElementById('user-details').innerHTML = `<p>Error fetching user details.</p>`;
                    document.getElementById('buy-now-popup').style.display = 'block';
                });
        }

        function closeBuyNowPopup() {
            document.getElementById('buy-now-popup').style.display = 'none';
        }
    </script>
</head>
<body>
    <header>
        <h1>Available Products</h1>
    </header>

    <main>
        <div class="product-list">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product">
                        <?php $productImage = !empty($product['image']) ? htmlspecialchars($product['image']) : 'placeholder.png'; ?>
                        <img src="<?= $productImage ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <h2><?= htmlspecialchars($product['name']) ?></h2>
                        <p><?= htmlspecialchars($product['description']) ?></p>
                        <p class="price">Price: Rs <?= htmlspecialchars($product['price']) ?></p>
                        <button onclick="openBuyNowPopup(<?= htmlspecialchars($product['user_id']) ?>)">Buy Now</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No products found in this category.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Popup for Buy Now -->
    <div id="buy-now-popup">
        <div class="popup-content">
            <h2>Seller Details</h2>
            <div id="user-details"></div>
            <button onclick="closeBuyNowPopup()">Close</button>
        </div>
    </div>
    <style>
  #buy-now-popup {
    display: none; /* Initially hidden */
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000; /* Ensures it appears above other content */
    background-color: rgba(0, 0, 0, 0.5); /* Optional for overlay effect */
    width: 100%; /* Covers the entire viewport */
    height: 100%; /* Covers the entire viewport */
  }

  .popup-content {
    background-color: #fff; /* Popup background */
    padding: 20px; /* Spacing inside the popup */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow */
    width: 300px; /* Width of the popup */
    max-width: 90%; /* Responsive adjustment */
    text-align: center; /* Center text */
    position: absolute; /* Positions it inside #buy-now-popup */
    top: 50%; /* Center vertically */
    left: 50%; /* Center horizontally */
    transform: translate(-50%, -50%); /* Adjust for natural offset */
  }
</style>

</body>
</html>
