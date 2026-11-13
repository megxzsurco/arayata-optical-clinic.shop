<?php
include 'db.php';
$msg = "";

// 1. Add User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $conn->real_escape_string($_POST['role']);
    
    $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$pass', '$role')");
    $msg = "System user created successfully!";
}

// 2. Edit / Update User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $id = (int)$_POST['user_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);
    
    if (!empty($_POST['password'])) {
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET name='$name', email='$email', password='$pass', role='$role' WHERE id=$id");
    } else {
        $conn->query("UPDATE users SET name='$name', email='$email', role='$role' WHERE id=$id");
    }
    $msg = "User account updated successfully!";
}

// 3. Delete User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $id = (int)$_POST['user_id'];
    $conn->query("DELETE FROM users WHERE id=$id");
    $msg = "User account deleted successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users - Arayata Optical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans flex h-screen overflow-hidden">
<?php include 'sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-users-gear text-blue-600 mr-2"></i> User Account Management</h2>
    </header>

    <main class="flex-1 overflow-y-auto p-6 space-y-6">
        <?php if ($msg): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded-lg text-sm border border-green-200 flex items-center shadow-sm">
            <i class="fa-solid fa-check-circle mr-2"></i> <?php echo htmlspecialchars($msg); ?>
        </div>
        <?php endif; ?>

        <!-- Create User Form -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h3 class="font-bold text-slate-800 text-base mb-4">Create System Account</h3>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" name="name" placeholder="Full Name" required class="border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                <input type="email" name="email" placeholder="Email Address" required class="border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                <input type="password" name="password" placeholder="Password" required class="border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                <select name="role" class="border border-gray-300 p-2.5 rounded-lg text-sm bg-white focus:outline-none focus:border-blue-500 cursor-pointer">
                    <option value="Admin">Admin</option>
                    <option value="Doctor">Doctor</option>
                    <option value="Staff">Staff</option>
                    <option value="Customer">Customer</option>
                </select>
                <button type="submit" name="add_user" class="bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-bold transition shadow-sm cursor-pointer">Create Account</button>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h3 class="font-bold text-slate-800 text-base mb-4">System Users List</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase border-b">
                            <th class="p-3">ID</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Password (Hash)</th>
                            <th class="p-3">Role</th>
                            <th class="p-3">Registered</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-slate-700 font-medium">
                        <?php
                        $res = $conn->query("SELECT * FROM users ORDER BY id DESC");
                        if ($res && $res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()) {
                                $pass_preview = htmlspecialchars(substr($row['password'], 0, 25)) . "...";
                                echo "<tr>
                                    <td class='p-3 font-medium'>#{$row['id']}</td>
                                    <td class='p-3 font-bold text-slate-800'>" . htmlspecialchars($row['name']) . "</td>
                                    <td class='p-3'>" . htmlspecialchars($row['email']) . "</td>
                                    <td class='p-3 font-mono text-xs text-slate-400' title='" . htmlspecialchars($row['password']) . "'>$pass_preview</td>
                                    <td class='p-3'><span class='bg-slate-100 text-slate-700 px-2.5 py-1 rounded text-xs font-semibold'>" . htmlspecialchars($row['role']) . "</span></td>
                                    <td class='p-3 text-xs text-slate-500'>" . htmlspecialchars($row['created_at']) . "</td>
                                    <td class='p-3 text-center space-x-2'>
                                        <button onclick=\"openEditModal({$row['id']}, '" . htmlspecialchars($row['name'], ENT_QUOTES) . "', '" . htmlspecialchars($row['email'], ENT_QUOTES) . "', '" . htmlspecialchars($row['role'], ENT_QUOTES) . "')\" class='bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition cursor-pointer'><i class='fa-solid fa-pen-to-square'></i> Edit</button>
                                        <button onclick=\"openDeleteModal({$row['id']}, '" . htmlspecialchars($row['name'], ENT_QUOTES) . "')\" class='bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition cursor-pointer'><i class='fa-solid fa-trash'></i> Delete</button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='p-6 text-center text-slate-400'>No users found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center p-4 z-50">
    <div class="bg-white p-6 rounded-2xl shadow-xl max-w-md w-full space-y-4">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base"><i class="fa-solid fa-user-pen text-blue-600 mr-2"></i> Edit User Account</h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name</label>
                <input type="text" name="name" id="edit_name" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Email Address</label>
                <input type="email" name="email" id="edit_email" required class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">New Password <span class="text-slate-400 font-normal">(Leave blank if unchanged)</span></label>
                <input type="password" name="password" placeholder="Enter new password to change" class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Role</label>
                <select name="role" id="edit_role" class="w-full border border-gray-300 p-2.5 rounded-lg text-sm bg-white focus:outline-none focus:border-blue-500 cursor-pointer">
                    <option value="Admin">Admin</option>
                    <option value="Doctor">Doctor</option>
                    <option value="Staff">Staff</option>
                    <option value="Customer">Customer</option>
                </select>
            </div>
            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="closeEditModal()" class="w-1/2 bg-slate-200 hover:bg-slate-300 text-slate-700 py-2.5 rounded-xl text-sm font-semibold transition cursor-pointer">Cancel</button>
                <button type="submit" name="edit_user" class="w-1/2 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm font-bold transition shadow-sm cursor-pointer">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center p-4 z-50">
    <div class="bg-white p-6 rounded-2xl shadow-xl max-w-sm w-full space-y-4 text-center">
        <div class="w-12 h-12 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto text-xl font-bold">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="font-bold text-slate-800 text-base">Delete User Account</h3>
        <p class="text-xs text-slate-500">Sigurado ka bang gusto mong burahin ang account ni <strong id="delete_username" class="text-slate-700"></strong>? Hindi na ito maibabalik pa.</p>
        <form method="POST" class="flex space-x-3 pt-2">
            <input type="hidden" name="user_id" id="delete_user_id">
            <button type="button" onclick="closeDeleteModal()" class="w-1/2 bg-slate-200 hover:bg-slate-300 text-slate-700 py-2.5 rounded-xl text-sm font-semibold transition cursor-pointer">Cancel</button>
            <button type="submit" name="delete_user" class="w-1/2 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl text-sm font-bold transition shadow-sm cursor-pointer">Delete</button>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, email, role) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
function openDeleteModal(id, name) {
    document.getElementById('delete_user_id').value = id;
    document.getElementById('delete_username').innerText = name;
    document.getElementById('deleteModal').classList.remove('hidden');
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
</body>
</html>