<?php 
// Force error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (file_exists('db.php')) {
    include 'db.php';
} else {
    die("Error: db.php file is missing.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Products - Arayata Optical</title>
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
        <h2 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-glasses text-blue-600 mr-2"></i> Optical Frame Catalog</h2>
        <span class="text-sm font-semibold text-slate-600">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Patient'); ?></span>
    </header>
    <main class="flex-1 overflow-y-auto p-6">
        <div class="mb-6">
            <h3 class="text-xl font-bold text-slate-800">Choose Your Frame</h3>
            <p class="text-sm text-slate-500">Piliin ang gustong uri ng frame para sa iyong eye glasses o virtual try-on. Ang mga stocks ay real-time na naka-update.</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php
            $res = $conn->query("SHOW TABLES LIKE 'inventory'");
            if ($res && $res->num_rows > 0) {
                $products_query = $conn->query("SELECT * FROM inventory ORDER BY id DESC");
                if ($products_query && $products_query->num_rows > 0) {
                    while($row = $products_query->fetch_assoc()) {
                        // Dynamic stock badge para sa patient view
                        if ($row['stock'] > 5) {
                            $stock_display = "<span class='text-green-600 font-bold'>Available ({$row['stock']} units)</span>";
                            $btn_state = "<button onclick=\"alert('Selected Frame: {$row['product_name']} (₱" . number_format($row['price'], 2) . ")')\" class='bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-2 rounded-lg font-semibold transition cursor-pointer'>Select Frame</button>";
                        } elseif ($row['stock'] > 0) {
                            $stock_display = "<span class='text-amber-600 font-bold'>Only {$row['stock']} left!</span>";
                            $btn_state = "<button onclick=\"alert('Selected Frame: {$row['product_name']} (₱" . number_format($row['price'], 2) . ")')\" class='bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-2 rounded-lg font-semibold transition cursor-pointer'>Select Frame</button>";
                        } else {
                            $stock_display = "<span class='text-red-500 font-semibold'>Out of Stock</span>";
                            $btn_state = "<button disabled class='bg-gray-300 text-gray-500 text-xs px-3 py-2 rounded-lg font-semibold cursor-not-allowed'>Sold Out</button>";
                        }

                        echo "<div class='bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex flex-col justify-between hover:border-blue-500 transition'>
                            <div>
                                <span class='bg-blue-100 text-blue-700 text-xs px-2.5 py-0.5 rounded-full font-semibold'>{$row['category']}</span>
                                <h4 class='font-bold text-slate-800 text-base mt-3'>{$row['product_name']}</h4>
                                <p class='text-xs text-slate-500 mt-1'>Stock: {$stock_display}</p>
                            </div>
                            <div class='mt-5 flex justify-between items-center pt-3 border-t border-gray-100'>
                                <span class='text-base font-extrabold text-blue-600'>₱" . number_format($row['price'], 2) . "</span>
                                {$btn_state}
                            </div>
                        </div>";
                    }
                } else {
                    echo "<div class='col-span-full text-center py-12 text-slate-400'>
                        <i class='fa-solid fa-box-open text-4xl mb-2'></i>
                        <p>No frames available in the catalog yet.</p>
                    </div>";
                }
            } else {
                echo "<div class='col-span-full text-center py-12 text-red-400'>
                    <p>Inventory database table is missing.</p>
                </div>";
            }
            ?>
        </div>
    </main>
</div>
</body>
</html>