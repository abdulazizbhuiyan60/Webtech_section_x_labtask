<?php


$errors = array();


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die('Invalid request method.');
}


$applicant_id  = trim($_POST['applicant_id']);
$full_name     = trim($_POST['full_name']);
$email         = trim($_POST['email']);
$phone         = trim($_POST['phone']);
$password      = trim($_POST['password']);
$gender        = isset($_POST['gender']) ? $_POST['gender'] : '';
$job_position  = $_POST['job_position'];
$qualification = trim($_POST['qualification']);
$address       = trim($_POST['address']);


if ($applicant_id == '') $errors[] = "Applicant ID is required.";
if ($full_name == '') $errors[] = "Name is required.";
if ($email == '') {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}
if ($phone == '') {
    $errors[] = "Phone number is required.";
} elseif (!preg_match('/^[0-9]{11}$/', $phone)) {
    $errors[] = "Phone number must be 11 digits.";
}
if ($password == '') {
    $errors[] = "Password is required.";
} elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters.";
}
if ($gender == '') $errors[] = "Please select your gender.";
if ($job_position == '') $errors[] = "Please select a job position.";
if ($qualification == '') $errors[] = "Qualification is required.";
if ($address == '') $errors[] = "Address is required.";


$cv_name = '';
$allowed = array('pdf', 'doc', 'docx');

if (!isset($_FILES['cv']) || $_FILES['cv']['name'] == '') {
    $errors[] = "Please upload your CV.";
} else {
    $file_name = $_FILES['cv']['name'];
    $file_size = $_FILES['cv']['size'];
    $file_tmp  = $_FILES['cv']['tmp_name'];
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $errors[] = "Only PDF, DOC, and DOCX files are allowed.";
    }
    if ($file_size > 2 * 1024 * 1024) {
        $errors[] = "File size must not exceed 2 MB.";
    }

    if (empty($errors)) {
        $cv_name = $applicant_id . "_" . $file_name;
        move_uploaded_file($file_tmp, "uploads/" . $cv_name);
    }
}


if (!empty($errors)) {
    echo "<h2>Application Failed!</h2>";
    echo "<ul>";
    foreach ($errors as $e) {
        echo "<li>" . htmlspecialchars($e) . "</li>";
    }
    echo "</ul>";
    echo '<a href="index.php">Go back</a>';
    exit;
}


$query = http_build_query(array(
    'applicant_id'  => $applicant_id,
    'full_name'     => $full_name,
    'email'         => $email,
    'phone'         => $phone,
    'gender'        => $gender,
    'job_position'  => $job_position,
    'qualification' => $qualification,
    'address'       => $address,
    'cv_filename'   => $cv_name,
));

header("Location: result.php?" . $query);
exit;
