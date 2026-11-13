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
$last_receipt = null;

// Handle POS Checkout and Auto-Reduce Inventory Stock
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $cname = $conn->real_escape_string($_POST['customer_name']);
    $item_id = (int)$_POST['item_id'];
    $qty = (int)$_POST['quantity'];
    $method = $conn->real_escape_string($_POST['payment_method']);

    // Fetch product details from inventory for price and stock
    $item_query = $conn->query("SELECT * FROM inventory WHERE id = $item_id");
    if ($item_query && $item_query->num_rows > 0) {
        $item = $item_query->fetch_assoc();
        if ($item['stock'] >= $qty) {
            $total_amount = $item['price'] * $qty;
            $product_name = $item['product_name'];

            // 1. Save to sales table including customer name
            $conn->query("INSERT INTO sales (customer_name, total_amount, payment_method) VALUES ('$cname', $total_amount, '$method')");
            $last_id = $conn->insert_id;

            // 2. Automatically reduce stock in inventory
            $conn->query("UPDATE inventory SET stock = stock - $qty WHERE id = $item_id");

            $msg = "Transaction successful! Sold $qty unit(s) of $product_name. Total: ₱" . number_format($total_amount, 2);
            
            $receipt_query = $conn->query("SELECT * FROM sales WHERE id = $last_id");
            if ($receipt_query) {
                $last_receipt = $receipt_query->fetch_assoc();
                $last_receipt['product_name'] = $product_name;
                $last_receipt['quantity'] = $qty;
            }
        } else {
            $msg = "Error: Not enough stock available! Current stock for {$item['product_name']} is only {$item['stock']}.";
        }
    } else {
        $msg = "Error: Selected product not found in inventory.";
    }
}

// Fetch Reports & Analytics Data
$sales_query = $conn->query("SELECT SUM(total_amount) as total_rev, COUNT(*) as total_trans FROM sales");
$sales_data = $sales_query ? $sales_query->fetch_assoc() : ['total_rev' => 0, 'total_trans' => 0];
$revenue = $sales_data['total_rev'] ?? 0;
$transactions = $sales_data['total_trans'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>POS & Reports - Arayata Optical</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Thermal Receipt Modal & Printing CSS Styles */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.receipt-card {
    background: #fff;
    width: 320px;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    font-family: monospace;
}
.receipt-header {
    text-align: center;
    border-bottom: 1px dashed #ccc;
    padding-bottom: 10px;
    margin-bottom: 10px;
}
.receipt-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    margin-bottom: 6px;
}
.receipt-divider {
    border-top: 1px dashed #ccc;
    margin: 10px 0;
}
.receipt-footer {
    text-align: center;
    font-size: 0.75rem;
    color: #555;
    margin-top: 15px;
    border-top: 1px dashed #ccc;
    padding-top: 10px;
}

@media print {
    body * {
        visibility: hidden;
    }
    #receiptModal, #receiptModal *, #printable-receipt, #printable-receipt * {
        visibility: visible;
    }
    #receiptModal {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: white;
    }
    .no-print {
        display: none !important;
    }
}
</style>
</head>
<body class="bg-slate-50 font-sans flex h-screen overflow-hidden">
<?php
if (file_exists('sidebar.php')) {
    include 'sidebar.php';
}
?>
<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-cash-register text-blue-600 mr-2"></i> POS Sales Counter & Reports</h2>
        <span class="text-sm font-semibold text-slate-600">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></span>
    </header>

    <main class="flex-1 overflow-y-auto p-6 space-y-6">
        <?php if($msg): ?>
        <div class="p-3 rounded-lg text-sm font-semibold shadow-sm flex items-center justify-between <?php echo strpos($msg, 'Error') !== false ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
            <div><i class="fa-solid fa-circle-check mr-2"></i><?php echo $msg; ?></div>
            <?php if($last_receipt): ?>
            <button onclick="window.print()" class="bg-green-700 text-white px-3 py-1 rounded text-xs hover:bg-green-800 transition"><i class="fa-solid fa-print mr-1"></i> Print Receipt</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Analytics Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Recorded Revenue</span>
                    <div class="text-3xl font-extrabold text-green-600 mt-1">₱<?php echo number_format($revenue, 2); ?></div>
                </div>
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-peso-sign"></i>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total POS Transactions</span>
                    <div class="text-3xl font-extrabold text-blue-600 mt-1"><?php echo $transactions; ?></div>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
        </div>

        <!-- Layout: POS Form & Recent Transactions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- POS Retail Checkout Counter Form -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 lg:col-span-1 h-fit">
                <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center">
                    <i class="fa-solid fa-cart-shopping text-blue-600 mr-2"></i> Retail Checkout Counter
                </h3>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Customer Name</label>
                        <input type="text" name="customer_name" placeholder="Enter customer name" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Select Product / Frame</label>
                        <select name="item_id" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">
                            <option value="">-- Choose Product from Inventory --</option>
                            <?php
                            $inv_dropdown = $conn->query("SELECT * FROM inventory WHERE stock > 0 ORDER BY product_name ASC");
                            if ($inv_dropdown) {
                                while($inv = $inv_dropdown->fetch_assoc()) {
                                    echo "<option value='{$inv['id']}'>{$inv['product_name']} (₱" . number_format($inv['price'], 2) . ") - Stock: {$inv['stock']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Quantity</label>
                        <input type="number" name="quantity" value="1" min="1" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500 bg-white">
                            <option value="Cash">Cash</option>
                            <option value="GCash">GCash</option>
                            <option value="Card">Card</option>
                        </select>
                    </div>
                    <button type="submit" name="checkout" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-semibold transition shadow-sm cursor-pointer">
                        Complete Payment & Deduct Stock
                    </button>
                </form>
            </div>

            <!-- Recent POS Transactions History Table with Printable Receipt Button -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 lg:col-span-2">
                <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center">
                    <i class="fa-solid fa-list-check text-blue-600 mr-2"></i> Recent POS Transactions History
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-100 text-slate-600 text-xs uppercase tracking-wider font-bold border-b border-gray-200">
                                <th class="p-3">ID</th>
                                <th class="p-3">Customer Name</th>
                                <th class="p-3">Amount</th>
                                <th class="p-3">Payment</th>
                                <th class="p-3">Date</th>
                                <th class="p-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php
                            $hist_query = $conn->query("SELECT * FROM sales ORDER BY id DESC LIMIT 15");
                            if ($hist_query && $hist_query->num_rows > 0) {
                                while($row = $hist_query->fetch_assoc()) {
                                    $rec_id = '#REC-' . str_pad($row['id'], 4, '0', STR_PAD_LEFT);
                                    $c_name = htmlspecialchars($row['customer_name'], ENT_QUOTES);
                                    $amount = '₱' . number_format($row['total_amount'], 2);
                                    $method = htmlspecialchars($row['payment_method'], ENT_QUOTES);
                                    $date = isset($row['created_at']) ? date('M d, Y h:i A', strtotime($row['created_at'])) : date('M d, Y');

                                    echo "<tr class='hover:bg-slate-50 transition'>
                                        <td class='p-3 font-medium'>#{$row['id']}</td>
                                        <td class='p-3 font-semibold text-slate-800'>{$c_name}</td>
                                        <td class='p-3 font-bold text-green-600'>{$amount}</td>
                                        <td class='p-3'><span class='bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-xs font-semibold'>{$method}</span></td>
                                        <td class='p-3 text-slate-400 text-xs'>{$date}</td>
                                        <td class='p-3 text-right'>
                                            <button onclick=\"openReceipt('{$rec_id}', '{$c_name}', '{$method}', '{$date}', 'COMPLETED', '{$amount}')\" class='bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer'>
                                                <i class='fa-solid fa-receipt mr-1'></i> Receipt
                                            </button>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='p-6 text-center text-slate-400'>No sales transactions found yet.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- INDIVIDUAL THERMAL RECEIPT MODAL -->
<div class="modal-overlay" id="receiptModal">
    <div class="receipt-card">
        <div class="receipt-header">
            <h2 style="font-size: 1rem; font-weight: bold;">ARAYATA OPTICAL</h2>
            <p style="font-size: 0.8rem;">Bacoor City, Cavite</p>
            <p style="font-size: 0.75rem; color: #666;">Official Sales Receipt</p>
        </div>
        <div class="receipt-body">
            <div class="receipt-row">
                <span class="label">Receipt No:</span>
                <span class="val" id="rec_id">#REC-0000</span>
            </div>
            <div class="receipt-row">
                <span class="label">Date:</span>
                <span class="val" id="rec_date">--</span>
            </div>
            <div class="receipt-divider"></div>
            <div class="receipt-row">
                <span class="label">Customer Name:</span>
                <span class="val" id="rec_customer" style="font-weight: bold;">--</span>
            </div>
            <div class="receipt-row">
                <span class="label">Payment:</span>
                <span class="val" id="rec_payment">--</span>
            </div>
            <div class="receipt-row">
                <span class="label">Status:</span>
                <span class="val" style="font-weight: bold; color: green;" id="rec_status">COMPLETED</span>
            </div>
            <div class="receipt-divider"></div>
            <div class="receipt-row" style="font-weight: bold; font-size: 0.9rem;">
                <span class="label">Total Paid:</span>
                <span class="val" id="rec_price">0.00</span>
            </div>
        </div>
        <div class="receipt-footer">
            <strong>THANK YOU FOR CHOOSING ARAYATA!</strong>
            <p>Please come again.</p>
        </div>
        <div class="modal-actions no-print" style="margin-top: 15px; display: flex; justify-content: flex-end; gap: 8px;">
            <button class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer" onclick="closeReceiptModal()">Close</button>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer" onclick="window.print()">Print Receipt</button>
        </div>
    </div>
</div>

<script>
function openReceipt(id, customer, payment, date, status, price) {
    document.getElementById('rec_id').innerText = id;
    document.getElementById('rec_customer').innerText = customer;
    document.getElementById('rec_payment').innerText = payment;
    document.getElementById('rec_date').innerText = date;
    document.getElementById('rec_status').innerText = status;
    document.getElementById('rec_price').innerText = price;
    document.getElementById('receiptModal').style.display = 'flex';
}

function closeReceiptModal() {
    document.getElementById('receiptModal').style.display = 'none';
}

window.onclick = function(e) {
    const modal = document.getElementById('receiptModal');
    if (e.target === modal) {
        closeReceiptModal();
    }
}
</script>
</body>
</html>