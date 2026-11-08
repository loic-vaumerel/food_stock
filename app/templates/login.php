<?php defined('FOOD_APP') || exit; ?>
<main class="auth-card">
    <?= render_to_string('partial_language', $v) ?>
    <h1><?= e(t('Food stock')) ?></h1>
    <p class="subtitle"><?= e(t('Sign in to continue')) ?></p>

    <?php if (($v['error'] ?? null) !== null): ?>
        <p class="alert" role="alert"><?= e((string) $v['error']) ?></p>
    <?php endif; ?>

    <form method="post" action="<?= e(app_url('login.php')) ?>">
        <?= csrf_field() ?>
        <label for="email"><?= e(t('E-mail')) ?></label>
        <input id="email" name="email" type="email" required autofocus
               maxlength="<?= EMAIL_MAX_LENGTH ?>" autocomplete="username"
               value="<?= e((string) ($v['email'] ?? '')) ?>">

        <label for="password"><?= e(t('Password')) ?></label>
        <input id="password" name="password" type="password" required
               maxlength="<?= PASSWORD_MAX_LENGTH ?>" autocomplete="current-password">

        <button type="submit" class="primary wide"><?= e(t('Sign in')) ?></button>
    </form>
</main>
