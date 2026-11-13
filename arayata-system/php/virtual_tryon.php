<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Face Shape - Arayata Optical</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
<body class="bg-slate-50 font-sans flex h-screen overflow-hidden">
<?php include 'sidebar.php'; ?>
<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shadow-sm"><h2 class="text-lg font-bold text-slate-800">AI Face Shape Analysis</h2></header>
    <main class="flex-1 overflow-y-auto p-6">
        <div class="bg-white p-12 rounded-xl shadow-sm border border-gray-200 text-center max-w-xl mx-auto">
            <i class="fa-solid fa-wand-magic-sparkles text-4xl text-blue-600 mb-3"></i>
            <h3 class="font-bold text-slate-800 text-lg">Facial Landmark Detection Scanner</h3>
            <p class="text-sm text-slate-500 mt-2 mb-6">Analyze facial geometry to instantly match customer profiles with ideal frame structures.</p>
            <button onclick="alert('Camera Initialized!')" class="bg-blue-600 text-white px-6 py-2 rounded text-sm font-semibold">Start AI Scan</button>
        </div>
    </main>
</div>
</body>
</html>