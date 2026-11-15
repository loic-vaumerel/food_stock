<?php defined('FOOD_APP') || exit; ?>
<?php
$entry = $v['entry'];
$entryUrl = app_url('move.php?id=' . $entry['id']);
$otherLocations = location_options($v['locations'], $entry['location_id']);
?>
<main class="content narrow">
    <div class="page-head">
        <h1><?= e($entry['product_name']) ?></h1>
        <a href="<?= e(app_url('index.php')) ?>"><?= e(t('Back to stock')) ?></a>
    </div>

    <?php if ($v['error'] !== null): ?>
        <p class="alert" role="alert"><?= e($v['error']) ?></p>
    <?php endif; ?>

    <dl class="card summary">
        <dt><?= e(t('Location')) ?></dt><dd><?= e($entry['location_name']) ?></dd>
        <dt><?= e(t('Packaging')) ?></dt><dd><?= e($entry['packaging']) ?></dd>
        <dt><?= e(t('Quantity')) ?></dt><dd><?= (int) $entry['quantity'] ?></dd>
    </dl>

    <section class="card">
        <h2><?= e(t('Move to another location')) ?></h2>
        <?php if ($otherLocations === []): ?>
            <p><?= e(t('Create another location first.')) ?> <a href="<?= e(app_url('locations.php')) ?>"><?= e(t('Locations')) ?></a></p>
        <?php else: ?>
        <form class="inline-form" method="post" action="<?= e($entryUrl) ?>">
            <?= csrf_field() ?>
            <label for="move-count"><?= e(t('Number of packages')) ?></label>
            <input id="move-count" name="count" type="number" required min="1" max="<?= (int) $entry['quantity'] ?>" step="1"
                   value="<?= (int) $entry['quantity'] ?>">
            <label for="move-location"><?= e(t('Destination')) ?></label>
            <select id="move-location" name="location" required>
                <?= select_options($otherLocations, '', 'Choose…') ?>
            </select>
            <button type="submit" class="primary"><?= e(t('Move')) ?></button>
        </form>
        <?php endif; ?>
    </section>
</main>
