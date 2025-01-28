<?php
// Database connection details
$host = 'localhost';
$db = 'medsave';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get user_id from the request
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

    // Fetch user details
    if ($user_id > 0) {
        $stmt = $pdo->prepare("SELECT user_id, firstName, lastName, email, contact, address FROM user WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode([
                "success" => true,
                "data" => $user
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "User not found."
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid user ID."
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>
