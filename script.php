<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mode = $_POST['mode'];

    // Database connection
    $con = new mysqli("localhost", "root", "", "medsave");
    if ($con->connect_error) {
        die("Failed to connect: " . $con->connect_error);
    }

    if ($mode === "register") {
        // Collect form fields
        $userId = $_POST['userId'];  // This is the user ID entered by the user
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = $_POST['email'];
        $contact = $_POST['contact'];
        $address = $_POST['address'];
        $password = $_POST['password'];

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user data into the database
        $stmt = $con->prepare("INSERT INTO user (userId, firstName, lastName, email, contact, address, password) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $userId, $firstName, $lastName, $email, $contact, $address, $hashedPassword);

        if ($stmt->execute()) {
            echo "<h1>Registration Successful</h1>";
        } else {
            echo "<h1>Registration Failed: " . $stmt->error . "</h1>";
        }
        $stmt->close();
    } else {
        echo "<h1>Invalid Operation</h1>";
    }

    // Close the database connection
    $con->close();
} else {
    echo "<h1>Unauthorized Access</h1>";
}
?>
