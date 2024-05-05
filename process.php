<?php
session_start();

$host = 'localhost';  
$dbname = 'SchoolDB';
$username = 'root';  
$password = '';  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}

$users = [
    "teacher" => ["password" => "teacher123", "role" => "teacher"],
    "student" => ["password" => "student123", "role" => "student"]
];

function verifyUser($username, $password) {
    global $users;
    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        return $users[$username]['role'];
    }
    return false;
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'];

if (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
    switch ($action) {
        case 'add':
            $stmt = $GLOBALS['pdo']->prepare("INSERT INTO marks (student_id, mark) VALUES (?, ?)");
            $stmt->execute([$data['studentId'], $data['marks']]);
            echo "Added marks successfully.";
            break;
        case 'edit':
            $stmt = $GLOBALS['pdo']->prepare("UPDATE marks SET mark = ? WHERE student_id = ?");
            $stmt->execute([$data['marks'], $data['studentId']]);
            echo "Updated marks successfully.";
            break;
        case 'delete':
            $stmt = $GLOBALS['pdo']->prepare("DELETE FROM marks WHERE student_id = ?");
            $stmt->execute([$data['studentId']]);
            echo "Deleted marks successfully.";
            break;
    }
} elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
    switch ($action) {
        case 'fetchMarks':
            $stmt = $GLOBALS['pdo']->prepare("SELECT mark FROM marks WHERE student_id = ?");
            $stmt->execute([$_SESSION['username']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['marks' => $result ? $result['mark'] : null]);
            break;
    }
}

switch ($action) {
    case 'login':
        $username = $data['username'];
        $password = $data['password'];
        $role = verifyUser($username, $password);
        if ($role) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            echo "Login successful as " . $role;
            if ($role == 'teacher') {
                echo "|teacher";
            } else if ($role == 'student') {
                echo "|student";
            }
        } else {
            echo "Invalid username or password";
        }
        break;
    case 'logout':
        session_unset();
        session_destroy();
        echo "Logged out successfully";
        break;
}
