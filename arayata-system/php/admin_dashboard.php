<?php
// Force error reporting right at the top
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include database connection safely
if (file_exists('db.php')) {
    include 'db.php';
} else {
    die("Error: db.php file is missing.");
}

// Fallback user name if session isn't set yet
$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Administrator';
$msg = "";

// Handle Appointment Form Submission directly inside Admin Dashboard
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_appt'])) {
    $pname = $conn->real_escape_string($_POST['patient_name']);
    $stype = $conn->real_escape_string($_POST['service_type']);
    $adate = $conn->real_escape_string($_POST['appointment_date']);
    $atime = $conn->real_escape_string($_POST['appointment_time']);
    
    $conn->query("INSERT INTO appointments (patient_name, service_type, appointment_date, appointment_time, status) VALUES ('$pname', '$stype', '$adate', '$atime', 'Pending')");
    $msg = "Appointment scheduled successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Arayata Optical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans flex h-screen overflow-hidden">
    <?php 
    if (file_exists('sidebar.php')) {
        include 'sidebar.php'; 
    }
    ?>
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800">Admin Dashboard</h2>
            <span class="text-sm font-semibold text-slate-600">Welcome, <?php echo htmlspecialchars($admin_name); ?></span>
        </header>

        <main class="flex-1 overflow-y-auto p-6 space-y-6">
            <?php if($msg): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded text-sm font-semibold shadow-sm">
                    <i class="fa-solid fa-circle-check mr-2"></i><?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <!-- Original Admin Dashboard Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-xs text-slate-500 font-semibold">Total Patients</p>
                    <h3 class="text-2xl font-extrabold text-blue-600 mt-1">
                        <?php 
                        $res = $conn->query("SHOW TABLES LIKE 'patients'");
                        if ($res && $res->num_rows > 0) {
                            $p = $conn->query("SELECT COUNT(*) as count FROM patients");
                            echo $p ? $p->fetch_assoc()['count'] : 0;
                        } else {
                            echo "0 (Table missing)";
                        }
                        ?>
                    </h3>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-xs text-slate-500 font-semibold">Appointments Today</p>
                    <h3 class="text-2xl font-extrabold text-indigo-600 mt-1">
                        <?php 
                        $res = $conn->query("SHOW TABLES LIKE 'appointments'");
                        if ($res && $res->num_rows > 0) {
                            $a = $conn->query("SELECT COUNT(*) as count FROM appointments");
                            echo $a ? $a->fetch_assoc()['count'] : 0;
                        } else {
                            echo "0 (Table missing)";
                        }
                        ?>
                    </h3>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-xs text-slate-500 font-semibold">Total Sales Revenue</p>
                    <h3 class="text-2xl font-extrabold text-green-600 mt-1">
                        <?php 
                        $res = $conn->query("SHOW TABLES LIKE 'sales'");
                        if ($res && $res->num_rows > 0) {
                            $s = $conn->query("SELECT SUM(total_amount) as total FROM sales");
                            $row = $s ? $s->fetch_assoc()['total'] : 0;
                            echo "₱" . number_format($row ?? 0, 2);
                        } else {
                            echo "₱0.00 (Table missing)";
                        }
                        ?>
                    </h3>
                </div>
            </div>

            <!-- Book New Appointment Form Section -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="font-bold text-slate-800 text-base mb-4"><i class="fa-solid fa-calendar-plus text-blue-600 mr-2"></i> Book New Appointment</h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <input type="text" name="patient_name" placeholder="Patient Name" required class="border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                    <input type="text" name="service_type" placeholder="Service Type" required class="border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                    <input type="date" name="appointment_date" required class="border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                    <input type="time" name="appointment_time" required class="border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                    <button type="submit" name="add_appt" class="bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">Save Schedule</button>
                </form>
            </div>

            <!-- Active Appointment Queue Table Section -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="font-bold text-slate-800 text-base mb-4"><i class="fa-solid fa-clock-rotate-left text-blue-600 mr-2"></i> Active Appointment Queue</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase border-b border-gray-200">
                                <th class="p-3">Patient Name</th>
                                <th class="p-3">Service</th>
                                <th class="p-3">Date</th>
                                <th class="p-3">Time</th>
                                <th class="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-slate-700">
                            <?php
                            $res = $conn->query("SHOW TABLES LIKE 'appointments'");
                            if ($res && $res->num_rows > 0) {
                                $appt_res = $conn->query("SELECT * FROM appointments ORDER BY appointment_date DESC");
                                if ($appt_res && $appt_res->num_rows > 0) {
                                    while($row = $appt_res->fetch_assoc()) {
                                        echo "<tr>
                                            <td class='p-3 font-medium'>{$row['patient_name']}</td>
                                            <td class='p-3'>{$row['service_type']}</td>
                                            <td class='p-3'>{$row['appointment_date']}</td>
                                            <td class='p-3'>{$row['appointment_time']}</td>
                                            <td class='p-3'><span class='bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-semibold'>{$row['status']}</span></td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='p-4 text-center text-slate-400'>No appointment records found.</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='p-4 text-center text-slate-400'>Appointments table does not exist yet.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>