<?php
include 'db.php';
$msg = "";

// 1. Handle Booking / Adding New Appointment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_appt'])) {
    $pname = $conn->real_escape_string($_POST['patient_name']);
    $stype = $conn->real_escape_string($_POST['service_type']);
    $adate = $conn->real_escape_string($_POST['appointment_date']);
    $atime = $conn->real_escape_string($_POST['appointment_time']);
    
    $conn->query("INSERT INTO appointments (patient_name, service_type, appointment_date, appointment_time, status) VALUES ('$pname', '$stype', '$adate', '$atime', 'Pending')");
    $msg = "Appointment scheduled successfully!";
}

// 2. Handle Updating Appointment (Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_appt'])) {
    $id = (int)$_POST['appt_id'];
    $pname = $conn->real_escape_string($_POST['patient_name']);
    $stype = $conn->real_escape_string($_POST['service_type']);
    $adate = $conn->real_escape_string($_POST['appointment_date']);
    $atime = $conn->real_escape_string($_POST['appointment_time']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $conn->query("UPDATE appointments SET patient_name = '$pname', service_type = '$stype', appointment_date = '$adate', appointment_time = '$atime', status = '$status' WHERE id = $id");
    $msg = "Appointment #$id successfully updated!";
}

// 3. Handle Deleting Appointment
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM appointments WHERE id = $del_id");
    header("Location: appointments.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Arayata Optical</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              venus: '#BAD6EB',
              meteor: '#D8E0F2',
            }
          }
        }
      }
    </script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --venus: #BAD6EB;
            --meteor: #D8E0F2;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--venus);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a2c4de;
        }

        /* Gradient background matching Venus & Meteor theme */
        body {
            background: linear-gradient(135deg, var(--meteor) 0%, #f8fafc 50%, var(--venus) 100%);
            background-attachment: fixed;
        }
    </style>
</head>
<body class="font-sans flex h-screen overflow-hidden text-slate-700 antialiased">

<?php 
if (file_exists('sidebar.php')) {
    include 'sidebar.php'; 
}
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md border-b border-[#BAD6EB]/40 h-20 flex items-center justify-between px-8 shadow-xs z-10">
        <div class="flex items-center space-x-4">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#BAD6EB] to-[#D8E0F2] flex items-center justify-center text-slate-700 font-bold shadow-inner border border-white">
                <i class="fa-solid fa-calendar-check text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl font-black text-slate-800 tracking-tight">Appointments Management</h2>
                <p class="text-xs text-slate-500">Patient Consultation & Exam Schedules</p>
            </div>
        </div>

        <div class="hidden sm:flex items-center space-x-2 px-4 py-1.5 rounded-full bg-[#D8E0F2]/60 border border-[#BAD6EB]/60 text-xs font-bold text-slate-700 shadow-xs">
            <i class="fa-solid fa-user-shield text-slate-600"></i>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></span>
        </div>
    </header>

    <!-- Main Content Body -->
    <main class="flex-1 overflow-y-auto p-6 md:p-10 space-y-8">
        
        <!-- Success Alert Notification -->
        <?php if($msg): ?>
            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-2xl text-sm font-semibold shadow-md border border-emerald-200/80 flex items-center space-x-3 transition-all">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                    <i class="fa-solid fa-check text-sm"></i>
                </div>
                <span><?php echo htmlspecialchars($msg); ?></span>
            </div>
        <?php endif; ?>

        <!-- Book New Appointment Form Card -->
        <div class="relative bg-white/90 backdrop-blur-xl p-8 rounded-3xl shadow-xl border border-white/80 transition-all duration-300 hover:shadow-[0_20px_50px_rgba(186,214,235,0.3)]">
            <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-[#D8E0F2] flex items-center justify-center text-slate-700 shadow-inner">
                    <i class="fa-solid fa-calendar-plus text-base"></i>
                </div>
                <div>
                    <h3 class="font-black text-slate-800 text-lg tracking-tight">Book New Appointment</h3>
                    <p class="text-xs text-slate-400">Schedule a patient consultation or eye exam</p>
                </div>
            </div>

            <form method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="flex flex-col space-y-1.5">
                    <label class="text-xs font-extrabold text-slate-600 uppercase tracking-wider">Patient Name</label>
                    <input type="text" name="patient_name" placeholder="Full Name" required class="w-full bg-slate-50/80 border border-[#BAD6EB] p-3 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] transition shadow-xs">
                </div>

                <div class="flex flex-col space-y-1.5">
                    <label class="text-xs font-extrabold text-slate-600 uppercase tracking-wider">Service Type</label>
                    <input type="text" name="service_type" placeholder="e.g. Eye Refraction" required class="w-full bg-slate-50/80 border border-[#BAD6EB] p-3 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] transition shadow-xs">
                </div>

                <div class="flex flex-col space-y-1.5">
                    <label class="text-xs font-extrabold text-slate-600 uppercase tracking-wider">Date</label>
                    <input type="date" name="appointment_date" required class="w-full bg-slate-50/80 border border-[#BAD6EB] p-3 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] transition shadow-xs">
                </div>

                <div class="flex flex-col space-y-1.5">
                    <label class="text-xs font-extrabold text-slate-600 uppercase tracking-wider">Time</label>
                    <input type="time" name="appointment_time" required class="w-full bg-slate-50/80 border border-[#BAD6EB] p-3 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] transition shadow-xs">
                </div>

                <div class="flex flex-col justify-end">
                    <button type="submit" name="add_appt" class="w-full group relative inline-flex items-center justify-center px-6 py-3.5 text-sm font-black text-slate-800 bg-[#BAD6EB] hover:bg-[#a4c5de] active:bg-[#90b6d7] rounded-2xl shadow-md hover:shadow-lg transition-all duration-200 ease-in-out focus:outline-none focus:ring-4 focus:ring-[#BAD6EB]/50 cursor-pointer">
                        <i class="fa-solid fa-clock text-slate-700 mr-2 group-hover:scale-110 transition-transform"></i>
                        <span>Save Schedule</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Active Appointment Queue Table Card -->
        <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-xl border border-white/80 p-8 transition-all duration-300">
            <div class="flex items-center justify-between pb-5 mb-6 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-[#D8E0F2] flex items-center justify-center text-slate-700 shadow-inner">
                        <i class="fa-solid fa-list-check text-base"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800 text-lg tracking-tight">Active Appointment Queue</h3>
                        <p class="text-xs text-slate-400">Live patient appointments and status updates</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-slate-500 bg-[#D8E0F2]/60 px-3 py-1.5 rounded-full border border-[#BAD6EB]/60">Real-time List</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-[#D8E0F2]/50 text-slate-700 text-xs font-extrabold uppercase tracking-wider rounded-xl">
                            <th class="p-4 rounded-l-xl">ID</th>
                            <th class="p-4">Patient Name</th>
                            <th class="p-4">Service</th>
                            <th class="p-4">Date</th>
                            <th class="p-4">Time</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 rounded-r-xl text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php
                        $res = $conn->query("SELECT * FROM appointments ORDER BY appointment_date DESC");
                        if ($res && $res->num_rows > 0) {
                            while($row = $res->fetch_assoc()) {
                                // Dynamic status badges
                                $status_badge = 'bg-slate-100 text-slate-600 border-slate-200';
                                if ($row['status'] == 'Completed') {
                                    $status_badge = 'bg-emerald-50 text-emerald-700 border-emerald-200/80';
                                } elseif ($row['status'] == 'Cancelled') {
                                    $status_badge = 'bg-rose-50 text-rose-700 border-rose-200/80';
                                } elseif ($row['status'] == 'Pending') {
                                    $status_badge = 'bg-amber-50 text-amber-700 border-amber-200/80';
                                }

                                echo "<tr class='hover:bg-[#D8E0F2]/20 transition-colors duration-150'>
                                    <td class='p-4 font-bold text-slate-800'>#{$row['id']}</td>
                                    <td class='p-4 font-black text-slate-800'>
                                        <div class='flex items-center space-x-2.5'>
                                            <div class='w-8 h-8 rounded-xl bg-[#D8E0F2] flex items-center justify-center text-xs font-bold text-slate-700 shadow-inner'>
                                                <i class='fa-solid fa-user text-[11px]'></i>
                                            </div>
                                            <span>{$row['patient_name']}</span>
                                        </div>
                                    </td>
                                    <td class='p-4'>
                                        <span class='px-3 py-1 text-xs rounded-xl bg-[#D8E0F2]/60 border border-[#BAD6EB]/50 font-bold text-slate-700'>
                                            {$row['service_type']}
                                        </span>
                                    </td>
                                    <td class='p-4 font-semibold text-slate-700'>{$row['appointment_date']}</td>
                                    <td class='p-4 font-semibold text-slate-700'>{$row['appointment_time']}</td>
                                    <td class='p-4'>
                                        <span class='{$status_badge} border px-3 py-1 rounded-xl text-xs font-bold inline-block shadow-2xs'>
                                            {$row['status']}
                                        </span>
                                    </td>
                                    <td class='p-4 text-center space-x-2'>
                                        <button onclick='openEditModal({$row['id']}, \"{$row['patient_name']}\", \"{$row['service_type']}\", \"{$row['appointment_date']}\", \"{$row['appointment_time']}\", \"{$row['status']}\")' class='text-slate-800 bg-[#BAD6EB] hover:bg-[#a4c5de] px-3.5 py-1.5 rounded-xl text-xs font-bold transition shadow-2xs inline-flex items-center space-x-1.5 cursor-pointer'>
                                            <i class='fa-solid fa-pen-to-square text-[11px]'></i>
                                            <span>Edit</span>
                                        </button>
                                        <a href='appointments.php?delete_id={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this appointment?\")' class='text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/60 px-3.5 py-1.5 rounded-xl text-xs font-bold transition shadow-2xs inline-flex items-center space-x-1.5'>
                                            <i class='fa-solid fa-trash text-[11px]'></i>
                                            <span>Delete</span>
                                        </a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr>
                                <td colspan='7' class='p-12 text-center text-slate-400'>
                                    <div class='w-16 h-16 rounded-2xl bg-[#D8E0F2]/50 text-slate-400 flex items-center justify-center mx-auto mb-3'>
                                        <i class='fa-solid fa-calendar-xmark text-2xl'></i>
                                    </div>
                                    <p class='font-medium text-slate-500'>No appointment records found.</p>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- Edit Appointment Modal -->
<div id="editModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50 transition-opacity">
    <div class="bg-white/95 backdrop-blur-xl p-8 rounded-3xl shadow-2xl max-w-md w-full border border-white transform transition-all">
        <div class="flex justify-between items-center mb-6 pb-3 border-b border-slate-100">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-xl bg-[#BAD6EB] flex items-center justify-center text-slate-700">
                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                </div>
                <h3 class="font-black text-slate-800 text-lg tracking-tight">Edit Appointment</h3>
            </div>
            <button onclick="closeEditModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-600 hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="appt_id" id="edit_appt_id">
            
            <div>
                <label class="block text-xs font-extrabold text-slate-600 mb-1.5 uppercase tracking-wider">Patient Name</label>
                <input type="text" name="patient_name" id="edit_patient_name" required class="w-full bg-slate-50 border border-[#BAD6EB] p-3 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#BAD6EB]">
            </div>

            <div>
                <label class="block text-xs font-extrabold text-slate-600 mb-1.5 uppercase tracking-wider">Service Type</label>
                <input type="text" name="service_type" id="edit_service_type" required class="w-full bg-slate-50 border border-[#BAD6EB] p-3 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#BAD6EB]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-extrabold text-slate-600 mb-1.5 uppercase tracking-wider">Date</label>
                    <input type="date" name="appointment_date" id="edit_appointment_date" required class="w-full bg-slate-50 border border-[#BAD6EB] p-3 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#BAD6EB]">
                </div>
                <div>
                    <label class="block text-xs font-extrabold text-slate-600 mb-1.5 uppercase tracking-wider">Time</label>
                    <input type="time" name="appointment_time" id="edit_appointment_time" required class="w-full bg-slate-50 border border-[#BAD6EB] p-3 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#BAD6EB]">
                </div>
            </div>

            <div>
                <label class="block text-xs font-extrabold text-slate-600 mb-1.5 uppercase tracking-wider">Status</label>
                <select name="status" id="edit_status" class="w-full bg-slate-50 border border-[#BAD6EB] p-3 rounded-2xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] cursor-pointer">
                    <option value="Pending">Pending</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeEditModal()" class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-600 py-3.5 rounded-2xl text-xs font-bold transition cursor-pointer">Cancel</button>
                <button type="submit" name="update_appt" class="w-1/2 bg-[#BAD6EB] hover:bg-[#a4c5de] active:bg-[#90b6d7] text-slate-800 py-3.5 rounded-2xl text-xs font-black transition shadow-md cursor-pointer">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, pname, stype, adate, atime, status) {
        document.getElementById('edit_appt_id').value = id;
        document.getElementById('edit_patient_name').value = pname;
        document.getElementById('edit_service_type').value = stype;
        document.getElementById('edit_appointment_date').value = adate;
        document.getElementById('edit_appointment_time').value = atime;
        document.getElementById('edit_status').value = status;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
</body>
</html>