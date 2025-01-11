<?php
// Load environment variables
$servername = getenv('DB_SERVER') ?: 'localhost';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'dataStore';

// Establish database connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    echo "An error occurred. Please try again later.";
    exit;
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    error_log("Error creating database: " . $conn->error);
    echo "An error occurred. Please try again later.";
    exit;
}

// Connect to the newly created database
$conn->select_db($dbname);

// Create table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS EmailList (
    id INT(6) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL
)";
if ($conn->query($sql) !== TRUE) {
    error_log("Error creating table: " . $conn->error);
    echo "An error occurred. Please try again later.";
    exit;
}

// Returning the check_email response before any other logic
if (isset($_POST['check_email'])) {
    $email = $_POST['email'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "error";
        exit;
    }

    $stmt = $conn->prepare("SELECT email FROM EmailList WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                echo "exists";
            } else {
                echo "not_exists";
            }
        } else {
            echo "error";
        }
        $stmt->close();
    } else {
        echo "error";
    }
    exit; // Avoid executing the rest of the script
}

// Get email and password from POST request and validate them
$email = $_POST['email'] ?? '';
$password_input = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email format.";
    exit;
}

if (strlen($password_input) < 8) {
    echo "Password must be at least 8 characters long.";
    exit;
}

// Check if email exists for login
$stmt = $conn->prepare("SELECT `password` FROM EmailList WHERE email = ?");
if ($stmt) {
    $stmt->bind_param("s", $email);
    if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($stored_password);
            $stmt->fetch();
            if (password_verify($password_input, $stored_password)) {
                echo "Login successful.";
                // Redirect to success page or dashboard
                header("Location: success.html");
                exit;
            } else {
                echo "Incorrect password.";
            }
        } else {
            // Hash the password
            $password_hashed = password_hash($password_input, PASSWORD_BCRYPT);

            // Use a prepared statement to insert both email and hashed password
            $stmt_insert = $conn->prepare("INSERT INTO EmailList (email, `password`) VALUES (?, ?)");
            if ($stmt_insert) {
                $stmt_insert->bind_param("ss", $email, $password_hashed);
                if ($stmt_insert->execute()) {
                    header("Location: success.html");
                    exit;
                } else {
                    if ($conn->errno === 1062) {
                        echo "This email is already subscribed.";
                    } else {
                        error_log("Error inserting record: " . $conn->error);
                        echo "An error occurred. Please try again later.";
                    }
                }
                $stmt_insert->close();
            } else {
                error_log("Error preparing statement: " . $conn->error);
                echo "An error occurred. Please try again later.";
            }
        }
    } else {
        error_log("Error checking email: " . $conn->error);
        echo "An error occurred. Please try again later.";
    }
    $stmt->close();
} else {
    error_log("Error preparing statement: " . $conn->error);
    echo "An error occurred. Please try again later.";
}

$conn->close();
?>
