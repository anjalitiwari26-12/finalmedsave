<?php
// Database connection settings
$host = 'localhost';
$db = 'medsave';
$user = 'root';
$pass = '';

try {
    // Establish a connection to the database
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $cat_id = filter_input(INPUT_POST, 'cat_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $usage_duration = filter_input(INPUT_POST, 'usage_duration', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Handle file upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/product/'; // Directory where files will be saved (relative to document root)
        
        // Ensure the upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create the directory with proper permissions
        }

        // Generate a unique name for the file and set the upload path
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $unique_name = uniqid() . '_' . $file_name; // Unique filename to avoid conflicts
        $image_path = $upload_dir . $unique_name; // Relative path to save in the database
        $full_path = __DIR__ . '/' . $image_path; // Full path for moving the file

        // Move the uploaded file to the upload directory
        if (!move_uploaded_file($file_tmp, $full_path)) {
            die("Failed to upload image.");
        }
    }

    // Check if the product already exists (you can customize this check based on your requirements)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name = :name AND user_id = :user_id");
    $stmt->execute([':name' => $name, ':user_id' => $user_id]);
    $product_exists = $stmt->fetchColumn();

    if ($product_exists) {
        die("<h1>This product is already added.</h1>");
    }

    try {
        // Insert data into the product table
        $stmt = $pdo->prepare("
            INSERT INTO products (cat_id, user_id, name, image, description, usage_duration, price)
            VALUES (:cat_id, :user_id, :name, :image, :description, :usage_duration, :price)
        ");

        $stmt->execute([
            ':cat_id' => $cat_id,
            ':user_id' => $user_id,
            ':name' => $name,
            ':image' => $image_path,
            ':description' => $description,
            ':usage_duration' => $usage_duration,
            ':price' => $price,
        ]);

        // Output the alert and redirect using JavaScript
        echo "<script>
                alert('Product added successfully!');
                window.location.href = 'index.html';  // Redirect to dashboard
              </script>";
        
        exit(); // Ensure no further PHP code is executed after the redirect

    } catch (PDOException $e) {
        die("Error saving product: " . $e->getMessage());
    }
} else {
    die("Invalid request method.");
}
?>
