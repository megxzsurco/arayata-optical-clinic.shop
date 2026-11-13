<?php
include 'db.php';
session_cache_limiter('private_no_expire');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] !== 'Customer' && !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: login.php");
    exit();
}

// Fetch try-on frames dynamically from the database table
$db_frames = $conn->query("SELECT * FROM tryon_frames ORDER BY id ASC");
$frames_array = [];
if ($db_frames && $db_frames->num_rows > 0) {
    while ($f = $db_frames->fetch_assoc()) {
        // Piliin kung anong icon ang gagamitin sa sidebar batay sa pangalan o default sa glasses
        $icon_class = "fa-glasses";
        $name_lower = strtolower($f['name']);
        if (strpos($name_lower, 'square') !== false) {
            $icon_class = "fa-square";
        } elseif (strpos($name_lower, 'round') !== false || strpos($name_lower, 'circle') !== false) {
            $icon_class = "fa-circle";
        }

        $frames_array[$f['id']] = [
            'name' => $f['name'],
            'desc' => $f['description'],
            'url'  => 'upload/' . $f['image_url'],
            'icon' => $icon_class
        ];
    }
}

session_write_close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Virtual Try-On - Arayata Optical Patient Portal</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
<style>
:root {
    --venus: #BAD6EB;
    --meteor: #D8E0F2;
}
</style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-[#e2ebf0]/30 to-[#f6f8fb] font-sans min-h-screen flex flex-col">

<!-- Top Navigation Bar -->
<header class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50 shadow-sm">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
    <div class="flex items-center space-x-3">
        <div class="bg-slate-800 text-[#BAD6EB] p-2 rounded-xl font-bold text-lg shadow-sm">
            <i class="fa-solid fa-glasses"></i>
        </div>
        <div>
            <h1 class="text-slate-900 font-bold text-base leading-tight">Arayata Optical</h1>
            <span class="text-xs text-slate-500 font-medium">Patient Portal</span>
        </div>
    </div>
    
    <nav class="hidden md:flex items-center space-x-6 text-sm font-semibold">
        <a href="my_portal.php" class="text-slate-600 hover:text-slate-900 transition flex items-center space-x-1.5">
            <i class="fa-solid fa-house"></i><span>Dashboard</span>
        </a>
        <a href="patient_face_shape.php" class="text-slate-600 hover:text-slate-900 transition flex items-center space-x-1.5">
            <i class="fa-solid fa-face-smile"></i><span>Face Shape AI</span>
        </a>
        <a href="patient_virtual_tryon.php" class="text-slate-900 font-extrabold flex items-center space-x-1.5">
            <i class="fa-solid fa-glasses"></i><span>Virtual Try-On</span>
        </a>
    </nav>
    
    <div class="flex items-center space-x-4">
        <a href="logout.php" class="text-sm font-semibold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3.5 py-2 rounded-xl transition flex items-center space-x-1.5">
            <i class="fa-solid fa-right-from-bracket"></i><span>Sign Out</span>
        </a>
    </div>
</div>
</header>

<!-- Main Content Area -->
<main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex flex-col justify-center">
<div class="text-center mb-6">
    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
        <i class="fa-solid fa-glasses text-blue-600 mr-2"></i> Interactive Virtual Try-On
    </h2>
    <p class="text-sm text-slate-600 mt-1">Use your live camera or upload a clear portrait photo to try on frames.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
    <!-- Feed / Upload Column -->
    <div class="lg:col-span-2 bg-white/90 backdrop-blur-md p-5 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col items-center">
        <div class="flex bg-slate-100 p-1 rounded-xl mb-4 text-xs font-semibold w-full max-w-xs">
            <button onclick="switchMode('camera')" id="tab-camera" class="flex-1 py-2 rounded-lg bg-slate-800 text-white shadow-sm transition cursor-pointer">Live Camera</button>
            <button onclick="switchMode('upload')" id="tab-upload" class="flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 transition cursor-pointer">Upload Photo</button>
        </div>
        
        <div id="loader" class="text-blue-600 font-medium mb-3 text-sm flex items-center">
            <i class="fa-solid fa-spinner fa-spin mr-2"></i>Initializing Try-On Engine...
        </div>

        <div id="mediaContainer" class="relative rounded-xl overflow-hidden bg-slate-900 w-full shadow-inner flex items-center justify-center" style="aspect-ratio: 4/3;">
            <video id="video" autoplay muted playsinline class="w-full h-full object-cover transform scale-x-[-1]"></video>
            <img id="uploadedImage" class="w-full h-full object-cover hidden" alt="Uploaded Portrait">
            
            <!-- Frame Overlay (Transparent PNG galing sa Database) -->
            <div id="frameOverlay" class="absolute pointer-events-none transition-all duration-75 hidden flex items-center justify-center">
                <img id="selectedFrameImg" alt="Eyeglass Frame" class="w-48 max-w-none opacity-95 drop-shadow-lg">
            </div>
        </div>

        <input type="file" id="imageUploadInput" accept="image/*" class="hidden" onchange="handleImageUpload(event)">
        
        <div class="w-full mt-4 flex items-center justify-between text-xs text-slate-500 px-1">
            <span id="statusText" class="font-medium text-slate-600">Initializing...</span>
            <div class="space-x-2">
                <button id="uploadTriggerBtn" onclick="document.getElementById('imageUploadInput').click()" class="hidden text-blue-600 hover:underline font-semibold cursor-pointer">Choose Photo</button>
                <button onclick="location.reload()" class="hover:text-blue-600 underline font-semibold cursor-pointer">Reset Engine</button>
            </div>
        </div>
    </div>

    <!-- Frame Selection Sidebar na gumagamit ng Clean Icons sa halip na solid picture box -->
    <div class="bg-white/90 backdrop-blur-md p-5 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col space-y-4">
        <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 pr-1">Choose Frame Style</h3>
        
        <div class="space-y-3 max-h-[360px] overflow-y-auto pr-1">
            <?php if (!empty($frames_array)): ?>
                <?php $first = true; foreach ($frames_array as $id => $frame): ?>
                <button onclick="switchFrame('<?php echo $id; ?>', this)" id="btn-<?php echo $id; ?>" class="frame-btn w-full text-left p-3 rounded-xl border <?php echo $first ? 'border-[#BAD6EB] bg-[#BAD6EB]/30' : 'border-slate-200 hover:bg-slate-50'; ?> transition flex items-center space-x-3 group cursor-pointer">
                    <!-- Malinis na icon box tulad ng naunang version -->
                    <div class="w-12 h-12 bg-white rounded-lg border border-slate-200 flex items-center justify-center text-slate-700 shadow-sm shrink-0">
                        <i class="fa-solid <?php echo $frame['icon']; ?> text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm group-hover:text-blue-600"><?php echo htmlspecialchars($frame['name']); ?></h4>
                        <span class="text-xs text-slate-500"><?php echo htmlspecialchars($frame['desc']); ?></span>
                    </div>
                </button>
                <?php $first = false; endforeach; ?>
            <?php else: ?>
                <p class="text-xs text-slate-400 text-center py-6">No try-on frames available in database yet. Please ask admin to upload frames.</p>
            <?php endif; ?>
        </div>

        <div class="pt-2 border-t border-slate-100">
            <a href="my_portal.php#product-catalog" class="w-full bg-slate-800 hover:bg-slate-900 text-white py-2.5 rounded-xl text-sm font-semibold transition flex items-center justify-center shadow">
                <i class="fa-solid fa-store mr-2"></i> Browse All Store Frames
            </a>
        </div>
    </div>
</div>
</main>

<!-- Footer -->
<footer class="py-4 text-center text-xs text-slate-400">
&copy; <?php echo date('Y'); ?> Arayata Optical System. All rights reserved.
</footer>

<script>
const frameData = <?php echo json_encode($frames_array); ?>;
const frameKeys = Object.keys(frameData);
let activeFrameKey = frameKeys.length > 0 ? frameKeys[0] : null;

const video = document.getElementById('video');
const uploadedImage = document.getElementById('uploadedImage');
const loader = document.getElementById('loader');
const statusText = document.getElementById('statusText');
const frameOverlay = document.getElementById('frameOverlay');
const selectedFrameImg = document.getElementById('selectedFrameImg');
const uploadTriggerBtn = document.getElementById('uploadTriggerBtn');
const MODEL_URL = 'https://vladmandic.github.io/face-api/model/';

let currentMode = 'camera';
let videoStream = null;
let trackingInterval = null;

function switchFrame(frameKey, btnElement) {
    if (!frameData[frameKey]) return;
    activeFrameKey = frameKey;
    document.querySelectorAll('.frame-btn').forEach(b => {
        b.classList.remove('border-[#BAD6EB]', 'bg-[#BAD6EB]/30');
        b.classList.add('border-slate-200');
    });
    if (btnElement) {
        btnElement.classList.remove('border-slate-200');
        btnElement.classList.add('border-[#BAD6EB]', 'bg-[#BAD6EB]/30');
    }
    const frame = frameData[frameKey];
    selectedFrameImg.src = frame.url;
    if (currentMode === 'camera') {
        statusText.innerText = "Active Frame: " + frame.name;
    }
}

async function switchMode(mode) {
    currentMode = mode;
    if (trackingInterval) clearInterval(trackingInterval);
    const tabCam = document.getElementById('tab-camera');
    const tabUp = document.getElementById('tab-upload');
    if (mode === 'camera') {
        tabCam.className = "flex-1 py-2 rounded-lg bg-slate-800 text-white shadow-sm transition";
        tabUp.className = "flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 transition";
        video.classList.remove('hidden');
        uploadedImage.classList.add('hidden');
        uploadTriggerBtn.classList.add('hidden');
        initCamera();
    } else {
        tabUp.className = "flex-1 py-2 rounded-lg bg-slate-800 text-white shadow-sm transition";
        tabCam.className = "flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 transition";
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
        }
        video.classList.add('hidden');
        uploadedImage.classList.remove('hidden');
        uploadTriggerBtn.classList.remove('hidden');
        loader.style.display = 'none';
        frameOverlay.classList.add('hidden');
        statusText.innerText = "Please click Choose Photo to upload a face picture.";
    }
}

async function initAIModels() {
    if (!faceapi.nets.tinyFaceDetector.params) {
        loader.innerHTML = "<i class='fa-solid fa-spinner fa-spin mr-2'></i> Loading AI Landmark Trackers...";
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
    }
}

async function initCamera() {
    if (!activeFrameKey) {
        loader.style.display = 'none';
        statusText.innerText = "No frames available.";
        return;
    }
    try {
        await initAIModels();
        loader.innerHTML = "<i class='fa-solid fa-spinner fa-spin mr-2'></i> Requesting Camera Access...";
        videoStream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } });
        video.srcObject = videoStream;
        await new Promise((resolve) => {
            video.onloadedmetadata = () => {
                video.play();
                resolve();
            };
        });
        loader.style.display = 'none';
        statusText.innerText = "Tracking active. Position your face in view";
        frameOverlay.classList.remove('hidden');
        switchFrame(activeFrameKey, document.getElementById('btn-' + activeFrameKey));
        
        trackingInterval = setInterval(async () => {
            if (currentMode !== 'camera' || video.paused || video.ended || !video.videoWidth) return;
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks();
            if (detection) {
                const landmarks = detection.landmarks;
                const leftEye = landmarks.getLeftEye();
                const rightEye = landmarks.getRightEye();
                const eyeDist = Math.hypot(leftEye[0].x - rightEye[3].x, leftEye[0].y - rightEye[3].y);
                const videoRect = video.getBoundingClientRect();
                const scaleX = videoRect.width / video.videoWidth;
                const scaleY = videoRect.height / video.videoHeight;
                const centerX = (leftEye[3].x + rightEye[0].x) / 2;
                const centerY = (leftEye[3].y + rightEye[0].y) / 2;
                const cssLeft = (video.videoWidth - centerX) * scaleX;
                const cssTop = centerY * scaleY;
                const frameWidth = eyeDist * scaleX * 2.9;

                frameOverlay.style.left = cssLeft + 'px';
                frameOverlay.style.top = cssTop + 'px';
                frameOverlay.style.width = frameWidth + 'px';
                frameOverlay.style.transform = 'translate(-50%, -50%)';
                statusText.innerText = "Face detected. Frame aligned";
            } else {
                statusText.innerText = "Searching for face in camera...";
            }
        }, 150);
    } catch (err) {
        console.error(err);
        loader.innerHTML = "<span class='text-red-500 font-medium text-xs'>Camera Error: Please verify permissions or use Localhost/HTTPS.</span>";
        statusText.innerText = "Camera unavailable";
    }
}

async function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    loader.style.display = 'flex';
    loader.innerHTML = "<i class='fa-solid fa-spinner fa-spin mr-2'></i> Scanning uploaded photo...";
    statusText.innerText = "Detecting facial landmarks...";
    const imageUrl = URL.createObjectURL(file);
    uploadedImage.src = imageUrl;
    uploadedImage.onload = async () => {
        await initAIModels();
        const detection = await faceapi.detectSingleFace(uploadedImage, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks();
        loader.style.display = 'none';
        if (detection) {
            const landmarks = detection.landmarks;
            const leftEye = landmarks.getLeftEye();
            const rightEye = landmarks.getRightEye();
            const eyeDist = Math.hypot(leftEye[0].x - rightEye[3].x, leftEye[0].y - rightEye[3].y);
            const imgRect = uploadedImage.getBoundingClientRect();
            const scaleX = imgRect.width / uploadedImage.naturalWidth;
            const scaleY = imgRect.height / uploadedImage.naturalHeight;
            const centerX = (leftEye[3].x + rightEye[0].x) / 2;
            const centerY = (leftEye[3].y + rightEye[0].y) / 2;
            const cssLeft = centerX * scaleX;
            const cssTop = centerY * scaleY;
            const frameWidth = eyeDist * scaleX * 2.9;

            frameOverlay.style.left = cssLeft + 'px';
            frameOverlay.style.top = cssTop + 'px';
            frameOverlay.style.width = frameWidth + 'px';
            frameOverlay.style.transform = 'translate(-50%, -50%)';
            frameOverlay.classList.remove('hidden');
            if (activeFrameKey) {
                switchFrame(activeFrameKey, document.getElementById('btn-' + activeFrameKey));
            }
            statusText.innerText = "Photo processed successfully. Frame fitted!";
        } else {
            frameOverlay.classList.add('hidden');
            statusText.innerText = "No face found. Please upload a clearer front-facing photo.";
        }
    };
}

window.addEventListener('DOMContentLoaded', () => {
    if (activeFrameKey) {
        switchFrame(activeFrameKey, document.getElementById('btn-' + activeFrameKey));
    }
    initCamera();
});
</script>
</body>
</html>