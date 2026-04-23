<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>NutriNova Admin - Post Management</title>
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
        <a href="?action=admin_list_posts" class="nav-link flex items-center gap-4 px-4 py-3 rounded-xl transition-colors duration-200 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 cursor-pointer active" style="color: #006e1c; font-weight: bold; border-right: 4px solid #006e1c; background-color: rgba(76, 175, 80, 0.1);">
            <span class="material-symbols-outlined">article</span>
            <span class="text-sm font-semibold">Post Management</span>
        </a>
        
        <a href="?action=admin_list_users" class="flex items-center gap-4 px-4 py-3 rounded-xl transition-colors duration-200 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 cursor-pointer">
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
        <h1 class="text-xl font-bold font-headline text-blue-900 dark:text-blue-300">Post Management</h1>
    </div>
</header>

<!-- Main Content -->
<main class="ml-72 pt-28 px-12 pb-12">
    <div class="mb-10">
        <span class="text-tertiary font-semibold text-sm tracking-wider uppercase">Dashboard</span>
        <h2 class="text-4xl font-extrabold font-headline text-slate-900 mt-2 tracking-tight">Post Management</h2>
        <p class="text-slate-600 mt-2 max-w-2xl text-lg">View, edit, and manage all community posts.</p>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-semibold uppercase">Total Posts</p>
                    <h3 class="text-4xl font-extrabold text-slate-900 mt-2"><?php echo htmlspecialchars($totalPosts); ?></h3>
                </div>
                <div class="p-4 bg-green-100 rounded-2xl">
                    <span class="material-symbols-outlined text-green-700">article</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-semibold uppercase">This Page</p>
                    <h3 class="text-4xl font-extrabold text-slate-900 mt-2"><?php echo htmlspecialchars(count($posts)); ?></h3>
                </div>
                <div class="p-4 bg-blue-100 rounded-2xl">
                    <span class="material-symbols-outlined text-blue-700">list</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Posts Table -->
    <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-100">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Post ID</th>
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Title</th>
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Author</th>
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Content Preview</th>
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Created Date</th>
                        <th class="px-8 py-4 text-left text-sm font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($posts) > 0): ?>
                        <?php foreach ($posts as $post): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-8 py-4">
                                <span class="inline-block px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-sm font-semibold">
                                    <?php echo htmlspecialchars($post['id']); ?>
                                </span>
                            </td>
                            <td class="px-8 py-4">
                                <p class="font-semibold text-slate-900 max-w-xs truncate"><?php echo htmlspecialchars($post['titre_post']); ?></p>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-slate-600"><?php echo htmlspecialchars($post['nom_auteur']); ?></p>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-slate-600 text-sm max-w-sm truncate"><?php echo htmlspecialchars(substr($post['contenu_post'], 0, 60) . '...'); ?></p>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-slate-600 text-sm"><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></p>
                            </td>
                            <td class="px-8 py-4">
                                <div class="flex gap-2">
                                    <a href="?action=admin_edit_post&id=<?php echo $post['id']; ?>" class="p-2 hover:bg-blue-100 rounded-lg transition-colors" title="Edit">
                                        <span class="material-symbols-outlined text-blue-600">edit</span>
                                    </a>
                                    <button onclick="confirmDeletePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['titre_post']); ?>')" class="p-2 hover:bg-red-100 rounded-lg transition-colors" title="Delete">
                                        <span class="material-symbols-outlined text-red-600">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-8 py-12 text-center">
                                <p class="text-slate-500 text-lg">No posts found</p>
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
            <a href="?action=admin_list_posts&page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition">Previous</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="px-4 py-2 bg-green-700 text-white rounded-lg"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?action=admin_list_posts&page=<?php echo $i; ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?action=admin_list_posts&page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition">Next</a>
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
            <h3 class="text-xl font-bold text-slate-900">Delete Post</h3>
        </div>
        <p class="text-slate-600 mb-6">Are you sure you want to delete <strong id="delete-post-title"></strong>? This action cannot be undone.</p>
        <div class="flex gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-slate-200 text-slate-900 rounded-lg hover:bg-slate-300 transition font-semibold">Cancel</button>
            <button onclick="executeDelete()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">Delete</button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50"></div>

<script>
    let postToDelete = null;

    /**
     * Show delete confirmation modal
     */
    function confirmDeletePost(postId, postTitle) {
        postToDelete = postId;
        document.getElementById('delete-post-title').textContent = postTitle;
        document.getElementById('delete-modal').classList.remove('hidden');
    }

    /**
     * Close delete modal
     */
    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.add('hidden');
        postToDelete = null;
    }

    /**
     * Execute post deletion
     */
    function executeDelete() {
        if (!postToDelete) return;

        const formData = new FormData();
        formData.append('id', postToDelete);

        fetch('?action=admin_delete_post', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Post deleted successfully');
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
            closeDeleteModal();
        });
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.classList.remove('hidden');
        
        if (type === 'error') {
            toast.style.backgroundColor = '#ef4444';
        } else {
            toast.style.backgroundColor = '#22c55e';
        }
        
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    /**
     * Logout function
     */
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            // Implement logout logic here
            window.location.href = '?action=logout';
        }
    }

    // Close modal when clicking outside
    document.getElementById('delete-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
</script>

</body>
</html>
