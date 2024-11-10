<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

$user_id = $_SESSION["id"]; // Get logged-in user's ID

// Check if a "like" request was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['like_tattoo_id'])) {
    $tattoo_id = $_POST['like_tattoo_id'];

    // Check if the user has already liked this tattoo
    $check_like = $pdo->prepare("SELECT * FROM likes WHERE user_id = :user_id AND tattoo_id = :tattoo_id");
    $check_like->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $check_like->bindParam(":tattoo_id", $tattoo_id, PDO::PARAM_INT);
    $check_like->execute();

    if ($check_like->rowCount() == 0) {
        // If not liked, insert the like
        $pdo->beginTransaction();
        $like_stmt = $pdo->prepare("INSERT INTO likes (user_id, tattoo_id) VALUES (:user_id, :tattoo_id)");
        $like_stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $like_stmt->bindParam(":tattoo_id", $tattoo_id, PDO::PARAM_INT);
        $like_stmt->execute();

        // Update the total likes in the tattoos table
        $update_likes = $pdo->prepare("UPDATE tattoos SET likes = likes + 1 WHERE id = :tattoo_id");
        $update_likes->bindParam(":tattoo_id", $tattoo_id, PDO::PARAM_INT);
        $update_likes->execute();

        $pdo->commit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    body { background-color: #000; color: #fff; }
    .navbar { background-color: #333; }
    .navbar a { color: #fff !important; }
    .container-tattoos { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; padding: 20px; }
    .tattoo-card { width: 200px; background-color: #222; padding: 15px; border-radius: 5px; text-align: center; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); }
    .tattoo-card img { width: 100%; height: 150px; border-radius: 5px; object-fit: cover; }
    .like-btn { background-color: #1976D2; color: #fff; border: none; padding: 5px 10px; border-radius: 5px; }
</style>

</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="#">Tattoo</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="data.php">Data</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container-tattoos">
        <?php
        $sql = "SELECT * FROM tattoos LIMIT 10";
        $result = $pdo->query($sql);

        while ($row = $result->fetch()) {
            $tattoo_id = $row['id'];
            $name = htmlspecialchars($row['name']);
            $description = htmlspecialchars($row['description']);
            $image_url = htmlspecialchars($row['image_url']);
            $likes = $row['likes'];

            // Check if the user has already liked this tattoo
            $user_like_check = $pdo->prepare("SELECT * FROM likes WHERE user_id = :user_id AND tattoo_id = :tattoo_id");
            $user_like_check->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            $user_like_check->bindParam(":tattoo_id", $tattoo_id, PDO::PARAM_INT);
            $user_like_check->execute();

            $liked = $user_like_check->rowCount() > 0;

            echo "<div class='tattoo-card'>
                    <img src='$image_url' alt='$name'>
                    <h5>$name</h5>
                    <p>$description</p>
                    <p>Likes: $likes</p>";
            if (!$liked) {
                echo "<form method='post' action='dashboard.php'>
                        <input type='hidden' name='like_tattoo_id' value='$tattoo_id'>
                        <button type='submit' class='like-btn'>Like</button>
                    </form>";
            } else {
                echo "<p style='color: #4CAF50;'>Liked</p>";
            }
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
