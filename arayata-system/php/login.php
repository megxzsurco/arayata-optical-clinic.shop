<?php
include 'db.php';
session_start();
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'Customer') {
                header("Location: my_portal.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Arayata Optical System</title>
    <!-- Tailwind CSS -->
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
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --venus: #BAD6EB;
            --meteor: #D8E0F2;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--venus);
            border-radius: 4px;
        }

        /* Ambient Animated Background */
        body {
            background: radial-gradient(circle at 10% 20%, #e2ebf0 0%, #f6f8fb 90%);
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
</head>
<body class="font-sans min-h-screen text-slate-700 antialiased flex items-center justify-center p-4 md:p-6 lg:p-8 relative overflow-x-hidden">

    <!-- Ambient Glowing Orbs -->
    <div class="blob-1 fixed -top-20 -left-20 w-96 h-96 bg-[#BAD6EB]/40 rounded-full blur-3xl pointer-events-none"></div>
    <div class="blob-2 fixed -bottom-20 -right-20 w-96 h-96 bg-[#D8E0F2]/70 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Main Container Card -->
    <div class="relative w-full max-w-5xl bg-white/80 backdrop-blur-2xl rounded-3xl shadow-[0_25px_60px_-15px_rgba(186,214,235,0.4)] border border-white/90 overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[620px]">
        
        <!-- Left Section: Visual Branding Hero (Visible on lg screens) -->
        <div class="hidden lg:flex lg:col-span-5 bg-gradient-to-br from-[#BAD6EB] via-[#C9DCED] to-[#D8E0F2] p-10 flex-col justify-between relative overflow-hidden">
            <!-- Decorative Inner Shapes -->
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/20 rounded-full blur-2xl"></div>
            
            <div class="relative z-10">
                <div class="inline-flex items-center space-x-3 bg-white/60 backdrop-blur-md px-4 py-2 rounded-2xl border border-white/50 shadow-sm">
                    <div class="w-8 h-8 bg-slate-800 text-white rounded-xl flex items-center justify-center text-sm shadow-xs">
                        <i class="fa-solid fa-glasses"></i>
                    </div>
                    <span class="font-extrabold text-slate-800 tracking-tight text-sm">Arayata Optical</span>
                </div>
            </div>

            <div class="relative z-10 my-auto py-8">
                <h2 class="text-3xl font-black text-slate-800 leading-tight mb-4">
                    Clear vision for a better tomorrow.
                </h2>
                <p class="text-slate-600 text-sm font-medium leading-relaxed">
                    Access customer records, manage appointments, and explore portal services tailored for your eyecare journey.
                </p>
                
                <!-- Quick Highlights -->
                <div class="mt-8 space-y-3">
                    <div class="flex items-center space-x-3 text-xs font-semibold text-slate-700 bg-white/40 backdrop-blur-xs p-3 rounded-xl border border-white/40">
                        <i class="fa-solid fa-shield-halved text-slate-700 text-sm"></i>
                        <span>Secure Account Portal & Data Encryption</span>
                    </div>
                    <div class="flex items-center space-x-3 text-xs font-semibold text-slate-700 bg-white/40 backdrop-blur-xs p-3 rounded-xl border border-white/40">
                        <i class="fa-solid fa-clock text-slate-700 text-sm"></i>
                        <span>Instant Access to Optical Records</span>
                    </div>
                </div>
            </div>

            <div class="relative z-10 text-xs font-semibold text-slate-500">
                &copy; <?php echo date('Y'); ?> Arayata Optical System. All rights reserved.
            </div>
        </div>

        <!-- Right Section: Login Form -->
        <div class="lg:col-span-7 p-8 sm:p-12 md:p-14 flex flex-col justify-center">
            
            <!-- Mobile Brand Header (Hidden on lg screens) -->
            <div class="lg:hidden text-center mb-8">
                <div class="w-14 h-14 bg-gradient-to-tr from-[#BAD6EB] to-[#D8E0F2] text-slate-800 rounded-2xl flex items-center justify-center mx-auto text-xl font-bold mb-3 shadow-sm border border-white">
                    <i class="fa-solid fa-glasses"></i>
                </div>
                <h1 class="text-xl font-black text-slate-800 tracking-tight">Arayata Optical System</h1>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Welcome Back</h2>
                <p class="text-xs font-semibold text-slate-500 mt-1">Please enter your credentials to sign in.</p>
            </div>

            <!-- Error Alert -->
            <?php if($error): ?>
                <div class="bg-rose-50 text-rose-700 p-4 rounded-2xl text-xs font-bold mb-6 border border-rose-200/80 shadow-xs flex items-center space-x-3 animate-shake">
                    <div class="w-7 h-7 bg-rose-100 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
                    </div>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- Form Section -->
            <form method="POST" class="space-y-5">
                <div class="space-y-1.5">
                    <label class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-envelope text-sm"></i>
                        </div>
                        <input type="email" name="email" required placeholder="name@example.com" 
                            class="w-full bg-slate-50/70 border border-slate-200 pl-11 pr-4 py-3.5 rounded-2xl text-sm font-semibold text-slate-800 placeholder-slate-400 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition-all duration-200 shadow-2xs">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider">Password</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input type="password" id="passwordInput" name="password" required placeholder="••••••••" 
                            class="w-full bg-slate-50/70 border border-slate-200 pl-11 pr-12 py-3.5 rounded-2xl text-sm font-semibold text-slate-800 placeholder-slate-400 outline-none focus:bg-white focus:border-[#BAD6EB] focus:ring-4 focus:ring-[#BAD6EB]/30 transition-all duration-200 shadow-2xs">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer">
                            <i id="toggleIcon" class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full group relative inline-flex items-center justify-center px-6 py-4 text-sm font-extrabold text-slate-800 bg-[#BAD6EB] hover:bg-[#a3c7e3] active:bg-[#8ebbdc] rounded-2xl shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 ease-in-out focus:outline-none focus:ring-4 focus:ring-[#BAD6EB]/50 cursor-pointer">
                    <i class="fa-solid fa-right-to-bracket text-slate-700 mr-2 group-hover:translate-x-1 transition-transform"></i>
                    <span>Sign In</span>
                </button>
            </form>

            <!-- Footer Signup Redirect -->
            <div class="mt-8 text-center text-xs text-slate-500 border-t border-slate-100 pt-6 font-medium">
                Don't have an account yet? 
                <a href="signup.php" class="text-slate-800 font-extrabold hover:text-[#5b91b8] hover:underline transition-colors ml-1">Create one here</a>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('passwordInput');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>