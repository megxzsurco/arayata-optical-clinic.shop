<?php
include 'db.php';
$msg = "";

// Awtomatikong idinaragdag ang queue_no at status columns kung wala pa sa database para hindi mag-crash[cite: 11]
$check_queue = $conn->query("SHOW COLUMNS FROM patients LIKE 'queue_no'");
if ($check_queue && $check_queue->num_rows == 0) {
    $conn->query("ALTER TABLE patients ADD COLUMN queue_no VARCHAR(50) DEFAULT NULL");
}

$check_status = $conn->query("SHOW COLUMNS FROM patients LIKE 'status'");
if ($check_status && $check_status->num_rows == 0) {
    $conn->query("ALTER TABLE patients ADD COLUMN status VARCHAR(50) DEFAULT 'Processing'");
}

// 1. Handle Register / Add New Patient with Auto Queue Number[cite: 11]
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_patient'])) {
    $fname = $conn->real_escape_string($_POST['first_name']);
    $lname = $conn->real_escape_string($_POST['last_name']);
    $age = (int)$_POST['age'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $contact = $conn->real_escape_string($_POST['contact_number']);
    
    // Generate automatic Queue Number[cite: 11]
    $q_res = $conn->query("SELECT MAX(id) as max_id FROM patients");
    $next_id = ($q_res ? $q_res->fetch_assoc()['max_id'] : 0) + 1;
    $queue_no = "Q-" . (100 + $next_id);
    
    // Save with default status Processing[cite: 11]
    $conn->query("INSERT INTO patients (first_name, last_name, age, gender, contact_number, queue_no, status) VALUES ('$fname', '$lname', $age, '$gender', '$contact', '$queue_no', 'Processing')");
    $msg = "Patient added successfully! Queue No: $queue_no";
}

// 2. Handle Edit / Update Patient Record (including Status)[cite: 11]
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_patient'])) {
    $id = (int)$_POST['patient_id'];
    $fname = $conn->real_escape_string($_POST['first_name']);
    $lname = $conn->real_escape_string($_POST['last_name']);
    $age = (int)$_POST['age'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $contact = $conn->real_escape_string($_POST['contact_number']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $conn->query("UPDATE patients SET first_name = '$fname', last_name = '$lname', age = $age, gender = '$gender', contact_number = '$contact', status = '$status' WHERE id = $id");
    $msg = "Patient record #$id successfully updated!";
}

// 3. Handle Delete Patient Record[cite: 11, 12]
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM patients WHERE id = $del_id");
    header("Location: patients.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patients Management - Arayata Optical</title>
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
    <header class="bg-white/85 backdrop-blur-xl border-b border-slate-200/80 h-24 flex items-center justify-between px-8 shadow-xs sticky top-0 z-30">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 flex items-center tracking-tight">
                <span class="w-12 h-12 rounded-2xl bg-[#BAD6EB]/50 text-slate-800 flex items-center justify-center mr-3.5 shadow-inner border border-white">
                    <i class="fa-solid fa-users text-base"></i>
                </span>
                Patient Management
            </h2>
            <p class="text-sm text-slate-500 font-medium ml-15.5 mt-0.5">Manage and track registered optical patients</p>
        </div>
        <div class="flex items-center space-x-3.5 bg-white backdrop-blur-md px-5 py-2.5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#BAD6EB] to-[#D8E0F2] text-slate-800 flex items-center justify-center font-bold text-sm shadow-sm border border-white">
                <?php echo strtoupper(substr($_SESSION['name'] ?? 'Admin', 0, 1)); ?>
            </div>
            <div class="text-left">
                <span class="block text-[11px] text-slate-400 font-semibold uppercase tracking-wider">Welcome</span>
                <span class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></span>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 md:p-8 space-y-8 bg-transparent">
        <?php if ($msg): ?>
        <div class="bg-emerald-50 text-emerald-900 p-4 rounded-2xl shadow-sm border border-emerald-200 flex items-center justify-between text-sm font-semibold animate-pulse">
            <div class="flex items-center">
                <i class="fa-solid fa-circle-check mr-3 text-emerald-600 text-lg"></i>
                <span><?php echo $msg; ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Register New Patient Form Card -->
        <div class="bg-white/85 backdrop-blur-xl p-8 rounded-3xl shadow-[0_20px_50px_-15px_rgba(186,214,235,0.3)] border border-white space-y-6">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 bg-[#BAD6EB]/40 text-slate-800 rounded-xl flex items-center justify-center font-bold shadow-inner border border-white">
                    <i class="fa-solid fa-user-plus text-sm"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-lg tracking-tight">Register New Patient</h3>
            </div>

            <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">First Name</label>
                    <input type="text" name="first_name" placeholder="First Name" required class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Last Name</label>
                    <input type="text" name="last_name" placeholder="Last Name" required class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Age</label>
                    <input type="number" name="age" placeholder="Age" required class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Gender</label>
                    <select name="gender" class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs cursor-pointer">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Contact Number</label>
                    <input type="text" name="contact_number" placeholder="Contact Number" required class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs">
                </div>
                <button type="submit" name="add_patient" class="md:col-span-5 bg-[#BAD6EB] hover:bg-[#a3c7e3] active:bg-[#8ebbdc] text-slate-900 py-3.5 rounded-2xl text-sm font-black transition shadow-md hover:shadow-lg cursor-pointer">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Save Patient Record
                </button>
            </form>
        </div>

        <!-- Patient Database List Table Card -->
        <div class="bg-white/85 backdrop-blur-xl p-8 rounded-3xl shadow-[0_20px_50px_-15px_rgba(186,214,235,0.3)] border border-white space-y-6">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 bg-[#BAD6EB]/40 text-slate-800 rounded-xl flex items-center justify-center font-bold shadow-inner border border-white">
                    <i class="fa-solid fa-table-list text-sm"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-lg tracking-tight">Patient Database List</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-100/70 text-slate-600 text-xs uppercase tracking-wider font-bold border-b border-slate-200">
                            <th class="p-4 rounded-l-2xl">Queue No.</th>
                            <th class="p-4">ID</th>
                            <th class="p-4">Full Name</th>
                            <th class="p-4">Age</th>
                            <th class="p-4">Gender</th>
                            <th class="p-4">Contact</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-center rounded-r-2xl">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    <?php
                    $res = $conn->query("SELECT * FROM patients ORDER BY id DESC");
                    if ($res && $res->num_rows > 0) {
                        while($row = $res->fetch_assoc()) {
                            $q_badge = !empty($row['queue_no']) ? $row['queue_no'] : "Q-". (100 + $row['id']);
                            $status_val = isset($row['status']) && !empty($row['status']) ? $row['status'] : 'Processing';
                            
                            // Status Badges Color Styling[cite: 14]
                            if ($status_val == 'Completed') {
                                $status_badge = "<span class='bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-emerald-200'>Completed</span>";
                            } elseif ($status_val == 'In Progress') {
                                $status_badge = "<span class='bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-blue-200'>In Progress</span>";
                            } else {
                                $status_badge = "<span class='bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-amber-200'>Processing</span>";
                            }

                            echo "<tr class='hover:bg-slate-50/80 transition'>
                            <td class='p-4 font-bold text-slate-900'><span class='bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold shadow-2xs'> $q_badge </span></td>
                            <td class='p-4 font-semibold text-slate-400'>#{$row['id']}</td>
                            <td class='p-4 font-bold text-slate-900'>{$row['first_name']} {$row['last_name']}</td>
                            <td class='p-4 font-medium'>{$row['age']}</td>
                            <td class='p-4 font-medium'>{$row['gender']}</td>
                            <td class='p-4 text-slate-500 font-normal'>{$row['contact_number']}</td>
                            <td class='p-4'>{$status_badge}</td>
                            <td class='p-4 text-center space-x-2'>
                                <button onclick='openEditModal({$row['id']}, \"" . htmlspecialchars($row['first_name'], ENT_QUOTES) . "\", \"" . htmlspecialchars($row['last_name'], ENT_QUOTES) . "\", {$row['age']}, \"{$row['gender']}\", \"" . htmlspecialchars($row['contact_number'], ENT_QUOTES) . "\", \"{$status_val}\")' class='text-blue-700 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer'>
                                    <i class='fa-solid fa-pen-to-square mr-1'></i> Edit
                                </button>
                                <a href='patients.php?delete_id={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this patient?\")' class='text-rose-700 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-2xs inline-block'>
                                    <i class='fa-solid fa-trash mr-1'></i> Delete
                                </a>
                            </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='p-8 text-center text-slate-400 font-medium'>No patient records found.</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Edit Patient Modal -->
<div id="editModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-md hidden flex items-center justify-center p-4 z-50">
    <div class="bg-white/95 backdrop-blur-2xl p-8 rounded-3xl shadow-2xl max-w-md w-full space-y-6 border border-white">
        <div class="flex justify-between items-center border-b border-slate-100 pb-4">
            <h3 class="font-bold text-slate-900 text-lg flex items-center tracking-tight">
                <i class="fa-solid fa-pen-to-square text-slate-700 mr-2.5"></i> Edit Patient Record
            </h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="patient_id" id="edit_patient_id">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">First Name</label>
                <input type="text" name="first_name" id="edit_first_name" required class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Last Name</label>
                <input type="text" name="last_name" id="edit_last_name" required class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Age</label>
                <input type="number" name="age" id="edit_age" required class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Gender</label>
                <select name="gender" id="edit_gender" class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs cursor-pointer">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Contact Number</label>
                <input type="text" name="contact_number" id="edit_contact_number" required class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Queue Status</label>
                <select name="status" id="edit_status" class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs cursor-pointer">
                    <option value="Processing">Processing</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="closeEditModal()" class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3.5 rounded-2xl text-sm font-bold transition cursor-pointer">Cancel</button>
                <button type="submit" name="update_patient" class="w-1/2 bg-[#BAD6EB] hover:bg-[#a3c7e3] active:bg-[#8ebbdc] text-slate-900 py-3.5 rounded-2xl text-sm font-black transition shadow-md hover:shadow-lg cursor-pointer">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, fname, lname, age, gender, contact, status) {
    document.getElementById('edit_patient_id').value = id;
    document.getElementById('edit_first_name').value = fname;
    document.getElementById('edit_last_name').value = lname;
    document.getElementById('edit_age').value = age;
    document.getElementById('edit_gender').value = gender;
    document.getElementById('edit_contact_number').value = contact;
    document.getElementById('edit_status').value = status;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
</body>
</html>