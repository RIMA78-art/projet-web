<?php
/**
 * Vue Back Office - Liste des ingrédients
 */

$ingredientCount = count($ingredients);
?>
<!DOCTYPE html>

<html class="light" lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Ingrédients - Admin NutriNova</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#006e1c",
                        "primary-container": "#4caf50",
                        "on-primary": "#ffffff",
                        "secondary": "#4c56af",
                        "surface": "#f8faf8",
                        "on-surface": "#191c1b",
                        "on-surface-variant": "#3f4a3c"
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
        <a class="flex items-center gap-3 px-3 py-2 text-zinc-600 hover:bg-zinc-100 rounded-lg transition-all" href="index.php?action=admin-meals&section=meal">
            <span class="material-symbols-outlined">restaurant_menu</span>
            Gestion Repas
        </a>
        <a class="flex items-center gap-3 px-3 py-2 bg-green-50 text-green-700 font-semibold rounded-lg transition-all" href="index.php?action=admin-ingredients&section=ingredient">
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
        <h2 class="text-2xl font-bold">Bibliothèque d'Ingrédients</h2>
        <a href="index.php?action=admin-ingredient-add&section=ingredient" class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-md font-medium hover:brightness-110 transition-all">
            <span class="material-symbols-outlined">add</span>
            Nouvel Ingrédient
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

        <div class="bg-white rounded-xl shadow-sm border border-zinc-100 p-5 flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">
            <div class="flex-1">
                <label for="ingredient-table-search" class="block text-xs font-bold uppercase tracking-wider text-zinc-500 mb-2">Recherche</label>
                <input id="ingredient-table-search" type="search" placeholder="Nom, calories, protéines ou eco-score" class="w-full rounded-lg border border-zinc-200 px-4 py-3 text-sm focus:border-primary focus:ring-primary">
            </div>
            <div class="flex items-center gap-3 text-sm text-zinc-500">
                <span class="font-semibold">Ingrédients affichés :</span>
                <span id="ingredient-visible-count" class="rounded-full bg-green-50 px-3 py-1 font-bold text-primary"><?php echo $ingredientCount; ?></span>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-200">
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider">
                            <button type="button" class="sort-button flex items-center gap-1" data-table="ingredients" data-sort="nom">Nom <span class="material-symbols-outlined text-base">swap_vert</span></button>
                        </th>
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider">
                            <button type="button" class="sort-button flex items-center gap-1" data-table="ingredients" data-sort="calories">Calories <span class="material-symbols-outlined text-base">swap_vert</span></button>
                        </th>
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider">
                            <button type="button" class="sort-button flex items-center gap-1" data-table="ingredients" data-sort="protein">Protéines <span class="material-symbols-outlined text-base">swap_vert</span></button>
                        </th>
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider">Glucides</th>
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider">Lipides</th>
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider">Eco-Score</th>
                        <th class="px-6 py-4 text-sm font-bold text-zinc-600 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="ingredients-table-body" class="divide-y divide-zinc-100">
                    <?php if (empty($ingredients)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500">
                                Aucun ingrédient. <a href="index.php?action=admin-ingredient-add&section=ingredient" class="text-primary font-semibold">Créer le premier</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ingredients as $ingredient): ?>
                            <tr class="ingredient-row hover:bg-zinc-50 transition-colors"
                                data-nom="<?php echo htmlspecialchars($ingredient['nom'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-calories="<?php echo htmlspecialchars((string) $ingredient['calories'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-protein="<?php echo htmlspecialchars((string) $ingredient['protein'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-carb="<?php echo htmlspecialchars((string) $ingredient['carb'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-fat="<?php echo htmlspecialchars((string) $ingredient['fat'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-eco_score="<?php echo htmlspecialchars((string) ($ingredient['eco_score'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="px-6 py-4 font-semibold text-on-surface"><?php echo htmlspecialchars($ingredient['nom']); ?></td>
                                <td class="px-6 py-4"><?php echo number_format($ingredient['calories'], 0); ?> kcal</td>
                                <td class="px-6 py-4"><?php echo number_format($ingredient['protein'], 1); ?>g</td>
                                <td class="px-6 py-4"><?php echo number_format($ingredient['carb'], 1); ?>g</td>
                                <td class="px-6 py-4"><?php echo number_format($ingredient['fat'], 1); ?>g</td>
                                <td class="px-6 py-4">
                                    <?php if ($ingredient['eco_score']): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">
                                            <?php echo htmlspecialchars($ingredient['eco_score']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-zinc-400">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="index.php?action=admin-ingredient-edit&section=ingredient&id=<?php echo $ingredient['id_ingredient']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                            <span class="material-symbols-outlined">edit</span>
                                        </a>
                                        <a href="index.php?action=admin-ingredient-delete&section=ingredient&id=<?php echo $ingredient['id_ingredient']; ?>" onclick="return confirm('Êtes-vous sûr?')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
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
        <div id="ingredients-empty-state" class="hidden rounded-xl border border-dashed border-zinc-200 bg-white p-8 text-center text-zinc-500">
            Aucun ingrédient ne correspond à la recherche en cours.
        </div>
    </div>
</main>
<script>
    const normalizeText = (value) => (value || '')
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

    const ingredientSearchInput = document.getElementById('ingredient-table-search');
    const ingredientRows = Array.from(document.querySelectorAll('.ingredient-row'));
    const ingredientsTableBody = document.getElementById('ingredients-table-body');
    const ingredientVisibleCount = document.getElementById('ingredient-visible-count');
    const ingredientsEmptyState = document.getElementById('ingredients-empty-state');
    const ingredientSortState = { key: 'nom', direction: 'asc' };

    const getIngredientComparableValue = (row, key) => {
        const value = row.dataset[key] || '';
        if (['calories', 'protein', 'carb', 'fat'].includes(key)) {
            return Number(value);
        }
        return normalizeText(value);
    };

    const sortIngredientRows = () => {
        ingredientRows.sort((left, right) => {
            const leftValue = getIngredientComparableValue(left, ingredientSortState.key);
            const rightValue = getIngredientComparableValue(right, ingredientSortState.key);

            if (leftValue < rightValue) {
                return ingredientSortState.direction === 'asc' ? -1 : 1;
            }
            if (leftValue > rightValue) {
                return ingredientSortState.direction === 'asc' ? 1 : -1;
            }
            return 0;
        });

        ingredientRows.forEach((row) => ingredientsTableBody.appendChild(row));
    };

    const filterIngredientRows = () => {
        const query = normalizeText(ingredientSearchInput.value);
        let visibleRows = 0;

        ingredientRows.forEach((row) => {
            const haystack = [row.dataset.nom, row.dataset.calories, row.dataset.protein, row.dataset.eco_score]
                .map((value) => normalizeText(value))
                .join(' ');
            const isVisible = !query || haystack.includes(query);

            row.classList.toggle('hidden', !isVisible);
            if (isVisible) {
                visibleRows += 1;
            }
        });

        ingredientVisibleCount.textContent = visibleRows;
        ingredientsEmptyState.classList.toggle('hidden', visibleRows !== 0 || ingredientRows.length === 0);
    };

    document.querySelectorAll('.sort-button[data-table="ingredients"]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextKey = button.dataset.sort;
            if (ingredientSortState.key === nextKey) {
                ingredientSortState.direction = ingredientSortState.direction === 'asc' ? 'desc' : 'asc';
            } else {
                ingredientSortState.key = nextKey;
                ingredientSortState.direction = 'asc';
            }
            sortIngredientRows();
            filterIngredientRows();
        });
    });

    ingredientSearchInput.addEventListener('input', filterIngredientRows);
    sortIngredientRows();
    filterIngredientRows();
</script>
</body>
</html>
