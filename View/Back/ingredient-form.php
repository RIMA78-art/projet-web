<?php
/**
 * Vue Back Office - Formulaire Ingrédient
 */
$is_edit = isset($ingredient) && $ingredient !== null;
$action_url = $is_edit 
    ? 'index.php?action=admin-ingredient-update&section=ingredient'
    : 'index.php?action=admin-ingredient-create&section=ingredient';
$page_title = $is_edit ? 'Modifier Ingrédient' : 'Nouvel Ingrédient';
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
        <a class="flex items-center gap-3 px-3 py-2 text-zinc-600 hover:bg-zinc-100 rounded-lg" href="index.php?action=admin-meals&section=meal">
            <span class="material-symbols-outlined">restaurant_menu</span>
            Repas
        </a>
        <a class="flex items-center gap-3 px-3 py-2 bg-green-50 text-green-700 font-semibold rounded-lg" href="index.php?action=admin-ingredients&section=ingredient">
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

    <div class="p-8 max-w-2xl mx-auto">
        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire -->
        <form method="POST" action="<?php echo $action_url; ?>" class="bg-white rounded-xl shadow-sm p-8 space-y-6">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id_ingredient" value="<?php echo $ingredient['id_ingredient']; ?>">
            <?php endif; ?>

            <!-- Nom -->
            <div>
                <label class="block text-sm font-semibold mb-2">Nom de l'ingrédient *</label>
                <input type="text" name="nom" required maxlength="255"
                       value="<?php echo $is_edit ? htmlspecialchars($ingredient['nom']) : ''; ?>"
                       class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                <p class="text-xs text-zinc-500 mt-1">Exemple: Poitrine de poulet bio</p>
            </div>

            <!-- Calories -->
            <div>
                <label class="block text-sm font-semibold mb-2">Calories par unité *</label>
                <input type="number" name="calories" required step="0.1" min="0"
                       value="<?php echo $is_edit ? $ingredient['calories'] : ''; ?>"
                       class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                <p class="text-xs text-zinc-500 mt-1">En kcal</p>
            </div>

            <!-- Protéines -->
            <div>
                <label class="block text-sm font-semibold mb-2">Protéines par unité *</label>
                <input type="number" name="protein" required step="0.1" min="0"
                       value="<?php echo $is_edit ? $ingredient['protein'] : ''; ?>"
                       class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                <p class="text-xs text-zinc-500 mt-1">En grammes</p>
            </div>

            <!-- Glucides -->
            <div>
                <label class="block text-sm font-semibold mb-2">Glucides par unité *</label>
                <input type="number" name="carb" required step="0.1" min="0"
                       value="<?php echo $is_edit ? $ingredient['carb'] : ''; ?>"
                       class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                <p class="text-xs text-zinc-500 mt-1">En grammes</p>
            </div>

            <!-- Lipides -->
            <div>
                <label class="block text-sm font-semibold mb-2">Lipides par unité *</label>
                <input type="number" name="fat" required step="0.1" min="0"
                       value="<?php echo $is_edit ? $ingredient['fat'] : ''; ?>"
                       class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                <p class="text-xs text-zinc-500 mt-1">En grammes</p>
            </div>

            <!-- Eco-Score -->
            <div>
                <label class="block text-sm font-semibold mb-2">Eco-Score (optionnel)</label>
                <input type="text" name="eco_score" maxlength="10"
                       value="<?php echo $is_edit && $ingredient['eco_score'] ? htmlspecialchars($ingredient['eco_score']) : ''; ?>"
                       class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none"
                       placeholder="A, B, C...">
                <p class="text-xs text-zinc-500 mt-1">Exemple: A, B, C, D, E</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-4 border-t border-zinc-200">
                <button type="submit" class="flex-1 px-4 py-3 bg-primary text-on-primary rounded-lg font-semibold hover:brightness-110 transition-all">
                    <?php echo $is_edit ? 'Mettre à jour' : 'Créer'; ?>
                </button>
                <a href="index.php?action=admin-ingredients&section=ingredient" class="flex-1 px-4 py-3 bg-zinc-100 text-zinc-700 rounded-lg font-semibold hover:bg-zinc-200 transition-all text-center">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</main>
</body>
</html>
