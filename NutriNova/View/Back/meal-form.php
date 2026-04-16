<?php
/**
 * Vue Back Office - Formulaire Repas
 */
$is_edit = isset($meal) && $meal !== null;
$action_url = $is_edit 
    ? 'index.php?action=admin-meal-update&section=meal'
    : 'index.php?action=admin-meal-create&section=meal';
$page_title = $is_edit ? 'Modifier Repas' : 'Nouveau Repas';
?>
<!DOCTYPE html>

<html class="light" lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo $page_title; ?> - Admin NutriNova</title>
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
        h1, h2 { font-family: 'Manrope', sans-serif; }
    </style>
</head>
<body class="bg-surface text-on-surface">
<!-- Navigation -->
<aside class="h-screen w-64 fixed left-0 top-0 bg-zinc-50 border-r border-zinc-200/50 flex flex-col p-4 space-y-2 z-40">
    <div class="mb-8 px-2">
        <h1 class="text-lg font-bold">Admin NutriNova</h1>
    </div>
    <nav class="flex-1 space-y-1">
        <a class="flex items-center gap-3 px-3 py-2 bg-green-50 text-green-700 font-semibold rounded-lg" href="index.php?action=admin-meals&section=meal">
            <span class="material-symbols-outlined">restaurant_menu</span>
            Repas
        </a>
        <a class="flex items-center gap-3 px-3 py-2 text-zinc-600 hover:bg-zinc-100 rounded-lg" href="index.php?action=admin-ingredients&section=ingredient">
            <span class="material-symbols-outlined">liquor</span>
            Ingrédients
        </a>
    </nav>
    <a class="flex items-center gap-3 px-3 py-2 text-zinc-600 hover:bg-zinc-100 rounded-lg" href="index.php">
        <span class="material-symbols-outlined">logout</span>
        Accueil
    </a>
</aside>

<!-- Main -->
<main class="ml-64 min-h-screen">
    <header class="h-16 flex items-center px-8 bg-surface/70 backdrop-blur-xl sticky top-0 z-30 border-b border-zinc-100">
        <h2 class="text-2xl font-bold"><?php echo $page_title; ?></h2>
    </header>

    <div class="p-8 max-w-4xl mx-auto">
        <!-- Messages d'erreur -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-3 gap-8">
            <!-- Formulaire -->
            <div class="col-span-2">
                <form method="POST" action="<?php echo $action_url; ?>" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-8 space-y-6">
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="id_meal" value="<?php echo $meal['id_meal']; ?>">
                    <?php endif; ?>

                    <!-- Nom -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Nom du repas *</label>
                        <input type="text" name="nom" required maxlength="255"
                               value="<?php echo $is_edit ? htmlspecialchars($meal['nom']) : ''; ?>"
                               class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Type de repas *</label>
                        <select name="type" required class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                            <option value="">-- Sélectionner --</option>
                            <option value="petit déjeuner" <?php echo $is_edit && strtolower($meal['type']) === 'petit déjeuner' ? 'selected' : ''; ?>>Petit déjeuner</option>
                            <option value="déjeuner" <?php echo $is_edit && strtolower($meal['type']) === 'déjeuner' ? 'selected' : ''; ?>>Déjeuner</option>
                            <option value="dîner" <?php echo $is_edit && strtolower($meal['type']) === 'dîner' ? 'selected' : ''; ?>>Dîner</option>
                        </select>
                    </div>

                    <!-- Calories -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Calories *</label>
                        <input type="number" name="calories" required step="0.1" min="0"
                               value="<?php echo $is_edit ? $meal['calories'] : '0'; ?>"
                               class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none"
                               placeholder="Sera recalculé automatiquement si vous ajoutez des ingrédients">
                        <p class="text-xs text-zinc-500 mt-1">En kcal (calcul automatique si ingrédients associés)</p>
                    </div>

                    <!-- Protéines -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Protéines *</label>
                        <input type="number" name="protein" required step="0.1" min="0"
                               value="<?php echo $is_edit ? $meal['protein'] : '0'; ?>"
                               class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none"
                               placeholder="En grammes">
                    </div>

                    <!-- Glucides -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Glucides *</label>
                        <input type="number" name="carb" required step="0.1" min="0"
                               value="<?php echo $is_edit ? $meal['carb'] : '0'; ?>"
                               class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none"
                               placeholder="En grammes">
                    </div>

                    <!-- Lipides -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Lipides *</label>
                        <input type="number" name="fat" required step="0.1" min="0"
                               value="<?php echo $is_edit ? $meal['fat'] : '0'; ?>"
                               class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none"
                               placeholder="En grammes">
                    </div>

                    <!-- Image -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Image du repas</label>
                        <div class="mb-3">
                            <input type="file" name="image" accept="image/*"
                                   class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                            <p class="text-xs text-zinc-500 mt-1">Formats acceptés: JPEG, PNG, GIF, WebP. Taille max: 5 MB</p>
                        </div>
                        <?php if ($is_edit && !empty($meal['image'])): ?>
                            <div class="bg-zinc-50 rounded-lg p-3">
                                <p class="text-xs text-zinc-600 mb-2">Image actuelle:</p>
                                <img src="<?php echo htmlspecialchars($meal['image']); ?>" alt="<?php echo htmlspecialchars($meal['nom']); ?>" class="w-full h-32 object-cover rounded-lg">
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4 border-t border-zinc-200">
                        <button type="submit" class="flex-1 px-4 py-3 bg-primary text-on-primary rounded-lg font-semibold hover:brightness-110 transition-all">
                            <?php echo $is_edit ? 'Mettre à jour' : 'Créer'; ?>
                        </button>
                        <a href="index.php?action=admin-meals&section=meal" class="flex-1 px-4 py-3 bg-zinc-100 text-zinc-700 rounded-lg font-semibold hover:bg-zinc-200 transition-all text-center">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>

            <!-- Gestion des Ingrédients (si édition) -->
            <?php if ($is_edit): ?>
            <div>
                <!-- Ajouter un ingrédient -->
                <form method="POST" action="index.php?action=admin-meal-add-ingredient&section=meal" class="bg-white rounded-xl shadow-sm p-6 mb-6 space-y-4">
                    <input type="hidden" name="meal_id" value="<?php echo $meal['id_meal']; ?>">
                    
                    <h3 class="font-bold text-on-surface">Ajouter un ingrédient</h3>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2">Ingrédient</label>
                        <select name="ingredient_id" required class="w-full px-3 py-2 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/40 outline-none">
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($ingredients as $ing): ?>
                                <option value="<?php echo $ing['id_ingredient']; ?>">
                                    <?php echo htmlspecialchars($ing['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-2">Quantité</label>
                        <input type="number" name="quantity" required step="0.1" min="0.1" value="1" class="w-full px-3 py-2 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/40 outline-none">
                    </div>
                    
                    <button type="submit" class="w-full px-3 py-2 bg-primary text-on-primary rounded-lg text-sm font-semibold hover:brightness-110 transition-all">
                        Ajouter
                    </button>
                </form>

                <!-- Liste des ingrédients -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-bold text-on-surface mb-4">Ingrédients (<?php echo count($meal_ingredients); ?>)</h3>
                    
                    <?php if (empty($meal_ingredients)): ?>
                        <p class="text-sm text-zinc-500">Aucun ingrédient ajouté.</p>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach ($meal_ingredients as $ing): ?>
                                <div class="p-3 bg-zinc-50 rounded-lg flex justify-between items-center text-sm">
                                    <div>
                                        <p class="font-semibold"><?php echo htmlspecialchars($ing['nom']); ?></p>
                                        <p class="text-xs text-zinc-500">Qty: <?php echo number_format($ing['quantity'], 1); ?> | <?php echo number_format($ing['calories'] * $ing['quantity'], 0); ?> kcal</p>
                                    </div>
                                    <a href="index.php?action=admin-meal-remove-ingredient&section=meal&meal_id=<?php echo $meal['id_meal']; ?>&ingredient_id=<?php echo $ing['id_ingredient']; ?>" class="text-red-600 hover:text-red-800 font-semibold p-2">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
