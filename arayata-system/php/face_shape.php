<?php
include 'db.php';

session_cache_limiter('private_no_expire');
session_start();

// Updated Security: Allow Admins, Staff, and Customers to view and use this page
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Customer', 'Admin', 'Staff'])) {
    header("Location: login.php");
    exit();
}

// Release session lock immediately so navigation to the dashboard is instant and clean
session_write_close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Face Shape Analyzer - Arayata Optical System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Load modern Face-API.js AI Engine -->
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
</head>
<body class="bg-slate-50 font-sans h-screen flex overflow-hidden">
    
    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-[#1E293B] text-slate-300 flex flex-col justify-between hidden md:flex">
        <div>
            <div class="p-5 border-b border-slate-700 flex items-center space-x-3">
                <div class="bg-blue-600 text-white p-2 rounded-lg font-bold text-xl"><i class="fa-solid fa-glasses"></i></div>
                <div>
                    <h1 class="text-white font-bold text-base leading-tight">Optical System</h1>
                    <span class="text-xs text-slate-400">Arayata Optical</span>
                </div>
            </div>
            <nav class="p-4 space-y-1 text-sm">
                <!-- Change admin_dashboard.php if your admin panel uses a different file name -->
                <a href="admin_dashboard.php" class="flex items-center space-x-3 p-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition cursor-pointer"><i class="fa-solid fa-house w-5"></i><span>Dashboard</span></a>
                <a href="face_shape.php" class="flex items-center space-x-3 p-2.5 rounded-lg bg-blue-600 text-white cursor-pointer"><i class="fa-solid fa-face-smile w-5"></i><span>Face Shape AI</span></a>
                <a href="virtual_tryon.php" class="flex items-center space-x-3 p-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition cursor-pointer"><i class="fa-solid fa-glasses w-5"></i><span>Virtual Try-On</span></a>
            </nav>
        </div>
        <div class="p-4 border-t border-slate-700">
            <a href="logout.php" class="flex items-center space-x-3 p-2.5 rounded-lg text-red-400 hover:bg-slate-800 transition"><i class="fa-solid fa-right-from-bracket w-5"></i><span>Sign Out</span></a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-y-auto">
        <header class="bg-white border-b border-gray-200 h-16 flex items-center px-6 shadow-sm justify-between">
            <h2 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-face-smile text-blue-600 mr-2"></i> AI Face Shape Analyzer</h2>
            <a href="logout.php" class="text-sm text-red-600 md:hidden font-semibold">Sign Out</a>
        </header>

        <main class="p-6">
            <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Camera Feed Box -->
                <div class="bg-white p-4 rounded-xl shadow border border-gray-200 flex flex-col items-center">
                    <div id="loader" class="text-blue-600 font-bold mb-2 text-sm"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Initializing AI Engine...</div>
                    <div class="relative rounded-lg overflow-hidden bg-black w-full" style="aspect-ratio: 4/3;">
                        <video id="video" autoplay muted playsinline class="w-full h-full object-cover transform scale-x-[-1]"></video>
                        <canvas id="overlay" class="absolute top-0 left-0 w-full h-full transform scale-x-[-1]"></canvas>
                    </div>
                    <button id="scanBtn" disabled class="mt-4 w-full bg-slate-300 text-slate-500 py-3 rounded font-bold transition">
                        <i class="fa-solid fa-camera mr-2"></i> Scan Face Shape
                    </button>
                </div>

                <!-- Results and Recommendations Box -->
                <div class="bg-white p-6 rounded-xl shadow border border-gray-200 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 border-b pb-2 mb-4">Analysis Result</h3>
                        
                        <div id="resultBox" class="hidden">
                            <div class="text-center mb-6">
                                <span class="text-xs font-semibold text-slate-500 uppercase">Detected Face Shape</span>
                                <h2 id="shapeName" class="text-4xl font-extrabold text-blue-600 mt-1">Round</h2>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-4">
                                <h4 class="font-bold text-blue-800 text-sm mb-1">Recommended Frames:</h4>
                                <p id="shapeRecommendation" class="text-sm text-blue-700"></p>
                            </div>
                            <div class="bg-green-50 text-green-700 p-2.5 rounded text-xs font-semibold text-center mb-4 border border-green-200">
                                <i class="fa-solid fa-circle-check mr-1"></i> Result safely stored in database!
                            </div>
                        </div>

                        <div id="waitingBox" class="h-48 flex flex-col items-center justify-center text-slate-400 py-12">
                            <i class="fa-solid fa-robot text-4xl mb-3"></i>
                            <p class="text-sm text-center">Position face clearly in the camera frame and click Scan.</p>
                        </div>
                    </div>

                    <button onclick="location.reload()" id="scanAgainBtn" class="hidden w-full border border-slate-300 text-slate-600 py-2 rounded font-semibold hover:bg-slate-50 transition">Scan Again</button>
                </div>

            </div>
        </main>
    </div>

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
                
                loader.style.display = 'none';
                scanBtn.disabled = false;
                scanBtn.className = "mt-4 w-full bg-blue-600 text-white py-3 rounded font-bold hover:bg-blue-700 transition shadow-lg cursor-pointer";
            } catch (err) {
                console.error(err);
                loader.innerHTML = "<span class='text-red-500 font-semibold text-xs'>Camera Error: Ensure you are visiting via http://localhost and granted camera permissions.</span>";
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
            const jawWidth = faceapi.euclideanDistance([landmarks[2].x, landmarks[2].y], [landmarks[14].x, landmarks[14].y]);
            const faceHeight = faceapi.euclideanDistance([landmarks[8].x, landmarks[8].y], [landmarks[27].x, landmarks[27].y]) * 1.5;
            
            let shape = "Oval";
            let rec = "You can pull off almost any style! Try geometric or oversized frames.";
            
            let ratio = faceHeight / jawWidth;
            if (ratio < 1.2) {
                shape = "Round";
                rec = "Angular frames like Square or Rectangular glasses will add beautiful contrast to your facial curves.";
            } else if (ratio > 1.5) {
                shape = "Oval";
                rec = "You have balanced proportions. Try wide, oversized, or classic Aviator frames!";
            } else {
                shape = "Square";
                rec = "Round or Oval glasses will soften your strong jawline and balance your features perfectly.";
            }

            document.getElementById('waitingBox').classList.add('hidden');
            document.getElementById('resultBox').classList.remove('hidden');
            document.getElementById('scanAgainBtn').classList.remove('hidden');
            document.getElementById('shapeName').innerText = shape;
            document.getElementById('shapeRecommendation').innerText = rec;
            scanBtn.innerHTML = "Analysis Complete";
            scanBtn.disabled = true;
            
            video.pause();

            // Background fetch call using keepalive
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