<?php
/**
 * Vue Front Office - Meilleurs repas
 */
?>
<!DOCTYPE html>
<html class="light" lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>🏆 Meilleurs Repas - NutriNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Inter:wght@100..900&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#006e1c",
                        "primary-container": "#4caf50",
                        "on-primary": "#ffffff",
                        "secondary": "#4c56af",
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; }
        body { font-family: 'Inter', sans-serif; }
        h1, h2 { font-family: 'Manrope', sans-serif; }
        .star-rating { display: inline-flex; gap: 2px; }
        .star { color: #fbbf24; font-size: 1.25rem; }
    </style>
</head>
<body class="bg-gradient-to-b from-primary/5 to-white text-on-surface">

<!-- Navigation -->
<header class="fixed top-0 w-full z-50 bg-white/70 backdrop-blur-xl shadow-sm">
    <nav class="flex justify-between items-center px-8 h-16 w-full max-w-screen-2xl mx-auto">
        <div class="flex items-center gap-8">
            <a href="index.php" class="text-xl font-black tracking-tighter text-green-700">NutriNova</a>
            <div class="hidden md:flex gap-6 items-center">
                <a class="text-zinc-500 hover:text-green-600 font-['Manrope'] font-bold tracking-tight transition-colors" href="index.php">Catalogue</a>
                <a class="text-zinc-500 hover:text-green-600 font-['Manrope'] font-bold tracking-tight transition-colors" href="index.php?action=nutrition-plan">Plan Nutritionnel</a>
                <a class="text-green-700 border-b-2 border-green-700 pb-1 font-['Manrope'] font-bold tracking-tight" href="index.php?action=top-meals">🏆 Top Repas</a>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="index.php?action=admin-meals&section=meal" class="px-4 py-2 bg-primary text-white rounded-md font-semibold text-sm hover:brightness-110 transition-all">Admin</a>
        </div>
    </nav>
    <div class="bg-zinc-100 h-[1px] w-full"></div>
</header>

<main class="pt-28 pb-20 px-8 max-w-screen-xl mx-auto">

    <!-- Titre -->
    <section class="mb-12 text-center">
        <h1 class="text-5xl font-extrabold tracking-tight mb-3">🏆 Meilleurs Repas</h1>
        <p class="text-xl text-on-surface-variant max-w-2xl mx-auto">
            Découvrez les repas les mieux évalués par notre communauté. Cliquez sur un repas pour l'évaluer vous aussi !
        </p>
    </section>

    <!-- Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-xl flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span>
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <!-- Grille des meilleurs repas -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php if (empty($topMeals)): ?>
            <div class="col-span-3 text-center py-12">
                <span class="material-symbols-outlined text-8xl text-primary/20 mb-4">restaurant</span>
                <p class="text-lg text-on-surface-variant">Aucun repas n'a encore été évalué. Soyez les premiers !</p>
            </div>
        <?php else: ?>
            <?php $rank = 1; foreach ($topMeals as $meal): ?>
                <?php
                $rating = $meal['avg_rating'] ?? 0;
                $totalRatings = $meal['total_ratings'] ?? 0;
                $fullStars = floor($rating);
                $hasHalfStar = ($rating - $fullStars) >= 0.5;
                ?>
                <article class="group cursor-pointer transform transition-all hover:scale-105">
                    <a href="index.php?action=meal-detail&id=<?php echo $meal['id_meal']; ?>" class="block">
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-shadow overflow-hidden">
                            <!-- Image -->
                            <div class="relative aspect-square overflow-hidden bg-zinc-100">
                                <?php if (!empty($meal['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($meal['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($meal['nom']); ?>" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <span class="material-symbols-outlined text-9xl text-primary/10">restaurant</span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Badge Top -->
                                <?php if ($rank <= 3): ?>
                                    <div class="absolute top-4 right-4 bg-yellow-400 text-yellow-900 rounded-full w-12 h-12 flex items-center justify-center font-bold text-lg shadow-lg">
                                        <?php echo ['🥇', '🥈', '🥉'][$rank - 1]; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Contenu -->
                            <div class="p-6">
                                <h3 class="text-xl font-bold mb-2 group-hover:text-primary transition-colors">
                                    <?php echo htmlspecialchars($meal['nom']); ?>
                                </h3>

                                <!-- Type -->
                                <p class="text-sm text-on-surface-variant mb-3">
                                    <span class="font-semibold"><?php echo htmlspecialchars(ucfirst($meal['type'])); ?></span>
                                    • <?php echo number_format($meal['calories'], 0); ?> kcal
                                </p>

                                <!-- Étoiles -->
                                <div class="mb-4">
                                    <div class="star-rating mb-2">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <span class="material-symbols-outlined star <?php echo $i < $fullStars ? 'text-yellow-400' : ($i === $fullStars && $hasHalfStar ? 'text-yellow-400 opacity-50' : 'text-gray-300'); ?>">star</span>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-sm text-zinc-600">
                                        <strong><?php echo number_format($rating, 1); ?>/5</strong> 
                                        (<?php echo $totalRatings; ?> évaluation<?php echo $totalRatings !== 1 ? 's' : ''; ?>)
                                    </p>
                                </div>

                                <!-- CTA -->
                                <button class="w-full py-2 bg-primary text-white rounded-lg font-semibold text-sm hover:brightness-110 transition-all">
                                    Voir et évaluer
                                </button>
                            </div>
                        </div>
                    </a>
                </article>
                <?php $rank++; endforeach; ?>
        <?php endif; ?>
    </section>

</main>

<!-- Footer -->
<footer class="bg-zinc-50 border-t border-zinc-200 py-6 text-center text-sm text-zinc-400 mt-16">
    © 2026 NutriNova - Nutrition Intelligente
</footer>

</body>
</html>
