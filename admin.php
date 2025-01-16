<?php
session_start();
require 'db.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users');
$stmt->execute();
$users = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM budgets');
$stmt->execute();
$budgets = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_user_id'])) {
        $user_id = $_POST['delete_user_id'];
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $user_id]);
    } elseif (isset($_POST['delete_budget_id'])) {
        $budget_id = $_POST['delete_budget_id'];
        $stmt = $pdo->prepare('DELETE FROM budgets WHERE id = :id');
        $stmt->execute(['id' => $budget_id]);
    }

    header('Location: admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Admin Panel</title>
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-12 text-end">
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center">
                <h1>Admin Panel</h1>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <h2>Users</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <h2>Budgets</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>User ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($budgets as $budget): ?>
                            <tr>
                                <td><?= htmlspecialchars($budget['id']) ?></td>
                                <td><?= htmlspecialchars($budget['description']) ?></td>
                                <td>$<?= htmlspecialchars($budget['amount']) ?></td>
                                <td><?= htmlspecialchars($budget['user_id']) ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="delete_budget_id" value="<?= $budget['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>