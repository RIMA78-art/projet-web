<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>NutriNova Admin - Error</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, .font-headline { font-family: 'Manrope', sans-serif; }
    </style>
</head>
<body class="bg-red-50 text-slate-900 min-h-screen flex items-center justify-center">

<div class="max-w-md mx-auto">
    <div class="bg-white rounded-3xl p-8 shadow border border-red-100 text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-red-600 text-4xl">error</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 mb-2 font-headline">Error</h1>
        <p class="text-slate-600 mb-6"><?php echo htmlspecialchars($error); ?></p>
        <a href="?action=index" class="inline-block px-6 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition font-semibold">Back to Home</a>
    </div>
</div>

</body>
</html>
