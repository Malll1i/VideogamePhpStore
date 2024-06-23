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

// Обработка фильтров
$genre_filter = isset($_GET['genre']) ? $_GET['genre'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

$sql = "SELECT id, title, description, requirements, genre, release_date FROM games WHERE 1=1";

if (!empty($genre_filter)) {
    $sql .= " AND genre = ?";
}

if (!empty($date_filter)) {
    $sql .= " AND release_date >= ?";
}

$stmt = $conn->prepare($sql);

if (!empty($genre_filter) && !empty($date_filter)) {
    $stmt->bind_param("ss", $genre_filter, $date_filter);
} elseif (!empty($genre_filter)) {
    $stmt->bind_param("s", $genre_filter);
} elseif (!empty($date_filter)) {
    $stmt->bind_param("s", $date_filter);
}

$stmt->execute();
$result = $stmt->get_result();

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

        .filter-form {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .filter-form label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .filter-form select, .filter-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .filter-form button {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .filter-form button:hover {
            background-color: #0056b3;
        }

        .review-form {
            margin-top: 20px;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .review-form label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .review-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .review-form button {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .review-form button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Available Games</h2>
            <form class="filter-form" method="GET" action="games.php">
                <label for="genre">Filter by Genre:</label>
                <select id="genre" name="genre">
                    <option value="">All Genres</option>
                    <option value="Action" <?php if($genre_filter == 'Action') echo 'selected'; ?>>Action</option>
                    <option value="Adventure" <?php if($genre_filter == 'Adventure') echo 'selected'; ?>>Adventure</option>
                    <option value="RPG" <?php if($genre_filter == 'RPG') echo 'selected'; ?>>RPG</option>
                    <option value="Strategy" <?php if($genre_filter == 'Strategy') echo 'selected'; ?>>Strategy</option>
                    <!-- Добавьте другие жанры по мере необходимости -->
                </select>
                
                <label for="date">Filter by Release Date:</label>
                <input type="date" id="date" name="date" value="<?php echo $date_filter; ?>">

                <button type="submit">Apply Filters</button>
            </form>

            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='game'>";
                    echo "<h3>" . $row['title'] . "</h3>";
                    echo "<p><strong>Description:</strong> " . $row['description'] . "</p>";
                    echo "<p><strong>Requirements:</strong> " . $row['requirements'] . "</p>";
                    echo "<p><strong>Genre:</strong> " . $row['genre'] . "</p>";
                    echo "<p><strong>Release Date:</strong> " . $row['release_date'] . "</p>";
                    echo "<button onclick='openModal(" . $row['id'] . ")'>Pre-order</button>";
                    
                    // Отзывы
                    $game_id = $row['id'];
                    $sql_reviews = "SELECT username, review_text, created_at FROM reviews WHERE game_id = ?";
                    $stmt_reviews = $conn->prepare($sql_reviews);
                    $stmt_reviews->bind_param("i", $game_id);
                    $stmt_reviews->execute();
                    $result_reviews = $stmt_reviews->get_result();
                    
                    echo "<div class='reviews'>";
                    echo "<h4>Reviews:</h4>";
                    if ($result_reviews->num_rows > 0) {
                        while($review = $result_reviews->fetch_assoc()) {
                            echo "<div class='review'>";
                            echo "<p><strong>" . $review['username'] . "</strong> (" . $review['created_at'] . "): " . $review['review_text'] . "</p>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No reviews yet.</p>";
                    }
                    echo "</div>";

                    echo "<div class='review-form'>";
                    echo "<h4>Leave a Review:</h4>";
                    echo "<form action='submit_review.php' method='POST'>";
                    echo "<input type='hidden' name='game_id' value='" . $row['id'] . "'>";
                    echo "<textarea name='review_text' rows='4' required></textarea>";
                    echo "<button type='submit'>Submit Review</button>";
                    echo "</form>";
                    echo "</div>";

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
            <h2>Pre-order</h2>
            <form id="preorderForm">
                <input type="hidden" id="gameId" name="game_id">
                <div class="input-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                <button type="submit">Submit</button>
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

            const formData = new FormData(this);

            fetch('preorder.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                closeModal();
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
