<?php

session_start();

require_once "managerdb.php";


/* =========================================================
   MESSAGE
========================================================= */

if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = "";
}


/* =========================================================
   ADD BOOK
========================================================= */

if (isset($_POST['add_book'])) {

    $title = trim($_POST['title'] ?? "");
    $author = trim($_POST['author'] ?? "");
    $category = trim($_POST['category'] ?? "");
    $price = floatval($_POST['price'] ?? 0);

    if (
        $title != "" &&
        $author != "" &&
        $category != "" &&
        $price > 0
    ) {

        $stmt = $conn->prepare(
            "INSERT INTO books
            (title, author, category, price, status)
            VALUES (?, ?, ?, ?, 'Available')"
        );

        $stmt->bind_param(
            "sssd",
            $title,
            $author,
            $category,
            $price
        );

        if ($stmt->execute()) {
            $_SESSION['message'] = "Book added successfully.";
        } else {
            $_SESSION['message'] = "Failed to add book.";
        }

        $stmt->close();

    } else {

        $_SESSION['message'] = "Please enter valid book information.";
    }

    header("Location: manager.php");
    exit;
}


/* =========================================================
   UPDATE BOOK
========================================================= */

if (isset($_POST['update_book'])) {

    $id = intval($_POST['book_id'] ?? 0);

    $title = trim($_POST['title'] ?? "");
    $author = trim($_POST['author'] ?? "");
    $category = trim($_POST['category'] ?? "");
    $price = floatval($_POST['price'] ?? 0);

    if (
        $id > 0 &&
        $title != "" &&
        $author != "" &&
        $category != "" &&
        $price > 0
    ) {

        $stmt = $conn->prepare(
            "UPDATE books
             SET title = ?,
                 author = ?,
                 category = ?,
                 price = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "sssdi",
            $title,
            $author,
            $category,
            $price,
            $id
        );

        if ($stmt->execute()) {
            $_SESSION['message'] = "Book updated successfully.";
        } else {
            $_SESSION['message'] = "Failed to update book.";
        }

        $stmt->close();

    } else {

        $_SESSION['message'] = "Invalid book information.";
    }

    header("Location: manager.php");
    exit;
}


/* =========================================================
   DELETE BOOK
========================================================= */

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    if ($id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM books
             WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Book deleted successfully.";
        } else {
            $_SESSION['message'] = "Unable to delete book.";
        }

        $stmt->close();
    }

    header("Location: manager.php");
    exit;
}


/* =========================================================
   APPROVE BORROW REQUEST
========================================================= */

if (isset($_GET['approve'])) {

    $requestID = intval($_GET['approve']);

    if ($requestID > 0) {

        $stmt = $conn->prepare(
            "UPDATE borrow_requests
             SET status = 'Approved'
             WHERE id = ?
             AND status = 'Pending'"
        );

        $stmt->bind_param(
            "i",
            $requestID
        );

        $stmt->execute();

        if ($stmt->affected_rows > 0) {

            $_SESSION['message'] =
                "Borrowing request approved.";

        } else {

            $_SESSION['message'] =
                "Request could not be approved.";
        }

        $stmt->close();
    }

    header("Location: manager.php");
    exit;
}


/* =========================================================
   ISSUE BOOK
========================================================= */

if (isset($_GET['issue'])) {

    $requestID = intval($_GET['issue']);

    if ($requestID <= 0) {

        $_SESSION['message'] =
            "Invalid borrowing request.";

        header("Location: manager.php");
        exit;
    }


    $conn->begin_transaction();

    try {

        /* GET REQUEST */

        $stmt = $conn->prepare(
            "SELECT book_id
             FROM borrow_requests
             WHERE id = ?
             AND status = 'Approved'
             FOR UPDATE"
        );

        $stmt->bind_param(
            "i",
            $requestID
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            throw new Exception(
                "Borrowing request is not approved."
            );
        }

        $request = $result->fetch_assoc();

        $bookID = intval($request['book_id']);

        $stmt->close();


        /* CHECK BOOK */

        $stmt = $conn->prepare(
            "SELECT status
             FROM books
             WHERE id = ?
             FOR UPDATE"
        );

        $stmt->bind_param(
            "i",
            $bookID
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            throw new Exception(
                "Book not found."
            );
        }

        $book = $result->fetch_assoc();

        $stmt->close();


        if ($book['status'] != 'Available') {

            throw new Exception(
                "Book is currently unavailable."
            );
        }


        /* SET BOOK BORROWED */

        $stmt = $conn->prepare(
            "UPDATE books
             SET status = 'Borrowed'
             WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $bookID
        );

        $stmt->execute();

        $stmt->close();


        /* SET REQUEST ISSUED */

        $stmt = $conn->prepare(
            "UPDATE borrow_requests
             SET status = 'Issued'
             WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $requestID
        );

        $stmt->execute();

        $stmt->close();


        $conn->commit();

        $_SESSION['message'] =
            "Book issued successfully.";

    } catch (Exception $e) {

        $conn->rollback();

        $_SESSION['message'] =
            $e->getMessage();
    }

    header("Location: manager.php");
    exit;
}


/* =========================================================
   RETURN BOOK
========================================================= */

if (isset($_GET['return'])) {

    $bookID = intval($_GET['return']);

    if ($bookID <= 0) {

        $_SESSION['message'] =
            "Invalid book.";

        header("Location: manager.php");
        exit;
    }


    $conn->begin_transaction();

    try {

        /* MAKE BOOK AVAILABLE */

        $stmt = $conn->prepare(
            "UPDATE books
             SET status = 'Available'
             WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $bookID
        );

        $stmt->execute();

        $stmt->close();


        /* CHANGE REQUEST TO RETURNED */

        $stmt = $conn->prepare(
            "UPDATE borrow_requests
             SET status = 'Returned'
             WHERE book_id = ?
             AND status = 'Issued'"
        );

        $stmt->bind_param(
            "i",
            $bookID
        );

        $stmt->execute();

        $stmt->close();


        $conn->commit();

        $_SESSION['message'] =
            "Returned book accepted successfully.";

    } catch (Exception $e) {

        $conn->rollback();

        $_SESSION['message'] =
            "Unable to return book.";
    }

    header("Location: manager.php");
    exit;
}


/* =========================================================
   EDIT BOOK
========================================================= */

$editBook = null;

if (isset($_GET['edit'])) {

    $editID = intval($_GET['edit']);

    if ($editID > 0) {

        $stmt = $conn->prepare(
            "SELECT *
             FROM books
             WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $editID
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $editBook =
                $result->fetch_assoc();
        }

        $stmt->close();
    }
}


/* =========================================================
   GET ALL BOOKS
========================================================= */

$books = [];

$result = $conn->query(
    "SELECT *
     FROM books
     ORDER BY id ASC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $books[] = $row;
    }
}


/* =========================================================
   GET BORROW REQUESTS
========================================================= */

$requests = [];

$result = $conn->query(
    "SELECT
        borrow_requests.id,
        borrow_requests.student,
        borrow_requests.book_id,
        borrow_requests.status,
        books.title AS book_title,
        books.price AS book_price,
        books.status AS book_status

     FROM borrow_requests

     INNER JOIN books
     ON borrow_requests.book_id = books.id

     ORDER BY borrow_requests.id DESC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $requests[] = $row;
    }
}


/* =========================================================
   STATISTICS
========================================================= */

$totalBooks = 0;
$availableBooks = 0;
$borrowedBooks = 0;
$pendingRequests = 0;


$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM books"
);

if ($result) {

    $row = $result->fetch_assoc();

    $totalBooks =
        intval($row['total']);
}


$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM books
     WHERE status = 'Available'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $availableBooks =
        intval($row['total']);
}


$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM books
     WHERE status = 'Borrowed'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $borrowedBooks =
        intval($row['total']);
}


$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM borrow_requests
     WHERE status = 'Pending'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $pendingRequests =
        intval($row['total']);
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Library Manager Dashboard
</title>


<style>

/* ==================================
   GENERAL
================================== */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica, sans-serif;
}


body {

    background: #f4ede4;
    color: #3b2418;
}


/* ==================================
   HEADER
================================== */

header {

    background: #5d3a24;
    color: white;

    padding: 22px 35px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow:
        0 3px 8px rgba(0,0,0,0.25);
}


header h1 {

    font-size: 26px;
}


.manager-name {

    background: #8b5e3c;

    padding: 10px 18px;

    border-radius: 25px;
}


/* ==================================
   LAYOUT
================================== */

.container {

    width: 94%;

    max-width: 1400px;

    margin: 30px auto;
}


.dashboard-title {

    margin-bottom: 20px;
}


.dashboard-title h2 {

    color: #5d3a24;

    margin-bottom: 6px;
}


/* ==================================
   STATISTICS
================================== */

.stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;

    margin-bottom: 30px;
}


.stat-card {

    background: white;

    padding: 22px;

    border-left:
        6px solid #7b4b2a;

    border-radius: 10px;

    box-shadow:
        0 3px 10px rgba(0,0,0,.12);
}


.stat-card h3 {

    font-size: 15px;

    color: #80614e;
}


.stat-card p {

    font-size: 27px;

    margin-top: 10px;

    font-weight: bold;

    color: #4c2b1a;
}


/* ==================================
   SECTIONS
================================== */

.section {

    background: white;

    padding: 25px;

    margin-bottom: 30px;

    border-radius: 12px;

    box-shadow:
        0 3px 12px rgba(0,0,0,.12);
}


.section h2 {

    margin-bottom: 20px;

    color: #5d3a24;

    border-bottom:
        2px solid #d8c1ae;

    padding-bottom: 10px;
}


/* ==================================
   FORM
================================== */

.book-form {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;
}


.form-group {

    display: flex;

    flex-direction: column;
}


.form-group label {

    margin-bottom: 6px;

    font-weight: bold;
}


.form-group input {

    padding: 11px;

    border:
        1px solid #b99b84;

    border-radius: 6px;

    outline: none;
}


.form-group input:focus {

    border-color: #5d3a24;
}


.form-button {

    grid-column: 1 / -1;
}


button {

    padding: 11px 20px;

    border: none;

    border-radius: 6px;

    background: #6b4226;

    color: white;

    cursor: pointer;

    font-size: 15px;
}


button:hover {

    background: #4c2b1a;
}


.cancel-button {

    display: inline-block;

    text-decoration: none;

    background: #777;

    color: white;

    padding: 11px 18px;

    border-radius: 6px;

    margin-left: 5px;
}


/* ==================================
   TABLE
================================== */

.table-wrapper {

    overflow-x: auto;
}


table {

    width: 100%;

    border-collapse: collapse;
}


th {

    background: #6b4226;

    color: white;

    padding: 13px;
}


td {

    padding: 12px;

    border-bottom:
        1px solid #e1d3c7;

    text-align: center;
}


tr:hover {

    background: #faf5f1;
}


/* ==================================
   STATUS
================================== */

.status {

    display: inline-block;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: bold;
}


.available {

    background: #d9f0df;

    color: #246636;
}


.borrowed {

    background: #f3d5d5;

    color: #8b2b2b;
}


.pending {

    background: #fff0c5;

    color: #7d6200;
}


.approved {

    background: #d9e8fa;

    color: #225a9b;
}


.issued {

    background: #eadcff;

    color: #65409b;
}


.returned {

    background: #d9f0df;

    color: #246636;
}


/* ==================================
   ACTION BUTTONS
================================== */

.action {

    display: inline-block;

    text-decoration: none;

    padding: 7px 12px;

    border-radius: 5px;

    color: white;

    margin: 2px;

    font-size: 13px;
}


.edit {

    background: #a16b3f;
}


.delete {

    background: #a33a3a;
}


.approve {

    background: #346b4a;
}


.issue {

    background: #515d88;
}


.return {

    background: #8a623d;
}


/* ==================================
   MESSAGE
================================== */

.message {

    background: #e3f3e6;

    color: #2d6839;

    padding: 14px;

    border-left:
        5px solid #3c8b4f;

    margin-bottom: 22px;

    border-radius: 5px;
}


/* ==================================
   EMPTY MESSAGE
================================== */

.empty {

    padding: 20px;

    text-align: center;

    color: #777;
}


/* ==================================
   RESPONSIVE
================================== */

@media(max-width: 900px) {

    .stats {

        grid-template-columns:
            repeat(2, 1fr);
    }


    .book-form {

        grid-template-columns:
            1fr 1fr;
    }
}


@media(max-width: 600px) {

    header {

        flex-direction: column;

        gap: 12px;
    }


    .stats {

        grid-template-columns: 1fr;
    }


    .book-form {

        grid-template-columns: 1fr;
    }
}

</style>

</head>


<body>


<header>

    <h1>
        📚 Library Management System
    </h1>


    <div class="manager-name">

        👨‍💼 Manager Panel

    </div>

</header>



<div class="container">


    <div class="dashboard-title">

        <h2>
            Manager Dashboard
        </h2>

        <p>
            Manage books, borrowing requests,
            issuing and returned books.
        </p>

    </div>



    <?php if ($_SESSION['message'] != ""): ?>

        <div class="message">

            <?php

            echo htmlspecialchars(
                $_SESSION['message']
            );

            $_SESSION['message'] = "";

            ?>

        </div>

    <?php endif; ?>



    <!-- =================================
         STATISTICS
    ================================== -->

    <div class="stats">


        <div class="stat-card">

            <h3>
                Total Books
            </h3>

            <p>
                <?php echo $totalBooks; ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>
                Available Books
            </h3>

            <p>
                <?php echo $availableBooks; ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>
                Borrowed Books
            </h3>

            <p>
                <?php echo $borrowedBooks; ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>
                Pending Requests
            </h3>

            <p>
                <?php echo $pendingRequests; ?>
            </p>

        </div>


    </div>



    <!-- =================================
         ADD / EDIT BOOK
    ================================== -->

    <div class="section">


        <h2>

            <?php

            if ($editBook) {

                echo "✏️ Edit Book";

            } else {

                echo "➕ Add New Book";
            }

            ?>

        </h2>



        <form
            method="POST"
            class="book-form"
            onsubmit="return validateBookForm();"
        >


            <?php if ($editBook): ?>

                <input
                    type="hidden"
                    name="book_id"
                    value="<?php echo intval($editBook['id']); ?>"
                >

            <?php endif; ?>



            <div class="form-group">

                <label>
                    Book Title
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"

                    value="<?php
                    echo $editBook
                        ? htmlspecialchars($editBook['title'])
                        : '';
                    ?>"

                    placeholder="Enter book title"
                >

            </div>



            <div class="form-group">

                <label>
                    Author
                </label>

                <input
                    type="text"
                    id="author"
                    name="author"

                    value="<?php
                    echo $editBook
                        ? htmlspecialchars($editBook['author'])
                        : '';
                    ?>"

                    placeholder="Enter author"
                >

            </div>



            <div class="form-group">

                <label>
                    Category
                </label>

                <input
                    type="text"
                    id="category"
                    name="category"

                    value="<?php
                    echo $editBook
                        ? htmlspecialchars($editBook['category'])
                        : '';
                    ?>"

                    placeholder="Enter category"
                >

            </div>



            <div class="form-group">

                <label>
                    Price (৳)
                </label>

                <input
                    type="number"
                    id="price"
                    name="price"

                    min="1"

                    step="0.01"

                    value="<?php
                    echo $editBook
                        ? htmlspecialchars($editBook['price'])
                        : '';
                    ?>"

                    placeholder="Enter price"
                >

            </div>



            <div class="form-button">


                <?php if ($editBook): ?>


                    <button
                        type="submit"
                        name="update_book"
                    >

                        Update Book

                    </button>


                    <a
                        href="manager.php"
                        class="cancel-button"
                    >
                        Cancel
                    </a>


                <?php else: ?>


                    <button
                        type="submit"
                        name="add_book"
                    >

                        Add Book

                    </button>


                <?php endif; ?>


            </div>


        </form>


    </div>



    <!-- =================================
         BOOK LIST
    ================================== -->

    <div class="section">


        <h2>
            📖 Manage Books
        </h2>


        <div class="table-wrapper">


            <table>


                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Book Name</th>

                        <th>Author</th>

                        <th>Category</th>

                        <th>Price</th>

                        <th>Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>



                <tbody>


                <?php if (count($books) > 0): ?>


                    <?php foreach ($books as $book): ?>


                        <tr>


                            <td>

                                <?php
                                echo intval(
                                    $book['id']
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $book['title']
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $book['author']
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $book['category']
                                );
                                ?>

                            </td>


                            <td>

                                ৳<?php
                                echo number_format(
                                    $book['price'],
                                    2
                                );
                                ?>

                            </td>


                            <td>

                                <span
                                    class="
                                        status
                                        <?php
                                        echo strtolower(
                                            $book['status']
                                        );
                                        ?>
                                    "
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $book['status']
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>


                                <a
                                    class="action edit"

                                    href="manager.php?edit=<?php
                                    echo intval(
                                        $book['id']
                                    );
                                    ?>"
                                >
                                    Edit
                                </a>



                                <a
                                    class="action delete"

                                    href="manager.php?delete=<?php
                                    echo intval(
                                        $book['id']
                                    );
                                    ?>"

                                    onclick="return confirmDelete();"
                                >
                                    Delete
                                </a>



                                <?php
                                if (
                                    $book['status']
                                    == 'Borrowed'
                                ):
                                ?>


                                    <a
                                        class="action return"

                                        href="manager.php?return=<?php
                                        echo intval(
                                            $book['id']
                                        );
                                        ?>"

                                        onclick="return confirmReturn();"
                                    >
                                        Accept Return
                                    </a>


                                <?php endif; ?>


                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="7"
                            class="empty"
                        >

                            No books found.

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>


            </table>


        </div>


    </div>



    <!-- =================================
         BORROW REQUESTS
    ================================== -->

    <div class="section">


        <h2>
            📋 Borrowing Requests
        </h2>


        <div class="table-wrapper">


            <table>


                <thead>


                    <tr>

                        <th>
                            Request ID
                        </th>

                        <th>
                            Student
                        </th>

                        <th>
                            Book
                        </th>

                        <th>
                            Book Price
                        </th>

                        <th>
                            Request Status
                        </th>

                        <th>
                            Manager Action
                        </th>

                    </tr>


                </thead>



                <tbody>


                <?php if (count($requests) > 0): ?>


                    <?php foreach ($requests as $request): ?>


                        <tr>


                            <td>

                                <?php
                                echo intval(
                                    $request['id']
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $request['student']
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $request['book_title']
                                );
                                ?>

                            </td>


                            <td>

                                ৳<?php
                                echo number_format(
                                    $request['book_price'],
                                    2
                                );
                                ?>

                            </td>


                            <td>


                                <span
                                    class="
                                        status
                                        <?php
                                        echo strtolower(
                                            $request['status']
                                        );
                                        ?>
                                    "
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $request['status']
                                    );
                                    ?>

                                </span>


                            </td>



                            <td>


                            <?php
                            if (
                                $request['status']
                                == 'Pending'
                            ):
                            ?>


                                <a
                                    class="action approve"

                                    href="manager.php?approve=<?php
                                    echo intval(
                                        $request['id']
                                    );
                                    ?>"
                                >
                                    Approve
                                </a>



                            <?php
                            elseif (
                                $request['status']
                                == 'Approved'
                            ):
                            ?>


                                <?php
                                if (
                                    $request['book_status']
                                    == 'Available'
                                ):
                                ?>


                                    <a
                                        class="action issue"

                                        href="manager.php?issue=<?php
                                        echo intval(
                                            $request['id']
                                        );
                                        ?>"

                                        onclick="return confirmIssue();"
                                    >

                                        Issue Book

                                    </a>


                                <?php else: ?>


                                    <span
                                        style="
                                            color:#a33a3a;
                                            font-weight:bold;
                                        "
                                    >
                                        Book unavailable
                                    </span>


                                <?php endif; ?>



                            <?php
                            elseif (
                                $request['status']
                                == 'Issued'
                            ):
                            ?>


                                <span>

                                    Waiting for return

                                </span>



                            <?php
                            elseif (
                                $request['status']
                                == 'Returned'
                            ):
                            ?>


                                <span
                                    style="
                                        color:#357447;
                                        font-weight:bold;
                                    "
                                >

                                    Completed

                                </span>


                            <?php endif; ?>


                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="6"
                            class="empty"
                        >

                            No borrowing requests found.

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>


            </table>


        </div>


    </div>


</div>



<script>


/* =========================================
   BOOK FORM VALIDATION
========================================= */

function validateBookForm() {

    const title =
        document
        .getElementById("title")
        .value
        .trim();


    const author =
        document
        .getElementById("author")
        .value
        .trim();


    const category =
        document
        .getElementById("category")
        .value
        .trim();


    const price =
        document
        .getElementById("price")
        .value
        .trim();



    if (title === "") {

        alert(
            "Please enter the book title."
        );

        document
        .getElementById("title")
        .focus();

        return false;
    }


    if (title.length < 2) {

        alert(
            "Book title must contain at least 2 characters."
        );

        document
        .getElementById("title")
        .focus();

        return false;
    }


    if (author === "") {

        alert(
            "Please enter the author's name."
        );

        document
        .getElementById("author")
        .focus();

        return false;
    }


    if (category === "") {

        alert(
            "Please enter the book category."
        );

        document
        .getElementById("category")
        .focus();

        return false;
    }


    if (price === "") {

        alert(
            "Please enter the book price."
        );

        document
        .getElementById("price")
        .focus();

        return false;
    }


    if (
        isNaN(price) ||
        Number(price) <= 0
    ) {

        alert(
            "Book price must be greater than zero."
        );

        document
        .getElementById("price")
        .focus();

        return false;
    }


    return true;
}



/* =========================================
   DELETE CONFIRMATION
========================================= */

function confirmDelete() {

    return confirm(
        "Are you sure you want to delete this book?"
    );
}



/* =========================================
   ISSUE CONFIRMATION
========================================= */

function confirmIssue() {

    return confirm(
        "Are you sure you want to issue this book?"
    );
}



/* =========================================
   RETURN CONFIRMATION
========================================= */

function confirmReturn() {

    return confirm(
        "Confirm that the student has returned this book?"
    );
}


</script>


</body>

</html>