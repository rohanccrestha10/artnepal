<?php
// Start session
session_start();
require_once '../config/database.php';

if (!is_admin_logged_in()) {
    redirect('login.php');
}

$users = [];
$stmt = prepared_query("
    SELECT user_id, full_name, email, phone_number, address, created_at 
    FROM users 
    ORDER BY created_at DESC
");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - ARTNEPAL Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
        .admin-sidebar { background: linear-gradient(135deg, #2F4F4F, #1C3A3A); color: white; padding: 2rem 1rem; }
        .admin-logo { font-size: 1.5rem; font-weight: bold; color: #D4AF37; margin-bottom: 2rem; text-align: center; }
        .admin-menu { list-style: none; }
        .admin-menu li { margin-bottom: 0.5rem; }
        .admin-menu a { display: block; padding: 0.75rem 1rem; color: white; text-decoration: none; border-radius: 5px; transition: background-color 0.3s ease; }
        .admin-menu a:hover, .admin-menu a.active { background-color: #D4AF37; color: #2F4F4F; }
        .admin-main { background: #F8F9FA; padding: 2rem; }
        .admin-header { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .admin-content { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .data-table th, .data-table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #DEB887; }
        .data-table th { background-color: #2F4F4F; color: white; font-weight: bold; }
        .data-table tr:hover { background-color: #F8F9FA; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-logo">ARTNEPAL ADMIN</div>
            <ul class="admin-menu">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="products.php">🛍️ Products</a></li>
                <li><a href="categories.php">📁 Categories</a></li>
                <li><a href="orders.php">📦 Orders</a></li>
                <li><a href="users.php" class="active">👥 Users</a></li>
                <li><a href="messages.php">💬 Messages</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </aside>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1 style="color: #2F4F4F; margin: 0;">Users Management</h1>
                <p style="color: #666; margin: 0.5rem 0 0 0;">View registered customers</p>
            </div>
            
            <div class="admin-content">
                <h2 style="color: #2F4F4F; margin-bottom: 1.5rem;">Registered Users</h2>
                
                <?php if (empty($users)): ?>
                    <p style="color: #666; text-align: center; padding: 2rem;">No users found.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($user['address'], 0, 30)) . (strlen($user['address']) > 30 ? '...' : ''); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
