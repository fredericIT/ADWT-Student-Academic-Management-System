<?php
$pageTitle = 'Login — ADWT Academic';
require __DIR__ . '/../layout/header.php';
?>

<div class="max-w-md mx-auto my-12 bg-white p-8 rounded-xl shadow-lg border border-gray-100">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Sign In to Your Account</h1>
        <p class="text-sm text-gray-500 mt-2">Enter your credentials to access the Academic System</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="/login" method="POST" class="space-y-6">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
            <input type="email"
                   id="email"
                   name="email"
                   value="<?= htmlspecialchars($email ?? '') ?>"
                   required
                   placeholder="user@university.ac"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm outline-none text-sm">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <input type="password"
                   id="password"
                   name="password"
                   required
                   placeholder="••••••••"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm outline-none text-sm">
        </div>

        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg shadow transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Sign In
        </button>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
