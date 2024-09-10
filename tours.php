<?php
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "my database"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming you have a database connection established

function processForm() {
    // Retrieve form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $datetime = $_POST['datetime'];
    $destination = $_POST['select1'];
    $numberOfPeople = $_POST['SelectPerson'];
    $category = $_POST['CategoriesSelect'];
    $specialRequest = $_POST['message'];

    // Validate data (add more validation as needed)
    if (empty($name) || empty($email) || empty($datetime) || empty($destination) || empty($numberOfPeople) || empty($category)) {
        // Handle validation error (e.g., display an error message)
        echo "Please fill in all required fields.";
        return;
    }

    // Insert data into the database
    $sql = "INSERT INTO tous (name, email, datetime, destination, numberOfPeople, category, specialRequest) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $name, $email, $datetime, $destination, $numberOfPeople, $category, $specialRequest);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Handle successful submission (e.g., display a confirmation message)
        echo "Booking successful!";
    } else {
        // Handle database error (e.g., display an error message)
        echo "Error inserting data.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    processForm();
}

?>