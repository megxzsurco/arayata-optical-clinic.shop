<?php
include 'db.php';
session_cache_limiter('private_no_expire');
session_start();
// Security: Allow Patients/Customers, Admins, or Staff to access this page[cite: 43]
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] !== 'Customer' && !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: login.php");
    exit();
}
// Release session lock immediately[cite: 43]
session_write_close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>AI Face Shape Analyzer - Arayata Optical Patient Portal</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Load modern Face-API.js AI Engine[cite: 43] -->
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
<style>
:root {
    --venus: #BAD6EB;
    --meteor: #D8E0F2;
}
</style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-[#e2ebf0]/30 to-[#f6f8fb] font-sans min-h-screen flex flex-col">

<!-- Patient Portal Top Navigation Bar -->
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
        <a href="patient_face_shape.php" class="text-slate-900 font-extrabold flex items-center space-x-1.5">
            <i class="fa-solid fa-face-smile"></i><span>Face Shape AI</span>
        </a>
        <a href="patient_virtual_tryon.php" class="text-slate-600 hover:text-slate-900 transition flex items-center space-x-1.5">
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
<main class="flex-1 max-w-4xl w-full mx-auto p-4 sm:px-6 lg:px-8 flex flex-col justify-center">
<!-- Page Title Header -->
<div class="text-center mb-8">
    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
        <i class="fa-solid fa-face-smile text-blue-600 mr-2"></i> AI Face Shape Analyzer
    </h2>
    <p class="text-sm text-slate-600 mt-1">Scan your face to discover your unique shape and get tailored frame recommendations[cite: 45].</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
    <!-- Camera Feed Box -->
    <div class="bg-white/90 backdrop-blur-md p-5 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col items-center">
        <div id="loader" class="text-blue-600 font-medium mb-3 text-sm flex items-center">
            <i class="fa-solid fa-spinner fa-spin mr-2"></i>Initializing AI Engine...
        </div>
        
        <div class="relative rounded-xl overflow-hidden bg-slate-900 w-full shadow-inner" style="aspect-ratio: 4/3;">
            <video id="video" autoplay muted playsinline class="w-full h-full object-cover transform scale-x-[-1]"></video>
            <canvas id="overlay" class="absolute top-0 left-0 w-full h-full transform scale-x-[-1]"></canvas>
        </div>
        
        <button id="scanBtn" disabled class="mt-4 w-full bg-slate-100 text-slate-400 py-3 rounded-xl font-bold transition flex justify-center cursor-not-allowed items-center shadow-xs">
            <i class="fa-solid fa-camera mr-2"></i> Scan Face Shape
        </button>
    </div>

    <!-- Results and Recommendations Box -->
    <div class="bg-white/90 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between h-full min-h-[350px]">
        <div>
            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Analysis Result</h3>
            
            <div id="resultBox" class="hidden">
                <div class="text-center mb-6">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Detected Face Shape</span>
                    <h2 id="shapeName" class="text-4xl font-black text-slate-900 mt-1">Round</h2>
                </div>
                
                <div class="bg-[#BAD6EB]/30 p-4 rounded-xl border border-[#BAD6EB] mb-4">
                    <h4 class="font-bold text-slate-900 text-sm mb-1 flex items-center">
                        <i class="fa-solid fa-glasses mr-2 text-blue-600"></i>Recommended Frames:
                    </h4>
                    <p id="shapeRecommendation" class="text-sm text-slate-700 leading-relaxed"></p>
                </div>
                
                <div class="bg-emerald-50 text-emerald-700 p-3 rounded-xl text-xs font-medium text-center mb-4 border border-emerald-200 flex items-center justify-center">
                    <i class="fa-solid fa-circle-check mx-1.5 text-emerald-600 text-base"></i> Result successfully saved to your profile[cite: 46]!
                </div>
                
                <!-- Direct Action Button to Virtual Try-On -->
                <a href="patient_virtual_tryon.php" class="w-full bg-slate-800 hover:bg-slate-900 text-white py-3 rounded-xl font-bold transition flex items-center justify-center shadow-md text-sm mb-3">
                    <i class="fa-solid fa-glasses mx-2"></i> Try On Recommended Frames
                </a>
            </div>

            <div id="waitingBox" class="h-48 flex flex-col items-center justify-center text-slate-400 py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-[#BAD6EB]/40 text-slate-800 flex items-center justify-center text-xl mb-3 shadow-sm">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <p class="text-sm font-medium text-slate-600">Position your face clearly in the camera frame and click Scan[cite: 46].</p>
            </div>
        </div>
        
        <button onclick="location.reload()" id="scanAgainBtn" class="hidden w-full border border-slate-300 text-slate-700 hover:bg-slate-50 py-2.5 rounded-xl font-semibold transition flex items-center justify-center cursor-pointer">
            <i class="fa-solid fa-rotate-right mr-2"></i> Scan Again
        </button>
    </div>
</div>
</main>

<!-- Footer -->
<footer class="py-4 text-center text-xs text-slate-400">
&copy; <?php echo date('Y'); ?> Arayata Optical System. All rights reserved.
</footer>

<script>
const video = document.getElementById('video');
const canvas = document.getElementById('overlay');
const scanBtn = document.getElementById('scanBtn');
const loader = document.getElementById('loader');
const MODEL_URL = 'https://vladmandic.github.io/face-api/model/';

async function initAI() {
    try {
        loader.innerHTML = "<i class='fa-solid fa-spinner fa-spin mr-2'></i> Downloading AI Models...";
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        loader.innerHTML = "<i class='fa-solid fa-spinner fa-spin mr-2'></i> Requesting Camera Access...";
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        loader.style.display = "none";
        scanBtn.disabled = false;
        scanBtn.className = "mt-4 w-full bg-slate-800 text-white font-extrabold hover:bg-slate-900 transition shadow-md cursor-pointer flex items-center justify-center py-3 rounded-xl";
    } catch (err) {
        console.error(err);
        loader.innerHTML = "<span class='text-red-500 font-medium text-xs'>Camera Error: Ensure you have granted camera permissions and are using HTTPS/localhost.</span>";
    }
}
initAI();

scanBtn.addEventListener('click', async () => {
    scanBtn.innerHTML = "<i class='fa-solid fa-spinner fa-spin mr-2'></i> Analyzing Proportions...";
    const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks();
    if (!detection) {
        alert("No face detected! Please face the camera clearly.");
        scanBtn.innerHTML = "<i class='fa-solid fa-camera mr-2'></i> Scan Face Shape";
        return;
    }
    const displaySize = { width: video.clientWidth, height: video.clientHeight };
    faceapi.matchDimensions(canvas, displaySize);
    const resizedDetections = faceapi.resizeResults(detection, displaySize);
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);
    
    const landmarks = detection.landmarks.positions;
    const foreheadWidth = faceapi.euclideanDistance([landmarks[0].x, landmarks[0].y], [landmarks[16].x, landmarks[16].y]);
    const cheekboneWidth = faceapi.euclideanDistance([landmarks[1].x, landmarks[1].y], [landmarks[15].x, landmarks[15].y]);
    const jawWidth = faceapi.euclideanDistance([landmarks[4].x, landmarks[4].y], [landmarks[12].x, landmarks[12].y]);
    const faceHeight = faceapi.euclideanDistance([landmarks[8].x, landmarks[8].y], [landmarks[27].x, landmarks[27].y]);
    
    const lengthToWidthRatio = faceHeight / cheekboneWidth;
    const jawToCheekRatio = jawWidth / cheekboneWidth;
    const foreheadToCheekRatio = foreheadWidth / cheekboneWidth;
    
    let shape = "Oval";
    let rec = "Your balanced proportions suit almost any frame style! Try wide, oversized, or modern aviator shapes[cite: 50].";
    
    if (lengthToWidthRatio < 1.18 && Math.abs(cheekboneWidth - faceHeight) < 25) {
        shape = "Round";
        rec = "Angular, geometric, or rectangular frames will add sharpness and contrast to your soft facial curves[cite: 49].";
    } else if (jawToCheekRatio > 0.88 && foreheadToCheekRatio > 0.85 && lengthToWidthRatio < 1.35) {
        shape = "Square";
        rec = "Round or oval glasses will soften your strong angular jawline and balance your features perfectly[cite: 49].";
    } else if (foreheadToCheekRatio > 0.92 && jawToCheekRatio < 0.75) {
        shape = "Heart";
        rec = "Bottom-heavy frames, cat-eye, or rounded styles will draw attention downward and widen the lower face appearance[cite: 49].";
    } else if (foreheadToCheekRatio < 0.78 && jawToCheekRatio < 0.78 && lengthToWidthRatio > 1.35) {
        shape = "Oblong / Rectangle";
        rec = "Tall frames with decorative or contrasting temples will add width and shorten your face proportions gracefully[cite: 49].";
    } else if (foreheadToCheekRatio > 0.85 && jawToCheekRatio < 0.72 && lengthToWidthRatio > 1.3) {
        shape = "Diamond";
        rec = "Cat-eye frames, oval styles, or rimless glasses will highlight your distinct cheekbones and delicate jawline[cite: 50].";
    }
    
    document.getElementById('waitingBox').classList.add('hidden');
    document.getElementById('resultBox').classList.remove('hidden');
    document.getElementById('scanAgainBtn').classList.remove('hidden');
    document.getElementById('shapeName').innerText = shape;
    document.getElementById('shapeRecommendation').innerText = rec;
    
    scanBtn.innerHTML = "Analysis Complete";
    scanBtn.disabled = true;
    scanBtn.className = "mt-4 w-full bg-slate-100 text-slate-400 py-3 rounded-xl font-bold cursor-not-allowed flex items-center justify-center";
    video.pause();
    
    fetch('save_face_shape.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'shape=' + encodeURIComponent(shape),
        keepalive: true
    }).catch(error => {
        console.error("Database save error:", error);
    });
});
</script>
</body>
</html>