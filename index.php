<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = 'localhost';
$db   = 'todo_app';
$user = 'root';
$pass = 'password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$error_message = '';
$success_message = '';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    if (isset($_POST['task']) && !empty($_POST['task'])) {
        $task = $_POST['task'];
        try {
            $stmt = $pdo->prepare("INSERT INTO tasks (task) VALUES (?)");
            $result = $stmt->execute([$task]);
            if ($result) {
                $taskId = $pdo->lastInsertId();
                echo json_encode(['success' => true, 'id' => $taskId, 'task' => $task]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add task.']);
            }
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error adding task: ' . $e->getMessage()]);
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['delete'];
        try {
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
            $result = $stmt->execute([$id]);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete task.']);
            }
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error deleting task: ' . $e->getMessage()]);
        }
    }
    exit;
}

// Fetch tasks
try {
    $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
    $tasks = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_message = "Error fetching tasks: " . $e->getMessage();
    $tasks = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TODO List App</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>TODO List</h1>
        <div id="message"></div>
        <form id="addTaskForm">
            <input type="text" name="task" placeholder="Enter a new task" required>
            <button type="submit">Add</button>
        </form>
        <ul id="taskList">
            <?php foreach ($tasks as $task): ?>
                <li>
                    <span class="task-text"><?php echo htmlspecialchars($task['task']); ?></span>
                    <button class="delete-btn" data-id="<?php echo $task['id']; ?>">Delete</button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script src="script.js"></script>
</body>
</html>