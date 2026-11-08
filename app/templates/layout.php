<?php defined('FOOD_APP') || exit; ?>
<!doctype html>
<html lang="<?= e(current_language()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e(t((string) $v['title'])) ?> · <?= e(t('Food stock')) ?></title>
    <link rel="stylesheet" href="<?= e(app_url('assets/style.css')) ?>">
</head>
<body>
<?php if ($v['user'] !== null): ?>
<?= render_to_string('partial_header', $v) ?>
<?php endif; ?>
<?php if ($v['flash'] !== null): ?>
    <p class="flash" role="status"><?= e(t($v['flash'])) ?></p>
<?php endif; ?>
<?= $v['body'] /* already-rendered template; every dynamic value inside it is escaped */ ?>
</body>
</html>
