<?php
/**
 * Vue Back Office - Liste des repas
 */
?>
<!DOCTYPE html>

<html class="light" lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Gestion Repas - Admin NutriNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    "colors": {
                        "primary": "#006e1c",
                        "primary-container": "#4caf50",
                        "on-primary": "#ffffff",
                        "secondary": "#4c56af",
                        "surface": "#f8faf8",
                        "on-surface": "#191c1b"
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Manrope', sans-serif; }
    </style>
</head>
<body class="bg-surface text-on-surface">
<!-- Navigation Drawer -->
<aside class="h-screen w-64 fixed left-0 top-0 bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200/50 flex flex-col p-4 space-y-2 z-40">
    <div class="mb-8 px-2">
        <h1 class="text-lg font-bold text-zinc-900">Admin NutriNova</h1>
        <p class="text-[10px] uppercase tracking-[0.2em] text-zinc-500 font-bold">Gestion Nutrition</p>
    </div>
    <nav class="flex-1 space-y-1">
        <a class="flex items-center gap-3 px-3 py-2 bg-green-50 text-green-700 font-semibold rounded-lg transition-all" href="index.php?action=admin-meals&section=meal">
            <span class="material-symbols-outlined">restaurant_menu</span>
            Gestion Repas
        </a>
        <a class="flex items-center gap-3 px-3 py-2 text-zinc-600 hover:bg-zinc-100 rounded-lg transition-all" href="index.php?action=admin-ingredients&section=ingredient">
            <span class="material-symbols-outlined">liquor</span>
            Ingrédients
        </a>
    </nav>
    <div class="pt-4 border-t border-zinc-200">
        <a class="flex items-center gap-3 px-3 py-2 text-zinc-600 hover:bg-zinc-100 rounded-lg transition-all" href="index.php">
            <span class="material-symbols-outlined">logout</span>
            Accueil Front
        </a>
    </div>
</aside>

<!-- Main Content -->
<main class="ml-64 min-h-screen">
    <!-- Header -->
    <header class="h-16 flex items-center justify-between px-8 bg-surface/70 backdrop-blur-xl sticky top-0 z-30 border-b border-zinc-100">
        <h2 class="text-2xl font-bold">Gestion des Repas</h2>
        <a href="index.php?action=admin-meal-add&section=meal" class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-md font-medium hover:brightness-110 transition-all">
            <span class="material-symbols-outlined">add</span>
            Nouveau Repas
        </a>
    </header>

    <!-- Content -->
    <div class="p-8 max-w-7xl mx-auto space-y-8">
        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center gap-2">
                <span class="material-symbols-outlined">check_circle</span>
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center gap-2">
                <span class="material-symbols-outlined">error</span>
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-6 bg-white rounded-lg shadow-sm border border-zinc-100">
                <p class="text-xs text-zinc-500 uppercase font-bold mb-2">Total Repas</p>
                <p class="text-3xl font-bold text-primary"><?php echo count($meals); ?></p>
            </div>
            <div class="p-6 bg-white rounded-lg shadow-sm border border-zinc-100">
                <p class="text-xs text-zinc-500 uppercase font-bold mb-2">Petit Déjeuner</p>
                <p class="text-3xl font-bold"><?php echo count(array_filter($meals, fn($m) => strtolower($m['type']) === 'petit déjeuner')); ?></p>
            </div>
            <div class="p-6 bg-white rounded-lg shadow-sm border border-zinc-100">
                <p class="text-xs text-zinc-500 uppercase font-bold mb-2">Déjeuner/Dîner</p>
                <p class="text-3xl font-bold"><?php echo count(array_filter($meals, fn($m) => strtolower($m['type']) !== 'petit déjeuner')); ?></p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-200">
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider">Calories</th>
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider">Protéines</th>
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider">Ingrédients</th>
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    <?php if (empty($meals)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-zinc-500">
                                Aucun repas. <a href="index.php?action=admin-meal-add&section=meal" class="text-primary font-semibold">Créer le premier</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($meals as $meal): ?>
                            <tr class="hover:bg-zinc-50 transition-colors">
                                <td class="px-6 py-4 font-semibold text-on-surface"><?php echo htmlspecialchars($meal['nom']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-secondary/20 text-secondary text-xs font-bold rounded">
                                        <?php echo htmlspecialchars(ucfirst($meal['type'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4"><?php echo number_format($meal['calories'], 0); ?> kcal</td>
                                <td class="px-6 py-4"><?php echo number_format($meal['protein'], 1); ?>g</td>
                                <td class="px-6 py-4">
                                    <a href="index.php?action=admin-meal-edit&section=meal&id=<?php echo $meal['id_meal']; ?>" class="text-blue-600 hover:text-blue-800 font-semibold text-xs">
                                        Gérer
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="index.php?action=admin-meal-edit&section=meal&id=<?php echo $meal['id_meal']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                            <span class="material-symbols-outlined">edit</span>
                                        </a>
                                        <a href="index.php?action=admin-meal-delete&section=meal&id=<?php echo $meal['id_meal']; ?>" onclick="return confirm('Êtes-vous sûr?')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                            <span class="material-symbols-outlined">delete</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
