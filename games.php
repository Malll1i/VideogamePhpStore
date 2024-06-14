<?php
session_start();

// Проверка авторизации пользователя
if (!isset($_SESSION['username']) || $_SESSION['username'] === 'admin') {
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

// Получение данных об играх
$sql = "SELECT id, title, description, requirements, genre, release_date FROM games";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .game {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            background-color: #fff;
        }

        .game h3 {
            margin-top: 0;
        }

        .game p {
            margin: 5px 0;
        }

        .game button {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .game button:hover {
            background-color: #45a049;
        }

        /* Стили для модального окна */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .modal button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .modal button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Available Games</h2>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='game'>";
                    echo "<h3>" . $row['title'] . "</h3>";
                    echo "<p><strong>Description:</strong> " . $row['description'] . "</p>";
                    echo "<p><strong>Requirements:</strong> " . $row['requirements'] . "</p>";
                    echo "<p><strong>Genre:</strong> " . $row['genre'] . "</p>";
                    echo "<p><strong>Release Date:</strong> " . $row['release_date'] . "</p>";
                    echo "<button onclick='openModal(" . $row['id'] . ")'>Предзаказ</button>";
                    echo "</div>";
                }
            } else {
                echo "<p>No games available.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Модальное окно -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Предзаказ</h2>
            <form id="preorderForm">
                <input type="hidden" id="gameId" name="game_id">
                <div class="input-group">
                    <label for="phone">Номер телефона</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                <button type="submit">Отправить</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(gameId) {
            document.getElementById('gameId').value = gameId;
            document.getElementById('modal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        document.getElementById('preorderForm').addEventListener('submit', function(event) {
            event.preventDefault();
            var gameId = document.getElementById('gameId').value;
            var phone = document.getElementById('phone').value;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "preorder.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert("Спасибо за отклик, менеджер свяжется с вами в ближайшее время.");
                    closeModal();
                }
            };

            xhr.send("game_id=" + gameId + "&phone=" + phone);
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
