<!DOCTYPE html>
<html>

<head>

    <title>Library Management System</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #dff6ff;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #40c9c6;
            color: white;
            text-align: center;
            padding: 25px;
        }

        .container {
            width: 70%;
            margin: 30px auto;
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        h2 {
            color: #159a9c;
            text-align: center;
        }

        .section {
            background-color: #eefcff;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #40c9c6;
            border-radius: 8px;
        }

        .section h3 {
            color: #159a9c;
        }

        label {
            display: inline-block;
            width: 160px;
            font-weight: bold;
            color: #247b7b;
        }

        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 250px;
            padding: 9px;
            border: 1px solid #9bdfe0;
            border-radius: 6px;
            box-sizing: border-box;
        }

        textarea {
            height: 80px;
            resize: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border: 2px solid #40c9c6;
        }

        .radio {
            width: auto;
        }

        .buttons {
            text-align: center;
        }

        input[type="submit"],
        input[type="reset"] {
            width: 130px;
            padding: 12px;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin: 5px;
        }

        input[type="submit"] {
            background-color: #20b2aa;
        }

        input[type="reset"] {
            background-color: #7ec8e3;
        }

        input[type="submit"]:hover {
            background-color: #159a9c;
        }

        input[type="reset"]:hover {
            background-color: #55a9c9;
        }

    </style>

</head>

<body>

<div class="header">

    <h1>Library Management System</h1>

    <p>Student Book Borrowing and Return System</p>

</div>


<div class="container">

    <h2>Library Transaction Form</h2>

    <form action="process.php" method="POST">


        <!-- Student Information -->

        <div class="section">

            <h3>Student Information</h3>

            <label>Student ID:</label>

            <input type="text" name="student_id">

            <br><br>


            <label>Student Name:</label>

            <input type="text" name="student_name">

            <br><br>


            <label>Email:</label>

            <input type="text" name="email">

            <br><br>


            <label>Department:</label>

            <select name="department">

                <option value="">Select Department</option>

                <option value="CSE">CSE</option>

                <option value="EEE">EEE</option>

                <option value="BBA">BBA</option>

                <option value="English">English</option>

            </select>

            <br><br>


            <label>Phone:</label>

            <input type="text" name="phone">

        </div>


        <!-- Book Information -->

        <div class="section">

            <h3>Book Information</h3>

            <label>Book ID:</label>

            <input type="text" name="book_id">

            <br><br>


            <label>Book Title:</label>

            <input type="text" name="book_title">

            <br><br>


            <label>Author:</label>

            <input type="text" name="author">

            <br><br>


            <label>Category:</label>

            <select name="category">

                <option value="">Select Category</option>

                <option value="Programming">Programming</option>

                <option value="Database">Database</option>

                <option value="Networking">Networking</option>

                <option value="Mathematics">Mathematics</option>

                <option value="Literature">Literature</option>

            </select>

        </div>


        <!-- Transaction Information -->

        <div class="section">

            <h3>Transaction Information</h3>

            <label>Transaction Type:</label>

            <input class="radio"
                   type="radio"
                   name="transaction_type"
                   value="Borrow">

            Borrow

            <input class="radio"
                   type="radio"
                   name="transaction_type"
                   value="Return">

            Return

            <br><br>


            <label>Issue Date:</label>

            <input type="date" name="issue_date">

            <br><br>


            <label>Return Date:</label>

            <input type="date" name="return_date">

            <br><br>


            <label>Book Condition:</label>

            <select name="book_condition">

                <option value="">Select Condition</option>

                <option value="Excellent">Excellent</option>

                <option value="Good">Good</option>

                <option value="Damaged">Damaged</option>

            </select>

        </div>


        <!-- Remarks -->

        <div class="section">

            <h3>Additional Information</h3>

            <label>Remarks:</label>

            <textarea name="remarks"></textarea>

        </div>


        <div class="buttons">

            <input type="submit" value="Submit">

            <input type="reset" value="Clear">

        </div>

    </form>

</div>

</body>

</html>