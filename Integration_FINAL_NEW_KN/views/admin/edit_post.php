<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>NutriNova Admin - Edit Post</title>
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
        <h1 class="text-xl font-bold font-headline text-blue-900 dark:text-blue-300">Edit Post</h1>
    </div>
</header>

<!-- Main Content -->
<main class="ml-72 pt-28 px-12 pb-12">
    <div class="mb-10">
        <a href="?action=admin_list_posts" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 transition-colors mb-4">
            <span class="material-symbols-outlined">arrow_back</span>
            <span class="text-sm font-semibold">Back to Posts</span>
        </a>
        <span class="text-tertiary font-semibold text-sm tracking-wider uppercase">Edit</span>
        <h2 class="text-4xl font-extrabold font-headline text-slate-900 mt-2 tracking-tight">Edit Post</h2>
        <p class="text-slate-600 mt-2 max-w-2xl text-lg">Update the post content and title.</p>
    </div>

    <!-- Edit Form -->
    <div class="max-w-4xl">
        <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-100 p-8">
            <form id="editPostForm" onsubmit="handleSubmit(event)">
                <input type="hidden" id="postId" name="id" value="<?php echo htmlspecialchars($post['id']); ?>">
                
                <!-- Post ID (Read-only) -->
                <div class="mb-8">
                    <label for="postIdReadonly" class="block text-sm font-semibold text-slate-700 mb-2">Post ID</label>
                    <input type="text" id="postIdReadonly" class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 font-mono" value="<?php echo htmlspecialchars($post['id']); ?>" readonly>
                </div>

                <!-- Author (Read-only) -->
                <div class="mb-8">
                    <label for="author" class="block text-sm font-semibold text-slate-700 mb-2">Author</label>
                    <input type="text" id="author" class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-600" value="<?php echo htmlspecialchars($post['nom_auteur']); ?>" readonly>
                </div>

                <!-- Created Date (Read-only) -->
                <div class="mb-8">
                    <label for="createdDate" class="block text-sm font-semibold text-slate-700 mb-2">Created Date</label>
                    <input type="text" id="createdDate" class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-600" value="<?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?>" readonly>
                </div>

                <!-- Title -->
                <div class="mb-8">
                    <label for="titre_post" class="block text-sm font-semibold text-slate-700 mb-2">Post Title <span class="text-red-600">*</span></label>
                    <input type="text" id="titre_post" name="titre_post" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all" value="<?php echo htmlspecialchars($post['titre_post']); ?>" required maxlength="255">
                    <p class="text-xs text-slate-500 mt-2">Maximum 255 characters</p>
                </div>

                <!-- Content -->
                <div class="mb-8">
                    <label for="contenu_post" class="block text-sm font-semibold text-slate-700 mb-2">Post Content <span class="text-red-600">*</span></label>
                    <textarea id="contenu_post" name="contenu_post" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all" rows="12" required maxlength="10000"><?php echo htmlspecialchars($post['contenu_post']); ?></textarea>
                    <p class="text-xs text-slate-500 mt-2">Maximum 10000 characters</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 pt-8 border-t border-slate-200">
                    <a href="?action=admin_list_posts" class="flex-1 px-6 py-3 bg-slate-200 text-slate-900 rounded-xl hover:bg-slate-300 transition font-semibold flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">close</span>
                        Cancel
                    </a>
                    <button type="submit" class="flex-1 px-6 py-3 bg-green-700 text-white rounded-xl hover:bg-green-800 transition font-semibold flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">save</span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</main>

<!-- Loading Overlay -->
<div id="loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8">
        <div class="flex items-center gap-3">
            <div class="animate-spin">
                <span class="material-symbols-outlined">hourglass_empty</span>
            </div>
            <p class="text-slate-900 font-semibold">Saving changes...</p>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50"></div>

<script>
    /**
     * Handle form submission
     */
    function handleSubmit(event) {
        event.preventDefault();
        
        const form = document.getElementById('editPostForm');
        const formData = new FormData(form);
        
        // Show loading overlay
        document.getElementById('loading-overlay').classList.remove('hidden');
        
        fetch('?action=admin_update_post', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('loading-overlay').classList.add('hidden');
            
            if (data.success) {
                showToast('Post updated successfully');
                setTimeout(() => {
                    window.location.href = '?action=admin_list_posts';
                }, 1500);
            } else {
                showToast('Error: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('loading-overlay').classList.add('hidden');
            showToast('An error occurred', 'error');
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
            window.location.href = '?action=logout';
        }
    }
</script>

</body>
</html>
