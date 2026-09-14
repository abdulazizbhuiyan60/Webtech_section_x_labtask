<?php

require_once "managerdb.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: mindex.php");
    exit;
}

$student_id = trim($_POST["student_id"] ?? "");
$student_name = trim($_POST["student_name"] ?? "");
$email = trim($_POST["email"] ?? "");
$department = trim($_POST["department"] ?? "");
$phone = trim($_POST["phone"] ?? "");

$book_id = intval($_POST["book_id"] ?? 0);
$book_title = trim($_POST["book_title"] ?? "");
$author = trim($_POST["author"] ?? "");
$category = trim($_POST["category"] ?? "");

$transaction_type = $_POST["transaction_type"] ?? "";
$issue_date = $_POST["issue_date"] ?? "";
$return_date = $_POST["return_date"] ?? "";
$book_condition = $_POST["book_condition"] ?? "";

$remarks = trim($_POST["remarks"] ?? "");

if ($student_id == "") {
    $errors[] = "Student ID is required.";
}

if ($student_name == "") {
    $errors[] = "Student name is required.";
}

if ($email == "") {
    $errors[] = "Email is required.";
}
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}

if ($department == "") {
    $errors[] = "Please select your department.";
}

if ($phone == "") {
    $errors[] = "Phone number is required.";
}

if ($book_id <= 0) {
    $errors[] = "Valid Book ID is required.";
}

if ($book_title == "") {
    $errors[] = "Book title is required.";
}

if ($author == "") {
    $errors[] = "Author name is required.";
}

if ($category == "") {
    $errors[] = "Please select a book category.";
}

if (
    $transaction_type != "Borrow" &&
    $transaction_type != "Return"
) {
    $errors[] = "Please select Borrow or Return.";
}

if ($issue_date == "") {
    $errors[] = "Issue date is required.";
}

if ($return_date == "") {
    $errors[] = "Return date is required.";
}

if (
    $issue_date != "" &&
    $return_date != "" &&
    $return_date < $issue_date
) {
    $errors[] = "Return date cannot be before issue date.";
}

if ($book_condition == "") {
    $errors[] = "Please select the book condition.";
}

if ($remarks == "") {
    $errors[] = "Remarks are required.";
}

if (count($errors) == 0) {

    $stmt = $conn->prepare(
        "SELECT id, title, author, category, status
         FROM books
         WHERE id = ?"
    );

    $stmt->bind_param("i", $book_id);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {

        $errors[] = "Book ID does not exist.";

    } else {

        $book = $result->fetch_assoc();

        $book_title = $book["title"];
        $author = $book["author"];
        $category = $book["category"];

        if ($transaction_type == "Borrow") {

            if ($book["status"] != "Available") {

                $errors[] =
                    "This book is currently unavailable.";

            } else {

                $check = $conn->prepare(
                    "SELECT id
                     FROM borrow_requests
                     WHERE student = ?
                     AND book_id = ?
                     AND status IN
                     ('Pending','Approved','Issued')"
                );

                $check->bind_param(
                    "si",
                    $student_name,
                    $book_id
                );

                $check->execute();

                $checkResult =
                    $check->get_result();

                if ($checkResult->num_rows > 0) {

                    $errors[] =
                        "You already have an active request for this book.";

                } else {

                    $insert = $conn->prepare(
                        "INSERT INTO borrow_requests
                        (student, book_id, status)
                        VALUES (?, ?, 'Pending')"
                    );

                    $insert->bind_param(
                        "si",
                        $student_name,
                        $book_id
                    );

                    if (!$insert->execute()) {

                        $errors[] =
                            "Could not submit borrowing request.";
                    }

                    $insert->close();
                }

                $check->close();
            }
        }

        if ($transaction_type == "Return") {

            $check = $conn->prepare(
                "SELECT id
                 FROM borrow_requests
                 WHERE book_id = ?
                 AND status = 'Issued'
                 LIMIT 1"
            );

            $check->bind_param(
                "i",
                $book_id
            );

            $check->execute();

            $checkResult =
                $check->get_result();

            if ($checkResult->num_rows == 0) {

                $errors[] =
                    "This book is not currently issued.";
            }

            $check->close();
        }
    }

    $stmt->close();
}

if (count($errors) == 0) {

    $data = http_build_query([
        "student_id" => $student_id,
        "student_name" => $student_name,
        "email" => $email,
        "department" => $department,
        "phone" => $phone,
        "book_id" => $book_id,
        "book_title" => $book_title,
        "author" => $author,
        "category" => $category,
        "transaction_type" => $transaction_type,
        "issue_date" => $issue_date,
        "return_date" => $return_date,
        "book_condition" => $book_condition,
        "remarks" => $remarks
    ]);

    header(
        "Location: success.php?" . $data
    );

    exit;
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Library Transaction Result</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #dff6ff;
            margin: 0;
            padding: 40px;
        }

        .container {
            width: 60%;
            margin: auto;
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        h2 {
            text-align: center;
            color: #159a9c;
        }

        h3 {
            color: #e74c3c;
            text-align: center;
        }

        .error {
            background-color: #ffe6e6;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            color: #c0392b;
        }

        .back {
            display: block;
            width: 130px;
            margin: 25px auto 0;
            padding: 12px;
            text-align: center;
            text-decoration: none;
            color: white;
            background-color: #20b2aa;
            border-radius: 6px;
        }

        .back:hover {
            background-color: #159a9c;
        }

    </style>

</head>

<body>

<div class="container">

    <h2>Library Transaction Result</h2>

    <?php if (count($errors) > 0): ?>

        <h3>Transaction Failed</h3>

        <?php foreach ($errors as $error): ?>

            <p class="error">
                <?php echo htmlspecialchars($error); ?>
            </p>

        <?php endforeach; ?>

        <a class="back" href="mindex.php">
            Go Back
        </a>

    <?php endif; ?>

</div>

</body>

</html>