<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>NutriNova Admin - User Management</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: '#006e1c',
                        secondary: '#0060a8',
                        tertiary: '#ff6b6b',
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, .font-headline { font-family: 'Manrope', sans-serif; }
        .custom-gradient {
            background: linear-gradient(135deg, #006e1c 0%, #0060a8 100%);
        }
    </style>
</head>
<body class="bg-blue-50 text-slate-900 min-h-screen">

<!-- SideNavBar -->
<aside class="h-screen w-72 fixed left-0 top-0 overflow-y-auto bg-slate-50 dark:bg-slate-900 flex flex-col py-8 px-6 z-50">
    <div class="mb-10 px-2">
        <span class="text-2xl font-black text-green-700 dark:text-green-500">NutriNova</span>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mt-1">Admin Console</p>
    </div>

    <nav class="flex-1 space-y-2">
        <a href="?action=admin_list_users" class="nav-link flex items-center gap-4 px-4 py-3 rounded-xl transition-colors duration-200 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 cursor-pointer active" style="color: #006e1c; font-weight: bold; border-right: 4px solid #006e1c; background-color: rgba(76, 175, 80, 0.1);">
            <span class="material-symbols-outlined">group</span>
            <span class="text-sm font-semibold">User Management</span>
        </a>
        
        <a href="?action=index" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-colors duration-200 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 cursor-pointer">
            <span class="material-symbols-outlined">arrow_back</span>
            <span class="text-sm font-semibold">Back to Dashboard</span>
        </a>
    </nav>

    <div class="mt-auto pt-6 border-t border-slate-200 dark:border-slate-800 space-y-1">
        <a onclick="logout()" class="flex items-center gap-4 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors cursor-pointer">
            <span class="material-symbols-outlined">logout</span>
            <span class="text-sm font-semibold">Logout</span>
        </a>
    </div>
</aside>

<!-- TopNavBar -->
<header class="fixed top-0 right-0 w-[calc(100%-18rem)] h-20 z-40 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl flex justify-between items-center px-12">
    <div class="flex items-center gap-4 flex-1">
        <h1 class="text-xl font-bold font-headline text-blue-900 dark:text-blue-300">User Management</h1>
        <div class="relative w-96 ml-8">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
            <input id="search-input" class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm focus:ring-2 focus:ring-primary/20" placeholder="Search users..." type="text"/>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="ml-72 pt-28 px-12 pb-12">
    <div class="mb-10">
        <span class="text-tertiary font-semibold text-sm tracking-wider uppercase">Dashboard</span>
        <h2 class="text-4xl font-extrabold font-headline text-slate-900 mt-2 tracking-tight">User Management</h2>
        <p class="text-slate-600 mt-2 max-w-2xl text-lg">Manage and monitor all registered users on the platform.</p>
    </div>

    <!-- Stats Grid -->
    <?php if ($stats): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-semibold uppercase">Total Users</p>
                    <h3 class="text-4xl font-extrabold text-slate-900 mt-2"><?php echo htmlspecialchars($stats['total_users']); ?></h3>
                </div>
                <div class="p-4 bg-green-100 rounded-2xl">
                    <span class="material-symbols-outlined text-green-700">people</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-semibold uppercase">New This Week</p>
                    <h3 class="text-4xl font-extrabold text-slate-900 mt-2"><?php echo htmlspecialchars($stats['new_users_week']); ?></h3>
                </div>
                <div class="p-4 bg-blue-100 rounded-2xl">
                    <span class="material-symbols-outlined text-blue-700">trending_up</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-semibold uppercase">Active Users</p>
                    <h3 class="text-4xl font-extrabold text-slate-900 mt-2"><?php echo htmlspecialchars($stats['active_users']); ?></h3>
                </div>
                <div class="p-4 bg-purple-100 rounded-2xl">
                    <span class="material-symbols-outlined text-purple-700">check_circle</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- User Table -->
    <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-100">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Name</th>
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Email</th>
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Height (cm)</th>
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Weight (kg)</th>
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Level</th>
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Registered</th>
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-8 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white font-bold">
                                        <?php echo htmlspecialchars(substr($user['Nom'], 0, 1) . substr($user['Prenom'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($user['Prenom'] . ' ' . $user['Nom']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-slate-600"><?php echo htmlspecialchars($user['Email']); ?></p>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-slate-600"><?php echo htmlspecialchars($user['Taille_cm'] ?? 'N/A'); ?></p>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-slate-600"><?php echo htmlspecialchars($user['Poids_kg'] ?? 'N/A'); ?></p>
                            </td>
                            <td class="px-8 py-4">
                                <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                    <?php echo htmlspecialchars($user['Niveau_sportif'] ?? 'Beginner'); ?>
                                </span>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-slate-600 text-sm"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                            </td>
                            <td class="px-8 py-4">
                                <div class="flex gap-2">
                                    <button onclick="viewUserDetails(<?php echo $user['id']; ?>)" class="p-2 hover:bg-blue-100 rounded-lg transition-colors" title="View">
                                        <span class="material-symbols-outlined text-blue-600">visibility</span>
                                    </button>
                                    <button onclick="confirmDeleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['Prenom'] . ' ' . $user['Nom']); ?>')" class="p-2 hover:bg-red-100 rounded-lg transition-colors" title="Delete">
                                        <span class="material-symbols-outlined text-red-600">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-8 py-12 text-center">
                                <p class="text-slate-500 text-lg">No users found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center gap-2 mt-8">
        <?php if ($page > 1): ?>
            <a href="?action=admin_list_users&page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition">Previous</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="px-4 py-2 bg-green-700 text-white rounded-lg"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?action=admin_list_users&page=<?php echo $i; ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?action=admin_list_users&page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition">Next</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</main>

<!-- Confirmation Modal -->
<div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-sm mx-auto">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-red-600">warning</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900">Delete User</h3>
        </div>
        <p class="text-slate-600 mb-6">Are you sure you want to delete <strong id="delete-user-name"></strong>? This action cannot be undone.</p>
        <div class="flex gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-slate-200 text-slate-900 rounded-lg hover:bg-slate-300 transition font-semibold">Cancel</button>
            <button onclick="executeDelete()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">Delete</button>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div id="details-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-lg mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-slate-900">User Details</h3>
            <button onclick="closeDetailsModal()" class="text-slate-500 hover:text-slate-700">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div id="details-content" class="space-y-4">
            <p class="text-slate-600">Loading...</p>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50"></div>

<script>
    let userToDelete = null;

    /**
     * Show delete confirmation modal
     */
    function confirmDeleteUser(userId, userName) {
        userToDelete = userId;
        document.getElementById('delete-user-name').textContent = userName;
        document.getElementById('delete-modal').classList.remove('hidden');
    }

    /**
     * Close delete modal
     */
    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.add('hidden');
        userToDelete = null;
    }

    /**
     * Execute user deletion
     */
    function executeDelete() {
        if (!userToDelete) return;

        const formData = new FormData();
        formData.append('id', userToDelete);

        fetch('?action=admin_delete_user', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('User deleted successfully');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast('Error: ' + data.error, 'error');
            }
            closeDeleteModal();
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }

    /**
     * View user details in modal
     */
    function viewUserDetails(userId) {
        const detailsContent = document.getElementById('details-content');
        detailsContent.innerHTML = '<p class="text-slate-600">Loading...</p>';
        document.getElementById('details-modal').classList.remove('hidden');

        fetch('?action=admin_get_user_details&id=' + userId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    detailsContent.innerHTML = `
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-slate-500 uppercase font-semibold">First Name</p>
                                <p class="text-slate-900 font-semibold">${escapeHtml(user.Prenom)}</p>
                            </div>
                            <div>
                                <p class="text-sm text-slate-500 uppercase font-semibold">Last Name</p>
                                <p class="text-slate-900 font-semibold">${escapeHtml(user.Nom)}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-sm text-slate-500 uppercase font-semibold">Email</p>
                                <p class="text-slate-900 font-semibold">${escapeHtml(user.Email)}</p>
                            </div>
                            <div>
                                <p class="text-sm text-slate-500 uppercase font-semibold">Height (cm)</p>
                                <p class="text-slate-900 font-semibold">${user.Taille_cm || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-slate-500 uppercase font-semibold">Weight (kg)</p>
                                <p class="text-slate-900 font-semibold">${user.Poids_kg || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-slate-500 uppercase font-semibold">Level</p>
                                <p class="text-slate-900 font-semibold">${escapeHtml(user.Niveau_sportif || 'Beginner')}</p>
                            </div>
                            <div>
                                <p class="text-sm text-slate-500 uppercase font-semibold">Objective</p>
                                <p class="text-slate-900 font-semibold">${escapeHtml(user.Objectif || 'Not specified')}</p>
                            </div>
                            <div>
                                <p class="text-sm text-slate-500 uppercase font-semibold">Registered</p>
                                <p class="text-slate-900 font-semibold">${new Date(user.created_at).toLocaleDateString()}</p>
                            </div>
                        </div>
                    `;
                } else {
                    detailsContent.innerHTML = '<p class="text-red-600">' + (data.error || 'Error loading user details') + '</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                detailsContent.innerHTML = '<p class="text-red-600">An error occurred</p>';
            });
    }

    /**
     * Close details modal
     */
    function closeDetailsModal() {
        document.getElementById('details-modal').classList.add('hidden');
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ' + 
                         (type === 'error' ? 'bg-red-600' : 'bg-green-600') + ' text-white';
        
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Close modals on escape key
     */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
            closeDetailsModal();
        }
    });

    /**
     * Close modals on outside click
     */
    document.getElementById('delete-modal')?.addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    document.getElementById('details-modal')?.addEventListener('click', function(e) {
        if (e.target === this) closeDetailsModal();
    });

    /**
     * Logout function
     */
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = '?action=logout';
        }
    }
</script>

</body>
</html>
