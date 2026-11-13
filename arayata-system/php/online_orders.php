<?php
include 'db.php';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $oid = (int)$_POST['order_id'];
    $status = $conn->real_escape_string($_POST['status']);
    
    // Kunin ang lumang detalye ng order bago i-update
    $check_q = $conn->query("SELECT * FROM orders WHERE id = $oid");
    if ($check_q && $check_q->num_rows > 0) {
        $order_row = $check_q->fetch_assoc();
        $old_status = $order_row['status'];
        
        // Huwag nang hayaang i-update kung Completed o Cancelled na dati pa
        if ($old_status === 'Completed' || $old_status === 'Cancelled') {
            header("Location: online_orders.php");
            exit();
        }
        
        $ordered_color = trim($order_row['color'] ?? '');
        $product_name = $conn->real_escape_string($order_row['product_name']);
        
        // 1. I-update ang status sa orders table
        $conn->query("UPDATE orders SET status='$status' WHERE id=$oid");
        
        $item_id = 0;
        if (array_key_exists('item_id', $order_row) && $order_row['item_id'] !== null) {
            $item_id = (int)$order_row['item_id'];
        }
        
        // 2. Kapag ginawang 'Completed' ang order
        if ($status === 'Completed' && $old_status !== 'Completed') {
            $price = $order_row['price'];
            $user_id = $order_row['user_id'];
            
            // --- KUNIN ANG TUNAY NA PANGALAN NG USER MULA SA USERS TABLE ---
            $user_q = $conn->query("SELECT name FROM users WHERE id = $user_id LIMIT 1");
            if ($user_q && $user_q->num_rows > 0) {
                $user_data = $user_q->fetch_assoc();
                $cname = $user_data['name']; // Mismong username/pangalan ng customer
            } else {
                $cname = "Customer #" . $user_id;
            }
            
            // I-record sa sales table gamit ang totoong pangalan ng customer para sa POS revenue at resibo
            $conn->query("INSERT INTO sales (customer_name, total_amount, payment_method) VALUES ('$cname', $price, 'Online')");
        }
        
        // 3. Kapag ginawang 'Cancelled' ang order -> Ibalik ang stock sa tamang kulay!
        if ($status === 'Cancelled' && $old_status !== 'Cancelled') {
            $inv_sql = $item_id > 0 ? "SELECT * FROM inventory WHERE id = $item_id" : "SELECT * FROM inventory WHERE product_name = '$product_name' LIMIT 1";
            $inv_res = $conn->query($inv_sql);
            
            if ($inv_res && $inv_res->num_rows > 0) {
                $inv_data = $inv_res->fetch_assoc();
                $inv_id = $inv_data['id'];
                $colors_str = $inv_data['colors'];
                
                $updated_colors_arr = [];
                $new_total_stock = 0;
                $color_found = false;
                
                $parts = explode(',', $colors_str);
                foreach ($parts as $part) {
                    $sub = explode(':', $part);
                    if (count($sub) == 2) {
                        $c_name = trim($sub[0]);
                        $c_qty = (int)trim($sub[1]);
                        
                        if (!empty($ordered_color) && strcasecmp($c_name, $ordered_color) == 0) {
                            $c_qty++;
                            $color_found = true;
                        }
                        
                        if ($c_qty > 0) {
                            $updated_colors_arr[] = "$c_name: $c_qty";
                            $new_total_stock += $c_qty;
                        }
                    }
                }
                
                if (!empty($ordered_color) && !$color_found) {
                    $updated_colors_arr[] = "$ordered_color: 1";
                    $new_total_stock += 1;
                }
                
                $new_colors_str = $conn->real_escape_string(implode(',', $updated_colors_arr));
                $conn->query("UPDATE inventory SET colors = '$new_colors_str', stock = $new_total_stock WHERE id = $inv_id");
            }
        }
    }
    
    $_SESSION['flash_msg'] = "Order fulfillment status updated successfully!";
    header("Location: online_orders.php");
    exit();
}

$msg = "";
if (isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Online Orders - Arayata Optical</title>
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
::-webkit-scrollbar-thumb:hover {
    background: #8EBBDC;
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
<script>
function confirmStatusUpdate(selectElement) {
    const selectedVal = selectElement.value;
    if (selectedVal === 'Completed') {
        return confirm("Are you sure that this order is already COMPLETED? Once updated, it can no longer be changed.");
    } else if (selectedVal === 'Cancelled') {
        return confirm("Are you sure you want to CANCEL this order? This will return the stock to the inventory and it can no longer be changed.");
    }
    return true;
}
</script>
</head>
<body class="h-screen text-slate-700 antialiased flex overflow-hidden relative">
<div class="blob-1 fixed top-20 left-20 w-96 h-96 bg-[#BAD6EB]/40 rounded-full blur-3xl pointer-events-none z-0"></div>
<div class="blob-2 fixed -bottom-20 -right-20 w-96 h-96 bg-[#D8E0F2]/70 rounded-full blur-3xl pointer-events-none z-0"></div>

<?php
if (file_exists('sidebar.php')) {
    include 'sidebar.php';
}
?>

<div class="flex-1 flex flex-col h-screen overflow-hidden z-10">
    <header class="bg-white/95 backdrop-blur-md border-b border-white/85 h-24 flex items-center justify-between px-8 shadow-[0_4px_20px_rgba(186,214,235,0.15)] shrink-0 z-30">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#BAD6EB] to-[#D8E0F2] flex items-center justify-center font-bold shadow-inner border border-white text-slate-800">
                <i class="fa-solid fa-shopping-cart text-base"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Online Orders Processing</h2>
                <p class="text-sm text-slate-500 font-medium mt-0.5">Arayata Optical Fulfillment Center</p>
            </div>
        </div>
        <div class="hidden sm:flex items-center space-x-2.5 px-4 py-2 rounded-2xl bg-white/90 backdrop-blur-md border border-white text-xs font-bold text-slate-700 shadow-sm">
            <i class="fa-solid fa-box-open text-blue-500"></i>
            <span>Order Management</span>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 md:p-8 space-y-8">
        <?php if ($msg): ?>
        <div class="bg-emerald-50/90 backdrop-blur-xl text-emerald-900 p-4 rounded-3xl text-sm border border-emerald-200/80 shadow-[0_10px_25px_-5px_rgba(16,185,129,0.15)] flex items-center space-x-3 transition-all animate-pulse">
            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0 font-bold shadow-2xs">
                <i class="fa-solid fa-check text-sm"></i>
            </div>
            <span class="font-semibold"><?php echo htmlspecialchars($msg); ?></span>
        </div>
        <?php endif; ?>

        <div class="bg-white/90 backdrop-blur-2xl rounded-3xl shadow-[0_20px_50px_-15px_rgba(186,214,235,0.35)] border border-white p-6 md:p-8 space-y-6 transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-3.5 h-3.5 rounded-full bg-[#BAD6EB] shadow-inner"></div>
                    <h3 class="font-bold text-slate-900 text-lg tracking-tight">Recent Frame Orders</h3>
                </div>
                <span class="text-xs text-slate-400 font-medium bg-slate-100 px-3 py-1 rounded-xl border border-slate-200/60 shadow-2xs">Live Database & POS Sync</span>
            </div>

            <div class="max-h-[600px] overflow-y-auto overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-left border-collapse text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-100">
                        <tr class="text-slate-600 text-xs uppercase tracking-wider font-bold border-b border-slate-200">
                            <th class="p-4">Order ID</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Frame Name</th>
                            <th class="p-4">Color</th>
                            <th class="p-4">Category</th>
                            <th class="p-4">Price</th>
                            <th class="p-4">Date Ordered</th>
                            <th class="p-4 text-center">Fulfillment Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium bg-white">
                        <?php
                        $res = $conn->query("SELECT orders.*, 
                            COALESCE(users.name, CONCAT('User #', orders.user_id)) as display_name 
                            FROM orders 
                            LEFT JOIN users ON orders.user_id = users.id 
                            ORDER BY orders.id DESC");

                        if ($res && $res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()) {
                                $customer_display = !empty($row['display_name']) ? $row['display_name'] : 'User #' . $row['user_id'];
                                $order_color = !empty($row['color']) ? htmlspecialchars($row['color']) : 'Standard';
                                $is_locked = ($row['status'] === 'Completed' || $row['status'] === 'Cancelled');
                                
                                echo "<tr class='hover:bg-slate-50/80 transition-colors duration-150'>
                                <td class='p-4 font-bold text-slate-900'>#" . $row['id'] . "</td>
                                <td class='p-4 text-slate-600 font-medium'>
                                    <div class='flex items-center space-x-2.5'>
                                        <div class='w-8 h-8 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-xs font-bold text-slate-700 shadow-2xs'>
                                            <i class='fa-solid fa-user text-[11px]'></i>
                                        </div>
                                        <span class='font-bold text-slate-800'>" . htmlspecialchars($customer_display) . "</span>
                                    </div>
                                </td>
                                <td class='p-4 font-bold text-slate-900'>" . htmlspecialchars($row['product_name']) . "</td>
                                <td class='p-4'>
                                    <span class='px-3 py-1 text-xs rounded-xl bg-blue-50 font-bold text-blue-700 border border-blue-200'>
                                        " . $order_color . "
                                    </span>
                                </td>
                                <td class='p-4'>
                                    <span class='px-3 py-1 text-xs rounded-xl bg-slate-100 font-bold text-slate-700 border border-slate-200'>
                                        " . htmlspecialchars($row['category']) . "
                                    </span>
                                </td>
                                <td class='p-4 font-bold text-slate-900'>P" . number_format($row['price'], 2) . "</td>
                                <td class='p-4 text-slate-500 text-xs font-semibold'>" . $row['created_at'] . "</td>
                                <td class='p-4 text-center'>";
                                
                                if ($is_locked) {
                                    $badge_color = ($row['status'] === 'Completed') ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-rose-100 text-rose-800 border-rose-300';
                                    echo "<span class='px-3 py-1.5 rounded-xl text-xs font-extrabold border " . $badge_color . " inline-block'>
                                        <i class='fa-solid " . (($row['status'] === 'Completed') ? 'fa-check-circle' : 'fa-ban') . " mr-1'></i> " . strtoupper($row['status']) . " (Locked)
                                    </span>";
                                } else {
                                    echo "<form method='POST' class='flex items-center justify-center space-x-2' onsubmit='return confirmStatusUpdate(this.status)'>
                                        <input type='hidden' name='order_id' value='" . $row['id'] . "'>
                                        <select name='status' class='border border-slate-200 p-2.5 rounded-xl text-xs font-bold bg-slate-50/70 text-slate-800 focus:outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-2 focus:ring-[#BAD6EB]/35 shadow-2xs cursor-pointer'>
                                            <option value='Pending' " . ($row['status']=='Pending'?'selected':'') . ">Pending</option>
                                            <option value='Processing' " . ($row['status']=='Processing'?'selected':'') . ">Processing</option>
                                            <option value='Approved' " . ($row['status']=='Approved'?'selected':'') . ">Approved</option>
                                            <option value='Completed' " . ($row['status']=='Completed'?'selected':'') . ">Completed</option>
                                            <option value='Cancelled' " . ($row['status']=='Cancelled'?'selected':'') . ">Cancelled</option>
                                        </select>
                                        <button type='submit' name='update_status' class='bg-[#BAD6EB] hover:bg-[#a3c7e3] active:bg-[#8ebbdc] text-slate-900 px-3.5 py-2.5 rounded-xl text-xs font-bold transition shadow-xs hover:shadow-md flex items-center space-x-1.5 cursor-pointer'>
                                            <i class='fa-solid fa-arrows-rotate text-[10px]'></i>
                                            <span>Update</span>
                                        </button>
                                    </form>";
                                }
                                
                                echo "</td></tr>";
                            }
                        } else {
                            echo "<tr>
                            <td colspan='8' class='p-12 text-center text-slate-400'>
                                <div class='w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 border border-slate-200 shadow-2xs'>
                                    <i class='fa-solid fa-box-open text-2xl'></i>
                                </div>
                                <p class='font-bold text-slate-600'>No online frame orders found yet.</p>
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