<?php defined('FOOD_APP') || exit; ?>
<header class="topbar">
    <a class="brand" href="<?= e(app_url('index.php')) ?>"><?= e(t('Food stock')) ?></a>
    <nav class="nav" aria-label="<?= e(t('Main navigation')) ?>">
        <?php foreach (NAV_PAGES as $page => [$path, $label]): ?>
            <a href="<?= e(app_url($path)) ?>"<?= $v['page'] === $page ? ' aria-current="page"' : '' ?>><?= e(t($label)) ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="user">
        <?= render_to_string('partial_language', $v) ?>
        <span class="email"><?= e((string) $v['user']['email']) ?></span>
        <form method="post" action="<?= e(app_url('logout.php')) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="secondary"><?= e(t('Log out')) ?></button>
        </form>
    </div>
</header>
