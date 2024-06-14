<?php
session_start();


if (!isset($_SESSION['username']) || $_SESSION['username'] === 'admin') {
    header("Location: login.html");
    exit();
}


$servername = "localhost";
$dbname = "videogames";
$username = "root";
$password = ""; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$user = $_SESSION['username'];
$game_id = $_POST['game_id'];
$phone = $_POST['phone'];

// Вставка данных в таблицу предзаказов
$sql = "INSERT INTO preorders (username, game_id, phone) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sis", $user, $game_id, $phone);

if ($stmt->execute()) {
    echo "предзаказ успешен!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$stmt->close();
$conn->close();
?>
