<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

$stmt = $pdo->prepare('SELECT * FROM budgets WHERE user_id = :user_id AND month = :month');
$stmt->execute(['user_id' => $user_id, 'month' => $selected_month]);
$budgets = $stmt->fetchAll();

$category_totals = [];
$total_spent = 0;
foreach ($budgets as $budget) {
    if (!isset($category_totals[$budget['category']])) {
        $category_totals[$budget['category']] = 0;
    }
    $category_totals[$budget['category']] += $budget['amount'];
    $total_spent += $budget['amount'];
}

$max_value = !empty($category_totals) ? max($category_totals) : 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_budget_id'])) {
        $budget_id = $_POST['delete_budget_id'];
        $stmt = $pdo->prepare('DELETE FROM budgets WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $budget_id, 'user_id' => $user_id]);
    } elseif (isset($_POST['set_budget'])) {
        $budget = $_POST['budget'];
        $stmt = $pdo->prepare('DELETE FROM user_budgets WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $user_id]);
        $stmt = $pdo->prepare('INSERT INTO user_budgets (user_id, budget) VALUES (:user_id, :budget)');
        $stmt->execute(['user_id' => $user_id, 'budget' => $budget]);
    } else {
        $amount = $_POST['amount'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $month = $_POST['month'];

        $stmt = $pdo->prepare('INSERT INTO budgets (user_id, amount, description, category, month) VALUES (:user_id, :amount, :description, :category, :month)');
        $stmt->execute(['user_id' => $user_id, 'amount' => $amount, 'description' => $description, 'category' => $category, 'month' => $month]);
    }

    header('Location: dashboard.php?month=' . $selected_month);
    exit;
}

$stmt = $pdo->prepare('SELECT budget FROM user_budgets WHERE user_id = :user_id');
$stmt->execute(['user_id' => $user_id]);
$user_budget = $stmt->fetchColumn();
$remaining_budget = $user_budget - $total_spent;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1>Dashboard</h1>
            </div>
            <nav>
                <ul>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <li><a href="admin.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="container">
        <div class="content">
            <h1>Welcome to Your Dashboard!</h1>
            <form method="GET">
                <label for="month">Select Month:</label>
                <input type="month" id="month" name="month" value="<?= htmlspecialchars($selected_month) ?>">
                <button type="submit">Filter</button>
            </form>
            <div class="form-container">
                <div class="form-box">
                    <h2>Add Transaction</h2>
                    <form method="POST">
                        <div>
                            <label for="amount">Amount</label>
                            <input type="number" step="0.01" name="amount" id="amount" required>
                        </div>
                        <div>
                            <label for="description">Description</label>
                            <input type="text" name="description" id="description" required>
                        </div>
                        <div>
                            <label for="category">Category</label>
                            <select name="category" id="category" required>
                                <option value="">Select a category</option>
                                <option value="Food">Food</option>
                                <option value="Rent">Rent</option>
                                <option value="Entertainment">Entertainment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label for="month">Month</label>
                            <input type="month" name="month" id="month" value="<?= htmlspecialchars($selected_month) ?>" required>
                        </div>
                        <button type="submit">Add Transaction</button>
                    </form>
                </div>
                <div class="form-box">
                    <h2>Set Budget</h2>
                    <form method="POST">
                        <div>
                            <label for="budget">Budget</label>
                            <input type="number" step="0.01" name="budget" id="budget" value="<?= htmlspecialchars($user_budget) ?>" required>
                        </div>
                        <button type="submit" name="set_budget">Set Budget</button>
                    </form>
                </div>
            </div>
            <h2>Your Transactions</h2>
            <ul>
                <?php if (!empty($budgets)): ?>
                    <?php foreach ($budgets as $budget): ?>
                        <li>
                            <?= htmlspecialchars($budget['description']) ?> - €<?= htmlspecialchars($budget['amount']) ?> (<?= htmlspecialchars($budget['category']) ?>)
                            <form method="POST">
                                <input type="hidden" name="delete_budget_id" value="<?= $budget['id'] ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No transactions found.</li>
                <?php endif; ?>
            </ul>
            <h2>Spending by Category</h2>
            <div class="chart">
                <?php if (!empty($category_totals)): ?>
                    <?php foreach ($category_totals as $category => $total): ?>
                        <div class="chart-bar" style="width: <?= ($total / $max_value) * 100 ?>%;">
                            <?= htmlspecialchars($category) ?>: €<?= htmlspecialchars($total) ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div>No spending data available.</div>
                <?php endif; ?>
            </div>
            <h2>Total Money Spent: €<?= htmlspecialchars($total_spent) ?></h2>
            <h2>Remaining Budget: €<?= htmlspecialchars($remaining_budget) ?></h2>
        </div>
    </div>
</body>
</html>