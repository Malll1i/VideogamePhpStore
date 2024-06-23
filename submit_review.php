<?php
session_start();

$servername = "localhost";
$dbname = "videogames";
$username = "root";
$password = ""; 

// Создание подключения к базе данных
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_id = $_POST['game_id'];
    $username = $_SESSION['username'];
    $review_text = $_POST['review_text'];

    $sql = "INSERT INTO reviews (game_id, username, review_text) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $game_id, $username, $review_text);

    if ($stmt->execute()) {
        echo "Review submitted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
