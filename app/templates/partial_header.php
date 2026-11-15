<?php defined('FOOD_APP') || exit; ?>
<header class="topbar">
    <a class="brand" href="<?= e(app_url('index.php')) ?>"><?= e(t('Food stock')) ?></a>
    <!-- Phone menu: checkbox + label, no JavaScript (the CSP forbids scripts). Hidden on desktop. -->
    <input type="checkbox" id="menu-toggle" class="menu-check visually-hidden">
    <label for="menu-toggle" class="menu-toggle"><i class="fa-solid fa-bars" aria-hidden="true"></i> <?= e(t('Menu')) ?></label>
    <div class="menu-panel">
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
                <button type="submit" class="secondary"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> <?= e(t('Log out')) ?></button>
            </form>
        </div>
    </div>
</header>
