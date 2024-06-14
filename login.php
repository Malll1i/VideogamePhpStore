<?php
session_start();

// Настройки подключения к базе данных
$servername = "localhost";
$dbname = "videogames";
$username = "root";
$password = ""; // Укажите пароль к вашей базе данных

// Создание подключения к базе данных
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получение данных из формы
$form_username = $_POST['username'];
$form_password = $_POST['password'];

// Проверка учетных данных администратора
if ($form_username === 'admin' && $form_password === '123') {
    $_SESSION['username'] = $form_username;
    header("Location: admin.php");
    exit();
}

// Создание запроса
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $form_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Пользователь найден
    $row = $result->fetch_assoc();
    
    // Проверка пароля
    if (password_verify($form_password, $row['password'])) {
        // Успешный вход
        $_SESSION['username'] = $form_username;
        header("Location: games.php");
        exit();
    } else {
        // Неверный пароль
        echo "Invalid password";
    }
} else {
    // Пользователь не найден
    echo "No user found with that username";
}

$stmt->close();
$conn->close();
?>
