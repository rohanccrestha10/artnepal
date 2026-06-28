<?php
// Start session
session_start();
require_once '../config/database.php';

if (!is_admin_logged_in()) {
    redirect('login.php');
}

$messages = [];
$stmt = prepared_query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

// Mark message as read if requested
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $message_id = (int)$_GET['mark_read'];
    prepared_query("UPDATE contact_messages SET is_read = 1 WHERE message_id = ?", "i", [$message_id]);
    header('Location: messages.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages Management - ARTNEPAL Admin</title>
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
        .badge { padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.75rem; font-weight: bold; }
        .badge-warning { background-color: #FFF3CD; color: #856404; }
        .badge-success { background-color: #D4EDDA; color: #155724; }
        .message-content { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
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
                <li><a href="users.php">👥 Users</a></li>
                <li><a href="messages.php" class="active">💬 Messages</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </aside>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1 style="color: #2F4F4F; margin: 0;">Messages Management</h1>
                <p style="color: #666; margin: 0.5rem 0 0 0;">View customer contact messages</p>
            </div>
            
            <div class="admin-content">
                <h2 style="color: #2F4F4F; margin-bottom: 1.5rem;">Contact Messages</h2>
                
                <?php if (empty($messages)): ?>
                    <p style="color: #666; text-align: center; padding: 2rem;">No messages found.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td><?php echo $msg['message_id']; ?></td>
                                    <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                    <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                    <td>
                                        <div class="message-content" title="<?php echo htmlspecialchars($msg['message']); ?>">
                                            <?php echo htmlspecialchars($msg['message']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $msg['is_read'] ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $msg['is_read'] ? 'Read' : 'Unread'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                                    <td>
                                        <?php if (!$msg['is_read']): ?>
                                            <a href="?mark_read=<?php echo $msg['message_id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.85rem; text-decoration: none;">
                                                Mark Read
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #666; font-size: 0.85rem;">Read</span>
                                        <?php endif; ?>
                                    </td>
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
