<?php
/**
 * Vue Front Office - Liste des repas
 */
?>
<!DOCTYPE html>

<html class="light" lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>NutriNova - Plateforme de Nutrition Intelligente</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Inter:wght@100..900&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "outline": "#6f7a6b",
                        "surface": "#f8faf8",
                        "on-tertiary": "#ffffff",
                        "outline-variant": "#becab9",
                        "on-surface-variant": "#3f4a3c",
                        "error-container": "#ffdad6",
                        "on-secondary-container": "#27308a",
                        "secondary-container": "#959efd",
                        "surface-container": "#eceeec",
                        "surface-dim": "#d8dad9",
                        "surface-container-low": "#f2f4f2",
                        "background": "#f8faf8",
                        "surface-container-highest": "#e1e3e1",
                        "error": "#ba1a1a",
                        "surface-tint": "#006e1c",
                        "surface-container-high": "#e6e9e7",
                        "on-background": "#191c1b",
                        "on-secondary-fixed-variant": "#343d96",
                        "on-secondary-fixed": "#000767",
                        "on-secondary": "#ffffff",
                        "tertiary-fixed-dim": "#ffaaf7",
                        "tertiary": "#a700ac",
                        "on-primary-container": "#003c0b",
                        "tertiary-container": "#f656f6",
                        "inverse-on-surface": "#eff1ef",
                        "surface-variant": "#e1e3e1",
                        "on-tertiary-container": "#5f0062",
                        "on-primary-fixed": "#002204",
                        "secondary-fixed-dim": "#bdc2ff",
                        "primary-fixed-dim": "#78dc77",
                        "secondary-fixed": "#e0e0ff",
                        "on-primary-fixed-variant": "#005313",
                        "primary": "#006e1c",
                        "secondary": "#4c56af",
                        "surface-container-lowest": "#ffffff",
                        "primary-fixed": "#94f990",
                        "on-error-container": "#93000a",
                        "primary-container": "#4caf50",
                        "on-error": "#ffffff",
                        "on-tertiary-fixed": "#380039",
                        "inverse-surface": "#2e3130",
                        "on-tertiary-fixed-variant": "#800084",
                        "inverse-primary": "#78dc77",
                        "on-surface": "#191c1b",
                        "tertiary-fixed": "#ffd7f7",
                        "surface-bright": "#f8faf8",
                        "on-primary": "#ffffff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "fontFamily": {
                        "headline": ["Manrope"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .brand-font { font-family: 'Manrope', sans-serif; }
    </style>
</head>
<body class="bg-surface text-on-surface">
<!-- TopNavBar -->
<header class="fixed top-0 w-full z-50 bg-white/70 backdrop-blur-xl shadow-sm">
    <nav class="flex justify-between items-center px-8 h-16 w-full max-w-screen-2xl mx-auto">
        <div class="flex items-center gap-8">
            <a href="index.php" class="text-xl font-black tracking-tighter text-green-700">NutriNova</a>
            <div class="hidden md:flex gap-6 items-center">
                <a class="text-green-700 border-b-2 border-green-700 pb-1 font-['Manrope'] font-bold tracking-tight transition-colors duration-200 ease-in-out" href="index.php">Catalogue</a>
                <a class="text-zinc-500 hover:text-green-600 font-['Manrope'] font-bold tracking-tight transition-colors duration-200 ease-in-out" href="#">Plan Nutritionnel</a>
                <a class="text-zinc-500 hover:text-green-600 font-['Manrope'] font-bold tracking-tight transition-colors duration-200 ease-in-out" href="#">Communauté</a>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <button class="px-4 py-2 bg-primary text-white rounded-md font-semibold text-sm hover:brightness-110 transition-all">
                <a href="index.php?action=admin-meals&section=meal" class="text-white">Admin</a>
            </button>
        </div>
    </nav>
    <div class="bg-zinc-100 h-[1px] w-full"></div>
</header>

<main class="pt-24 pb-20 px-8 max-w-screen-2xl mx-auto">
    <!-- Messages d'alerte -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <!-- Editorial Hero Section -->
    <section class="mb-16">
        <div class="flex flex-col md:flex-row gap-12 items-end">
            <div class="flex-1">
                <h1 class="text-6xl font-extrabold tracking-tight text-on-surface mb-6 leading-none">
                    La Cuisine <br/><span class="text-primary italic">Intelligente</span>
                </h1>
                <p class="text-lg text-on-surface-variant max-w-xl body-sm">
                    Explorez notre bibliothèque de repas scientifiquement validés, conçus pour votre longevité et votre santé. Tous les ingrédients sont soigneusement sélectionnés.
                </p>
            </div>
            <div class="flex gap-4 pb-2">
                <div class="flex flex-col items-center p-4 bg-primary-container/20 rounded-xl min-w-[120px]">
                    <span class="text-2xl font-bold text-primary"><?php echo count($meals); ?></span>
                    <span class="text-xs uppercase tracking-widest text-on-surface-variant font-semibold">Repas</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Search & Filter Controls -->
    <section class="mb-12 sticky top-20 z-40 bg-surface/80 backdrop-blur-md py-4">
        <div class="flex gap-4">
            <button class="px-6 py-2 bg-primary text-on-primary rounded-md font-medium text-sm transition-all hover:translate-y-[-1px]">Tous les repas</button>
            <button class="px-6 py-2 bg-secondary-fixed text-on-secondary-fixed rounded-md font-medium text-sm hover:bg-secondary-fixed-dim transition-all">Petit déjeuner</button>
            <button class="px-6 py-2 bg-secondary-fixed text-on-secondary-fixed rounded-md font-medium text-sm hover:bg-secondary-fixed-dim transition-all">Déjeuner</button>
            <button class="px-6 py-2 bg-secondary-fixed text-on-secondary-fixed rounded-md font-medium text-sm hover:bg-secondary-fixed-dim transition-all">Dîner</button>
        </div>
    </section>

    <!-- Recipe Grid -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php if (empty($meals)): ?>
            <div class="col-span-3 text-center py-12">
                <p class="text-on-surface-variant text-lg">Aucun repas disponible pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($meals as $index => $meal): ?>
                <article class="<?php echo $index === 0 ? 'md:col-span-2 md:row-span-2' : ''; ?> group cursor-pointer">
                    <div class="bg-surface-container-lowest rounded-xl shadow-sm h-full flex flex-col overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="relative aspect-square overflow-hidden bg-surface-container-low">
                            <?php if (!empty($meal['image'])): ?>
                                <img src="<?php echo htmlspecialchars($meal['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($meal['nom']); ?>" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-9xl text-primary/10">restaurant</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-6 flex flex-col justify-between flex-grow">
                            <div>
                                <h3 class="text-2xl font-bold tracking-tight mb-2 group-hover:text-primary transition-colors">
                                    <?php echo htmlspecialchars($meal['nom']); ?>
                                </h3>
                                <p class="text-sm text-on-surface-variant line-clamp-3 mb-4">
                                    Type: <span class="font-semibold"><?php echo htmlspecialchars(ucfirst($meal['type'])); ?></span>
                                </p>
                            </div>
                            <div>
                                <div class="flex justify-between items-center text-xs font-bold text-zinc-400 uppercase tracking-widest pt-4 border-t border-zinc-100 mb-4">
                                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-base">local_fire_department</span> <?php echo number_format($meal['calories'], 0); ?> kcal</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <div class="bg-primary/10 p-2 rounded text-center">
                                        <p class="font-bold text-primary"><?php echo number_format($meal['protein'], 1); ?>g</p>
                                        <p class="text-on-surface-variant">Protéines</p>
                                    </div>
                                    <div class="bg-secondary/10 p-2 rounded text-center">
                                        <p class="font-bold text-secondary"><?php echo number_format($meal['carb'], 1); ?>g</p>
                                        <p class="text-on-surface-variant">Glucides</p>
                                    </div>
                                    <div class="bg-tertiary/10 p-2 rounded text-center">
                                        <p class="font-bold text-tertiary"><?php echo number_format($meal['fat'], 1); ?>g</p>
                                        <p class="text-on-surface-variant">Lipides</p>
                                    </div>
                                </div>
                                <a href="index.php?action=meal-detail&id=<?php echo $meal['id_meal']; ?>" class="mt-4 block w-full px-4 py-2 bg-primary text-on-primary rounded-md font-semibold text-sm hover:brightness-110 transition-all text-center">
                                    Voir détails
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<!-- Footer -->
<footer class="bg-surface-container-low pt-16 pb-8 px-8 border-t border-zinc-200">
    <div class="max-w-screen-2xl mx-auto text-center">
        <p class="text-sm text-zinc-500">© 2026 NutriNova - Nutrition Intelligente</p>
    </div>
</footer>
</body>
</html>
