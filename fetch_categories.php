<?php
// Database connection
$host = 'localhost';
$db = 'medsave';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch categories from the database
    $stmt = $pdo->prepare("SELECT cat_id, name, image, description FROM categories");
    $stmt->execute();

    // Fetch all categories as an associative array
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the categories as a JSON response
    echo json_encode($categories);
} catch (PDOException $e) {
    // Handle database errors
    echo json_encode(['error' => $e->getMessage()]);
}
?>
