<?php
include 'db.php';
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pickup'])) {
    $name = $conn->real_escape_string($_POST['patient_name']);
    $item = $conn->real_escape_string($_POST['item_description']);
    $date = $conn->real_escape_string($_POST['pickup_date']);
    $time = $conn->real_escape_string($_POST['pickup_time']);
    $conn->query("INSERT INTO appointments (patient_name, service_type, appointment_date, appointment_time, status) VALUES ('$name', 'Pickup: $item', '$date', '$time', 'Pending')");
    $msg = "Pickup slot scheduled!";
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pickup Schedule - Arayata Optical</title>
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

        /* Custom Scrollbar */
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
        ::-webkit-scrollbar-thumb:hover {
            background: #8EBBDC;
        }

        /* Gradient background matching Venus & Meteor theme */
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
    <header class="bg-white/90 backdrop-blur-2xl border-b border-white/80 h-24 flex items-center justify-between px-8 shadow-[0_4px_20px_rgba(186,214,235,0.15)] sticky top-0 z-30">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#BAD6EB] to-[#D8E0F2] flex items-center justify-center text-slate-800 font-bold shadow-inner border border-white">
                <i class="fa-solid fa-calendar-days text-base"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Pickup Schedule Management</h2>
                <p class="text-sm text-slate-500 font-medium mt-0.5">Arayata Optical Service Center</p>
            </div>
        </div>
        
        <div class="hidden sm:flex items-center space-x-2.5 px-4 py-2 rounded-2xl bg-white/90 backdrop-blur-md border border-white text-xs font-bold text-slate-700 shadow-sm">
            <i class="fa-solid fa-clock text-blue-500"></i>
            <span>Schedule Control</span>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 md:p-8 space-y-8 bg-transparent">
        <?php if ($msg): ?>
            <div class="bg-emerald-50/90 backdrop-blur-xl text-emerald-900 p-4 rounded-3xl text-sm border border-emerald-200/80 shadow-[0_10px_25px_-5px_rgba(16,185,129,0.15)] flex items-center space-x-3 transition-all animate-pulse">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0 font-bold shadow-2xs">
                    <i class="fa-solid fa-check text-sm"></i>
                </div>
                <span class="font-semibold"><?php echo $msg; ?></span>
            </div>
        <?php endif; ?>

        <!-- New Item Pickup Slot Card Box -->
        <div class="bg-white/90 backdrop-blur-2xl p-8 rounded-3xl shadow-[0_20px_50px_-15px_rgba(186,214,235,0.35)] border border-white space-y-6">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 bg-[#BAD6EB]/40 text-slate-800 rounded-xl flex items-center justify-center font-bold shadow-inner border border-white">
                    <i class="fa-solid fa-circle-plus text-sm"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-lg tracking-tight">New Item Pickup Slot</h3>
            </div>

            <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Customer Name</label>
                    <input type="text" name="patient_name" placeholder="Customer Name" required class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Item Description</label>
                    <input type="text" name="item_description" placeholder="Item Description" required class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Pickup Date</label>
                    <input type="date" name="pickup_date" required class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Pickup Time</label>
                    <input type="time" name="pickup_time" required class="w-full bg-slate-50/70 border border-slate-200 p-3.5 rounded-2xl text-sm font-semibold text-slate-800 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition shadow-2xs cursor-pointer">
                </div>
                <button type="submit" name="add_pickup" class="md:col-span-4 bg-[#BAD6EB] hover:bg-[#a3c7e3] active:bg-[#8ebbdc] text-slate-900 py-3.5 rounded-2xl text-sm font-black transition shadow-md hover:shadow-lg cursor-pointer">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Save Pickup Slot
                </button>
            </form>
        </div>

        <!-- Pickup Schedule Table Card Box -->
        <div class="bg-white/90 backdrop-blur-2xl p-8 rounded-3xl shadow-[0_20px_50px_-15px_rgba(186,214,235,0.35)] border border-white space-y-6">
            <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 bg-[#BAD6EB]/40 text-slate-800 rounded-xl flex items-center justify-center font-bold shadow-inner border border-white">
                    <i class="fa-solid fa-list-check text-sm"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-lg tracking-tight">Scheduled Pickups List</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-600 text-xs uppercase tracking-wider font-bold border-b border-slate-200/70">
                            <th class="p-4 rounded-l-2xl">Customer</th>
                            <th class="p-4">Item Details</th>
                            <th class="p-4">Date</th>
                            <th class="p-4 rounded-r-2xl">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        <?php
                        $res = $conn->query("SELECT * FROM appointments WHERE service_type LIKE 'Pickup%'");
                        if ($res && $res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()) {
                                echo "<tr class='hover:bg-slate-50/85 transition'>
                                    <td class='p-4 font-bold text-slate-900'>{$row['patient_name']}</td>
                                    <td class='p-4 font-semibold text-slate-700'>{$row['service_type']}</td>
                                    <td class='p-4 text-slate-500 font-medium'>{$row['appointment_date']}</td>
                                    <td class='p-4 text-slate-500 font-medium'>{$row['appointment_time']}</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr>
                                <td colspan='4' class='p-12 text-center text-slate-400'>
                                    <div class='w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 border border-slate-200 shadow-2xs'>
                                        <i class='fa-solid fa-calendar-xmark text-2xl'></i>
                                    </div>
                                    <p class='font-bold text-slate-600'>No pickup schedule records found.</p>
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

</body>
</html>