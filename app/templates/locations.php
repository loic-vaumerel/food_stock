<?php defined('FOOD_APP') || exit; ?>
<?php $url = app_url('locations.php'); ?>
<main class="content narrow">
    <h1><?= e(t('Locations')) ?></h1>

    <?php if ($v['error'] !== null): ?>
        <p class="alert" role="alert"><?= e($v['error']) ?></p>
    <?php endif; ?>

    <form class="card inline-form" method="post" action="<?= e($url) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <label for="new-location"><?= e(t('New location')) ?></label>
        <input id="new-location" name="name" required maxlength="<?= NAME_MAX_LENGTH ?>">
        <button type="submit" class="primary"><?= e(t('Add')) ?></button>
    </form>

    <?php if ($v['locations'] === []): ?>
        <p class="empty"><?= e(t('There is no location yet.')) ?></p>
    <?php else: ?>
    <ul class="card list">
        <?php foreach ($v['locations'] as $location): ?>
            <?php $id = (int) $location['id']; $count = (int) $location['entry_count']; ?>
            <li>
                <form class="inline-form grow" method="post" action="<?= e($url) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="rename">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <label for="location-<?= $id ?>" class="visually-hidden"><?= e(t('Name')) ?></label>
                    <input id="location-<?= $id ?>" name="name" required maxlength="<?= NAME_MAX_LENGTH ?>"
                           value="<?= e((string) $location['name']) ?>">
                    <button type="submit" class="secondary"><?= e(t('Rename')) ?></button>
                </form>
                <span class="muted"><?= e(t('Stock entries: {count}', ['count' => $count])) ?></span>
                <?php if ($count === 0): ?>
                    <form method="post" action="<?= e($url) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button type="submit" class="danger"><?= e(t('Delete')) ?></button>
                    </form>
                <?php else: ?>
                    <span class="muted" title="<?= e(t('This location still contains stock entries and cannot be deleted.')) ?>"><?= e(t('Not empty')) ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</main>
