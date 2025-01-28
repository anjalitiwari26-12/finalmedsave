<?php 
// Database connection
$host = 'localhost'; // Database host
$dbname = 'medsave'; // Database name
$username = 'root'; // Database username
$password = ''; // Database password

// Create connection
$con = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Collect form fields from POST request
$userId = trim($_POST['user_id']);
$firstName = trim($_POST['firstName']);
$lastName = trim($_POST['lastName']);
$email = trim($_POST['email']);
$contact = trim($_POST['contact']);
$address = trim($_POST['address']);
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

// Validate inputs
if (empty($userId) || empty($firstName) || empty($email) || empty($contact) || empty($password) || empty($confirmPassword)) {
    die("<h1>All required fields must be filled out.</h1>");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("<h1>Invalid email format.</h1>");
}

if ($password !== $confirmPassword) {
    die("<h1>Passwords do not match.</h1>");
}

if (!preg_match('/^\d{10}$/', $contact)) {
    die("<h1>Contact number must be exactly 10 digits.</h1>");
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
    die("<h1>Password must include uppercase, lowercase, digit, and special character, and be at least 8 characters long.</h1>");
}

// Validate that userId is numeric
if (!ctype_digit($userId)) {
    die("<h1>User ID must be numeric.</h1>");
}

// Check if userId already exists in the database
$stmt = $con->prepare("SELECT user_id FROM user WHERE user_id = ?");
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

// If the userId already exists, show an error message
if ($result->num_rows > 0) {
    die("<h1>User ID is already taken. Please choose another one.</h1>");
}

// Hash the password for security
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare the SQL statement to insert data into the database
$stmt = $con->prepare("INSERT INTO user (user_id, firstName, lastName, email, contact, address, password) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $userId, $firstName, $lastName, $email, $contact, $address, $hashedPassword);

// Execute the query and check for success
if ($stmt->execute()) {
    // Start a session and set session variables
    session_start();
    $_SESSION['user_id'] = $userId;
    $_SESSION['first_name'] = $firstName;

    // Redirect to dashboard.php
    header("Location: dashboard.php");
    exit();
} else {
    echo "<h1>Registration Failed: " . $stmt->error . "</h1>";
}

// Close the prepared statement and the database connection
$stmt->close();
$con->close();
?>
