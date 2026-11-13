<?php
include 'db.php';
session_cache_limiter('private_no_expire');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$msg = "";
$edit_mode = false;
$edit_data = ['id' => '', 'name' => '', 'description' => '', 'image_url' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_frame'])) {
    $frame_name =$conn->real_escape_string($_POST['frame_name']);$frame_desc = $conn->real_escape_string($_POST['frame_desc']);
    
    if (isset($_FILES['frame_image']) && $_FILES['frame_image']['error'] == 0) {$target_dir = "upload/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['frame_image']['name']);$target_file = $target_dir .$file_name;
        
        if (move_uploaded_file($_FILES['frame_image']['tmp_name'], $target_file)) {$conn->query("INSERT INTO tryon_frames (name, description, image_url) VALUES ('$frame_name', '$frame_desc', '$file_name')");
            $_SESSION['msg'] = "Virtual try-on frame successfully added!";
        } else {
            $_SESSION['msg'] = "Error uploading image file.";
        }
    }
    header("Location: manage_tryon_frames.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_frame'])) {
    $id = (int)$_POST['frame_id'];
    $frame_name =$conn->real_escape_string($_POST['frame_name']);$frame_desc = $conn->real_escape_string($_POST['frame_desc']);
    
    if (isset($_FILES['frame_image']) && $_FILES['frame_image']['error'] == 0) {$target_dir = "upload/";
        $file_name = time() . '_' . basename($_FILES['frame_image']['name']);$target_file = $target_dir .$file_name;
        
        if (move_uploaded_file($_FILES['frame_image']['tmp_name'],$target_file)) {
            $img_query =$conn->query("SELECT image_url FROM tryon_frames WHERE id = $id");
            if ($img_query && $row =$img_query->fetch_assoc()) {
                $old_file = "upload/" . $row['image_url'];
                if (file_exists($old_file)) { unlink($old_file); }
            }
            $conn->query("UPDATE tryon_frames SET name = '$frame_name', description = '$frame_desc', image_url = '$file_name' WHERE id =$id");
        }
    } else {
        $conn->query("UPDATE tryon_frames SET name = '$frame_name', description = '$frame_desc' WHERE id =$id");
    }
    $_SESSION['msg'] = "Virtual try-on frame successfully updated!";
    header("Location: manage_tryon_frames.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_delete_id'])) {
    $id = (int)$_POST['ajax_delete_id'];
    $img_query =$conn->query("SELECT image_url FROM tryon_frames WHERE id = $id");
    if ($img_query && $row =$img_query->fetch_assoc()) {
        $file_path = "upload/" . $row['image_url'];
        if (file_exists($file_path)) { unlink($file_path); }
    }
    $conn->query("DELETE FROM tryon_frames WHERE id = $id");
    echo json_encode(['status' => 'success', 'id' => $id]);
    exit();
}

$msg =$_SESSION['msg'] ?? "";
unset($_SESSION['msg']);

if (isset($_GET['edit'])) {$edit_mode = true;
    $edit_id = (int)$_GET['edit'];
    $res =$conn->query("SELECT * FROM tryon_frames WHERE id = $edit_id");
    if ($res &&$res->num_rows > 0) {
        $edit_data =$res->fetch_assoc();
    }
}

$frames_result =$conn->query("SELECT * FROM tryon_frames ORDER BY id DESC");
session_write_close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Virtual Try-On Frames - Arayata Optical System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-50 via-[#e2ebf0]/30 to-[#f6f8fb] font-sans min-h-screen flex">
    <?php include 'sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-h-screen overflow-y-auto">
        <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 h-20 flex items-center px-8 shadow-sm justify-between sticky top-0 z-40">
            <h2 class="text-xl font-black text-slate-800 flex items-center tracking-tight">
                <span class="w-9 h-9 rounded-xl bg-[#BAD6EB]/40 text-slate-800 flex items-center justify-center mr-3 shadow-inner">
                    <i class="fa-solid fa-glasses text-sm"></i>
                </span>
                Virtual Try-On Frame Management
            </h2>
            <span class="text-xs font-extrabold bg-[#BAD6EB]/40 text-slate-800 px-3.5 py-1.5 rounded-full border border-[#BAD6EB] uppercase tracking-wider">Admin Panel</span>
        </header>

        <main class="p-8 space-y-8">
            <div id="alertBox" class="<?php echo $msg ? '' : 'hidden'; ?> bg-emerald-600 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-circle-check mr-3 text-lg"></i>
                    <span id="alertText" class="text-sm font-bold"><?php echo $msg; ?></span>
                </div>
            </div>

            <div class="bg-white/90 backdrop-blur-md p-8 rounded-2xl shadow-sm border border-slate-200 max-w-xl">
                <h3 class="font-black text-slate-900 text-base mb-4 flex items-center">
                    <i class="fa-solid <?php echo $edit_mode ? 'fa-pen-to-square text-amber-600' : 'fa-plus-circle text-blue-600'; ?> mr-2.5"></i> 
                    <?php echo $edit_mode ? 'Edit Try-On Frame' : 'Add New Try-On Frame'; ?>
                </h3>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?php if ($edit_mode): ?>
                        <input type="hidden" name="frame_id" value="<?php echo $edit_data['id']; ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Frame Name</label>
                        <input type="text" name="frame_name" value="<?php echo htmlspecialchars($edit_data['name']); ?>" placeholder="e.g. Classic Aviator" required class="w-full border border-slate-200 p-3 rounded-xl text-sm focus:outline-none focus:border-[#8EBBDC] focus:ring-2 focus:ring-[#BAD6EB]/30 transition bg-slate-50/50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Description / Style</label>
                        <input type="text" name="frame_desc" value="<?php echo htmlspecialchars($edit_data['description']); ?>" placeholder="e.g. Metal Wire Teardrop" required class="w-full border border-slate-200 p-3 rounded-xl text-sm focus:outline-none focus:border-[#8EBBDC] focus:ring-2 focus:ring-[#BAD6EB]/30 transition bg-slate-50/50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Frame Image <?php echo $edit_mode ? '(Leave blank if you do not want to change image)' : '(Transparent PNG recommended)'; ?>
                        </label>
                        <input type="file" name="frame_image" accept="image/*" <?php echo $edit_mode ? '' : 'required'; ?> class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-[#BAD6EB]/40 file:text-slate-800 hover:file:bg-[#BAD6EB]/70 cursor-pointer">
                    </div>
                    
                    <div class="flex space-x-3 pt-2">
                        <?php if ($edit_mode): ?>
                            <a href="manage_tryon_frames.php" class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3.5 rounded-xl text-sm font-bold text-center transition">Cancel</a>
                            <button type="submit" name="update_frame" class="w-1/2 bg-amber-600 hover:bg-amber-700 text-white py-3.5 rounded-xl text-sm font-extrabold transition shadow-md cursor-pointer">
                                Update Frame
                            </button>
                        <?php else: ?>
                            <button type="submit" name="add_frame" class="w-full bg-slate-800 hover:bg-slate-900 text-white py-3.5 rounded-xl text-sm font-extrabold transition shadow-md cursor-pointer">
                                Upload & Save Frame
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="bg-white/90 backdrop-blur-md p-8 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="font-black text-slate-900 text-base mb-6 flex items-center">
                    <i class="fa-solid fa-list text-blue-600 mr-2.5"></i> Existing Try-On Frames
                </h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider font-bold border-b border-slate-200">
                                <th class="p-3.5 rounded-l-xl">Preview</th>
                                <th class="p-3.5">Name</th>
                                <th class="p-3.5">Description</th>
                                <th class="p-3.5 rounded-r-xl text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="frameTableBody" class="divide-y divide-slate-100 text-slate-700 font-medium">
                            <?php if ($frames_result &&$frames_result->num_rows > 0): ?>
                                <?php while ($row =$frames_result->fetch_assoc()): ?>
                                <tr id="row-<?php echo $row['id']; ?>" class="hover:bg-slate-50/50 transition">
                                    <td class="p-3.5">
                                        <img src="upload/<?php echo htmlspecialchars($row['image_url']); ?>" class="w-24 h-10 object-contain bg-slate-900 p-1.5 rounded-xl shadow-xs">
                                    </td>
                                    <td class="p-3.5 font-bold text-slate-900"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="p-3.5 text-slate-600"><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td class="p-3.5 text-center space-x-2">
                                        <a href="manage_tryon_frames.php?edit=<?php echo $row['id']; ?>" class="text-amber-600 hover:text-amber-800 font-bold text-xs bg-amber-50 px-3.5 py-2 rounded-xl border border-amber-100 transition inline-block">Edit</a>
                                        <button onclick="seamlessDelete(<?php echo $row['id']; ?>)" class="text-rose-600 hover:text-rose-800 font-bold text-xs bg-rose-50 px-3.5 py-2 rounded-xl border border-rose-100 transition cursor-pointer inline-block">Delete</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr id="emptyRow">
                                    <td colspan="4" class="py-10 text-center text-slate-400 font-medium">No try-on frames uploaded yet. Add one using the form above!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

<script>
function seamlessDelete(id) {
    if (!confirm('Are you sure you want to delete this try-on frame?')) return;

    const formData = new FormData();
    formData.append('ajax_delete_id', id);

    fetch('manage_tryon_frames.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const row = document.getElementById('row-' + data.id);
            if (row) {
                row.remove();
            }

            const alertBox = document.getElementById('alertBox');
            const alertText = document.getElementById('alertText');
            alertBox.classList.remove('hidden');
            alertText.innerText = "Try-on frame successfully deleted!";

            setTimeout(() => {
                alertBox.classList.add('hidden');
            }, 3000);

            const tbody = document.getElementById('frameTableBody');
            if (tbody.children.length === 0) {
                tbody.innerHTML = `<tr id="emptyRow"><td colspan="4" class="py-10 text-center text-slate-400 font-medium">No try-on frames uploaded yet. Add one using the form above!</td></tr>`;
            }
        } else {
            alert('Failed to delete frame.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during deletion.');
    });
}
</script>
</body>
</html>