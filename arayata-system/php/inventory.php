<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (file_exists('db.php')) {
    include 'db.php';
} else {
    die("Error: db.php file is missing.");
}

$msg = "";
$target_dir = __DIR__ . '/upload/';
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Default products array[cite: 19]
$products = [
    ['name' => 'Isometal', 'category' => 'Metal Frame', 'price' => 1500, 'stock' => 15, 'colors' => 'Black, Silver, Gold', 'image' => 'default_placeholder.jpg'],
    ['name' => 'Isomplastic', 'category' => 'Plastic Frame', 'price' => 1000, 'stock' => 20, 'colors' => 'Black, Brown, Tortoise', 'image' => 'default_placeholder.jpg'],
    ['name' => 'Sporty Type', 'category' => 'Sporty', 'price' => 3000, 'stock' => 10, 'colors' => 'Matte Black, Neon Blue', 'image' => 'default_placeholder.jpg'],
    ['name' => 'Metal Frame / Sporty Type', 'category' => 'Hybrid', 'price' => 3500, 'stock' => 12, 'colors' => 'Gunmetal, Silver', 'image' => 'default_placeholder.jpg'],
    ['name' => 'Metal Frame', 'category' => 'Metal', 'price' => 2000, 'stock' => 25, 'colors' => 'Gold, Silver, Rose Gold', 'image' => 'default_placeholder.jpg'],
    ['name' => 'Full Rim / Half Rim', 'category' => 'Standard', 'price' => 2200, 'stock' => 18, 'colors' => 'Black, Gray', 'image' => 'default_placeholder.jpg'],
    ['name' => 'Kids / Flexible', 'category' => 'Kids', 'price' => 2500, 'stock' => 14, 'colors' => 'Blue, Pink, Red', 'image' => 'default_placeholder.jpg'],
    ['name' => 'Titanium Frame / Flexible', 'category' => 'Titanium', 'price' => 7500, 'stock' => 8, 'colors' => 'Titanium Gray, Matte Black', 'image' => 'default_placeholder.jpg'],
    ['name' => 'Aviator Frame', 'category' => 'Classic', 'price' => 5000, 'stock' => 10, 'colors' => 'Gold, Silver', 'image' => 'classic av.jpg'],
    ['name' => 'Cat Eye Frame', 'category' => 'Fashion', 'price' => 3500, 'stock' => 12, 'colors' => 'Black, Tortoise, Burgundy', 'image' => 'default_placeholder.jpg'],
    ['name' => 'Round Frame', 'category' => 'Classic', 'price' => 3000, 'stock' => 15, 'colors' => 'Gold, Silver, Black', 'image' => 'lens.jpg']
];

// Seed inventory table if empty[cite: 20]
$check_empty = $conn->query("SELECT COUNT(*) as total FROM inventory");
if ($check_empty) {
    $row_count = $check_empty->fetch_assoc()['total'];
    if ($row_count == 0) {
        foreach ($products as $p) {
            $name = $conn->real_escape_string($p['name']);
            $cat = $conn->real_escape_string($p['category']);
            $colors = $conn->real_escape_string($p['colors']);
            $stock = (int)$p['stock'];
            $price = (float)$p['price'];
            $image = $conn->real_escape_string($p['image']);
            
            $conn->query("INSERT INTO inventory (product_name, category, colors, stock, price, image) VALUES ('$name', '$cat', '$colors', $stock, $price, '$image')");
        }
        $msg = "Default optical products successfully loaded!";
    }
}

// Handle Add Stock Item with Per-Color Stocks & Auto Summation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $name = $conn->real_escape_string($_POST['product_name']);
    $cat = $conn->real_escape_string($_POST['category']);
    $colors_input = isset($_POST['colors']) ? trim($_POST['colors']) : '';
    $price = (float)$_POST['price'];
    
    // Automatically calculate total stock from color quantities (e.g., "Black: 10, Gold: 5" -> 15)
    $total_stock = 0;
    $color_parts = explode(',', $colors_input);
    foreach ($color_parts as $part) {
        $sub = explode(':', $part);
        if (count($sub) == 2) {
            $total_stock += (int)trim($sub[1]);
        }
    }
    
    $colors = $conn->real_escape_string($colors_input);
    $image_name = "default_placeholder.jpg";

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        if (empty($ext)) { $ext = 'jpg'; }
        $image_name = time() . "_" . preg_replace("/[^a-zA-Z0-9]/", "", $name) . "." . $ext;
        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $target_dir . $image_name)) {
            $msg = "Error: Failed to move uploaded file.";
        }
    }

    $conn->query("INSERT INTO inventory (product_name, category, colors, stock, price, image) VALUES ('$name', '$cat', '$colors', $total_stock, $price, '$image_name')");
    if (empty($msg)) $msg = "Inventory item added successfully with auto-calculated stock!";
}

// Handle Update Stock Item (Edit) with Per-Color Stocks & Auto Summation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_item'])) {
    $id = (int)$_POST['item_id'];
    $name = $conn->real_escape_string($_POST['product_name']);
    $cat = $conn->real_escape_string($_POST['category']);
    $colors_input = isset($_POST['colors']) ? trim($_POST['colors']) : '';
    $price = (float)$_POST['price'];

    // Automatically calculate total stock from color quantities
    $total_stock = 0;
    $color_parts = explode(',', $colors_input);
    foreach ($color_parts as $part) {
        $sub = explode(':', $part);
        if (count($sub) == 2) {
            $total_stock += (int)trim($sub[1]);
        }
    }
    
    $colors = $conn->real_escape_string($colors_input);
    $image_update_sql = "";

    if (isset($_FILES['edit_product_image']) && $_FILES['edit_product_image']['error'] == 0) {
        $fileTmpPath = $_FILES['edit_product_image']['tmp_name'];
        $fileName = $_FILES['edit_product_image']['name'];
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        if (empty($ext)) { $ext = 'jpg'; }
        $newFileName = time() . "_" . preg_replace("/[^a-zA-Z0-9]/", "", $name) . "." . $ext;
        $dest_path = $target_dir . $newFileName;
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $image_update_sql = ", image = '" . $conn->real_escape_string($newFileName) . "'";
        }
    }

    $sql = "UPDATE inventory SET product_name = '$name', category = '$cat', colors = '$colors', stock = $total_stock, price = $price $image_update_sql WHERE id = $id";
    $conn->query($sql);
    $msg = "Inventory item #$id successfully updated with recalculated stock!";
}

// Handle Delete Stock Item[cite: 22]
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM inventory WHERE id = $del_id");
    header("Location: inventory.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inventory Tracking - Arayata Optical</title>
<!-- Tailwind CSS[cite: 22] -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                venus: '#BAD6EB',
                meteor: '#D8E0F2'
            }
        }
    }
}
</script>
<!-- FontAwesome Icons[cite: 23] -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --venus: #BAD6EB;
    --meteor: #D8E0F2;
}
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
body {
    background: linear-gradient(135deg, var(--meteor) 0%, #f8fafc 50%, var(--venus) 100%);
    background-attachment: fixed;
}
</style>
</head>
<body class="font-sans flex h-screen overflow-hidden text-slate-700 antialiased">
<?php if (file_exists('sidebar.php')) { include 'sidebar.php'; } ?>
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header[cite: 23, 24] -->
    <header class="bg-white/80 backdrop-blur-md border-b border-[#BAD6EB]/40 h-20 flex items-center justify-between px-8 shadow-xs z-10">
        <div class="flex items-center space-x-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#BAD6EB] to-[#D8E0F2] flex items-center justify-center font-bold shadow-inner border border-white text-slate-700">
                <i class="fa-solid fa-boxes-stacked text-base"></i>
            </div>
            <div>
                <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Inventory Tracking & Stocks</h2>
                <p class="text-xs text-slate-500">Arayata Optical Stock Control & Management</p>
            </div>
        </div>
        <div class="hidden sm:flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-[#D8E0F2]/60 border border-[#BAD6EB]/60 text-xs font-semibold text-slate-700">
            <i class="fa-solid fa-user-shield text-slate-600"></i>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></span>
        </div>
    </header>

    <!-- Main Scrollable Area -->
    <main class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6">
        <!-- Alert Notification -->
        <?php if ($msg): ?>
        <div class="bg-white/90 backdrop-blur-md text-slate-800 p-4 rounded-2xl text-sm font-semibold shadow-md border border-[#BAD6EB] flex items-center space-x-3">
            <div class="w-8 h-8 rounded-full bg-[#D8E0F2] flex items-center justify-center text-slate-700 flex-shrink-0">
                <i class="fa-solid fa-circle-check text-sm"></i>
            </div>
            <span><?php echo htmlspecialchars($msg); ?></span>
        </div>
        <?php endif; ?>

        <!-- Add Item Form Card[cite: 24, 25] -->
        <div class="bg-white/90 backdrop-blur-md shadow-xl border border-white p-6 md:p-8 rounded-3xl">
            <div class="flex items-center space-x-2 mb-5">
                <div class="w-3 h-3 rounded-full bg-[#BAD6EB]"></div>
                <h3 class="font-extrabold text-slate-800 text-lg tracking-tight">
                    <i class="fa-solid fa-plus text-slate-600 mr-1.5"></i> Add New Stock Item
                </h3>
            </div>
            <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div class="flex flex-col">
                    <label class="text-[11px] font-bold text-slate-500 mb-1">Product Name</label>
                    <input type="text" name="product_name" placeholder="e.g. Aviator Frame" required class="w-full bg-slate-50/50 border border-[#BAD6EB]/80 p-2.5 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] transition">
                </div>
                <div class="flex flex-col">
                    <label class="text-[11px] font-bold text-slate-500 mb-1">Category</label>
                    <input type="text" name="category" placeholder="e.g. Classic" required class="w-full bg-slate-50/50 border border-[#BAD6EB]/80 p-2.5 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] transition">
                </div>
                <div class="flex flex-col">
                    <label class="text-[11px] font-bold text-slate-500 mb-1">Colors (e.g. Black: 10, Gold: 5)</label>
                    <input type="text" name="colors" placeholder="e.g. Black: 10, Gold: 5" required class="w-full bg-slate-50/50 border border-[#BAD6EB]/80 p-2.5 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] transition">
                </div>
                <div class="flex flex-col">
                    <label class="text-[11px] font-bold text-slate-500 mb-1">Stock Units (Auto-calculated)</label>
                    <input type="number" name="stock" id="add_stock_display" placeholder="0" readonly class="w-full bg-slate-100 border border-[#BAD6EB]/80 p-2.5 rounded-xl text-sm font-medium text-slate-500 cursor-not-allowed">
                </div>
                <div class="flex flex-col">
                    <label class="text-[11px] font-bold text-slate-500 mb-1">Price (P)</label>
                    <input type="number" step="0.01" name="price" placeholder="0.00" required class="w-full bg-slate-50/50 border border-[#BAD6EB]/80 p-2.5 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#BAD6EB] transition">
                </div>
                <div class="flex flex-col">
                    <label class="text-[11px] font-bold text-slate-500 mb-1">Product Image</label>
                    <input type="file" name="product_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#D8E0F2] file:text-slate-700 hover:file:bg-[#BAD6EB] file:transition cursor-pointer">
                </div>
                <div class="md:col-span-6 pt-2">
                    <button type="submit" name="add_item" class="w-full bg-[#BAD6EB] hover:bg-[#a4c5de] active:bg-[#90b6d7] text-slate-800 py-3 rounded-xl text-sm font-bold transition shadow-md hover:shadow-lg flex items-center justify-center space-x-2 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-slate-700"></i>
                        <span>Save Stock Entry</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Inventory List Table Card[cite: 26, 27] -->
        <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-xl border border-white p-6 md:p-8">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full bg-[#BAD6EB]"></div>
                    <h3 class="font-extrabold text-slate-800 text-lg tracking-tight">
                        <i class="fa-solid fa-table-list text-slate-600 mr-1.5"></i> Stock Inventory List (Editable)
                    </h3>
                </div>
                <span class="text-xs text-slate-400 font-medium">Click Edit to modify details</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-[#D8E0F2]/50 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-xl">
                            <th class="p-4 rounded-l-xl">ID</th>
                            <th class="p-4">Image</th>
                            <th class="p-4">Product Name</th>
                            <th class="p-4">Category</th>
                            <th class="p-4">Available Colors</th>
                            <th class="p-4">Stock Units</th>
                            <th class="p-4">Price</th>
                            <th class="p-4 rounded-r-xl text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php
                            // Query inventory and count pending pre-orders for each product name
                            $inv_res = $conn->query("
                                SELECT i.*, 
                                (SELECT COUNT(*) FROM orders o WHERE o.product_name = i.product_name AND (o.status = 'Pending' OR o.status = 'Reserved')) as pending_count 
                                FROM inventory i 
                                ORDER BY i.id ASC
                            ");

                            if ($inv_res && $inv_res->num_rows > 0) {
                                while($row = $inv_res->fetch_assoc()) {
                                    $stock_color = $row['stock'] > 5 ? 'text-slate-800 font-bold' : 'text-rose-600 font-bold';
                                    $img_thumb = !empty($row['image']) && file_exists("upload/" . $row['image']) ? "upload/" . htmlspecialchars($row['image']) : "upload/default_placeholder.jpg";
                                    
                                    $item_colors = isset($row['colors']) ? htmlspecialchars($row['colors'], ENT_QUOTES) : '';
                                    $display_colors = !empty($item_colors) ? $item_colors : 'N/A';

                                    // Create a pending badge note if there are pending orders
                                    $pending_badge = "";
                                    if ($row['pending_count'] > 0) {
                                        $pending_badge = "<span class='ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200 shadow-xs animate-pulse'>
                                            <i class='fa-solid fa-clock mr-1'></i> " . $row['pending_count'] . " Pending Pre-Order(s)
                                        </span>";
                                    }

                                    echo "<tr class='hover:bg-[#D8E0F2]/20 transition-colors duration-150'>
                                        <td class='p-4 font-bold text-slate-800'>#{$row['id']}</td>
                                        <td class='p-4'>
                                            <img src='{$img_thumb}' class='w-12 h-12 object-cover rounded-xl border border-[#BAD6EB]/60 shadow-xs bg-slate-100'>
                                        </td>
                                        <td class='p-4 font-extrabold text-slate-800'>
                                            {$row['product_name']} {$pending_badge}
                                        </td>
                                        <td class='p-4'>
                                            <span class='px-2.5 py-1 text-xs rounded-lg bg-[#D8E0F2]/60 border border-[#BAD6EB]/40 font-semibold text-slate-700'>
                                                {$row['category']}
                                            </span>
                                        </td>
                                        <td class='p-4 text-xs font-semibold text-slate-600'>{$display_colors}</td>
                                        <td class='p-4 {$stock_color}'>{$row['stock']} units</td>
                                        <td class='p-4 font-extrabold text-slate-800'>₱" . number_format($row['price'], 2) . "</td>
                                        <td class='p-4 text-center space-x-2'>
                                            <button onclick='openEditModal({$row['id']}, \"" . htmlspecialchars($row['product_name'], ENT_QUOTES) . "\", \"" . htmlspecialchars($row['category'], ENT_QUOTES) . "\", {$row['stock']}, {$row['price']}, \"{$item_colors}\")' class='text-slate-800 bg-[#BAD6EB] hover:bg-[#a4c5de] px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center space-x-1 cursor-pointer'>
                                                <i class='fa-solid fa-pen-to-square text-[11px]'></i>
                                                <span>Edit</span>
                                            </button>
                                            <a href='inventory.php?delete_id={$row['id']}' onclick='return confirm(\"Delete this item?\")' class='text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/60 px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center space-x-1'>
                                                <i class='fa-solid fa-trash text-[11px]'></i>
                                                <span>Delete</span>
                                            </a>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr>
                                    <td colspan='8' class='p-12 text-center text-slate-400'>
                                        <div class='w-16 h-16 rounded-2xl bg-[#D8E0F2]/50 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl'>
                                            <i class='fa-solid fa-box-open'></i>
                                        </div>
                                        <p class='font-medium text-slate-500'>No inventory items found.</p>
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

<!-- Edit Modal with Color and Image Upload[cite: 29, 30] -->
<div id="editModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50 transition-opacity">
    <div class="bg-white/95 backdrop-blur-xl p-8 rounded-3xl shadow-2xl max-w-md w-full border border-white transform transition-all">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 rounded-full bg-[#BAD6EB]"></div>
                <h3 class="font-extrabold text-slate-800 text-lg tracking-tight">
                    <i class="fa-solid fa-pen-to-square text-slate-600 mr-1.5"></i> Edit Stock Item
                </h3>
            </div>
            <button onclick="closeEditModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-600 hover:bg-slate-200 flex items-center justify-center transition cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="item_id" id="edit_item_id">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Product Name</label>
                <input type="text" name="product_name" id="edit_product_name" required class="w-full bg-slate-50 border border-[#BAD6EB] p-2.5 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#BAD6EB]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Category</label>
                <input type="text" name="category" id="edit_category" required class="w-full bg-slate-50 border border-[#BAD6EB] p-2.5 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#BAD6EB]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Available Colors</label>
                <input type="text" name="colors" id="edit_colors" required class="w-full bg-slate-50 border border-[#BAD6EB] p-2.5 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#BAD6EB]">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Stock Units (Auto-calculated)</label>
                    <input type="number" name="stock" id="edit_stock" readonly class="w-full bg-slate-100 border border-[#BAD6EB] p-2.5 rounded-xl text-sm font-medium text-slate-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Price (P)</label>
                    <input type="number" step="0.01" name="price" id="edit_price" required class="w-full bg-slate-50 border border-[#BAD6EB] p-2.5 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#BAD6EB]">
                </div>
                </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Update Image (Optional)</label>
                <input type="file" name="edit_product_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#D8E0F2] file:text-slate-700 hover:file:bg-[#BAD6EB] file:transition cursor-pointer">
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeEditModal()" class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-600 py-3 rounded-xl text-xs font-bold transition cursor-pointer">Cancel</button>
                <button type="submit" name="update_item" class="w-1/2 bg-[#BAD6EB] hover:bg-[#a4c5de] active:bg-[#90b6d7] text-slate-800 py-3 rounded-xl text-xs font-bold transition shadow-md cursor-pointer">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Helper function to calculate total stock from color string (e.g. "Black: 10, Gold: 5" -> 15)
    function calculateTotalStock(colorsString) {
        let total = 0;
        if (!colorsString) return total;
        
        const parts = colorsString.split(',');
        parts.forEach(part => {
            const sub = part.split(':');
            if (sub.length === 2) {
                const qty = parseInt(sub[1].trim());
                if (!isNaN(qty)) {
                    total += qty;
                }
            }
        });
        return total;
    }

    // Live auto-calculation listener for Add Form
    const addColorsInput = document.querySelector('input[name="colors"]');
    const addStockInput = document.getElementById('add_stock_display');
    if (addColorsInput && addStockInput) {
        addColorsInput.addEventListener('input', function() {
            addStockInput.value = calculateTotalStock(this.value);
        });
    }

    // Live auto-calculation listener for Edit Modal
    const editColorsInput = document.getElementById('edit_colors');
    const editStockInput = document.getElementById('edit_stock');
    if (editColorsInput && editStockInput) {
        editColorsInput.addEventListener('input', function() {
            editStockInput.value = calculateTotalStock(this.value);
        });
    }

    function openEditModal(id, name, category, stock, price, colors) {
        document.getElementById('edit_item_id').value = id;
        document.getElementById('edit_product_name').value = name;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_colors').value = colors || '';
        
        // Compute stock live when opening modal
        const computedStock = calculateTotalStock(colors);
        document.getElementById('edit_stock').value = computedStock > 0 ? computedStock : stock;
        
        document.getElementById('edit_price').value = price;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
</body>
</html>