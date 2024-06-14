<?php
session_start();

// Проверка авторизации администратора
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: login.html");
    exit();
}

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

// Обработка отправки формы для добавления игры
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_title = $_POST['title'];
    $game_description = $_POST['description'];
    $game_requirements = $_POST['requirements'];
    $game_genre = $_POST['genre'];
    $game_release_date = $_POST['release_date'];

    $sql = "INSERT INTO games (title, description, requirements, genre, release_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $game_title, $game_description, $game_requirements, $game_genre, $game_release_date);

    if ($stmt->execute()) {
        echo "Game added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
}

// Получение данных о предзаказах
$sql_preorders = "SELECT username, phone FROM preorders";
$result_preorders = $conn->query($sql_preorders);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .game, .preorder {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            background-color: #fff;
        }

        .game h3, .preorder h3 {
            margin-top: 0;
        }

        .game p, .preorder p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Admin Panel</h2>
            <form action="admin.php" method="POST">
                <div class="input-group">
                    <label for="title">Название игры</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="input-group">
                    <label for="description">Описание</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="input-group">
                    <label for="requirements">Системные требования</label>
                    <textarea id="requirements" name="requirements" required></textarea>
                </div>
                <div class="input-group">
                    <label for="genre">Жанр</label>
                    <input type="text" id="genre" name="genre" required>
                </div>
                <div class="input-group">
                    <label for="release_date">Дата выхода</label>
                    <input type="date" id="release_date" name="release_date" required>
                </div>
                <button type="submit">Добавить игру</button>
            </form>

            <h2>предзаказы</h2>
            <?php
            if ($result_preorders->num_rows > 0) {
                while($row = $result_preorders->fetch_assoc()) {
                    echo "<div class='preorder'>";
                    echo "<h3>Пользователь: " . $row['username'] . "</h3>";
                    echo "<p><strong>Номер телефона:</strong> " . $row['phone'] . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Предзаказов нет.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
