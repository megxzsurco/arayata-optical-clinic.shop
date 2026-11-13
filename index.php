<?php
// Force error reporting right at the top
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (file_exists('db.php')) {
    include 'db.php';
} else {
    die("Error: db.php file is missing.");
}
$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Administrator';
$msg = "";
// Handle Appointment Submission directly from index.php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_appt'])) {
    $pname = $conn->real_escape_string($_POST['patient_name']);
    $stype = $conn->real_escape_string($_POST['service_type']);
    $adate = $conn->real_escape_string($_POST['appointment_date']);
    $atime = $conn->real_escape_string($_POST['appointment_time']);
    $conn->query("INSERT INTO appointments (patient_name, service_type, appointment_date, appointment_time, status) VALUES ('$pname', '$stype', '$adate', '$atime', 'Pending')");
    $msg = "Appointment scheduled successfully!";
}
// Fetch dashboard overview counts securely with fallbacks
function getCount($conn, $table) {
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    if ($res && $res->num_rows > 0) {
        $q = $conn->query("SELECT COUNT(*) as count FROM $table");
        if ($q) {
            $row = $q->fetch_assoc();
            return $row['count'] ?? 0;
        }
    }
    return 0;
}
function getSum($conn, $table, $column) {
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    if ($res && $res->num_rows > 0) {
        $q = $conn->query("SELECT SUM($column) as total FROM $table");
        if ($q) {
            $row = $q->fetch_assoc();
            return $row['total'] ?? 0;
        }
    }
    return 0;
}
$patient_count = getCount($conn, 'patients');
$appointment_count = getCount($conn, 'appointments');
$inventory_count = getSum($conn, 'inventory', 'stock');
$sales_revenue = getSum($conn, 'sales', 'total_amount');
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Overview - Arayata Optical</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          venus: '#BAD6EB',
          meteor: '#D8E0F2',
          venusDark: '#8EBBDC',
        }
      }
    }
  }
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --venus: #BAD6EB;
        --meteor: #D8E0F2;
    }
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    ::-webkit-scrollbar-thumb {
        background: var(--venus);
        border-radius: 4px;
    }
    body {
        background: radial-gradient(circle at 10% 20%, #e2ebf0 0%, #f6f8fb 90%);
        background-attachment: fixed;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .blob-1 {
        animation: float 10s ease-in-out infinite alternate;
    }
    .blob-2 {
        animation: float 14s ease-in-out infinite alternate-reverse;
    }
    @keyframes float {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(30px, 40px) scale(1.1); }
    }
</style>
</head>
<body class="min-h-screen text-slate-700 antialiased flex overflow-hidden relative">

    <!-- Ambient Glowing Orbs Background -->
    <div class="blob-1 fixed -top-20 -left-20 w-96 h-96 bg-[#BAD6EB]/40 rounded-full blur-3xl pointer-events-none z-0"></div>
    <div class="blob-2 fixed -bottom-20 -right-20 w-96 h-96 bg-[#D8E0F2]/70 rounded-full blur-3xl pointer-events-none z-0"></div>

<?php
if (file_exists('sidebar.php')) {
    include 'sidebar.php';
}
?>
<div class="flex-1 flex flex-col overflow-hidden z-10">
    <!-- Header Navbar -->
    <header class="bg-white/90 backdrop-blur-xl border-b border-slate-200/80 h-24 flex items-center justify-between px-8 shadow-xs sticky top-0 z-30">
        <div>
            <h2 class="text-xl font-bold text-slate-800 flex items-center tracking-tight">
                <span class="w-11 h-11 rounded-2xl bg-[#BAD6EB]/40 text-slate-700 flex items-center justify-center mr-3 shadow-inner border border-white">
                    <i class="fa-solid fa-house text-sm"></i>
                </span>
                Dashboard Overview
            </h2>
            <p class="text-xs text-slate-500 font-medium ml-14 mt-1">Arayata Optical System Management</p>
        </div>
        <div class="flex items-center space-x-3 bg-white backdrop-blur-md px-4 py-2.5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#BAD6EB] to-[#D8E0F2] text-slate-800 flex items-center justify-center font-bold text-xs shadow-sm border border-white">
                <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
            </div>
            <div class="text-left">
                <span class="block text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Welcome Back</span>
                <span class="text-xs font-bold text-slate-800"><?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 md:p-8 space-y-8 bg-transparent">
        <?php if ($msg): ?>
        <div class="bg-emerald-50 text-emerald-800 p-4 rounded-2xl shadow-sm border border-emerald-200 flex items-center justify-between text-xs font-semibold animate-pulse">
            <div class="flex items-center">
                <i class="fa-solid fa-circle-check mr-3 text-emerald-600 text-base"></i>
                <span><?php echo $msg; ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- 4-Column Overview Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <!-- Total Patients Card -->
            <div class="bg-white/90 backdrop-blur-xl p-6 rounded-3xl shadow-[0_15px_35px_-10px_rgba(186,214,235,0.3)] border border-white flex items-center justify-between transition hover:-translate-y-1 duration-200">
                <div>
                    <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Total Patients</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-1"><?php echo $patient_count; ?></h3>
                    <span class="text-[11px] text-slate-400 font-medium mt-1.5 inline-block"><i class="fa-solid fa-arrow-trend-up text-blue-500 mr-1"></i> Active records</span>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-[#BAD6EB] to-[#90b6d7] text-slate-800 rounded-2xl flex items-center justify-center shadow-md text-lg font-bold border border-white">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <!-- Appointments Card -->
            <div class="bg-white/90 backdrop-blur-xl p-6 rounded-3xl shadow-[0_15px_35px_-10px_rgba(186,214,235,0.3)] border border-white flex items-center justify-between transition hover:-translate-y-1 duration-200">
                <div>
                    <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Appointments</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-1"><?php echo $appointment_count; ?></h3>
                    <span class="text-[11px] text-slate-400 font-medium mt-1.5 inline-block"><i class="fa-regular fa-calendar text-blue-500 mr-1"></i> Scheduled queue</span>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-[#D8E0F2] to-[#BAD6EB] text-slate-800 rounded-2xl flex items-center justify-center shadow-md text-lg font-bold border border-white">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>

            <!-- Stock Units Card -->
            <div class="bg-white/90 backdrop-blur-xl p-6 rounded-3xl shadow-[0_15px_35px_-10px_rgba(186,214,235,0.3)] border border-white flex items-center justify-between transition hover:-translate-y-1 duration-200">
                <div>
                    <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Stock Units</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-1"><?php echo $inventory_count; ?></h3>
                    <span class="text-[11px] text-slate-400 font-medium mt-1.5 inline-block"><i class="fa-solid fa-boxes-stacked text-blue-500 mr-1"></i> Available items</span>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-[#BAD6EB] via-[#D8E0F2] to-[#BAD6EB] text-slate-800 rounded-2xl flex items-center justify-center shadow-md text-lg font-bold border border-white">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
            </div>

            <!-- Total Revenue Card -->
            <div class="bg-white/90 backdrop-blur-xl p-6 rounded-3xl shadow-[0_15px_35px_-10px_rgba(186,214,235,0.3)] border border-white flex items-center justify-between transition hover:-translate-y-1 duration-200">
                <div>
                    <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">Total Revenue</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">₱<?php echo number_format($sales_revenue, 2); ?></h3>
                    <span class="text-[11px] text-slate-400 font-medium mt-1.5 inline-block"><i class="fa-solid fa-peso-sign text-blue-500 mr-1"></i> Earnings total</span>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-[#90b6d7] to-[#BAD6EB] text-slate-800 rounded-2xl flex items-center justify-center shadow-md text-lg font-bold border border-white">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
        </div>

        <!-- Patients Database Table Preview -->
        <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-[0_20px_50px_-15px_rgba(186,214,235,0.3)] border border-white p-6 md:p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-bold text-slate-800 text-base flex items-center tracking-tight">
                        <i class="fa-solid fa-user-group text-slate-600 mr-2.5"></i> Registered Patients Database & Queue
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Real-time listing of recently registered patients.</p>
                </div>
                <a href="patients.php" class="text-xs bg-[#BAD6EB] hover:bg-[#a3c7e3] active:bg-[#8ebbdc] text-slate-800 px-4 py-2.5 rounded-xl font-bold transition shadow-2xs flex items-center cursor-pointer">
                    <span>Manage Patients</span> <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs md:text-sm">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-600 uppercase tracking-wider font-bold border-b border-slate-200">
                            <th class="p-3.5 rounded-l-2xl">Queue No.</th>
                            <th class="p-3.5">ID</th>
                            <th class="p-3.5">Full Name</th>
                            <th class="p-3.5">Age</th>
                            <th class="p-3.5">Gender</th>
                            <th class="p-3.5 rounded-r-2xl">Contact</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    <?php
                    $res = $conn->query("SHOW TABLES LIKE 'patients'");
                    if ($res && $res->num_rows > 0) {
                        $pat_res = $conn->query("SELECT * FROM patients ORDER BY id DESC LIMIT 10");
                        if ($pat_res && $pat_res->num_rows > 0) {
                            while ($row = $pat_res->fetch_assoc()) {
                                $q_badge = !empty($row['queue_no']) ? $row['queue_no'] : "Q-". (100 + $row['id']);
                                echo "<tr class='hover:bg-slate-50/80 transition'>
                                <td class='p-3.5 font-bold text-slate-800'><span class='bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs'> $q_badge </span></td>
                                <td class='p-3.5 text-slate-400 font-semibold'>#{$row['id']}</td>
                                <td class='p-3.5 font-bold text-slate-800'>{$row['first_name']} {$row['last_name']}</td>
                                <td class='p-3.5 text-slate-600'>{$row['age']}</td>
                                <td class='p-3.5 text-slate-600'> {$row['gender']}</td>
                                <td class='p-3.5 text-slate-500 font-normal'> {$row['contact_number']}</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='p-6 text-center text-slate-400 font-medium'>No patient records found in the database.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='p-6 text-center text-rose-500 font-semibold'>Patients table missing.</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Active Appointment Queue Preview Box -->
        <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-[0_20px_50px_-15px_rgba(186,214,235,0.3)] border border-white p-6 md:p-8">
            <div class="mb-6">
                <h3 class="font-bold text-slate-800 text-base flex items-center tracking-tight">
                    <i class="fa-solid fa-clock-rotate-left text-slate-600 mr-2.5"></i> Recent System Appointments
                </h3>
                <p class="text-xs text-slate-500 mt-1 font-medium">Overview of scheduled eye care consultations and checkups.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs md:text-sm">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-600 uppercase tracking-wider font-bold border-b border-slate-200">
                            <th class="p-3.5 rounded-l-2xl">Patient Name</th>
                            <th class="p-3.5">Service</th>
                            <th class="p-3.5">Date</th>
                            <th class="p-3.5">Time</th>
                            <th class="p-3.5 rounded-r-2xl text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    <?php
                    $res = $conn->query("SHOW TABLES LIKE 'appointments'");
                    if ($res && $res->num_rows > 0) {
                        $appt_res = $conn->query("SELECT * FROM appointments ORDER BY appointment_date DESC LIMIT 5");
                        if ($appt_res && $appt_res->num_rows > 0) {
                            while ($row = $appt_res->fetch_assoc()) {
                                echo "<tr class='hover:bg-slate-50/80 transition'>
                                <td class='p-3.5 font-bold text-slate-800'>{$row['patient_name']}</td>
                                <td class='p-3.5 font-semibold text-slate-700'>{$row['service_type']}</td>
                                <td class='p-3.5 text-slate-500'>{$row['appointment_date']}</td>
                                <td class='p-3.5 text-slate-500'>{$row['appointment_time']}</td>
                                <td class='p-3.5 text-center'><span class='bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider'>{$row['status']}</span></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='p-6 text-center text-slate-400 font-medium'>No appointment records found.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='p-6 text-center text-rose-500 font-semibold'>Appointments table missing.</td></tr>";
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