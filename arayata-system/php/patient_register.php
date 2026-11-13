<?php
include 'db.php';
session_start();
$msg = "";
$assigned_queue = "";

// Handle Patient Self-Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_patient'])) {
    $fname = $conn->real_escape_string($_POST['first_name']);
    $lname = $conn->real_escape_string($_POST['last_name']);
    $age = (int)$_POST['age'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $contact = $conn->real_escape_string($_POST['contact_number']);
    
    // Generate automatic Queue Number
    $q_res = $conn->query("SELECT MAX(id) as max_id FROM patients");
    $next_id = ($q_res ? $q_res->fetch_assoc()['max_id'] : 0) + 1;
    $assigned_queue = "Q-" . (100 + $next_id);

    // Save to shared database table so Admin can see it immediately
    $conn->query("INSERT INTO patients (first_name, last_name, age, gender, contact_number, queue_no) VALUES ('$fname', '$lname', $age, '$gender', '$contact', '$assigned_queue')");
    $msg = "Registration successful! Your Queue Number is <strong>{$assigned_queue}</strong>. Please wait to be called.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Registration - Arayata Optical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans flex h-screen overflow-hidden">
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-hospital-user text-blue-600 mr-2"></i> Patient Portal - Walk-In & Queue Registration</h2>
            <a href="my_portal.php" class="text-sm font-semibold text-blue-600 hover:underline"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Portal</a>
        </header>

        <main class="flex-1 overflow-y-auto p-6 flex items-center justify-center">
            <div class="max-w-xl w-full bg-white p-8 rounded-2xl shadow-sm border border-gray-200 space-y-6">
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3 text-xl font-bold">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-800">Patient Queue Registration</h3>
                    <p class="text-xs text-slate-500 mt-1">Isulat ang iyong mga detalye para makakuha ng queue number para sa check-up o konsulta.</p>
                </div>

                <?php if($msg): ?>
                    <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-xl text-center text-sm shadow-sm">
                        <i class="fa-solid fa-circle-check text-blue-600 mr-1 text-base"></i> <?php echo $msg; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">First Name</label>
                            <input type="text" name="first_name" placeholder="Enter first name" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Last Name</label>
                            <input type="text" name="last_name" placeholder="Enter last name" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Age</label>
                            <input type="number" name="age" placeholder="Enter age" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Gender</label>
                            <select name="gender" class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Contact Number</label>
                        <input type="text" name="contact_number" placeholder="Enter contact number" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                    </div>
                    <button type="submit" name="register_patient" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl text-sm font-bold transition shadow-sm cursor-pointer">
                        Register & Get Queue Number
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>