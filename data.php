<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

$join_type = $_GET['join'] ?? 'left';

switch ($join_type) {
    case 'right':
        // Right Join: Show all tattoo data, even if the artist is unknown
        $sql = "SELECT tattoos.id AS Tattoo_ID, tattoos.name AS Tattoo, tattoos.description, tattoos.image_url, tattoos.likes, artists.name AS Artist, artists.country AS Country 
                FROM tattoos 
                RIGHT JOIN artists ON tattoos.artist_id = artists.id";
        break;

    case 'union':
        // Union Join: Show users and tattoos they liked, with null for unliked tattoos
        $sql = "SELECT users.username AS User, tattoos.name AS Tattoo
                FROM users
                LEFT JOIN likes ON users.id = likes.user_id
                LEFT JOIN tattoos ON likes.tattoo_id = tattoos.id
                UNION
                SELECT users.username AS User, tattoos.name AS Tattoo
                FROM users
                RIGHT JOIN likes ON users.id = likes.user_id
                RIGHT JOIN tattoos ON likes.tattoo_id = tattoos.id";
        break;

    default:
        // Left Join: Show all users who logged in and the tattoos they liked
        $sql = "SELECT users.id AS User_ID, users.username AS Username, tattoos.name AS Tattoo
                FROM users
                LEFT JOIN likes ON users.id = likes.user_id
                LEFT JOIN tattoos ON likes.tattoo_id = tattoos.id";
        break;
}

$result = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #000;
            color: #fff;
        }
        .navbar {
            background-color: #333;
        }
        .navbar a {
            color: #fff !important;
        }
        .container-buttons {
            margin: 20px;
            text-align: center;
        }
        .btn {
            margin: 5px;
        }
        .table-container {
            background-color: #fff;
            color: #000;
            border-radius: 8px;
            padding: 15px;
        }
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

    <div class="container-buttons">
        <a href="data.php?join=left" class="btn btn-light">Logged-in Users with Liked Tattoos</a>
        <a href="data.php?join=right" class="btn btn-light">All Tattoos with Artists</a>
        <a href="data.php?join=union" class="btn btn-light">Users and Liked Tattoos (Including Unliked)</a>
    </div>

    <div class="container mt-4 table-container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <?php if ($join_type === 'left'): ?>
                        <th>User ID</th>
                        <th>Username</th>
                       
                    <?php elseif ($join_type === 'right'): ?>
                        <th>Tattoo ID</th>
                        <th>Tattoo</th>
                        <th>Description</th>
                        <th>Image URL</th>
                        <th>Likes</th>
                        <th>Artist</th>
                        <th>Country</th>
                    <?php else: ?>
                        <th>User</th>
                        <th>user like Tattoo</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch()): ?>
                    <tr>
                        <?php if ($join_type === 'left'): ?>
                            <td><?php echo htmlspecialchars($row['User_ID']); ?></td>
                            <td><?php echo htmlspecialchars($row['Username']); ?></td>
                            
                        <?php elseif ($join_type === 'right'): ?>
                            <td><?php echo htmlspecialchars($row['Tattoo_ID']); ?></td>
                            <td><?php echo htmlspecialchars($row['Tattoo']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['image_url']); ?></td>
                            <td><?php echo htmlspecialchars($row['likes']); ?></td>
                            <td><?php echo htmlspecialchars($row['Artist']); ?></td>
                            <td><?php echo htmlspecialchars($row['Country']); ?></td>
                        <?php else: ?>
                            <td><?php echo htmlspecialchars($row['User']); ?></td>
                            <td><?php echo htmlspecialchars($row['Tattoo'] ?? 'None'); ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
