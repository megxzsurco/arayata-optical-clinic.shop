<?php
// Ensure database connection exists before counting pending/reserved orders
if (isset($conn)) {
    $badge_query = $conn->query("SELECT COUNT(*) as pending_count FROM orders WHERE status = 'Pending' OR status = 'Reserved'");
    if ($badge_query) {
        $badge_row = $badge_query->fetch_assoc();
        $pending_orders = $badge_row['pending_count'];
    } else {
        $pending_orders = 0;
    }
} else {
    $pending_orders = 0;
}
?>
<aside class="w-64 bg-gradient-to-b from-[#BAD6EB] via-[#C9DCED] to-[#D8E0F2] text-slate-800 flex flex-col justify-between hidden md:flex shadow-2xl border-r border-white/60">
<div>
    <!-- Brand Header -->
    <div class="p-6 border-b border-white/50 flex items-center space-x-3.5 bg-white/30 backdrop-blur-md">
        <div class="bg-slate-800 text-[#BAD6EB] p-2.5 rounded-xl font-bold text-xl shadow-md ring-2 ring-white/40">
            <i class="fa-solid fa-glasses"></i>
        </div>
        <div>
            <h1 class="text-slate-900 font-extrabold text-base leading-tight tracking-wide">Arayata Optical</h1>
            <span class="text-[11px] text-slate-600 font-bold tracking-wider uppercase">Admin Control Panel</span>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="p-4 space-y-2 text-sm overflow-y-auto h-[calc(100vh-160px)] custom-scrollbar">
    <?php
    $nav_items = [
        ['href' => 'index.php', 'icon' => 'fa-chart-pie', 'label' => 'Dashboard'],
        ['href' => 'patients.php', 'icon' => 'fa-user-injured', 'label' => 'Patients'],
        ['href' => 'appointments.php', 'icon' => 'fa-calendar-days', 'label' => 'Appointments'],
        ['href' => 'eye_exam.php', 'icon' => 'fa-eye', 'label' => 'Eye Examination'],
        ['href' => 'inventory.php', 'icon' => 'fa-boxes-stacked', 'label' => 'Inventory'],
        ['href' => 'product_catalog.php', 'icon' => 'fa-store', 'label' => 'Product Catalog'],
        ['href' => 'manage_tryon_frames.php', 'icon' => 'fa-glasses', 'label' => 'Try-On Management'], // <--- Idinagdag natin dito
        ['href' => 'online_orders.php', 'icon' => 'fa-cart-shopping', 'label' => 'Online Orders', 'badge' => $pending_orders],
        ['href' => 'pickup_schedule.php', 'icon' => 'fa-clock', 'label' => 'Pickup Schedule'],
        ['href' => 'pos.php', 'icon' => 'fa-cash-register', 'label' => 'POS / Sales'],
        ['href' => 'user_management.php', 'icon' => 'fa-users-gear', 'label' => 'User Management']
    ];

    $current_page = basename($_SERVER['PHP_SELF']);

    foreach ($nav_items as $item) {
        $isActive = ($current_page == $item['href']);
        
        $activeClass = $isActive
            ? "bg-slate-800 text-white shadow-lg shadow-slate-800/20 border-l-4 border-slate-900 translate-x-1 font-bold"
            : "hover:bg-white/50 hover:text-slate-900 text-slate-700 hover:translate-x-1 border-l-4 border-transparent font-semibold";

        echo '<a href="' . $item['href'] . '" class="group flex items-center justify-between p-3 rounded-xl transition-all duration-300 ' . $activeClass . '">';
        echo '<div class="flex items-center space-x-3">';
        
        $iconBg = $isActive ? "bg-white/20 text-white" : "bg-white/60 text-slate-700 group-hover:bg-slate-800 group-hover:text-[#BAD6EB]";
        echo '<div class="w-7 h-7 rounded-lg flex items-center justify-center transition shadow-sm ' . $iconBg . '">';
        echo '<i class="fa-solid ' . $item['icon'] . ' text-xs"></i>';
        echo '</div>';
        echo '<span>' . $item['label'] . '</span>';
        echo '</div>';

        if (isset($item['badge']) && $item['badge'] > 0) {
            echo '<span class="bg-rose-500 text-white text-xs font-black px-2.5 py-0.5 rounded-full shadow-md animate-pulse">';
            echo $item['badge'];
            echo '</span>';
        }

        echo '</a>';
    }
    ?>
    </nav>
</div>

<!-- Sign Out Footer Button -->
<div class="p-4 border-t border-white/50 bg-white/20">
    <a href="logout.php" class="flex items-center space-x-3 p-3 rounded-xl bg-white/60 hover:bg-slate-800 hover:text-white text-slate-800 transition-all duration-300 font-bold shadow-sm border border-white/60 hover:shadow-lg hover:scale-[1.02]">
        <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
        <span>Sign Out</span>
    </a>
</div>
</aside>