<?php

$servername = "localhost";
$dbname = "videogames";
$username = "root";
$password = ""; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$form_username = $_POST['username'];
$form_password = $_POST['password'];

// Проверка на существующего пользователя
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $form_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    
    echo "Пользователь уже существует";
} else {
    // Хеширование пароля
    $hashed_password = password_hash($form_password, PASSWORD_DEFAULT);

    
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $form_username, $hashed_password);

    if ($stmt->execute()) {
        echo "Успешная регистрация <a href='login.html'>login</a>.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$stmt->close();
$conn->close();
?>
