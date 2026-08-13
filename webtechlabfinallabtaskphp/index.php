<?php

?>
<!DOCTYPE html>
<html>
<head>
<title>Job Application Form</title>
</head>
<body>

<h1>Online Job Application Form</h1>

<form action="process.php" method="POST" enctype="multipart/form-data">

    Applicant ID: <input type="text" name="applicant_id"><br><br>

    Full Name: <input type="text" name="full_name"><br><br>

    Email: <input type="text" name="email"><br><br>

    Phone Number: <input type="text" name="phone"><br><br>

    Password: <input type="password" name="password"><br><br>

    Gender:
    <input type="radio" name="gender" value="Male"> Male
    <input type="radio" name="gender" value="Female"> Female<br><br>

    Job Position:
    <select name="job_position">
        <option value="">-- Select --</option>
        <option value="Software Developer">Software Developer</option>
        <option value="Web Developer">Web Developer</option>
        <option value="Database Administrator">Database Administrator</option>
        <option value="Network Engineer">Network Engineer</option>
    </select><br><br>

    Educational Qualification: <input type="text" name="qualification"><br><br>

    Address:<br>
    <textarea name="address" rows="3" cols="30"></textarea><br><br>

    Upload CV (PDF/DOC/DOCX, max 2MB): <input type="file" name="cv"><br><br>

    <input type="submit" name="submit" value="Submit Application">

</form>

</body>
</html>
