<?php
/**
 * Vue Back Office - Formulaire Repas
 */
$is_edit = isset($meal) && $meal !== null;
$action_url = $is_edit 
    ? 'index.php?action=admin-meal-update&section=meal'
    : 'index.php?action=admin-meal-create&section=meal';
$page_title = $is_edit ? 'Modifier Repas' : 'Nouveau Repas';
$ingredients_json = json_encode($ingredients, JSON_UNESCAPED_UNICODE);
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

                    <?php if (!$is_edit): ?>
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 space-y-3">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-emerald-800">Remplissage automatique local</p>
                                    <p class="text-xs text-emerald-700">Génère un repas exemple ou remplis automatiquement à partir d'une image.</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" id="scan-image-btn" class="px-4 py-2 bg-zinc-800 text-white rounded-lg text-sm font-semibold hover:bg-zinc-700 transition-all">
                                        Scanner l'image
                                    </button>
                                    <button type="button" id="autofill-meal-btn" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-semibold hover:brightness-110 transition-all">
                                        Auto-remplir le formulaire
                                    </button>
                                </div>
                            </div>
                            <div class="hidden" id="scan-preview-wrapper">
                                <p class="text-xs text-emerald-700 mb-2">Aperçu image à analyser</p>
                                <img id="scan-preview" alt="Aperçu du repas" class="h-36 w-full object-cover rounded-lg border border-emerald-200 bg-white">
                            </div>
                            <p id="autofill-feedback" class="hidden text-xs font-semibold text-emerald-700"></p>
                        </div>
                    <?php endif; ?>

                    <!-- Nom -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Nom du repas *</label>
                        <input id="meal-nom" type="text" name="nom" required maxlength="255"
                               value="<?php echo $is_edit ? htmlspecialchars($meal['nom']) : ''; ?>"
                               class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Type de repas *</label>
                        <select id="meal-type" name="type" required class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                            <option value="">-- Sélectionner --</option>
                            <option value="petit déjeuner" <?php echo $is_edit && strtolower($meal['type']) === 'petit déjeuner' ? 'selected' : ''; ?>>Petit déjeuner</option>
                            <option value="déjeuner" <?php echo $is_edit && strtolower($meal['type']) === 'déjeuner' ? 'selected' : ''; ?>>Déjeuner</option>
                            <option value="dîner" <?php echo $is_edit && strtolower($meal['type']) === 'dîner' ? 'selected' : ''; ?>>Dîner</option>
                        </select>
                    </div>

                    <!-- Calories -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Calories *</label>
                        <input id="meal-calories" type="number" name="calories" required step="0.1" min="0"
                               value="<?php echo $is_edit ? $meal['calories'] : '0'; ?>"
                               class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none"
                               placeholder="Sera recalculé automatiquement si vous ajoutez des ingrédients">
                        <p class="text-xs text-zinc-500 mt-1">En kcal (calcul automatique si ingrédients associés)</p>
                    </div>

                    <!-- Protéines -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Protéines *</label>
                        <input id="meal-protein" type="number" name="protein" required step="0.1" min="0"
                               value="<?php echo $is_edit ? $meal['protein'] : '0'; ?>"
                               class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none"
                               placeholder="En grammes">
                    </div>

                    <!-- Glucides -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Glucides *</label>
                        <input id="meal-carb" type="number" name="carb" required step="0.1" min="0"
                               value="<?php echo $is_edit ? $meal['carb'] : '0'; ?>"
                               class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none"
                               placeholder="En grammes">
                    </div>

                    <!-- Lipides -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Lipides *</label>
                        <input id="meal-fat" type="number" name="fat" required step="0.1" min="0"
                               value="<?php echo $is_edit ? $meal['fat'] : '0'; ?>"
                               class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none"
                               placeholder="En grammes">
                    </div>

                    <!-- Image -->
                    <div>
                        <label class="block text-sm font-semibold mb-2">Image du repas</label>
                        <div class="mb-3">
                            <input id="meal-image" type="file" name="image" accept="image/*"
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
<?php if (!$is_edit): ?>
<script>
    const mealCatalog = <?php echo $ingredients_json ?: '[]'; ?>;

    const autofillButton = document.getElementById('autofill-meal-btn');
    const scanImageButton = document.getElementById('scan-image-btn');
    const imageInput = document.getElementById('meal-image');
    const scanPreview = document.getElementById('scan-preview');
    const scanPreviewWrapper = document.getElementById('scan-preview-wrapper');
    const feedback = document.getElementById('autofill-feedback');
    const fields = {
        nom: document.getElementById('meal-nom'),
        type: document.getElementById('meal-type'),
        calories: document.getElementById('meal-calories'),
        protein: document.getElementById('meal-protein'),
        carb: document.getElementById('meal-carb'),
        fat: document.getElementById('meal-fat')
    };

    const normalizeText = (value) => (value || '')
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

    const mealTemplates = {
        'petit déjeuner': {
            names: ['Bol énergie matinal', 'Petit-déj équilibré', 'Assiette réveil protéines'],
            keywords: ['oeuf', 'lait', 'yaourt', 'avoine', 'banane', 'pain', 'fromage']
        },
        'déjeuner': {
            names: ['Déjeuner fit du midi', 'Assiette active', 'Repas complet performance'],
            keywords: ['poulet', 'riz', 'patate', 'quinoa', 'legume', 'salade', 'thon']
        },
        'dîner': {
            names: ['Dîner léger digestif', 'Assiette du soir', 'Repas récupération'],
            keywords: ['poisson', 'soupe', 'legume', 'tofu', 'salade', 'courgette', 'brocoli']
        }
    };

    const visionHints = {
        'petit déjeuner': {
            names: ['Petit-déj détecté', 'Assiette matinale suggérée', 'Bol du matin suggéré'],
            labels: ['egg', 'omelet', 'toast', 'bagel', 'croissant', 'cereal', 'oatmeal', 'pancake', 'waffle', 'coffee', 'banana', 'orange'],
            ingredientKeywords: ['oeuf', 'pain', 'banane', 'lait', 'yaourt', 'avoine', 'fromage']
        },
        'déjeuner': {
            names: ['Déjeuner suggéré par image', 'Repas du midi détecté', 'Assiette déjeuner suggérée'],
            labels: ['burger', 'sandwich', 'pizza', 'pasta', 'rice', 'salad', 'chicken', 'beef', 'steak', 'taco', 'burrito'],
            ingredientKeywords: ['poulet', 'riz', 'pate', 'spaghetti', 'thon', 'salade', 'patate', 'tomate', 'boeuf', 'quinoa']
        },
        'dîner': {
            names: ['Dîner suggéré par image', 'Repas du soir détecté', 'Assiette dîner suggérée'],
            labels: ['fish', 'salmon', 'soup', 'broccoli', 'zucchini', 'tofu', 'shrimp', 'vegetable', 'stew'],
            ingredientKeywords: ['poisson', 'tofu', 'brocoli', 'courgette', 'salade', 'soupe']
        }
    };

    const foodFamilies = {
        rice: {
            labels: ['rice', 'riz', 'basmati', 'friedrice', 'risotto'],
            ingredientKeywords: ['riz', 'basmati', 'quinoa'],
            typeBoost: { 'petit déjeuner': 0, 'déjeuner': 3, 'dîner': 1 }
        },
        pasta: {
            labels: ['pasta', 'spaghetti', 'penne', 'macaroni', 'noodle', 'ramen', 'lasagna', 'tagliatelle', 'fettuccine', 'pate'],
            ingredientKeywords: ['pate', 'spaghetti', 'tomate', 'fromage', 'thon', 'poulet'],
            typeBoost: { 'petit déjeuner': 0, 'déjeuner': 3, 'dîner': 1 }
        },
        salad: {
            labels: ['salad', 'lettuce', 'vegetable', 'crudite'],
            ingredientKeywords: ['salade', 'tomate', 'concombre', 'brocoli', 'courgette'],
            typeBoost: { 'petit déjeuner': 0, 'déjeuner': 2, 'dîner': 2 }
        },
        soup: {
            labels: ['soup', 'broth', 'veloute', 'potage'],
            ingredientKeywords: ['soupe', 'courgette', 'brocoli', 'tomate'],
            typeBoost: { 'petit déjeuner': 0, 'déjeuner': 1, 'dîner': 3 }
        },
        fish: {
            labels: ['fish', 'salmon', 'tuna', 'shrimp', 'seafood'],
            ingredientKeywords: ['poisson', 'thon', 'saumon'],
            typeBoost: { 'petit déjeuner': 0, 'déjeuner': 1, 'dîner': 3 }
        },
        breakfast: {
            labels: ['egg', 'omelet', 'toast', 'cereal', 'oatmeal', 'pancake', 'waffle', 'croissant', 'bagel'],
            ingredientKeywords: ['oeuf', 'pain', 'avoine', 'lait', 'yaourt', 'banane'],
            typeBoost: { 'petit déjeuner': 4, 'déjeuner': 0, 'dîner': 0 }
        }
    };

    const pickRandom = (items) => items[Math.floor(Math.random() * items.length)];

    const pickIngredientsByType = (type) => {
        const template = mealTemplates[type];
        const scored = mealCatalog
            .map((ingredient) => {
                const label = normalizeText(ingredient.nom);
                const score = template.keywords.reduce((total, keyword) => {
                    return total + (label.includes(normalizeText(keyword)) ? 1 : 0);
                }, 0);
                return { ingredient, score };
            })
            .sort((left, right) => right.score - left.score);

        const ranked = scored.filter((item) => item.score > 0).map((item) => item.ingredient);
        const fallback = mealCatalog.filter((ingredient) => !ranked.includes(ingredient));
        const selected = ranked.slice(0, 3);

        while (selected.length < 3 && fallback.length > 0) {
            const randomIndex = Math.floor(Math.random() * fallback.length);
            selected.push(fallback.splice(randomIndex, 1)[0]);
        }

        return selected;
    };

    const generateMacros = (ingredients) => {
        const multipliers = [1.0, 1.2, 0.8];
        return ingredients.reduce((totals, ingredient, index) => {
            const quantity = multipliers[index] || 1;
            totals.calories += Number(ingredient.calories || 0) * quantity;
            totals.protein += Number(ingredient.protein || 0) * quantity;
            totals.carb += Number(ingredient.carb || 0) * quantity;
            totals.fat += Number(ingredient.fat || 0) * quantity;
            return totals;
        }, { calories: 0, protein: 0, carb: 0, fat: 0 });
    };

    const setValue = (field, value) => {
        field.value = value;
        field.dispatchEvent(new Event('input', { bubbles: true }));
    };

    const ensurePreview = () => {
        const file = imageInput.files && imageInput.files[0];
        if (!file) {
            return Promise.resolve(null);
        }
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = () => {
                scanPreview.onload = () => resolve(scanPreview);
                scanPreview.src = reader.result;
                scanPreviewWrapper.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });
    };

    const createTypeScores = () => ({
            'petit déjeuner': 0,
            'déjeuner': 0,
            'dîner': 0
    });

    const scoreTypeFromTokens = (tokens) => {
        const scores = createTypeScores();

        Object.entries(visionHints).forEach(([type, config]) => {
            tokens.forEach((token) => {
                config.labels.forEach((label) => {
                    if (token.includes(normalizeText(label))) {
                        scores[type] += 1;
                    }
                });
            });
        });

        const bestEntry = Object.entries(scores).sort((left, right) => right[1] - left[1])[0];
        if (!bestEntry || bestEntry[1] === 0) {
            return 'déjeuner';
        }
        return bestEntry[0];
    };

    const rgbToHsl = (red, green, blue) => {
        const r = red / 255;
        const g = green / 255;
        const b = blue / 255;
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        const lightness = (max + min) / 2;
        let hue = 0;
        let saturation = 0;

        if (max !== min) {
            const delta = max - min;
            saturation = lightness > 0.5 ? delta / (2 - max - min) : delta / (max + min);
            switch (max) {
                case r:
                    hue = (g - b) / delta + (g < b ? 6 : 0);
                    break;
                case g:
                    hue = (b - r) / delta + 2;
                    break;
                default:
                    hue = (r - g) / delta + 4;
                    break;
            }
            hue /= 6;
        }

        return {
            h: hue * 360,
            s: saturation,
            l: lightness
        };
    };

    const readImageFeatures = (imageElement) => {
        const canvas = document.createElement('canvas');
        const maxWidth = 160;
        const ratio = imageElement.naturalWidth > 0 ? imageElement.naturalHeight / imageElement.naturalWidth : 1;
        canvas.width = maxWidth;
        canvas.height = Math.max(1, Math.round(maxWidth * ratio));
        const context = canvas.getContext('2d', { willReadFrequently: true });
        context.drawImage(imageElement, 0, 0, canvas.width, canvas.height);

        const pixels = context.getImageData(0, 0, canvas.width, canvas.height).data;
        let redSum = 0;
        let greenSum = 0;
        let blueSum = 0;
        let satSum = 0;
        let lightSum = 0;
        let count = 0;
        let whiteRatio = 0;
        let yellowRatio = 0;
        let beigeRatio = 0;
        let greenRatio = 0;
        let redRatio = 0;

        const width = canvas.width;
        const height = canvas.height;
        const luminance = new Float32Array(width * height);

        for (let index = 0, pixelIndex = 0; index < pixels.length; index += 4, pixelIndex += 1) {
            const red = pixels[index];
            const green = pixels[index + 1];
            const blue = pixels[index + 2];

            redSum += red;
            greenSum += green;
            blueSum += blue;
            count += 1;

            const hsl = rgbToHsl(red, green, blue);
            satSum += hsl.s;
            lightSum += hsl.l;

            if (hsl.l > 0.72 && hsl.s < 0.22) {
                whiteRatio += 1;
            }
            if (hsl.h >= 35 && hsl.h <= 70 && hsl.s > 0.2 && hsl.l > 0.3) {
                yellowRatio += 1;
            }
            if (hsl.h >= 28 && hsl.h <= 58 && hsl.s >= 0.12 && hsl.s <= 0.45 && hsl.l >= 0.45) {
                beigeRatio += 1;
            }
            if (hsl.h >= 70 && hsl.h <= 170 && hsl.s > 0.25 && hsl.l > 0.2) {
                greenRatio += 1;
            }
            if ((hsl.h <= 20 || hsl.h >= 340) && hsl.s > 0.28 && hsl.l > 0.2) {
                redRatio += 1;
            }

            luminance[pixelIndex] = (0.299 * red) + (0.587 * green) + (0.114 * blue);
        }

        if (count === 0) {
            return null;
        }

        let edgeCount = 0;
        let edgeSamples = 0;
        for (let y = 0; y < height - 1; y += 2) {
            for (let x = 0; x < width - 1; x += 2) {
                const current = y * width + x;
                const right = current + 1;
                const down = current + width;
                const gradient = Math.abs(luminance[current] - luminance[right]) + Math.abs(luminance[current] - luminance[down]);
                if (gradient > 55) {
                    edgeCount += 1;
                }
                edgeSamples += 1;
            }
        }

        return {
            avgRed: redSum / count,
            avgGreen: greenSum / count,
            avgBlue: blueSum / count,
            meanSaturation: satSum / count,
            meanLightness: lightSum / count,
            whiteRatio: whiteRatio / count,
            yellowRatio: yellowRatio / count,
            beigeRatio: beigeRatio / count,
            greenRatio: greenRatio / count,
            redRatio: redRatio / count,
            edgeDensity: edgeSamples > 0 ? edgeCount / edgeSamples : 0
        };
    };

    const getFamilyScoresFromPixels = (features) => {
        if (!features) {
            return {};
        }

        const scores = {
            rice: 0,
            pasta: 0,
            salad: 0,
            soup: 0,
            fish: 0,
            breakfast: 0
        };

        scores.rice += (features.whiteRatio * 6) + ((1 - features.meanSaturation) * 1.8) + (features.meanLightness * 1.2) + (Math.max(0, 0.25 - features.edgeDensity) * 6);
        scores.pasta += (features.yellowRatio * 6.5) + (features.beigeRatio * 4.5) + (features.edgeDensity * 1.4) + (features.meanSaturation * 1.1) - (features.whiteRatio * 1.2);
        scores.salad += (features.greenRatio * 7) + (features.meanSaturation * 2.2) + (features.edgeDensity * 1.1);
        scores.soup += (features.redRatio * 3.8) + (features.yellowRatio * 2.2) + (Math.max(0, 0.22 - features.edgeDensity) * 6);
        scores.fish += (features.whiteRatio * 2.4) + (Math.max(0, 0.2 - features.meanSaturation) * 3) + (Math.max(0, 0.18 - features.edgeDensity) * 5);
        scores.breakfast += (features.meanLightness * 2.2) + (Math.max(0, 0.3 - features.edgeDensity) * 5) + (features.whiteRatio * 2.6);

        return scores;
    };

    const getFamilyScoresFromTokens = (tokens) => {
        const scores = {
            rice: 0,
            pasta: 0,
            salad: 0,
            soup: 0,
            fish: 0,
            breakfast: 0
        };

        Object.entries(foodFamilies).forEach(([family, config]) => {
            tokens.forEach((token) => {
                config.labels.forEach((label) => {
                    if (token.includes(normalizeText(label))) {
                        scores[family] += 3;
                    }
                });
            });
        });

        return scores;
    };

    const mergeFamilyScores = (pixelScores, tokenScores) => {
        const merged = {};
        Object.keys(foodFamilies).forEach((family) => {
            merged[family] = Number(pixelScores[family] || 0) + Number(tokenScores[family] || 0);
        });
        return merged;
    };

    const getTopFamilies = (scores, maxFamilies = 2) => {
        return Object.entries(scores)
            .sort((left, right) => right[1] - left[1])
            .slice(0, maxFamilies)
            .filter((entry) => entry[1] > 0.5)
            .map((entry) => entry[0]);
    };

    const inferTypeFromFamilies = (familyNames) => {
        const typeScores = createTypeScores();
        familyNames.forEach((familyName, index) => {
            const weight = index === 0 ? 1.3 : 1;
            const family = foodFamilies[familyName];
            if (!family) {
                return;
            }
            Object.entries(family.typeBoost).forEach(([type, boost]) => {
                typeScores[type] += boost * weight;
            });
        });

        const bestEntry = Object.entries(typeScores).sort((left, right) => right[1] - left[1])[0];
        if (!bestEntry || bestEntry[1] <= 0) {
            return 'déjeuner';
        }
        return bestEntry[0];
    };

    const buildKeywordsFromFamilies = (type, familyNames) => {
        const merged = new Set(visionHints[type].ingredientKeywords);
        familyNames.forEach((familyName) => {
            const family = foodFamilies[familyName];
            if (family) {
                family.ingredientKeywords.forEach((keyword) => merged.add(keyword));
            }
        });
        return Array.from(merged);
    };

    const getFileNameTokens = (fileName) => {
        if (!fileName) {
            return [];
        }
        return normalizeText(fileName)
            .split(/[^a-z0-9]+/)
            .filter(Boolean);
    };

    const inferSuggestionFromImage = (imageElement, fileName) => {
        const tokens = getFileNameTokens(fileName);
        const tokenType = tokens.length > 0 ? scoreTypeFromTokens(tokens) : null;
        const pixelFeatures = readImageFeatures(imageElement);
        const pixelFamilyScores = getFamilyScoresFromPixels(pixelFeatures);
        const tokenFamilyScores = getFamilyScoresFromTokens(tokens);
        const mergedFamilyScores = mergeFamilyScores(pixelFamilyScores, tokenFamilyScores);
        const topFamilies = getTopFamilies(mergedFamilyScores, 2);
        const familyType = inferTypeFromFamilies(topFamilies);
        const suggestedType = tokenType || familyType;
        const keywords = buildKeywordsFromFamilies(suggestedType, topFamilies);

        return {
            suggestedType,
            keywords,
            topFamilies
        };
    };

    const pickIngredientsByKeywords = (keywords) => {
        const scored = mealCatalog
            .map((ingredient) => {
                const label = normalizeText(ingredient.nom);
                const score = keywords.reduce((total, keyword) => {
                    return total + (label.includes(normalizeText(keyword)) ? 1 : 0);
                }, 0);
                return { ingredient, score };
            })
            .sort((left, right) => right.score - left.score);

        const selected = scored.filter((item) => item.score > 0).slice(0, 3).map((item) => item.ingredient);
        if (selected.length >= 3) {
            return selected;
        }

        const used = new Set(selected.map((item) => item.id_ingredient));
        for (const ingredient of mealCatalog) {
            if (selected.length >= 3) {
                break;
            }
            if (!used.has(ingredient.id_ingredient)) {
                selected.push(ingredient);
                used.add(ingredient.id_ingredient);
            }
        }
        return selected;
    };

    const applySuggestion = (type, names, selectedIngredients, extraMessage = '') => {
        const macros = generateMacros(selectedIngredients);
        const mealName = pickRandom(names);

        setValue(fields.type, type);
        setValue(fields.nom, mealName);
        setValue(fields.calories, macros.calories.toFixed(1));
        setValue(fields.protein, macros.protein.toFixed(1));
        setValue(fields.carb, macros.carb.toFixed(1));
        setValue(fields.fat, macros.fat.toFixed(1));

        const ingredientText = selectedIngredients.map((item) => item.nom).join(', ');
        feedback.textContent = extraMessage + ' Suggestions ingrédients: ' + ingredientText + '.';
        feedback.classList.remove('hidden');
    };

    autofillButton.addEventListener('click', () => {
        if (!Array.isArray(mealCatalog) || mealCatalog.length === 0) {
            feedback.textContent = 'Aucun ingrédient trouvé. Ajoutez des ingrédients avant l\'auto-remplissage.';
            feedback.classList.remove('hidden');
            return;
        }

        const mealTypes = ['petit déjeuner', 'déjeuner', 'dîner'];
        const selectedType = pickRandom(mealTypes);
        const template = mealTemplates[selectedType];
        const selectedIngredients = pickIngredientsByType(selectedType);
        applySuggestion(selectedType, template.names, selectedIngredients, 'Repas généré localement. ');
    });

    imageInput.addEventListener('change', ensurePreview);

    // ========================================================
    // MODEL-BASED SCAN (Random Forest centroid, 105 features)
    // ========================================================

    const MODEL_LABEL_TO_TYPE = {
        breakfast: 'petit déjeuner',
        rice:      'déjeuner',
        pasta:     'déjeuner',
        salad:     'déjeuner',
        soup:      'dîner',
        fish_meat: 'dîner',
    };

    const MODEL_LABEL_KEYWORDS = {
        breakfast: ['oeuf', 'pain', 'avoine', 'lait', 'yaourt', 'banane', 'fromage'],
        rice:      ['riz', 'basmati', 'quinoa', 'poulet', 'boeuf'],
        pasta:     ['pate', 'spaghetti', 'tomate', 'fromage', 'thon', 'poulet'],
        salad:     ['salade', 'tomate', 'concombre', 'brocoli', 'courgette'],
        soup:      ['soupe', 'courgette', 'brocoli', 'tomate', 'legume'],
        fish_meat: ['poisson', 'thon', 'saumon', 'poulet', 'boeuf'],
    };

    const MODEL_LABEL_NAMES = {
        breakfast: ['Assiette matinale', 'Bol du matin', 'Petit-déjeuner équilibré'],
        rice:      ['Plat de riz', 'Bol de riz', 'Repas au riz'],
        pasta:     ['Plat de pâtes', 'Assiette pasta', 'Pâtes maison'],
        salad:     ['Salade fraîche', 'Bol de salade', 'Assiette verte'],
        soup:      ['Soupe du jour', 'Velouté maison', 'Potage équilibré'],
        fish_meat: ['Plat viande & poisson', 'Assiette protéinée', 'Plat principal'],
    };

    let cachedModel = null;

    const loadModel = async () => {
        if (cachedModel) return cachedModel;
        const response = await fetch('index.php?action=ml-model');
        if (!response.ok) throw new Error('Model fetch failed: ' + response.status);
        cachedModel = await response.json();
        return cachedModel;
    };

    const rgbToHsvModel = (r, g, b) => {
        const rf = r / 255, gf = g / 255, bf = b / 255;
        const maxc = Math.max(rf, gf, bf);
        const minc = Math.min(rf, gf, bf);
        const delta = maxc - minc;
        let hue = 0, sat = 0;
        const val = maxc;
        if (delta > 0) {
            sat = delta / (maxc + 1e-8);
            if (maxc === rf) hue = ((gf - bf) / (delta + 1e-8) + 6) % 6;
            else if (maxc === gf) hue = (bf - rf) / (delta + 1e-8) + 2;
            else hue = (rf - gf) / (delta + 1e-8) + 4;
            hue /= 6;
        }
        return [hue, sat, val];
    };

    const stdDev = (arr, mean) => {
        let s = 0;
        for (const v of arr) s += (v - mean) ** 2;
        return Math.sqrt(s / Math.max(1, arr.length));
    };

    const histBins = (values, binCount, min, max) => {
        const bins = new Float32Array(binCount);
        const range = max - min + 1e-10;
        for (const v of values) {
            const idx = Math.min(binCount - 1, Math.floor(((v - min) / range) * binCount));
            bins[idx]++;
        }
        const total = values.length || 1;
        for (let i = 0; i < binCount; i++) bins[i] /= total;
        return bins;
    };

    const extractFeaturesFromCanvas = (imageEl) => {
        const SIZE = 96;
        const canvas = document.createElement('canvas');
        canvas.width = SIZE; canvas.height = SIZE;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(imageEl, 0, 0, SIZE, SIZE);
        const data = ctx.getImageData(0, 0, SIZE, SIZE).data;
        const N = SIZE * SIZE;

        const rArr = new Float32Array(N), gArr = new Float32Array(N), bArr = new Float32Array(N);
        const hArr = new Float32Array(N), sArr = new Float32Array(N), vArr = new Float32Array(N);
        const gray = new Float32Array(N);

        for (let i = 0, p = 0; i < N; i++, p += 4) {
            const r = data[p], g = data[p+1], b = data[p+2];
            rArr[i] = r / 255; gArr[i] = g / 255; bArr[i] = b / 255;
            const [h, s, v] = rgbToHsvModel(r, g, b);
            hArr[i] = h; sArr[i] = s; vArr[i] = v;
            gray[i] = 0.299 * rArr[i] + 0.587 * gArr[i] + 0.114 * bArr[i];
        }

        const mean = arr => { let s = 0; for (const v of arr) s += v; return s / arr.length; };

        const rMean = mean(rArr), gMean = mean(gArr), bMean = mean(bArr);
        const sMean = mean(sArr), vMean = mean(vArr);
        const sStd = stdDev(sArr, sMean), vStd = stdDev(vArr, vMean);
        let whiteCnt = 0, darkCnt = 0;
        for (let i = 0; i < N; i++) {
            if (gray[i] > 0.78) whiteCnt++;
            if (gray[i] < 0.20) darkCnt++;
        }
        const globalStats = [
            rMean, gMean, bMean,
            stdDev(rArr, rMean), stdDev(gArr, gMean), stdDev(bArr, bMean),
            sMean, vMean, sStd, vStd,
            whiteCnt / N, darkCnt / N,
        ];

        const hueHist = histBins(hArr, 36, 0, 1);
        const satHist = histBins(sArr, 12, 0, 1);

        let beigeC = 0, yellowC = 0, greenC = 0, redC = 0, brownC = 0;
        for (let i = 0; i < N; i++) {
            const h = hArr[i], s = sArr[i], v = vArr[i];
            if (gray[i] > 0.75) {}  // white already counted
            if (h > 0.07 && h < 0.15 && s > 0.08 && s < 0.50 && v > 0.45) beigeC++;
            if (h > 0.09 && h < 0.18 && s > 0.18) yellowC++;
            if (h > 0.22 && h < 0.44 && s > 0.22) greenC++;
            if ((h < 0.07 || h > 0.93) && s > 0.20) redC++;
            if (h > 0.03 && h < 0.10 && s > 0.20 && s < 0.80 && v < 0.55) brownC++;
        }
        const colorRatios = [
            whiteCnt / N, beigeC / N, yellowC / N,
            greenC / N, redC / N, brownC / N,
        ];

        let gxSum = 0, gySum = 0, gxN = 0, gyN = 0;
        for (let row = 0; row < SIZE; row++) {
            for (let col = 0; col < SIZE - 1; col++) {
                gxSum += Math.abs(gray[row * SIZE + col] - gray[row * SIZE + col + 1]);
                gxN++;
            }
        }
        for (let row = 0; row < SIZE - 1; row++) {
            for (let col = 0; col < SIZE; col++) {
                gySum += Math.abs(gray[row * SIZE + col] - gray[(row + 1) * SIZE + col]);
                gyN++;
            }
        }
        const gx = gxSum / Math.max(1, gxN);
        const gy = gySum / Math.max(1, gyN);
        const edgeFeats = [gx, gy, (gx + gy) / 2];

        // 3×3 spatial grid, 4 features per cell = 36
        const ROWS = 3, COLS = 3;
        const spatial = [];
        for (let row = 0; row < ROWS; row++) {
            for (let col = 0; col < COLS; col++) {
                const y0 = Math.floor((row * SIZE) / ROWS);
                const y1 = Math.floor(((row + 1) * SIZE) / ROWS);
                const x0 = Math.floor((col * SIZE) / COLS);
                const x1 = Math.floor(((col + 1) * SIZE) / COLS);
                let hS = 0, sS = 0, vS = 0, cnt = 0;
                let exSum = 0, exN = 0;
                for (let y = y0; y < y1; y++) {
                    for (let x = x0; x < x1; x++) {
                        const idx = y * SIZE + x;
                        hS += hArr[idx]; sS += sArr[idx]; vS += vArr[idx]; cnt++;
                        if (x < x1 - 1) { exSum += Math.abs(gray[idx] - gray[idx + 1]); exN++; }
                        if (y < y1 - 1) { exSum += Math.abs(gray[idx] - gray[idx + SIZE]); exN++; }
                    }
                }
                const n = Math.max(1, cnt);
                spatial.push(hS / n, sS / n, vS / n, exSum / Math.max(1, exN));
            }
        }

        return [
            ...globalStats,
            ...hueHist,
            ...satHist,
            ...colorRatios,
            ...edgeFeats,
            ...spatial,
        ];
    };

    const predictWithModel = (model, features) => {
        const mu = model.normalization.mu;
        const sigma = model.normalization.sigma;
        const norm = features.map((v, i) => (v - mu[i]) / (sigma[i] + 1e-8));

        const distances = {};
        for (const [label, params] of Object.entries(model.classes)) {
            const c = params.centroid;
            const s = params.std;
            let dist = 0;
            for (let i = 0; i < norm.length; i++) {
                const d = (norm[i] - c[i]) / (s[i] + 1e-6);
                dist += d * d;
            }
            distances[label] = Math.sqrt(dist / norm.length);
        }

        const sorted = Object.entries(distances).sort((a, b) => a[1] - b[1]);
        const best = sorted[0][0];
        const inv = sorted.map(([l, d]) => [l, 1 / (d + 1e-8)]);
        const total = inv.reduce((s, [, v]) => s + v, 0);
        const probs = Object.fromEntries(inv.map(([l, v]) => [l, (v / total * 100).toFixed(1)]));

        return { best, probs };
    };

    scanImageButton.addEventListener('click', async () => {
        if (!Array.isArray(mealCatalog) || mealCatalog.length === 0) {
            feedback.textContent = 'Aucun ingrédient en base. Créez des ingrédients avant le scan.';
            feedback.classList.remove('hidden');
            return;
        }

        const previewImage = await ensurePreview();
        if (!previewImage) {
            feedback.textContent = 'Sélectionnez une image avant de lancer le scan.';
            feedback.classList.remove('hidden');
            return;
        }

        try {
            scanImageButton.disabled = true;
            scanImageButton.textContent = 'Chargement...';

            const model = await loadModel();
            scanImageButton.textContent = 'Analyse en cours...';

            const features = extractFeaturesFromCanvas(previewImage);

            if (features.length !== model.feature_size) {
                throw new Error(`Feature mismatch: expected ${model.feature_size}, got ${features.length}`);
            }

            const { best, probs } = predictWithModel(model, features);
            const mealType = MODEL_LABEL_TO_TYPE[best] || 'déjeuner';
            const keywords = MODEL_LABEL_KEYWORDS[best] || [];
            const names = MODEL_LABEL_NAMES[best] || ['Repas suggéré'];
            const selectedIngredients = pickIngredientsByKeywords(keywords);

            applySuggestion(
                mealType,
                names,
                selectedIngredients,
                `Analyse complète. Suggestions basées sur: ${best.replace('_', ' & ')}.`
            );
        } catch (error) {
            feedback.textContent = 'Scan IA échoué: ' + error.message + '. Vérifiez que XAMPP tourne.';
            feedback.classList.remove('hidden');
        } finally {
            scanImageButton.disabled = false;
            scanImageButton.textContent = 'Scanner l\'image';
        }
    });
</script>
<?php endif; ?>
</body>
</html>
