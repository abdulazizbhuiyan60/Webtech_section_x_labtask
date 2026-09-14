<?php

$student_id = $_GET["student_id"] ?? "";
$student_name = $_GET["student_name"] ?? "";
$email = $_GET["email"] ?? "";
$department = $_GET["department"] ?? "";
$phone = $_GET["phone"] ?? "";

$book_id = $_GET["book_id"] ?? "";
$book_title = $_GET["book_title"] ?? "";
$author = $_GET["author"] ?? "";
$category = $_GET["category"] ?? "";

$transaction_type = $_GET["transaction_type"] ?? "";
$issue_date = $_GET["issue_date"] ?? "";
$return_date = $_GET["return_date"] ?? "";
$book_condition = $_GET["book_condition"] ?? "";

$remarks = $_GET["remarks"] ?? "";

?>

<!DOCTYPE html>

<html>

<head>

    <title>Library Transaction Successful</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #dff6ff;
            margin: 0;
            padding: 30px;
        }

        .container {
            width: 70%;
            margin: auto;
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .header {
            background-color: #40c9c6;
            color: white;
            text-align: center;
            padding: 20px;
            border-radius: 10px;
        }

        .success {
            background-color: #d9fff8;
            color: #168b82;
            text-align: center;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            font-weight: bold;
        }

        .section {
            background-color: #eefcff;
            padding: 20px;
            margin-top: 20px;
            border-left: 5px solid #40c9c6;
            border-radius: 8px;
        }

        .section h2 {
            color: #159a9c;
        }

        .row {
            padding: 10px;
            border-bottom: 1px solid #cceff0;
        }

        .label {
            display: inline-block;
            width: 180px;
            font-weight: bold;
            color: #247b7b;
        }

        .value {
            color: #333;
        }

        .back {
            display: block;
            width: 150px;
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

    <div class="header">

        <h1>Library Management System</h1>

        <p>Transaction Details</p>

    </div>


    <div class="success">

        Library transaction submitted successfully!

    </div>


    <!-- Student Information -->

    <div class="section">

        <h2>Student Information</h2>

        <div class="row">
            <span class="label">Student ID:</span>
            <span class="value">
                <?php echo htmlspecialchars($student_id); ?>
            </span>
        </div>

        <div class="row">
            <span class="label">Student Name:</span>
            <span class="value">
                <?php echo htmlspecialchars($student_name); ?>
            </span>
        </div>

        <div class="row">
            <span class="label">Email:</span>
            <span class="value">
                <?php echo htmlspecialchars($email); ?>
            </span>
        </div>

        <div class="row">
            <span class="label">Department:</span>
            <span class="value">
                <?php echo htmlspecialchars($department); ?>
            </span>
        </div>

        <div class="row">
            <span class="label">Phone:</span>
            <span class="value">
                <?php echo htmlspecialchars($phone); ?>
            </span>
        </div>

    </div>


    <!-- Book Information -->

    <div class="section">

        <h2>Book Information</h2>

        <div class="row">
            <span class="label">Book ID:</span>
            <span class="value">
                <?php echo htmlspecialchars($book_id); ?>
            </span>
        </div>

        <div class="row">
            <span class="label">Book Title:</span>
            <span class="value">
                <?php echo htmlspecialchars($book_title); ?>
            </span>
        </div>

        <div class="row">
            <span class="label">Author:</span>
            <span class="value">
                <?php echo htmlspecialchars($author); ?>
            </span>
        </div>

        <div class="row">
            <span class="label">Category:</span>
            <span class="value">
                <?php echo htmlspecialchars($category); ?>
            </span>
        </div>

    </div>


    <!-- Transaction Information -->

    <div class="section">

        <h2>Transaction Information</h2>

        <div class="row">
            <span class="label">Transaction Type:</span>
            <span class="value">
                <?php echo htmlspecialchars($transaction_type); ?>
            </span>
        </div>

        <div class="row">
            <span class="label">Issue Date:</span>
            <span class="value">
                <?php echo htmlspecialchars($issue_date); ?>
            </span>
        </div>

        <div class="row">
            <span class="label">Return Date:</span>
            <span class="value">
                <?php echo htmlspecialchars($return_date); ?>
            </span>
        </div>

        <div class="row">
            <span class="label">Book Condition:</span>
            <span class="value">
                <?php echo htmlspecialchars($book_condition); ?>
            </span>
        </div>

        <div class="row">
            <span class="label">Remarks:</span>
            <span class="value">
                <?php echo htmlspecialchars($remarks); ?>
            </span>
        </div>

    </div>


    <a class="back" href="mindex.php">

        Back to Library

    </a>

</div>

</body>

</html>