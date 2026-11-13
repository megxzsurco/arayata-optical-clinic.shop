<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (file_exists('db.php')) { include 'db.php'; } else { die("Error: db.php missing."); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog - Arayata Optical</title>
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

        /* Smooth background atmosphere */
        body {
            background: linear-gradient(135deg, var(--meteor) 0%, #f8fafc 50%, var(--venus) 100%);
            background-attachment: fixed;
        }
    </style>
</head>
<body class="font-sans flex h-screen overflow-hidden text-slate-700 antialiased">

<?php if (file_exists('sidebar.php')) { include 'sidebar.php'; } ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md border-b border-[#BAD6EB]/40 h-20 flex items-center justify-between px-8 shadow-xs z-10">
        <div class="flex items-center space-x-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#BAD6EB] to-[#D8E0F2] flex items-center justify-center text-slate-700 font-bold shadow-inner border border-white">
                <i class="fa-solid fa-store text-base"></i>
            </div>
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Admin Product Catalog</h2>
                <p class="text-xs text-slate-500">Real-time inventory & pricing overview</p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <div class="hidden sm:flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-[#D8E0F2]/60 border border-[#BAD6EB]/60 text-xs font-semibold text-slate-700">
                <i class="fa-solid fa-circle-user text-slate-600"></i>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6">
        
        <!-- Action Banner -->
        <div class="bg-white/90 backdrop-blur-md p-6 rounded-3xl shadow-xl border border-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-[#BAD6EB]"></span>
                    <h3 class="text-xl font-extrabold text-slate-800 tracking-tight">Inventory Catalog Grid</h3>
                </div>
                <p class="text-xs text-slate-500 mt-1">Real-time monitoring ng mga stocks, kategorya, at presyo.</p>
            </div>
            <a href="inventory.php" class="bg-[#BAD6EB] hover:bg-[#a4c5de] active:bg-[#90b6d7] text-slate-800 px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-xs hover:shadow-md flex items-center space-x-2">
                <i class="fa-solid fa-boxes-stacked text-slate-700"></i>
                <span>Manage Inventory</span>
            </a>
        </div>

        <!-- Product Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php
            $catalog_res = $conn->query("SELECT * FROM inventory ORDER BY id ASC");
            if ($catalog_res && $catalog_res->num_rows > 0) {
                while($row = $catalog_res->fetch_assoc()) {
                    
                    // Dynamic Stock Badge Styling
                    if ($row['stock'] > 5) {
                        $stock_badge = "<span class='bg-emerald-50 text-emerald-700 border border-emerald-200/80 text-[11px] px-2.5 py-1 rounded-lg font-bold flex items-center space-x-1'><i class='fa-solid fa-check text-[9px] mr-1'></i> Stock: {$row['stock']}</span>";
                    } elseif ($row['stock'] > 0) {
                        $stock_badge = "<span class='bg-amber-50 text-amber-700 border border-amber-200/80 text-[11px] px-2.5 py-1 rounded-lg font-bold flex items-center space-x-1'><i class='fa-solid fa-triangle-exclamation text-[9px] mr-1'></i> Low: {$row['stock']}</span>";
                    } else {
                        $stock_badge = "<span class='bg-rose-50 text-rose-700 border border-rose-200/80 text-[11px] px-2.5 py-1 rounded-lg font-bold flex items-center space-x-1'><i class='fa-solid fa-xmark text-[9px] mr-1'></i> Out of Stock</span>";
                    }

                    echo "<div class='group bg-white/90 backdrop-blur-md p-6 rounded-3xl shadow-lg border border-white flex flex-col justify-between hover:shadow-xl hover:border-[#BAD6EB] transition-all duration-300 transform hover:-translate-y-1'>
                        <div>
                            <div class='flex justify-between items-center gap-2 mb-3'>
                                <span class='bg-[#D8E0F2] text-slate-700 text-[11px] px-3 py-1 rounded-lg font-bold tracking-wide uppercase border border-[#BAD6EB]/50'>
                                    " . htmlspecialchars($row['category']) . "
                                </span>
                                {$stock_badge}
                            </div>
                            <h4 class='font-black text-slate-800 text-lg leading-snug mt-2 group-hover:text-slate-900 transition-colors'>
                                " . htmlspecialchars($row['product_name']) . "
                            </h4>
                        </div>
                        
                        <div class='mt-6 pt-4 border-t border-slate-100 flex justify-between items-center'>
                            <div>
                                <span class='text-[10px] uppercase font-bold text-slate-400 block'>Unit Price</span>
                                <span class='text-lg font-extrabold text-slate-800'>₱" . number_format($row['price'], 2) . "</span>
                            </div>
                            <a href='pos.php' class='bg-[#BAD6EB] hover:bg-[#a4c5de] active:bg-[#90b6d7] text-slate-800 text-xs px-4 py-2.5 rounded-xl transition font-bold flex items-center space-x-1.5 shadow-xs group-hover:shadow-md'>
                                <i class='fa-solid fa-cash-register text-slate-700 text-[11px]'></i>
                                <span>Sell POS</span>
                            </a>
                        </div>
                    </div>";
                }
            } else {
                echo "<div class='col-span-full bg-white/80 backdrop-blur-md rounded-3xl p-12 text-center text-slate-400 shadow-sm border border-white'>
                    <div class='w-16 h-16 rounded-2xl bg-[#D8E0F2]/50 text-slate-400 flex items-center justify-center mx-auto mb-3'>
                        <i class='fa-solid fa-box-open text-2xl'></i>
                    </div>
                    <p class='font-medium text-slate-500'>No products available in inventory.</p>
                </div>";
            }
            ?>
        </div>
    </main>
</div>

</body>
</html>