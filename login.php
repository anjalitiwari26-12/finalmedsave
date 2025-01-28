<?php
// Start the session
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// If the user is already logged in, redirect to the dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form fields
    $userId = $_POST['user_id']; // User ID entered by the user
    $password = $_POST['password']; // Password entered by the user

    // Database connection
    $con = new mysqli("localhost", "root", "", "medsave");
    if ($con->connect_error) {
        die("Failed to connect: " . $con->connect_error);
    }

    // Prepare SQL query to fetch the user data based on userId
    $stmt = $con->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt->bind_param("s", $userId); // Bind the userId to the query

    // Execute the query and get the result
    $stmt->execute();
    $stmt_result = $stmt->get_result();

    // Check if the user exists
    if ($stmt_result->num_rows > 0) {
        // Fetch the user data
        $data = $stmt_result->fetch_assoc();

        // Verify the password using password_verify()
        if (password_verify($password, $data['password'])) {
            // Login successful, store session data
            $_SESSION['user_id'] = $data['user_id'];  // Store user_id in session
            $_SESSION['first_name'] = $data['firstName']; // Store first name in session
            $_SESSION['last_name'] = $data['lastName'];   // Store last name in session

            // Redirect to a secure page or dashboard
            header('Location: dashboard.php'); // Redirect to dashboard
            exit();
        } else {
            // Invalid password
            echo "<p>Invalid password. Please try again.</p>";
        }
    } else {
        // User not found
        echo "<p>No user found with the provided user ID. Please check your credentials.</p>";
    }

    // Close the prepared statement and the database connection
    $stmt->close();
    $con->close();
}
?>
