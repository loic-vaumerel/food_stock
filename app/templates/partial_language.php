<?php defined('FOOD_APP') || exit; ?>
<?php $current = current_language(); ?>
<form class="lang-switch" method="post" action="<?= e(app_url('language.php')) ?>" aria-label="<?= e(t('Language')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="page" value="<?= e((string) $v['page']) ?>">
    <?php if (isset($v['entry']['id'])): ?>
        <input type="hidden" name="entry" value="<?= (int) $v['entry']['id'] ?>">
    <?php endif; ?>
    <?php foreach (LANGUAGES as $code => $name): ?>
        <button type="submit" name="lang" value="<?= e($code) ?>" lang="<?= e($code) ?>" title="<?= e($name) ?>"
                aria-pressed="<?= $code === $current ? 'true' : 'false' ?>"><?= e(strtoupper($code)) ?></button>
    <?php endforeach; ?>
</form>
