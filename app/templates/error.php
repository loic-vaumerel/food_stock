<?php defined('FOOD_APP') || exit; ?>
<main class="auth-card">
    <h1><?= e(t('Something went wrong')) ?></h1>
    <p><?= e((string) $v['message']) ?></p>
    <p><a href="<?= e(app_url('')) ?>"><?= e(t('Back to home')) ?></a></p>
</main>
