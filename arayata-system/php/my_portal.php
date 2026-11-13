<?php
include 'db.php';
session_cache_limiter('private_no_expire');
session_start();

// Strict Security: Allow Customers, Admins, or Staff to view their portal/dashboard
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Customer', 'Admin', 'Staff'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Valued User';
$user_role = $_SESSION['role'];

$msg = "";
if (isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

$assigned_queue = "";

// Handle Patient Self-Registration from the Portal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_patient'])) {
    $fname = $conn->real_escape_string($_POST['first_name']);
    $lname = $conn->real_escape_string($_POST['last_name']);
    $age = (int)$_POST['age'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $contact = $conn->real_escape_string($_POST['contact_number']);
    
    // Generate automatic Queue Number
    $q_res = $conn->query("SELECT MAX(id) as max_id FROM patients");
    $next_id = ($q_res ? $q_res->fetch_assoc()['max_id'] : 0) + 1;
    $assigned_queue = "Q-" . (100 + $next_id);
    
    // Save with default status 'Processing'
    $conn->query("INSERT INTO patients (first_name, last_name, age, gender, contact_number, queue_no, status) VALUES ('$fname', '$lname', $age, '$gender', '$contact', '$assigned_queue', 'Processing')");
    
    $_SESSION['flash_msg'] = "Registration successful! Your Queue Number is <strong>" . $assigned_queue . "</strong>. Please wait to be called.";
    header("Location: my_portal.php");
    exit();
}

// Handle Pre-Order / Reservation Placement from Portal Modal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order_modal'])) {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $p_name = $conn->real_escape_string($_POST['product_name']);
    $p_cat = $conn->real_escape_string($_POST['product_category']);
    $p_price = (float)$_POST['product_price'];
    $selected_color = trim($_POST['selected_color']);
    
    // 1. Save to orders table with the chosen color & reservation status
    $conn->query("INSERT INTO orders (user_id, product_name, category, price, color, status, item_id) VALUES ('$user_id', '$p_name', '$p_cat', '$p_price', '$selected_color', 'Reserved', '$product_id')");
    
    // 2. Fetch current product colors and stock string from inventory using ID
    if ($product_id > 0) {
        $inv_check = $conn->query("SELECT colors, stock FROM inventory WHERE id = $product_id");
        if ($inv_check && $row = $inv_check->fetch_assoc()) {
            $colors_str = $row['colors'];
            $updated_colors_arr = [];
            $new_total_stock = 0;
            
            $parts = explode(',', $colors_str);
            foreach ($parts as $part) {
                $sub = explode(':', $part);
                if (count($sub) == 2) {
                    $c_name = trim($sub[0]);
                    $c_qty = (int)trim($sub[1]);
                    
                    if (strcasecmp($c_name, $selected_color) == 0 && $c_qty > 0) {
                        $c_qty--;
                    }
                    
                    if ($c_qty > 0) {
                        $updated_colors_arr[] = "$c_name: $c_qty";
                        $new_total_stock += $c_qty;
                    }
                }
            }
            
            $new_colors_str = $conn->real_escape_string(implode(',', $updated_colors_arr));
            
            // 3. Update database inventory table instantly
            $conn->query("UPDATE inventory SET colors = '$new_colors_str', stock = $new_total_stock WHERE id = $product_id");
        }
    }
    
    $_SESSION['flash_msg'] = "Pre-order reservation for <strong>" . htmlspecialchars($p_name) . " (" . htmlspecialchars($selected_color) . ")</strong> successfully placed!";
    header("Location: my_portal.php");
    exit();
}

// Fetch recent customer frame reservations for this user
$orders_result = $conn->query("SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY id DESC LIMIT 5");

// Release session lock immediately so navigation remains fast and error-free
session_write_close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Portal Dashboard - Arayata Optical System</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --venus: #BAD6EB;
    --meteor: #D8E0F2;
}
</style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-[#e2ebf0]/30 to-[#f6f8fb] font-sans h-screen flex overflow-hidden">
<!-- Portal Sidebar Navigation (Venus & Meteor Theme) -->
<aside class="w-64 bg-gradient-to-b from-[#BAD6EB] via-[#C9DCED] to-[#D8E0F2] text-slate-800 flex flex-col justify-between hidden md:flex shadow-2xl border-r border-white/60">
<div>
<div class="p-6 border-b border-white/50 flex items-center space-x-3.5 bg-white/30 backdrop-blur-md">
<div class="bg-slate-800 text-[#BAD6EB] p-2.5 rounded-xl font-bold text-xl shadow-md ring-2 ring-white/40">
<i class="fa-solid fa-glasses"></i>
</div>
<div>
<h1 class="text-slate-900 font-extrabold text-base tracking-wide leading-tight">Patient Portal</h1>
<span class="text-[11px] text-slate-600 font-bold tracking-wider uppercase">Arayata Optical</span>
</div>
</div>
<nav class="p-4 space-y-1.5 text-sm">
<a href="my_portal.php" class="group flex items-center space-x-3 p-3 rounded-xl bg-slate-800 text-white shadow-lg shadow-slate-800/20 border-l-4 border-slate-900 font-bold translate-x-1">
<div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center text-white transition shadow-sm">
<i class="fa-solid fa-house text-xs"></i>
</div>
<span>Dashboard</span>
</a>
<a href="#product-catalog" class="group flex items-center space-x-3 p-3 rounded-xl hover:bg-white/50 hover:text-slate-900 text-slate-700 transition-all duration-200 font-semibold border-l-4 border-transparent hover:translate-x-1">
<div class="w-7 h-7 rounded-lg bg-white/60 flex items-center justify-center text-slate-700 group-hover:bg-slate-800 group-hover:text-[#BAD6EB] transition shadow-sm">
<i class="fa-solid fa-store text-xs"></i>
</div>
<span>Browse Frames</span>
</a>
<a href="patient_face_shape.php" class="group flex items-center space-x-3 p-3 rounded-xl hover:bg-white/50 hover:text-slate-900 text-slate-700 transition-all duration-200 font-semibold border-l-4 border-transparent hover:translate-x-1">
<div class="w-7 h-7 rounded-lg bg-white/60 flex items-center justify-center text-slate-700 group-hover:bg-slate-800 group-hover:text-[#BAD6EB] transition shadow-sm">
<i class="fa-solid fa-face-smile text-xs"></i>
</div>
<span>Face Shape AI</span>
</a>
<a href="patient_virtual_tryon.php" class="group flex items-center space-x-3 p-3 rounded-xl hover:bg-white/50 hover:text-slate-900 text-slate-700 transition-all duration-200 font-semibold border-l-4 border-transparent hover:translate-x-1">
<div class="w-7 h-7 rounded-lg bg-white/60 flex items-center justify-center text-slate-700 group-hover:bg-slate-800 group-hover:text-[#BAD6EB] transition shadow-sm">
<i class="fa-solid fa-glasses text-xs"></i>
</div>
<span>Virtual Try-On</span>
</a>
</nav>
</div>
<div class="p-4 border-t border-white/50 bg-white/25">
<a href="logout.php" class="flex items-center space-x-3 p-3 rounded-xl bg-white/60 hover:bg-slate-800 hover:text-white text-slate-800 transition-all duration-300 font-bold shadow-sm border border-white/60 hover:shadow-lg">
<i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
<span>Sign Out</span>
</a>
</div>
</aside>

<!-- Main Content Area -->
<div class="flex-1 flex flex-col overflow-y-auto">
<header class="bg-white/80 backdrop-blur-md border-b border-slate-200 h-20 flex items-center px-8 shadow-sm justify-between sticky top-0 z-40">
<h2 class="text-xl font-black text-slate-800 flex items-center tracking-tight">
<span class="w-9 h-9 rounded-xl bg-[#BAD6EB]/40 text-slate-800 flex items-center justify-center mr-3 shadow-inner">
<i class="fa-solid fa-house text-sm"></i>
</span>
Patient Dashboard
</h2>
<div class="flex items-center space-x-4">
<span class="text-xs font-extrabold bg-[#BAD6EB]/40 text-slate-800 px-3.5 py-1.5 rounded-full border border-[#BAD6EB] uppercase tracking-wider">Role: <?php echo htmlspecialchars($user_role); ?></span>
<a href="logout.php" class="text-sm text-red-600 md:hidden font-semibold">Sign Out</a>
</div>
</header>

<main class="p-8 space-y-8">
<?php if ($msg): ?>
<div class="bg-emerald-600 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between">
<div class="flex items-center">
<i class="fa-solid fa-circle-check mr-3 text-lg"></i>
<span class="text-sm font-bold"><?php echo $msg; ?></span>
</div>
</div>
<?php endif; ?>

<!-- Welcome Banner -->
<div class="bg-gradient-to-r from-slate-800 via-slate-900 to-blue-950 rounded-2xl p-8 text-white shadow-xl relative overflow-hidden">
<div class="absolute right-0 top-0 bottom-0 w-1/3 bg-white/5 skew-x-12 pointer-events-none"></div>
<h1 class="text-2xl sm:text-3xl font-black mb-2 tracking-tight">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
<p class="text-slate-300 text-sm max-w-2xl font-medium leading-relaxed">Explore our premium optical frame collection, reserve your preferred frame colors, register for your walk-in queue, or test frames virtually.</p>
</div>

<!-- Patient Queue Registration Section -->
<div class="bg-white/90 backdrop-blur-md p-8 rounded-2xl shadow-sm border border-slate-200 space-y-6">
<div class="flex items-center space-x-4 border-b border-slate-100 pb-4">
<div class="w-12 h-12 bg-[#BAD6EB]/40 rounded-2xl flex items-center justify-center text-xl text-slate-800 font-bold shadow-inner">
<i class="fa-solid fa-user-plus"></i>
</div>
<div>
<h3 class="text-base font-extrabold text-slate-900">Walk-In & Queue Registration</h3>
<p class="text-xs text-slate-400 mt-0.5">Please fill in your details to obtain a consultation queue number.</p>
</div>
</div>
<form method="POST" class="space-y-4">
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div>
<label class="block text-xs font-bold text-slate-700 mb-1">First Name</label>
<input type="text" name="first_name" placeholder="Enter first name" required class="w-full border border-slate-200 p-3 rounded-xl text-sm focus:outline-none focus:border-[#8EBBDC] focus:ring-2 focus:ring-[#BAD6EB]/30 transition bg-slate-50/50">
</div>
<div>
<label class="block text-xs font-bold text-slate-700 mb-1">Last Name</label>
<input type="text" name="last_name" placeholder="Enter last name" required class="w-full border border-slate-200 p-3 rounded-xl text-sm focus:outline-none focus:border-[#8EBBDC] focus:ring-2 focus:ring-[#BAD6EB]/30 transition bg-slate-50/50">
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div>
<label class="block text-xs font-bold text-slate-700 mb-1">Age</label>
<input type="number" name="age" placeholder="Enter age" required class="w-full border border-slate-200 p-3 rounded-xl text-sm focus:outline-none focus:border-[#8EBBDC] focus:ring-2 focus:ring-[#BAD6EB]/30 transition bg-slate-50/50">
</div>
<div>
<label class="block text-xs font-bold text-slate-700 mb-1">Gender</label>
<select name="gender" class="w-full border border-slate-200 p-3 rounded-xl text-sm focus:outline-none focus:border-[#8EBBDC] focus:ring-2 focus:ring-[#BAD6EB]/30 transition bg-white font-medium text-slate-700">
<option value="Male">Male</option>
<option value="Female">Female</option>
</select>
</div>
</div>
<div>
<label class="block text-xs font-bold text-slate-700 mb-1">Contact Number</label>
<input type="text" name="contact_number" placeholder="Enter contact number" required class="w-full border border-slate-200 p-3 rounded-xl text-sm focus:outline-none focus:border-[#8EBBDC] focus:ring-2 focus:ring-[#BAD6EB]/30 transition bg-slate-50/50">
</div>
<button type="submit" name="register_patient" class="w-full bg-[#BAD6EB] hover:bg-[#a3c7e3] text-slate-900 py-3.5 rounded-xl text-sm font-extrabold transition shadow-md cursor-pointer">
Register & Get Queue Number
</button>
</form>
</div>

<!-- Product Catalog / Browse Frames Section -->
<div id="product-catalog" class="bg-white/90 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200 p-8">
<div class="flex justify-between items-center mb-6">
<div>
<h3 class="text-base font-extrabold text-slate-900 flex items-center">
<i class="fa-solid fa-store text-blue-600 mr-2.5"></i> Browse Product Catalog & Frames
</h3>
<p class="text-xs text-slate-400 mt-0.5">Choose from our premium selection of optical frames for pre-order.</p>
</div>
<span class="text-xs bg-[#BAD6EB]/40 text-slate-800 px-3.5 py-1.5 rounded-full font-bold border border-[#BAD6EB]">Select your style</span>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
<?php
$catalog_query = $conn->query("SELECT * FROM inventory ORDER BY id ASC");
if ($catalog_query && $catalog_query->num_rows > 0) {
    while ($row = $catalog_query->fetch_assoc()) {
        $stock_display = $row['stock'] > 0 
            ? "<span class='font-bold text-emerald-600'>Available: " . $row['stock'] . " units</span>" 
            : "<span class='font-semibold text-rose-500'>Out of Stock</span>";
        
        $available_colors = !empty($row['colors']) ? $row['colors'] : 'Standard';
        $safe_colors = htmlspecialchars($available_colors, ENT_QUOTES);
        $safe_name = htmlspecialchars($row['product_name'], ENT_QUOTES);
        $safe_cat = htmlspecialchars($row['category'], ENT_QUOTES);
        
        $order_btn = $row['stock'] > 0 
            ? "<button type='button' onclick=\"openCheckoutModal(" . $row['id'] . ", '" . $safe_name . "', '" . $safe_cat . "', " . $row['price'] . ", '" . $safe_colors . "')\" class='bg-slate-800 hover:bg-slate-900 text-white text-xs px-4 py-2 rounded-xl transition font-bold shadow-sm cursor-pointer'>Pre-Order</button>" 
            : "<button type='button' disabled class='bg-slate-200 text-slate-400 font-bold cursor-not-allowed text-xs px-4 py-2 rounded-xl'>Sold Out</button>";
        
        $img_src = !empty($row['image']) ? "upload/" . $row['image'] : "upload/default_placeholder.jpg";
        
        echo "<div class='bg-gradient-to-br from-slate-50 to-[#BAD6EB]/10 p-5 rounded-2xl border border-slate-200 flex flex-col justify-between hover:border-[#8EBBDC] hover:shadow-md transition'>
            <div>
                <span class='bg-[#BAD6EB]/50 text-slate-800 font-black uppercase text-[10px] px-2.5 py-1 rounded-full tracking-wider'>" . $row['category'] . "</span>
                <div class='mt-3 mb-3 h-32 w-full bg-white rounded-xl overflow-hidden flex items-center justify-center border border-slate-200 shadow-xs'>
                    <img src='" . $img_src . "' class='object-cover h-full w-full' alt=''>
                </div>
                <h4 class='font-extrabold text-slate-900 text-base mt-3 leading-snug'>" . htmlspecialchars($row['product_name']) . "</h4>
                <p class='text-xs text-slate-500 mt-1 font-medium'>" . $stock_display . "</p>
            </div>
            <div class='mt-5 pt-3 border-t border-slate-200 flex justify-between items-center'>
                <span class='text-sm font-black text-slate-900'>P" . number_format($row['price'], 2) . "</span>
                " . $order_btn . "
            </div>
        </div>";
    }
} else {
    echo "<div class='col-span-full text-center py-10 text-slate-400 font-medium'>No products available in the inventory yet.</div>";
}
?>
</div>
</div>

<!-- Recent Pre-Orders History Table -->
<div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200 p-8">
<h3 class="text-base font-extrabold text-slate-900 mb-6 flex items-center">
<i class="fa-solid fa-shopping-cart text-blue-600 mr-2.5"></i> Your Frame Pre-Orders & Reservation Status
</h3>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse text-sm">
<thead>
<tr class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider font-bold border-b border-slate-200">
<th class="p-3.5 rounded-l-xl">Order ID</th>
<th class="p-3.5">Frame Name</th>
<th class="p-3.5">Category</th>
<th class="p-3.5">Color</th>
<th class="p-3.5">Price</th>
<th class="p-3.5 rounded-r-xl text-center">Status</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
<?php if ($orders_result && $orders_result->num_rows > 0): ?>
<?php while ($ord = $orders_result->fetch_assoc()): ?>
<tr class="hover:bg-slate-50/50 transition">
<td class="p-3.5 font-extrabold text-slate-500">#<?php echo $ord['id']; ?></td>
<td class="p-3.5 font-bold text-slate-900"><?php echo htmlspecialchars($ord['product_name']); ?></td>
<td class="p-3.5 text-slate-600"><?php echo htmlspecialchars($ord['category']); ?></td>
<td class="p-3.5 text-slate-600 font-semibold"><?php echo htmlspecialchars($ord['color'] ?? 'Standard'); ?></td>
<td class="p-3.5 text-slate-900 font-bold">P<?php echo number_format($ord['price'], 2); ?></td>
<td class="p-3.5 text-center">
<span class="bg-[#BAD6EB]/50 text-slate-800 px-3 py-1 rounded-full font-bold text-xs uppercase tracking-wider"><?php echo htmlspecialchars($ord['status']); ?></span>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="6" class="py-10 text-center text-slate-400 font-medium">No frame pre-orders placed yet. Click "Pre-Order" on any frame above!</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</main>
</div>

<!-- Pre-Order / Reservation Modal -->
<div id="checkoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center p-4 z-50 backdrop-blur-sm">
<div class="bg-white p-8 rounded-3xl shadow-2xl max-w-md w-full space-y-6 border border-slate-100">
<div class="flex justify-between items-center border-b border-slate-100 pb-4">
<h3 class="font-black text-slate-900 text-lg flex items-center">
<i class="fa-solid fa-bookmark text-blue-600 mr-2.5"></i> Confirm Frame Reservation
</h3>
<button onclick="closeCheckoutModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
<i class="fa-solid fa-xmark text-xl"></i>
</button>
</div>
<div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 text-sm space-y-1">
<p class="text-[11px] text-slate-500 font-black uppercase tracking-wider">Item Summary</p>
<p class="font-extrabold text-slate-900 text-base" id="modal_product_name"></p>
<p class="text-blue-700 font-black text-lg" id="modal_product_price"></p>
</div>
<form method="POST" class="space-y-4">
<input type="hidden" name="product_id" id="input_product_id">
<input type="hidden" name="product_name" id="input_product_name">
<input type="hidden" name="product_category" id="input_product_category">
<input type="hidden" name="product_price" id="input_product_price">
<div>
<label class="block text-xs font-bold text-slate-700 mb-1">Select Frame Color</label>
<select name="selected_color" id="modal_color_select" required class="w-full border border-slate-200 p-3 rounded-xl text-sm focus:outline-none focus:border-[#8EBBDC] bg-white font-medium text-slate-700 cursor-pointer">
<!-- Populated dynamically via JS -->
</select>
</div>
<div class="flex space-x-3 pt-2">
<button type="button" onclick="closeCheckoutModal()" class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 rounded-xl text-sm font-bold transition cursor-pointer">Cancel</button>
<button type="submit" name="place_order_modal" class="w-1/2 bg-slate-800 hover:bg-slate-900 text-white py-3 rounded-xl text-sm font-bold transition shadow-md cursor-pointer">Confirm Reservation</button>
</div>
</form>
</div>
</div>

<!-- Javascript for Pre-Order Modal & Color Selection -->
<script>
function openCheckoutModal(id, name, category, price, colorsString) {
    document.getElementById('input_product_id').value = id;
    document.getElementById('modal_product_name').innerText = name;
    document.getElementById('modal_product_price').innerText = 'P' + parseFloat(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('input_product_name').value = name;
    document.getElementById('input_product_category').value = category;
    document.getElementById('input_product_price').value = price;
    
    const colorSelect = document.getElementById('modal_color_select');
    colorSelect.innerHTML = '';
    
    const colorsArray = colorsString.split(',');
    colorsArray.forEach(colorItem => {
        const trimmedItem = colorItem.trim();
        if (trimmedItem) {
            const sub = trimmedItem.split(':');
            const colorName = sub[0] ? sub[0].trim() : trimmedItem;
            const colorStock = sub[1] ? parseInt(sub[1].trim()) : 0;
            
            const opt = document.createElement('option');
            opt.value = colorName;
            if (colorStock > 0) {
                opt.innerText = colorName + ' (Available: ' + colorStock + ' units)';
            } else {
                opt.innerText = colorName + ' (Out of Stock)';
                opt.disabled = true;
            }
            colorSelect.appendChild(opt);
        }
    });
    
    document.getElementById('checkoutModal').classList.remove('hidden');
}

function closeCheckoutModal() {
    document.getElementById('checkoutModal').classList.add('hidden');
}
</script>
</body>
</html>