<?php
/**
 * Vue Front Office - Plan Nutritionnel
 */
$goal_labels = [
    'loss'     => 'Perte de poids (-500 kcal)',
    'maintain' => 'Maintien du poids',
    'gain'     => 'Prise de masse (+300 kcal)',
];
$activity_labels = [
    '1.2'   => 'Sédentaire (peu ou pas d\'exercice)',
    '1.375' => 'Légèrement actif (1-3 j/semaine)',
    '1.55'  => 'Modérément actif (3-5 j/semaine)',
    '1.725' => 'Très actif (6-7 j/semaine)',
];
$priority_labels = [
    'maintain_muscle' => 'Maintien musculaire (favoriser les repas riches en protéines)',
    'reduce_sugar_sodium' => 'Réduction sucre/sodium (proxy via glucides et densité énergétique)',
];
?>
<!DOCTYPE html>
<html class="light" lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Plan Nutritionnel - NutriNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
                        "tertiary": "#a700ac",
                        "surface": "#f8faf8",
                        "on-surface": "#191c1b",
                        "on-surface-variant": "#3f4a3c",
                        "surface-container": "#eceeec",
                        "outline-variant": "#becab9",
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; }
        body  { font-family: 'Inter', sans-serif; }
        h1,h2 { font-family: 'Manrope', sans-serif; }
        .macro-bar { transition: width .6s cubic-bezier(.4,0,.2,1); }

        @media print {
            header, footer, form, .no-print { display: none !important; }
            body  { background: white !important; font-size: 12px; }
            main  { padding-top: 0 !important; }
            .print-header { display: block !important; }
            .lg\:col-span-2 { grid-column: span 3; }
            .grid { display: block; }
            .bg-white { border: 1px solid #e5e7eb; margin-bottom: 16px; }
            a { text-decoration: none !important; color: inherit !important; }
            .shadow-sm { box-shadow: none !important; }
        }
        .print-header { display: none; }
    </style>
</head>
<body class="bg-surface text-on-surface">

<!-- Navigation -->
<header class="fixed top-0 w-full z-50 bg-white/70 backdrop-blur-xl shadow-sm">
    <nav class="flex justify-between items-center px-8 h-16 w-full max-w-screen-2xl mx-auto">
        <div class="flex items-center gap-8">
            <a href="index.php" class="text-xl font-black tracking-tighter text-green-700">NutriNova</a>
            <div class="hidden md:flex gap-6 items-center">
                <a class="text-zinc-500 hover:text-green-600 font-['Manrope'] font-bold tracking-tight transition-colors" href="index.php">Catalogue</a>
                <a class="text-green-700 border-b-2 border-green-700 pb-1 font-['Manrope'] font-bold tracking-tight" href="index.php?action=nutrition-plan">Plan Nutritionnel</a>
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
    <section class="mb-10">
        <h1 class="text-5xl font-extrabold tracking-tight mb-3">Plan <span class="text-primary italic">Nutritionnel</span></h1>
        <p class="text-on-surface-variant text-lg max-w-2xl">
            Renseignez vos informations corporelles. Le système calcule vos besoins caloriques via la formule de Harris-Benedict et sélectionne les repas du catalogue les plus adaptés.
        </p>
    </section>

    <!-- Erreur -->
    <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl flex items-center gap-2">
            <span class="material-symbols-outlined">error</span>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- ===== Formulaire ===== -->
        <?php if (!isset($is_pdf_download) || !$is_pdf_download): ?>
        <div class="lg:col-span-1">
            <form method="POST" action="index.php?action=nutrition-plan"
                  class="bg-white rounded-2xl shadow-sm border border-zinc-100 p-8 space-y-6 sticky top-24">
                <h2 class="text-xl font-bold">Vos informations</h2>

                <!-- Genre -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Genre</label>
                    <div class="flex gap-3">
                        <label class="flex-1 flex items-center justify-center gap-2 border rounded-lg py-2 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                            <input type="radio" name="gender" value="male" class="accent-primary"
                                <?php echo (!isset($input['gender']) || $input['gender'] === 'male') ? 'checked' : ''; ?>>
                            <span class="material-symbols-outlined text-base">male</span> Homme
                        </label>
                        <label class="flex-1 flex items-center justify-center gap-2 border rounded-lg py-2 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                            <input type="radio" name="gender" value="female" class="accent-primary"
                                <?php echo (isset($input['gender']) && $input['gender'] === 'female') ? 'checked' : ''; ?>>
                            <span class="material-symbols-outlined text-base">female</span> Femme
                        </label>
                    </div>
                </div>

                <!-- Poids -->
                <div>
                    <label class="block text-sm font-semibold mb-2" for="weight">
                        Poids (kg)
                    </label>
                    <input id="weight" type="number" name="weight" min="30" max="300" step="0.1" required
                           value="<?php echo htmlspecialchars($input['weight'] ?? ''); ?>"
                           placeholder="ex: 70"
                           class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                </div>

                <!-- Taille -->
                <div>
                    <label class="block text-sm font-semibold mb-2" for="height">
                        Taille (cm)
                    </label>
                    <input id="height" type="number" name="height" min="100" max="250" step="1" required
                           value="<?php echo htmlspecialchars($input['height'] ?? ''); ?>"
                           placeholder="ex: 175"
                           class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                </div>

                <!-- Âge -->
                <div>
                    <label class="block text-sm font-semibold mb-2" for="age">
                        Âge (ans)
                    </label>
                    <input id="age" type="number" name="age" min="10" max="100" step="1" required
                           value="<?php echo htmlspecialchars($input['age'] ?? ''); ?>"
                           placeholder="ex: 25"
                           class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 focus:border-transparent outline-none">
                </div>

                <!-- Activité -->
                <div>
                    <label class="block text-sm font-semibold mb-2" for="activity">Niveau d'activité</label>
                    <select id="activity" name="activity"
                            class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 outline-none">
                        <?php foreach ($activity_labels as $val => $label): ?>
                            <option value="<?php echo $val; ?>"
                                <?php echo (isset($input['activity']) && (string)$input['activity'] === $val) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Objectif -->
                <div>
                    <label class="block text-sm font-semibold mb-2" for="goal">Objectif</label>
                    <select id="goal" name="goal"
                            class="w-full px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-primary/40 outline-none">
                        <?php foreach ($goal_labels as $val => $label): ?>
                            <option value="<?php echo $val; ?>"
                                <?php echo (isset($input['goal']) && $input['goal'] === $val) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Priorités avancées -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Priorités avancées (optionnel)</label>
                    <div class="space-y-2">
                        <?php $selected_priorities = $input['priorities'] ?? []; ?>
                        <?php foreach ($priority_labels as $value => $label): ?>
                            <label class="flex items-start gap-3 border rounded-lg p-3 cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-all">
                                <input type="checkbox" name="priorities[]" value="<?php echo $value; ?>" class="mt-1 accent-primary"
                                    <?php echo in_array($value, $selected_priorities, true) ? 'checked' : ''; ?>>
                                <span class="text-sm text-on-surface"><?php echo htmlspecialchars($label); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-primary text-on-primary rounded-xl font-bold text-base hover:brightness-110 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">calculate</span>
                    Générer mon plan
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- ===== Résultats ===== -->
        <div class="<?php echo (isset($is_pdf_download) && $is_pdf_download) ? 'lg:col-span-3' : 'lg:col-span-2'; ?> space-y-8">

            <?php if ($plan): ?>

                <!-- Résumé calorique -->
                <div class="bg-white rounded-2xl shadow-sm border border-zinc-100 p-8">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">monitoring</span>
                        Résumé de vos besoins
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-primary/8 rounded-xl p-4 text-center">
                            <p class="text-3xl font-extrabold text-primary"><?php echo $plan['bmr']; ?></p>
                            <p class="text-xs text-on-surface-variant font-semibold uppercase tracking-wider mt-1">kcal de base<br>(BMR)</p>
                        </div>
                        <div class="bg-emerald-50 rounded-xl p-4 text-center">
                            <p class="text-3xl font-extrabold text-emerald-700"><?php echo $plan['tdee']; ?></p>
                            <p class="text-xs text-on-surface-variant font-semibold uppercase tracking-wider mt-1">kcal/jour<br>(Objectif)</p>
                        </div>
                        <div class="bg-amber-50 rounded-xl p-4 text-center">
                            <p class="text-3xl font-extrabold text-amber-600"><?php echo $plan['targets']['breakfast']; ?></p>
                            <p class="text-xs text-on-surface-variant font-semibold uppercase tracking-wider mt-1">kcal<br>Matin (25%)</p>
                        </div>
                        <div class="bg-blue-50 rounded-xl p-4 text-center">
                            <p class="text-3xl font-extrabold text-blue-600"><?php echo $plan['targets']['lunch']; ?></p>
                            <p class="text-xs text-on-surface-variant font-semibold uppercase tracking-wider mt-1">kcal<br>Midi (40%)</p>
                        </div>
                    </div>

                    <?php if (!empty($plan['priorities'])): ?>
                    <div class="mt-5 p-4 bg-indigo-50 border border-indigo-100 rounded-xl">
                        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-700 mb-2">Profil multi-objectifs actif</p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($plan['priorities'] as $priority): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-white border border-indigo-200 text-indigo-700">
                                    <?php echo htmlspecialchars($priority_labels[$priority] ?? $priority); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($plan['macro_targets'])): ?>
                            <p class="text-xs text-indigo-700 mt-3">
                                Cibles journalières estimées: <?php echo (int)$plan['macro_targets']['protein']; ?>g protéines,
                                <?php echo (int)$plan['macro_targets']['carb']; ?>g glucides,
                                <?php echo (int)$plan['macro_targets']['fat']; ?>g lipides.
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Formule expliquée -->
                    <div class="mt-6 p-4 bg-zinc-50 rounded-xl text-xs text-zinc-500 leading-relaxed">
                        <strong class="text-zinc-700">Formule Harris-Benedict :</strong>
                        <?php if ($input['gender'] === 'male'): ?>
                            BMR = 88.36 + (13.40 × <?php echo $input['weight']; ?>) + (4.80 × <?php echo $input['height']; ?>) − (5.68 × <?php echo $input['age']; ?>) = <strong class="text-zinc-700"><?php echo $plan['bmr']; ?> kcal</strong>
                        <?php else: ?>
                            BMR = 447.59 + (9.25 × <?php echo $input['weight']; ?>) + (3.10 × <?php echo $input['height']; ?>) − (4.33 × <?php echo $input['age']; ?>) = <strong class="text-zinc-700"><?php echo $plan['bmr']; ?> kcal</strong>
                        <?php endif; ?>
                        &nbsp;× activité = TDEE &nbsp;→ ajusté pour objectif = <strong class="text-zinc-700"><?php echo $plan['tdee']; ?> kcal/jour</strong>
                    </div>
                </div>

                <!-- Plan des 3 repas -->
                <div class="bg-white rounded-2xl shadow-sm border border-zinc-100 p-8">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">restaurant_menu</span>
                        Plan de la journée
                    </h2>

                    <?php
                    $slots = [
                        ['key' => 'breakfast', 'label' => 'Petit-déjeuner',  'icon' => 'free_breakfast', 'color' => 'amber',   'pct' => 25],
                        ['key' => 'lunch',     'label' => 'Déjeuner',         'icon' => 'lunch_dining',   'color' => 'blue',    'pct' => 40],
                        ['key' => 'dinner',    'label' => 'Dîner',            'icon' => 'dinner_dining',  'color' => 'indigo',  'pct' => 35],
                    ];
                    foreach ($slots as $slot):
                        $meal = $plan[$slot['key']];
                        $target = $plan['targets'][$slot['key']];
                        $c = $slot['color'];
                    ?>
                    <div class="mb-6 last:mb-0 p-5 rounded-xl border border-zinc-100 hover:border-primary/30 transition-all">
                        <!-- En-tête slot -->
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-<?php echo $c; ?>-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-<?php echo $c; ?>-600"><?php echo $slot['icon']; ?></span>
                            </div>
                            <div>
                                <p class="font-bold text-on-surface"><?php echo $slot['label']; ?></p>
                                <p class="text-xs text-on-surface-variant">Cible : <?php echo $target; ?> kcal (<?php echo $slot['pct']; ?>% de la journée)</p>
                            </div>
                        </div>

                        <?php if ($meal): ?>
                            <div class="flex flex-col sm:flex-row gap-4 items-start">
                                <!-- Image -->
                                <div class="w-full sm:w-28 h-28 rounded-xl overflow-hidden bg-zinc-100 flex-shrink-0">
                                    <?php if (!empty($meal['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($meal['image']); ?>"
                                             alt="<?php echo htmlspecialchars($meal['nom']); ?>"
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center">
                                            <span class="material-symbols-outlined text-5xl text-zinc-300">restaurant</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Infos -->
                                <div class="flex-1">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <h3 class="font-bold text-lg"><?php echo htmlspecialchars($meal['nom']); ?></h3>
                                        <?php
                                        $diff = abs((int)$meal['calories'] - $target);
                                        $diffSign = ((int)$meal['calories'] - $target) > 0 ? '+' : '';
                                        ?>
                                        <span class="text-xs px-2 py-1 rounded-full <?php echo $diff <= 50 ? 'bg-green-100 text-green-700' : 'bg-zinc-100 text-zinc-500'; ?> font-semibold whitespace-nowrap">
                                            <?php echo $diffSign . ((int)$meal['calories'] - $target); ?> kcal vs cible
                                        </span>
                                    </div>

                                    <!-- Macros -->
                                    <div class="grid grid-cols-4 gap-2 text-xs mb-3">
                                        <div class="text-center bg-orange-50 rounded-lg p-2">
                                            <p class="font-bold text-orange-600"><?php echo number_format($meal['calories'], 0); ?></p>
                                            <p class="text-zinc-500">kcal</p>
                                        </div>
                                        <div class="text-center bg-primary/8 rounded-lg p-2">
                                            <p class="font-bold text-primary"><?php echo number_format($meal['protein'], 1); ?>g</p>
                                            <p class="text-zinc-500">Protéines</p>
                                        </div>
                                        <div class="text-center bg-secondary/8 rounded-lg p-2">
                                            <p class="font-bold text-secondary"><?php echo number_format($meal['carb'], 1); ?>g</p>
                                            <p class="text-zinc-500">Glucides</p>
                                        </div>
                                        <div class="text-center bg-purple-50 rounded-lg p-2">
                                            <p class="font-bold text-purple-600"><?php echo number_format($meal['fat'], 1); ?>g</p>
                                            <p class="text-zinc-500">Lipides</p>
                                        </div>
                                    </div>

                                    <!-- Barre de remplissage vs cible -->
                                    <?php $fillPct = min(100, round(($meal['calories'] / max(1, $target)) * 100)); ?>
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-zinc-100 rounded-full overflow-hidden">
                                            <div class="macro-bar h-full rounded-full <?php echo $fillPct >= 85 && $fillPct <= 115 ? 'bg-green-500' : 'bg-amber-400'; ?>"
                                                 style="width: <?php echo $fillPct; ?>%"></div>
                                        </div>
                                        <span class="text-xs text-zinc-400 font-semibold"><?php echo $fillPct; ?>%</span>
                                    </div>
                                    <p class="text-xs text-zinc-400 mt-1">Couverture de l'objectif calorique du repas</p>

                                    <a href="index.php?action=meal-detail&id=<?php echo $meal['id_meal']; ?>"
                                       class="mt-3 inline-flex items-center gap-1 text-xs text-primary font-semibold hover:underline">
                                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                                        Voir les détails
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="p-4 bg-zinc-50 rounded-xl text-sm text-zinc-500 flex items-center gap-2">
                                <span class="material-symbols-outlined text-zinc-400">info</span>
                                Aucun repas de type <strong><?php echo $slot['label']; ?></strong> dans le catalogue.
                                <a href="index.php?action=admin-meal-add&section=meal" class="text-primary font-semibold ml-1">En ajouter ?</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Total journée -->
                <?php
                $totalCal  = array_sum(array_filter(array_map(fn($k) => (int)($plan[$k]['calories'] ?? 0), ['breakfast','lunch','dinner'])));
                $totalProt = array_sum(array_filter(array_map(fn($k) => (float)($plan[$k]['protein'] ?? 0), ['breakfast','lunch','dinner'])));
                $totalCarb = array_sum(array_filter(array_map(fn($k) => (float)($plan[$k]['carb'] ?? 0),    ['breakfast','lunch','dinner'])));
                $totalFat  = array_sum(array_filter(array_map(fn($k) => (float)($plan[$k]['fat'] ?? 0),     ['breakfast','lunch','dinner'])));
                ?>
                <div class="bg-white rounded-2xl shadow-sm border border-zinc-100 p-8">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">summarize</span>
                        Total journée
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-orange-50 rounded-xl">
                            <p class="text-3xl font-extrabold text-orange-600"><?php echo number_format($totalCal, 0); ?></p>
                            <p class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mt-1">kcal total</p>
                            <p class="text-xs text-zinc-400 mt-1">Objectif : <?php echo $plan['tdee']; ?> kcal</p>
                        </div>
                        <div class="text-center p-4 bg-primary/8 rounded-xl">
                            <p class="text-3xl font-extrabold text-primary"><?php echo number_format($totalProt, 1); ?>g</p>
                            <p class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mt-1">Protéines</p>
                        </div>
                        <div class="text-center p-4 bg-secondary/8 rounded-xl">
                            <p class="text-3xl font-extrabold text-secondary"><?php echo number_format($totalCarb, 1); ?>g</p>
                            <p class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mt-1">Glucides</p>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-xl">
                            <p class="text-3xl font-extrabold text-purple-600"><?php echo number_format($totalFat, 1); ?>g</p>
                            <p class="text-xs text-zinc-500 uppercase tracking-wider font-semibold mt-1">Lipides</p>
                        </div>
                    </div>
                </div>

                <!-- Boutons Export + Code QR -->
                <div class="no-print grid grid-cols-1 <?php echo (isset($is_pdf_download) && $is_pdf_download) ? 'gap-3' : 'md:grid-cols-3 gap-6'; ?> items-start">
                    <!-- Code QR (masqué si accès via QR) -->
                    <?php if ($qr_code_url && (!isset($is_pdf_download) || !$is_pdf_download)): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-zinc-100 p-6 text-center">
                        <h3 class="text-lg font-bold mb-4 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-primary">qr_code_2</span>
                            Partager ce plan
                        </h3>
                        <p class="text-sm text-zinc-600 mb-4">Scannez avec votre téléphone pour y accéder</p>
                        <img src="<?php echo htmlspecialchars($qr_code_url); ?>" alt="QR Code - Plan Nutritionnel" class="mx-auto rounded-lg border-2 border-zinc-100" width="250" height="250">
                    </div>
                    <?php endif; ?>

                    <!-- Boutons Export -->
                    <div class="<?php echo (isset($is_pdf_download) && $is_pdf_download) ? '' : 'md:col-span-2'; ?> space-y-3">
                        <?php if (!empty($whatsapp_share_url) && !empty($whatsapp_app_url) && (!isset($is_pdf_download) || !$is_pdf_download)): ?>
                        <a href="<?php echo htmlspecialchars($whatsapp_app_url); ?>" onclick="return shareOnWhatsApp(event, '<?php echo htmlspecialchars($whatsapp_app_url, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($whatsapp_share_url, ENT_QUOTES); ?>');"
                           class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-green-600 text-white rounded-xl font-semibold text-sm hover:bg-green-700 transition-all shadow-sm">
                            <span class="material-symbols-outlined text-base">share</span>
                            Partager sur WhatsApp
                        </a>
                        <?php endif; ?>

                        <!-- Export CSV -->
                        <form method="POST" action="index.php?action=export-plan-csv">
                            <input type="hidden" name="weight"   value="<?php echo htmlspecialchars($input['weight']); ?>">
                            <input type="hidden" name="height"   value="<?php echo htmlspecialchars($input['height']); ?>">
                            <input type="hidden" name="age"      value="<?php echo htmlspecialchars($input['age']); ?>">
                            <input type="hidden" name="gender"   value="<?php echo htmlspecialchars($input['gender']); ?>">
                            <input type="hidden" name="activity" value="<?php echo htmlspecialchars($input['activity']); ?>">
                            <input type="hidden" name="goal"     value="<?php echo htmlspecialchars($input['goal']); ?>">
                            <?php foreach (($input['priorities'] ?? []) as $priority): ?>
                                <input type="hidden" name="priorities[]" value="<?php echo htmlspecialchars($priority); ?>">
                            <?php endforeach; ?>
                            <button type="submit"
                                    class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 text-white rounded-xl font-semibold text-sm hover:bg-emerald-700 transition-all shadow-sm">
                                <span class="material-symbols-outlined text-base">download</span>
                                Exporter CSV
                            </button>
                        </form>

                        <!-- Export PDF (impression navigateur) -->
                        <button onclick="printPlan()"
                                class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-rose-600 text-white rounded-xl font-semibold text-sm hover:bg-rose-700 transition-all shadow-sm">
                            <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                            Exporter PDF
                        </button>
                    </div>
                </div>

            <?php else: ?>
                <!-- État vide -->
                <div class="flex flex-col items-center justify-center h-full min-h-[400px] text-center space-y-4 bg-white rounded-2xl shadow-sm border border-zinc-100 p-12">
                    <span class="material-symbols-outlined text-7xl text-primary/20">nutrition</span>
                    <h2 class="text-2xl font-bold text-on-surface">Votre plan apparaîtra ici</h2>
                    <p class="text-on-surface-variant max-w-sm">
                        Remplissez le formulaire à gauche avec votre poids, taille, âge et objectif pour générer un plan personnalisé basé sur les repas du catalogue.
                    </p>
                    <div class="flex gap-2 text-sm text-zinc-400">
                        <span class="material-symbols-outlined text-base">arrow_back</span>
                        Renseignez vos informations
                    </div>
                </div>
            <?php endif; ?>
        </div><!-- /résultats -->
    </div>
</main>

<footer class="no-print bg-zinc-50 border-t border-zinc-200 py-6 text-center text-sm text-zinc-400 mt-8">
    © 2026 NutriNova - Nutrition Intelligente
</footer>
<script>
    function printPlan() {
        document.title = 'Plan Nutritionnel NutriNova - ' + new Date().toLocaleDateString('fr-FR');
        window.print();
    }

    function shareOnWhatsApp(event, appUrl, webUrl) {
        event.preventDefault();

        const startedAt = Date.now();
        window.location.href = appUrl;

        window.setTimeout(function() {
            if (Date.now() - startedAt < 1600) {
                window.open(webUrl, '_blank', 'noopener');
            }
        }, 900);

        return false;
    }

    // Lancer l'impression automatiquement si c'est un accès via QR code (téléchargement PDF)
    <?php if (isset($is_pdf_download) && $is_pdf_download): ?>
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.title = 'Plan Nutritionnel NutriNova - ' + new Date().toLocaleDateString('fr-FR');
                window.print();
            }, 500);
        });
    <?php endif; ?>
</script>
</body>
</html>
