<?php
session_start();

// Only logged-in manager accounts can access this page.
if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'manager') {
    header('Location: login.php');
    exit;
}

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'bookhaven';

$users = [];
$books = [];
$dbError = '';
$bookError = '';
$customerError = '';
$successMessage = isset($_SESSION['book_success']) ? (string) $_SESSION['book_success'] : '';
$customerSuccessMessage = isset($_SESSION['customer_success']) ? (string) $_SESSION['customer_success'] : '';
unset($_SESSION['book_success'], $_SESSION['customer_success']);

if (empty($_SESSION['book_csrf_token'])) {
    $_SESSION['book_csrf_token'] = bin2hex(random_bytes(32));
}

if (empty($_SESSION['customer_csrf_token'])) {
    $_SESSION['customer_csrf_token'] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    $dbError = 'Database connection failed: ' . $conn->connect_error;
} else {
    $conn->set_charset('utf8mb4');

    // Make sure the simple books table exists.
    $createBooksTable = "CREATE TABLE IF NOT EXISTS books (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(150) NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

    if (!$conn->query($createBooksTable)) {
        $bookError = 'Could not prepare the books table: ' . $conn->error;
    }

    // Add a new book to the books table.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_book') {
        $submittedToken = (string) ($_POST['csrf_token'] ?? '');
        $bookName = trim((string) ($_POST['book_name'] ?? ''));

        if (!hash_equals((string) $_SESSION['book_csrf_token'], $submittedToken)) {
            $bookError = 'Invalid form request. Please refresh the page and try again.';
        } elseif ($bookName === '') {
            $bookError = 'Please enter a book name.';
        } elseif (strlen($bookName) > 150) {
            $bookError = 'Book name must be 150 characters or less.';
        } else {
            $stmt = $conn->prepare('INSERT INTO books (name) VALUES (?)');

            if ($stmt) {
                $stmt->bind_param('s', $bookName);

                if ($stmt->execute()) {
                    $_SESSION['book_success'] = 'Book added successfully.';
                    $_SESSION['book_csrf_token'] = bin2hex(random_bytes(32));
                    $stmt->close();
                    header('Location: manager.php');
                    exit;
                }

                $bookError = 'Could not add the book: ' . $stmt->error;
                $stmt->close();
            } else {
                $bookError = 'Could not prepare the add-book request: ' . $conn->error;
            }
        }
    }

    // Delete a book from the books table.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_book') {
        $submittedToken = (string) ($_POST['csrf_token'] ?? '');
        $bookId = filter_var($_POST['book_id'] ?? null, FILTER_VALIDATE_INT);

        if (!hash_equals((string) $_SESSION['book_csrf_token'], $submittedToken)) {
            $bookError = 'Invalid form request. Please refresh the page and try again.';
        } elseif ($bookId === false || $bookId === null || $bookId < 1) {
            $bookError = 'Invalid book ID.';
        } else {
            $stmt = $conn->prepare('DELETE FROM books WHERE id = ?');

            if ($stmt) {
                $stmt->bind_param('i', $bookId);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $_SESSION['book_success'] = 'Book deleted successfully.';
                    } else {
                        $_SESSION['book_success'] = 'The book was already removed.';
                    }

                    $_SESSION['book_csrf_token'] = bin2hex(random_bytes(32));
                    $stmt->close();
                    header('Location: manager.php');
                    exit;
                }

                $bookError = 'Could not delete the book: ' . $stmt->error;
                $stmt->close();
            } else {
                $bookError = 'Could not prepare the delete-book request: ' . $conn->error;
            }
        }
    }

    // Delete a customer from the users table.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_customer') {
        $submittedToken = (string) ($_POST['csrf_token'] ?? '');
        $customerId = filter_var($_POST['customer_id'] ?? null, FILTER_VALIDATE_INT);

        if (!hash_equals((string) $_SESSION['customer_csrf_token'], $submittedToken)) {
            $customerError = 'Invalid form request. Please refresh the page and try again.';
        } elseif ($customerId === false || $customerId === null || $customerId < 1) {
            $customerError = 'Invalid customer ID.';
        } else {
            $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');

            if ($stmt) {
                $stmt->bind_param('i', $customerId);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $_SESSION['customer_success'] = 'Customer removed successfully.';
                    } else {
                        $_SESSION['customer_success'] = 'The customer was already removed.';
                    }

                    $_SESSION['customer_csrf_token'] = bin2hex(random_bytes(32));
                    $stmt->close();
                    header('Location: manager.php');
                    exit;
                }

                $customerError = 'Could not remove the customer: ' . $stmt->error;
                $stmt->close();
            } else {
                $customerError = 'Could not prepare the delete-customer request: ' . $conn->error;
            }
        }
    }

    // Load registered customers.
    $result = $conn->query('SELECT id, full_name, email, created_at FROM users ORDER BY id DESC');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $result->free();
    } else {
        $dbError = 'Could not load users: ' . $conn->error;
    }

    // Load books from the books table.
    $bookResult = $conn->query('SELECT id, name FROM books ORDER BY id DESC');
    if ($bookResult) {
        while ($row = $bookResult->fetch_assoc()) {
            $books[] = $row;
        }
        $bookResult->free();
    } elseif ($bookError === '') {
        $bookError = 'Could not load books: ' . $conn->error;
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookhaven Manager Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f6f1e6;
            color: #2b2420;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* ================= HEADER ================= */
        .site-header {
            min-height: 72px;
            padding: 0 6%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            background: #fffdf8;
            border-bottom: 1px solid #e3d9c4;
            position: sticky;
            top: 0;
            z-index: 20;
            box-shadow: 0 2px 10px rgba(92, 26, 30, 0.06);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #5c1a1e;
            font-size: 24px;
            font-weight: 700;
        }

        .logo-icon {
            color: #b0873f;
            font-size: 18px;
        }

        .site-nav {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .site-nav a {
            color: #5c5248;
            font-size: 14px;
            font-weight: 600;
            padding: 26px 0 23px;
            border-bottom: 3px solid transparent;
            transition: 0.2s;
        }

        .site-nav a:hover,
        .site-nav a.active {
            color: #5c1a1e;
            border-bottom-color: #b0873f;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .manager-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 13px;
            border: 1px solid #e3d9c4;
            border-radius: 999px;
            color: #5c1a1e;
            background: #fdf8ee;
            font-size: 13px;
            font-weight: 700;
        }

        .manager-chip .material-icons {
            font-size: 18px;
            color: #b0873f;
        }

        /* ================= HERO ================= */
        .dashboard-hero {
            background: #5c1a1e;
            color: #fffdf8;
            padding: 58px 6%;
            position: relative;
            overflow: hidden;
        }

        .dashboard-hero::after {
            content: "◆";
            position: absolute;
            right: 7%;
            top: 50%;
            transform: translateY(-50%);
            font-size: 150px;
            color: rgba(176, 135, 63, 0.12);
        }

        .hero-content {
            width: min(1180px, 100%);
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero-eyebrow {
            color: #d9bc82;
            font-size: 12px;
            letter-spacing: 2px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .dashboard-hero h1 {
            font-size: clamp(30px, 5vw, 46px);
            margin-bottom: 12px;
        }

        .dashboard-hero p {
            max-width: 650px;
            color: #eadfd2;
            line-height: 1.7;
            font-size: 15px;
        }

        /* ================= MAIN ================= */
        .page {
            width: min(1180px, 92%);
            margin: 34px auto 55px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 26px;
        }

        .stat-card {
            background: #fffdf8;
            border: 1px solid #e3d9c4;
            border-radius: 10px;
            padding: 22px;
            box-shadow: 0 5px 18px rgba(92, 26, 30, 0.06);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #b0873f;
        }

        .stat-title {
            color: #5c5248;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-number {
            color: #5c1a1e;
            font-size: 32px;
            font-weight: 800;
        }

        .panel {
            background: #fffdf8;
            border: 1px solid #e3d9c4;
            border-radius: 10px;
            box-shadow: 0 5px 18px rgba(92, 26, 30, 0.05);
            margin-bottom: 28px;
            overflow: hidden;
        }

        .panel-header {
            min-height: 66px;
            padding: 18px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #fbf6ec;
            border-bottom: 1px solid #eadfca;
        }

        .panel-header h2 {
            color: #5c1a1e;
            font-size: 20px;
        }

        .badge {
            background: #efe2c9;
            color: #7a5726;
            border: 1px solid #ddc494;
            padding: 6px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        /* ================= FORM ================= */
        .add-book-form {
            padding: 24px 22px;
            display: flex;
            gap: 12px;
            align-items: end;
            flex-wrap: wrap;
        }

        .form-field {
            flex: 1 1 320px;
        }

        .form-field label {
            display: block;
            margin-bottom: 7px;
            color: #2b2420;
            font-size: 13px;
            font-weight: 700;
        }

        .form-field input {
            width: 100%;
            height: 46px;
            padding: 0 13px;
            border: 1px solid #e3d9c4;
            border-radius: 5px;
            outline: none;
            background: #fff;
            color: #2b2420;
            font-size: 14px;
            transition: 0.2s;
        }

        .form-field input:focus {
            border-color: #b0873f;
            box-shadow: 0 0 0 3px rgba(176, 135, 63, 0.13);
        }

        .add-btn {
            height: 46px;
            padding: 0 22px;
            border: 0;
            border-radius: 5px;
            background: #b0873f;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }

        .add-btn:hover {
            background: #8f6a2c;
        }

        /* ================= MESSAGES ================= */
        .error-box,
        .success-box {
            margin: 20px 22px 0;
            padding: 12px 14px;
            border-radius: 5px;
            font-size: 13px;
            line-height: 1.5;
        }

        .error-box {
            color: #842029;
            background: #f8d7da;
            border: 1px solid #f5c2c7;
        }

        .success-box {
            color: #5c1a1e;
            background: #f3e8d3;
            border: 1px solid #dec79d;
        }

        .empty {
            padding: 30px;
            text-align: center;
            color: #6b6058;
        }

        /* ================= TABLE ================= */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        th,
        td {
            padding: 15px 18px;
            border-bottom: 1px solid #eee3d2;
            text-align: left;
            font-size: 14px;
            vertical-align: middle;
        }

        th {
            background: #fffaf1;
            color: #5c1a1e;
            font-size: 12px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        td {
            color: #4d443e;
        }

        tbody tr:hover {
            background: #fcf8f0;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .id-pill {
            display: inline-block;
            min-width: 34px;
            padding: 5px 8px;
            text-align: center;
            background: #f1e5d1;
            color: #7b592b;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .delete-form {
            display: inline;
        }

        .delete-btn {
            border: 1px solid #c98c8f;
            border-radius: 5px;
            padding: 8px 12px;
            background: #fff7f6;
            color: #8f3035;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }

        .delete-btn:hover {
            color: #fff;
            background: #8f3035;
            border-color: #8f3035;
        }

        /* ================= FOOTER ================= */
        footer {
            margin-top: 58px;
            background: #5c1a1e;
            color: #fffdf8;
        }

        .footer-top {
            width: min(1180px, 92%);
            margin: 0 auto;
            padding: 46px 0 38px;
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr;
            gap: 40px;
        }

        .footer-brand .logo {
            color: #fffdf8;
            margin-bottom: 13px;
        }

        .footer-brand p {
            color: #d9cbbf;
            font-size: 13px;
            line-height: 1.7;
            max-width: 250px;
        }

        .footer-links h4,
        .footer-social h4 {
            color: #d9bc82;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .footer-links a {
            color: #e8ddd3;
            font-size: 13px;
            margin-bottom: 10px;
            transition: 0.2s;
        }

        .footer-links a:hover {
            color: #d9bc82;
        }

        .social-icons {
            display: flex;
            gap: 14px;
        }

        .social-icons a {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(217, 188, 130, 0.5);
            border-radius: 50%;
            color: #d9bc82;
            transition: 0.2s;
        }

        .social-icons a:hover {
            color: #5c1a1e;
            background: #d9bc82;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.12);
            text-align: center;
            padding: 18px 20px;
            color: #cdbfb4;
            font-size: 12px;
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 900px) {
            .site-nav {
                gap: 17px;
            }

            .footer-top {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 720px) {
            .site-header {
                padding: 0 4%;
            }

            .site-nav {
                display: none;
            }

            .manager-chip span:last-child {
                display: none;
            }

            .dashboard-hero {
                padding: 44px 5%;
            }

            .dashboard-hero::after {
                font-size: 105px;
                right: 2%;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .add-btn {
                width: 100%;
            }

            .footer-top {
                grid-template-columns: 1fr;
                gap: 26px;
            }
        }
    </style>
</head>
<body>

    <header class="site-header">
        <a class="logo" href="index.php">
            <span class="logo-icon">◆</span>
            Bookhaven
        </a>

        <nav class="site-nav">
            <a href="index.php">Home</a>
            <a href="manager.php" class="active">Manager</a>
            <a href="contact.php">Contact Us</a>
        </nav>

        <div class="header-actions">
            <div class="manager-chip">
                <span class="material-icons">admin_panel_settings</span>
                <span>Manager</span>
            </div>
        </div>
    </header>

    <section class="dashboard-hero">
        <div class="hero-content">
            <div class="hero-eyebrow">Bookhaven Management</div>
            <h1>Manager Dashboard</h1>
            <p>Manage customers and books from one place while keeping the Bookhaven catalog up to date.</p>
        </div>
    </section>

    <main class="page">
        <section class="stats">
            <div class="stat-card">
                <div class="stat-title">Registered Customers</div>
                <div class="stat-number"><?php echo count($users); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-title">Books Added</div>
                <div class="stat-number"><?php echo count($books); ?></div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <h2>Add New Book</h2>
                <span class="badge">Books Database</span>
            </div>

            <?php if ($successMessage !== ''): ?>
                <div class="success-box"><?php echo e($successMessage); ?></div>
            <?php endif; ?>

            <?php if ($bookError !== ''): ?>
                <div class="error-box"><?php echo e($bookError); ?></div>
            <?php endif; ?>

            <form class="add-book-form" method="post" action="manager.php">
                <input type="hidden" name="action" value="add_book">
                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['book_csrf_token']); ?>">

                <div class="form-field">
                    <label for="bookName">Book Name</label>
                    <input
                        type="text"
                        id="bookName"
                        name="book_name"
                        maxlength="150"
                        placeholder="Enter book name"
                        required
                    >
                </div>

                <button type="submit" class="add-btn">Add Book</button>
            </form>
        </section>

        <section class="panel">
            <div class="panel-header">
                <h2>Customer List</h2>
                <span class="badge"><?php echo count($users); ?> customers</span>
            </div>

            <?php if ($customerSuccessMessage !== ''): ?>
                <div class="success-box"><?php echo e($customerSuccessMessage); ?></div>
            <?php endif; ?>

            <?php if ($customerError !== ''): ?>
                <div class="error-box"><?php echo e($customerError); ?></div>
            <?php endif; ?>

            <?php if ($dbError !== ''): ?>
                <div class="error-box"><?php echo e($dbError); ?></div>
            <?php elseif (count($users) === 0): ?>
                <div class="empty">No registered customers found.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Registered At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><span class="id-pill"><?php echo e($user['id']); ?></span></td>
                                    <td><?php echo e($user['full_name']); ?></td>
                                    <td><?php echo e($user['email']); ?></td>
                                    <td><?php echo e($user['created_at']); ?></td>
                                    <td>
                                        <form
                                            class="delete-form"
                                            method="post"
                                            action="manager.php"
                                            onsubmit="return confirm('Are you sure you want to remove this customer?');"
                                        >
                                            <input type="hidden" name="action" value="delete_customer">
                                            <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['customer_csrf_token']); ?>">
                                            <input type="hidden" name="customer_id" value="<?php echo e($user['id']); ?>">
                                            <button type="submit" class="delete-btn">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section class="panel">
            <div class="panel-header">
                <h2>Book List</h2>
                <span class="badge"><?php echo count($books); ?> books</span>
            </div>

            <?php if ($bookError !== '' && count($books) === 0): ?>
                <div class="error-box"><?php echo e($bookError); ?></div>
            <?php elseif (count($books) === 0): ?>
                <div class="empty">No books have been added yet.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Book Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                                <tr>
                                    <td><span class="id-pill"><?php echo e($book['id']); ?></span></td>
                                    <td><?php echo e($book['name']); ?></td>
                                    <td>
                                        <form
                                            class="delete-form"
                                            method="post"
                                            action="manager.php"
                                            onsubmit="return confirm('Are you sure you want to delete this book?');"
                                        >
                                            <input type="hidden" name="action" value="delete_book">
                                            <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['book_csrf_token']); ?>">
                                            <input type="hidden" name="book_id" value="<?php echo e($book['id']); ?>">
                                            <button type="submit" class="delete-btn">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="footer-top">
            <div class="footer-brand">
                <div class="logo">
                    <span class="logo-icon">◆</span>
                    Bookhaven
                </div>
                <p>Discover new worlds, one page at a time.</p>
            </div>

            <div class="footer-links">
                <h4>Browse</h4>
                <a href="index.php">Home</a>
                <a href="#">Fiction</a>
                <a href="#">Non-Fiction</a>
                <a href="#">Children's</a>
            </div>

            <div class="footer-links">
                <h4>Help</h4>
                <a href="contact.php">Contact Us</a>
                <a href="#">Membership</a>
                <a href="#">Renewals</a>
                <a href="#">FAQ</a>
            </div>

            <div class="footer-social">
                <h4>Follow Us</h4>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 Bookhaven. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
