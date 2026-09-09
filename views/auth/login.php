<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — ADWT Student Academic Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#1d4ed8', dark: '#1e3a8a' }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex items-center justify-center p-4">

<div class="max-w-md w-full">
    <!-- Brand Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-brand text-white text-3xl shadow-lg mb-4">
            🎓
        </div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">SAMS Portal</h1>
        <p class="text-sm text-gray-500 mt-1">Student Academic Management System &bull; Group 1</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
            <span>Sign In to Your Account</span>
        </h2>

        <?php if (!empty($error)): ?>
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="mb-5 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><?= htmlspecialchars($success === 'logged_out' ? 'You have been logged out successfully.' : $success) ?></span>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                    Username or Email
                </label>
                <input type="text"
                       id="username"
                       name="username"
                       required
                       placeholder="e.g. admin or student1"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent transition-all">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                    Password
                </label>
                <input type="password"
                       id="password"
                       name="password"
                       required
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent transition-all">
            </div>

            <button type="submit"
                    class="w-full mt-2 bg-brand hover:bg-brand-dark text-white font-semibold py-2.5 px-4 rounded-xl text-sm shadow-md shadow-brand/20 transition-all">
                Sign In
            </button>
        </form>

        <!-- Quick Login Helper Pills for Demonstration & Grading -->
        <div class="mt-8 pt-6 border-t border-gray-100">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3 text-center">
                Demo Quick-Fill Accounts
            </p>
            <div class="grid grid-cols-3 gap-2 text-xs">
                <button type="button"
                        onclick="fillDemo('admin', 'admin123')"
                        class="p-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-800 font-medium text-center transition-colors">
                    Admin
                </button>
                <button type="button"
                        onclick="fillDemo('lecturer1', 'lecturer123')"
                        class="p-2 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 font-medium text-center transition-colors">
                    Lecturer
                </button>
                <button type="button"
                        onclick="fillDemo('student1', 'student123')"
                        class="p-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-medium text-center transition-colors">
                    Student
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function fillDemo(username, password) {
        document.getElementById('username').value = username;
        document.getElementById('password').value = password;
    }
</script>

</body>
</html>
