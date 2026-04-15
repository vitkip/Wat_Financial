<?php require BASE_PATH . '/app/views/layouts/head.php'; ?>
<body class="min-h-screen bg-background flex items-center justify-center p-4">

<div class="w-full max-w-sm">
    <?= $content ?>
</div>

<script>
// CSRF Auto-Inject for auth forms
(function () {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) return;
    const token = meta.getAttribute('content');
    document.querySelectorAll('form[method="POST"], form[method="post"]').forEach(form => {
        if (!form.querySelector('input[name="_csrf"]')) {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = '_csrf'; inp.value = token;
            form.appendChild(inp);
        }
    });
})();
</script>

</body>
</html>