<?php
/**
 * Vue Front Office - Détail d'un repas
 */

$totalMacros = max(0.1, $meal['protein'] + $meal['carb'] + $meal['fat']);
$proteinPercent = ($meal['protein'] / $totalMacros) * 100;
$carbPercent = ($meal['carb'] / $totalMacros) * 100;
$fatPercent = ($meal['fat'] / $totalMacros) * 100;
$ingredientContribution = [];

foreach ($meal['ingredients'] as $ingredient) {
    $ingredientCalories = (float) $ingredient['calories'] * (float) $ingredient['quantity'];
    $ingredientContribution[] = [
        'nom' => $ingredient['nom'],
        'calories' => $ingredientCalories,
        'ratio' => $meal['calories'] > 0 ? ($ingredientCalories / $meal['calories']) * 100 : 0,
    ];
}

usort($ingredientContribution, function ($left, $right) {
    return $right['calories'] <=> $left['calories'];
});

$topIngredientContribution = array_slice($ingredientContribution, 0, 5);
?>
<!DOCTYPE html>

<html class="light" lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo htmlspecialchars($meal['nom']); ?> - NutriNova</title>
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
        </div>
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-zinc-500 hover:text-green-600 font-['Manrope'] font-bold tracking-tight transition-colors duration-200">Retour</a>
        </div>
    </nav>
    <div class="bg-zinc-100 h-[1px] w-full"></div>
</header>

<main class="pt-24 pb-20 px-8 max-w-screen-2xl mx-auto">
    <!-- Hero Section -->
    <section class="mb-12">
        <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
            <div class="aspect-video bg-surface-container-low flex items-center justify-center overflow-hidden">
                <?php if (!empty($meal['image'])): ?>
                    <img src="<?php echo htmlspecialchars($meal['image']); ?>" 
                         alt="<?php echo htmlspecialchars($meal['nom']); ?>" 
                         class="w-full h-full object-cover">
                <?php else: ?>
                    <span class="material-symbols-outlined text-9xl text-primary/10">restaurant</span>
                <?php endif; ?>
            </div>
            <div class="p-12">
                <div class="flex items-start justify-between mb-8">
                    <div>
                        <h1 class="text-5xl font-bold tracking-tight text-on-surface mb-2">
                            <?php echo htmlspecialchars($meal['nom']); ?>
                        </h1>
                        <p class="text-lg text-on-surface-variant">
                            Type: <span class="font-semibold text-primary"><?php echo htmlspecialchars(ucfirst($meal['type'])); ?></span>
                        </p>
                    </div>
                </div>

                <!-- Nutrition Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12">
                    <div class="p-6 bg-primary/5 rounded-xl border border-primary/10">
                        <span class="material-symbols-outlined text-primary text-4xl mb-2 block">local_fire_department</span>
                        <p class="text-3xl font-bold text-on-surface"><?php echo number_format($meal['calories'], 0); ?></p>
                        <p class="text-sm text-on-surface-variant">Calories</p>
                    </div>
                    <div class="p-6 bg-secondary/5 rounded-xl border border-secondary/10">
                        <span class="material-symbols-outlined text-secondary text-4xl mb-2 block">fitness_center</span>
                        <p class="text-3xl font-bold text-on-surface"><?php echo number_format($meal['protein'], 1); ?>g</p>
                        <p class="text-sm text-on-surface-variant">Protéines</p>
                    </div>
                    <div class="p-6 bg-tertiary/5 rounded-xl border border-tertiary/10">
                        <span class="material-symbols-outlined text-tertiary text-4xl mb-2 block">grain</span>
                        <p class="text-3xl font-bold text-on-surface"><?php echo number_format($meal['carb'], 1); ?>g</p>
                        <p class="text-sm text-on-surface-variant">Glucides</p>
                    </div>
                    <div class="p-6 bg-orange-500/5 rounded-xl border border-orange-500/10">
                        <span class="material-symbols-outlined text-orange-500 text-4xl mb-2 block">blender</span>
                        <p class="text-3xl font-bold text-on-surface"><?php echo number_format($meal['fat'], 1); ?>g</p>
                        <p class="text-sm text-on-surface-variant">Lipides</p>
                    </div>
                </div>

                <!-- Recommandations -->
                <div class="p-6 bg-surface-container-low rounded-xl mb-12">
                    <h2 class="text-xl font-bold mb-4">Recommandations nutritionnelles</h2>
                    <ul class="space-y-2 text-on-surface-variant">
                        <?php if ($meal['calories'] < 300): ?>
                            <li class="flex items-start gap-2"><span class="text-primary font-bold">•</span> Repas léger, idéal comme collation</li>
                        <?php elseif ($meal['calories'] < 600): ?>
                            <li class="flex items-start gap-2"><span class="text-primary font-bold">•</span> Portion équilibrée, parfait pour un repas principal</li>
                        <?php else: ?>
                            <li class="flex items-start gap-2"><span class="text-primary font-bold">•</span> Repas complet et nourrissant</li>
                        <?php endif; ?>
                        
                        <?php if ($meal['protein'] > $meal['calories'] * 0.15 / 4): ?>
                            <li class="flex items-start gap-2"><span class="text-primary font-bold">•</span> Riche en protéines, excellent pour la muscle</li>
                        <?php endif; ?>
                        
                        <?php if ($meal['fat'] < $meal['calories'] * 0.35 / 9): ?>
                            <li class="flex items-start gap-2"><span class="text-primary font-bold">•</span> Modéré en lipides, bonne option pour santé cardiaque</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-[1.2fr_0.8fr] gap-8">
                    <section class="p-6 bg-surface-container-low rounded-xl">
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <h2 class="text-xl font-bold">Statistique nutritionnelle</h2>
                                <p class="text-sm text-on-surface-variant">Répartition des macronutriments du repas.</p>
                            </div>
                            <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-bold uppercase tracking-widest text-primary">Analyse</span>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <div class="mb-2 flex items-center justify-between text-sm font-semibold">
                                    <span>Protéines</span>
                                    <span><?php echo number_format($proteinPercent, 1); ?>%</span>
                                </div>
                                <div class="h-3 rounded-full bg-white overflow-hidden">
                                    <div class="h-full rounded-full bg-primary" style="width: <?php echo number_format($proteinPercent, 2, '.', ''); ?>%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-2 flex items-center justify-between text-sm font-semibold">
                                    <span>Glucides</span>
                                    <span><?php echo number_format($carbPercent, 1); ?>%</span>
                                </div>
                                <div class="h-3 rounded-full bg-white overflow-hidden">
                                    <div class="h-full rounded-full bg-secondary" style="width: <?php echo number_format($carbPercent, 2, '.', ''); ?>%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-2 flex items-center justify-between text-sm font-semibold">
                                    <span>Lipides</span>
                                    <span><?php echo number_format($fatPercent, 1); ?>%</span>
                                </div>
                                <div class="h-3 rounded-full bg-white overflow-hidden">
                                    <div class="h-full rounded-full bg-orange-500" style="width: <?php echo number_format($fatPercent, 2, '.', ''); ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="p-6 bg-surface-container-low rounded-xl">
                        <h2 class="text-xl font-bold mb-2">Top ingrédients énergétiques</h2>
                        <p class="text-sm text-on-surface-variant mb-5">Contribution calorique des principaux ingrédients du repas.</p>
                        <?php if (empty($topIngredientContribution)): ?>
                            <p class="text-sm text-on-surface-variant">Aucune donnée disponible.</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($topIngredientContribution as $stat): ?>
                                    <div>
                                        <div class="mb-2 flex items-center justify-between gap-4 text-sm">
                                            <span class="font-semibold text-on-surface"><?php echo htmlspecialchars($stat['nom']); ?></span>
                                            <span class="text-on-surface-variant"><?php echo number_format($stat['calories'], 0); ?> kcal</span>
                                        </div>
                                        <div class="h-3 rounded-full bg-white overflow-hidden">
                                            <div class="h-full rounded-full bg-tertiary" style="width: <?php echo number_format(min(100, $stat['ratio']), 2, '.', ''); ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </div>
    </section>

    <!-- Ingrédients -->
    <section class="mb-12">
        <h2 class="text-3xl font-bold mb-6">Ingrédients (<?php echo count($meal['ingredients']); ?>)</h2>
        
        <?php if (empty($meal['ingredients'])): ?>
            <div class="p-8 text-center bg-surface-container-low rounded-xl">
                <p class="text-on-surface-variant">Aucun ingrédient associé.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($meal['ingredients'] as $ingredient): ?>
                    <div class="p-6 bg-surface-container-lowest rounded-xl border border-zinc-200 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <h3 class="text-lg font-bold text-on-surface">
                                <?php echo htmlspecialchars($ingredient['nom']); ?>
                            </h3>
                            <span class="text-xs font-bold bg-primary/10 text-primary px-2 py-1 rounded">
                                <?php echo number_format($ingredient['quantity'], 1); ?> unité(s)
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-on-surface-variant text-xs uppercase tracking-widest font-bold mb-1">Calories</p>
                                <p class="font-semibold"><?php echo number_format($ingredient['calories'] * $ingredient['quantity'], 0); ?> kcal</p>
                            </div>
                            <div>
                                <p class="text-on-surface-variant text-xs uppercase tracking-widest font-bold mb-1">Protéines</p>
                                <p class="font-semibold"><?php echo number_format($ingredient['protein'] * $ingredient['quantity'], 1); ?>g</p>
                            </div>
                            <div>
                                <p class="text-on-surface-variant text-xs uppercase tracking-widest font-bold mb-1">Glucides</p>
                                <p class="font-semibold"><?php echo number_format($ingredient['carb'] * $ingredient['quantity'], 1); ?>g</p>
                            </div>
                            <div>
                                <p class="text-on-surface-variant text-xs uppercase tracking-widest font-bold mb-1">Lipides</p>
                                <p class="font-semibold"><?php echo number_format($ingredient['fat'] * $ingredient['quantity'], 1); ?>g</p>
                            </div>
                        </div>
                        
                        <?php if ($ingredient['eco_score']): ?>
                            <div class="mt-4 pt-4 border-t border-zinc-100">
                                <p class="text-xs text-on-surface-variant">Eco-score: <span class="font-bold"><?php echo htmlspecialchars($ingredient['eco_score']); ?></span></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Évaluations -->
    <section class="space-y-8">
        <!-- Résumé des évaluations -->
        <?php
        $controller = new NutritionController();
        $ratings = $controller->getMealRatings($meal['id_meal']);
        $avgRating = $controller->getMealAverageRating($meal['id_meal']);
        $avgScore = (float)($avgRating['avg_rating'] ?? 0);
        $totalRatings = (int)($avgRating['total_ratings'] ?? 0);
        $fullStars = floor($avgScore);
        $hasHalfStar = ($avgScore - $fullStars) >= 0.5;
        ?>

        <!-- En-tête évaluations -->
        <div class="bg-gradient-to-r from-yellow-50 to-amber-50 rounded-2xl border border-yellow-200 p-8">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold mb-2">Évaluation communautaire</h2>
                    <p class="text-on-surface-variant">Partagez votre avis sur ce repas</p>
                </div>
                <div class="mt-6 md:mt-0 text-center md:text-right">
                    <div class="flex justify-center md:justify-end gap-1 mb-2">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <span class="material-symbols-outlined text-3xl <?php echo $i < $fullStars ? 'text-yellow-400' : ($i === $fullStars && $hasHalfStar ? 'text-yellow-400 opacity-50' : 'text-gray-300'); ?>">star</span>
                        <?php endfor; ?>
                    </div>
                    <p class="text-2xl font-bold"><?php echo number_format($avgScore, 1); ?>/5</p>
                    <p class="text-sm text-on-surface-variant">
                        <?php echo $totalRatings; ?> évaluation<?php echo $totalRatings !== 1 ? 's' : ''; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulaire d'évaluation -->
        <div class="bg-white rounded-2xl border border-zinc-200 p-8">
            <h3 class="text-2xl font-bold mb-6">✍️ Laisser une évaluation</h3>
            
            <form method="POST" action="index.php?action=add-rating" class="space-y-6">
                <input type="hidden" name="meal_id" value="<?php echo $meal['id_meal']; ?>">

                <!-- Note -->
                <div>
                    <label class="block text-sm font-bold mb-3">Votre note *</label>
                    <div class="flex gap-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="cursor-pointer group">
                                <input type="radio" name="rating" value="<?php echo $i; ?>" class="hidden peer" required>
                                <span class="material-symbols-outlined text-4xl text-gray-300 group-hover:text-yellow-400 peer-checked:text-yellow-400 transition-colors">star</span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Nom -->
                <div>
                    <label for="name" class="block text-sm font-bold mb-2">Votre nom (optionnel)</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="Entrez votre nom ou restez anonyme"
                        maxlength="100"
                        class="w-full px-4 py-2 border border-zinc-200 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-bold mb-2">Email (optionnel)</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="votre@email.com"
                        maxlength="255"
                        class="w-full px-4 py-2 border border-zinc-200 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                </div>

                <!-- Commentaire -->
                <div>
                    <label for="comment" class="block text-sm font-bold mb-2">Commentaire (optionnel)</label>
                    <textarea 
                        id="comment" 
                        name="comment" 
                        placeholder="Partagerez votre expérience avec ce repas..."
                        maxlength="500"
                        rows="4"
                        class="w-full px-4 py-2 border border-zinc-200 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none resize-none"></textarea>
                </div>

                <button type="submit" class="w-full py-3 bg-primary text-white rounded-lg font-bold hover:brightness-110 transition-all">
                    Soumettre mon évaluation
                </button>
            </form>
        </div>

        <!-- Liste des évaluations -->
        <?php if (!empty($ratings)): ?>
        <div class="bg-white rounded-2xl border border-zinc-200 p-8">
            <h3 class="text-2xl font-bold mb-6">Avis des visiteurs</h3>
            <div class="space-y-6">
                <?php foreach ($ratings as $rating): ?>
                    <div class="pb-6 border-b border-zinc-100 last:border-0 last:pb-0">
                        <!-- En-tête avis -->
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <p class="font-bold"><?php echo htmlspecialchars($rating['visitor_name'] ?? 'Anonyme'); ?></p>
                                <p class="text-xs text-on-surface-variant">
                                    <?php echo date('d M Y à H:i', strtotime($rating['created_at'])); ?>
                                </p>
                            </div>
                            <!-- Étoiles -->
                            <div class="flex gap-1">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <span class="material-symbols-outlined text-lg <?php echo $i < (int)$rating['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>">star</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Commentaire -->
                        <?php if (!empty($rating['comment'])): ?>
                            <p class="text-on-surface text-sm">
                                <?php echo htmlspecialchars($rating['comment']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <!-- CTA -->
    <section class="text-center">
        <a href="index.php" class="inline-block px-8 py-4 bg-primary text-on-primary rounded-lg font-bold text-lg hover:brightness-110 transition-all">
            Voir d'autres repas
        </a>
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
