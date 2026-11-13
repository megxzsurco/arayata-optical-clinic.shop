<?php
include 'db.php';
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_exam'])) {$pid = (int)$_POST['patient_id'];$doc = $conn->real_escape_string($_POST['doctor_name']);
    $sph_od =$conn->real_escape_string($_POST['sph_od']);$cyl_od = $conn->real_escape_string($_POST['cyl_od']);
    $axis_od =$conn->real_escape_string($_POST['axis_od']);$sph_os = $conn->real_escape_string($_POST['sph_os']);
    $cyl_os =$conn->real_escape_string($_POST['cyl_os']);$axis_os = $conn->real_escape_string($_POST['axis_os']);
    $rem =$conn->real_escape_string($_POST['remarks']);$conn->query("INSERT INTO eye_examinations (patient_id, doctor_name, sph_od, cyl_od, axis_od, sph_os, cyl_os, axis_os, remarks) VALUES ($pid, '$doc', '$sph_od', '$cyl_od', '$axis_od', '$sph_os', '$cyl_os', '$axis_os', '$rem')");
    $msg = "Prescription successfully recorded!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eye Examination - Arayata Optical</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
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

        /* Atmosphere Background with Venus & Meteor gradient */
        body {
            background: linear-gradient(135deg, var(--meteor) 0%, #f8fafc 50%, var(--venus) 100%);
            background-attachment: fixed;
        }
    </style>
</head>
<body class="font-sans flex h-screen overflow-hidden text-slate-700 antialiased">

<?php include 'sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Bar Navigation Header -->
    <header class="bg-white/80 backdrop-blur-md border-b border-[#BAD6EB]/40 h-20 flex items-center justify-between px-8 shadow-xs z-10">
        <div class="flex items-center space-x-4">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#BAD6EB] to-[#D8E0F2] flex items-center justify-center text-slate-700 font-bold shadow-inner border border-white">
                <i class="fa-solid fa-eye text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl font-black text-slate-800 tracking-tight">Eye Examination Logs</h2>
                <p class="text-xs text-slate-500">Refraction & Clinical Prescription Suite</p>
            </div>
        </div>

        <div class="hidden sm:flex items-center space-x-2 px-4 py-1.5 rounded-full bg-[#D8E0F2]/60 border border-[#BAD6EB]/60 text-xs font-bold text-slate-700 shadow-xs">
            <i class="fa-solid fa-file-prescription text-slate-600"></i>
            <span>Clinical Optometry Records</span>
        </div>
    </header>

    <!-- Main Content Body -->
    <main class="flex-1 overflow-y-auto p-6 md:p-10 space-y-6">
        
        <!-- Notification Alert -->
        <?php if($msg): ?>
            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-2xl text-sm font-semibold shadow-md border border-emerald-200/80 flex items-center space-x-3 transition-all animate-fade-in max-w-3xl mx-auto">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                    <i class="fa-solid fa-check text-sm"></i>
                </div>
                <span><?php echo htmlspecialchars($msg); ?></span>
            </div>
        <?php endif; ?>

        <!-- Glassmorphism Container Card -->
        <div class="relative bg-white/90 backdrop-blur-xl p-8 md:p-10 rounded-3xl shadow-2xl border border-white/80 max-w-3xl mx-auto transition-all duration-300 hover:shadow-[0_20px_50px_rgba(186,214,235,0.35)]">
            
            <!-- Header Title Inside Card -->
            <div class="flex items-center justify-between pb-6 mb-8 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-b from-[#D8E0F2] to-[#BAD6EB] flex items-center justify-center text-slate-700 shadow-inner border border-white">
                        <i class="fa-solid fa-notes-medical text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800 text-xl tracking-tight">New Refraction Prescription</h3>
                        <p class="text-xs text-slate-400">Enter patient refraction values below</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-full border border-slate-100">Rx Entry</span>
            </div>

            <form method="POST" class="space-y-6">
                <!-- Patient Selection & Optometrist Input -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider">
                            <i class="fa-solid fa-user-injured text-[#BAD6EB] mr-1"></i> Patient Name
                        </label>
                        <select name="patient_id" required class="w-full bg-slate-50/80 border border-[#BAD6EB] p-3.5 rounded-2xl text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] transition shadow-xs">
                            <option value="">-- Choose Patient Record --</option>
                            <?php 
                            $p_res =$conn->query("SELECT id, first_name, last_name FROM patients"); 
                            while($p =$p_res->fetch_assoc()) { 
                                echo "<option value='{$p['id']}'>{$p['first_name']} {$p['last_name']}</option>"; 
                            } 
                            ?>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider">
                            <i class="fa-solid fa-user-md text-[#BAD6EB] mr-1"></i> Optometrist Name
                        </label>
                        <input type="text" name="doctor_name" placeholder="Dr. Firstname Lastname" required class="w-full bg-slate-50/80 border border-[#BAD6EB] p-3.5 rounded-2xl text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] transition shadow-xs">
                    </div>
                </div>

                <!-- Prescription Parameters: OD & OS Side by Side -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                    
                    <!-- Right Eye (OD) Box -->
                    <div class="bg-gradient-to-b from-[#D8E0F2]/50 to-white p-6 rounded-2xl border border-[#BAD6EB]/80 shadow-xs space-y-4 relative overflow-hidden">
                        <div class="w-20 h-20 bg-[#BAD6EB]/20 rounded-full blur-xl absolute -top-5 -right-5 pointer-events-none"></div>
                        <div class="flex items-center justify-between border-b border-[#BAD6EB]/60 pb-3">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-eye text-slate-700 text-sm"></i>
                                <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Right Eye (OD)</span>
                            </div>
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-white text-slate-600 border border-[#BAD6EB] shadow-2xs">Oculus Dexter</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2.5">
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider block mb-1">SPH</label>
                                <input type="text" name="sph_od" placeholder="-0.00" class="w-full border border-[#BAD6EB] p-2.5 rounded-xl text-sm font-bold bg-white text-center text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] shadow-2xs">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider block mb-1">CYL</label>
                                <input type="text" name="cyl_od" placeholder="-0.00" class="w-full border border-[#BAD6EB] p-2.5 rounded-xl text-sm font-bold bg-white text-center text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] shadow-2xs">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider block mb-1">Axis</label>
                                <input type="text" name="axis_od" placeholder="180°" class="w-full border border-[#BAD6EB] p-2.5 rounded-xl text-sm font-bold bg-white text-center text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] shadow-2xs">
                            </div>
                        </div>
                    </div>

                    <!-- Left Eye (OS) Box -->
                    <div class="bg-gradient-to-b from-[#D8E0F2]/50 to-white p-6 rounded-2xl border border-[#BAD6EB]/80 shadow-xs space-y-4 relative overflow-hidden">
                        <div class="w-20 h-20 bg-[#BAD6EB]/20 rounded-full blur-xl absolute -top-5 -right-5 pointer-events-none"></div>
                        <div class="flex items-center justify-between border-b border-[#BAD6EB]/60 pb-3">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-eye text-slate-700 text-sm"></i>
                                <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Left Eye (OS)</span>
                            </div>
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-white text-slate-600 border border-[#BAD6EB] shadow-2xs">Oculus Sinister</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2.5">
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider block mb-1">SPH</label>
                                <input type="text" name="sph_os" placeholder="-0.00" class="w-full border border-[#BAD6EB] p-2.5 rounded-xl text-sm font-bold bg-white text-center text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] shadow-2xs">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider block mb-1">CYL</label>
                                <input type="text" name="cyl_os" placeholder="-0.00" class="w-full border border-[#BAD6EB] p-2.5 rounded-xl text-sm font-bold bg-white text-center text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] shadow-2xs">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider block mb-1">Axis</label>
                                <input type="text" name="axis_os" placeholder="180°" class="w-full border border-[#BAD6EB] p-2.5 rounded-xl text-sm font-bold bg-white text-center text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] shadow-2xs">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Clinical Remarks Field -->
                <div class="space-y-1.5 pt-2">
                    <label class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider">
                        <i class="fa-solid fa-comment-medical text-[#BAD6EB] mr-1"></i> Clinical Remarks & Notes
                    </label>
                    <textarea name="remarks" rows="3" placeholder="Add diagnosis, frame recommendations, or lens preferences..." class="w-full bg-slate-50/80 border border-[#BAD6EB] p-4 rounded-2xl text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] transition shadow-xs"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" name="save_exam" class="w-full group relative inline-flex items-center justify-center px-8 py-4 text-sm font-black text-slate-800 bg-[#BAD6EB] hover:bg-[#a4c5de] active:bg-[#90b6d7] rounded-2xl shadow-md