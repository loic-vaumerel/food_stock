<?php defined('FOOD_APP') || exit; ?>
<?php $url = app_url('containers.php'); ?>
<main class="content narrow">
    <h1><?= e(t('Containers')) ?></h1>

    <?php if ($v['error'] !== null): ?>
        <p class="alert" role="alert"><?= e($v['error']) ?></p>
    <?php endif; ?>

    <form class="card inline-form" method="post" action="<?= e($url) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <label for="new-container"><?= e(t('New container type')) ?></label>
        <input id="new-container" name="name" required maxlength="<?= CONTAINER_NAME_MAX_LENGTH ?>">
        <button type="submit" class="primary"><?= e(t('Add')) ?></button>
    </form>

    <?php if ($v['containers'] === []): ?>
        <p class="empty"><?= e(t('There is no container type yet.')) ?></p>
    <?php else: ?>
    <ul class="card list">
        <?php foreach ($v['containers'] as $container): ?>
            <?php $id = (int) $container['id']; $count = (int) $container['entry_count']; ?>
            <li>
                <form class="inline-form grow" method="post" action="<?= e($url) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="rename">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <label for="container-<?= $id ?>" class="visually-hidden"><?= e(t('Name')) ?></label>
                    <input id="container-<?= $id ?>" name="name" required maxlength="<?= CONTAINER_NAME_MAX_LENGTH ?>"
                           value="<?= e((string) $container['name']) ?>">
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
                    <span class="muted" title="<?= e(t('This container type is used by stock entries and cannot be deleted.')) ?>"><?= e(t('In use')) ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</main>
