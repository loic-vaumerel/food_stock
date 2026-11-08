<?php defined('FOOD_APP') || exit; ?>
<?php
$entry = $v['entry'];
$entryUrl = app_url('entry.php?id=' . $entry['id']);
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

    <dl class="card summary<?= $entry['status'] !== '' ? ' row-' . e($entry['status']) : '' ?>">
        <dt><?= e(t('Location')) ?></dt><dd><?= e($entry['location_name']) ?></dd>
        <dt><?= e(t('Packaging')) ?></dt><dd><?= e($entry['packaging']) ?></dd>
        <dt><?= e(t('Quantity')) ?></dt><dd><?= (int) $entry['quantity'] ?></dd>
        <dt><?= e(t('Stored on')) ?></dt><dd><?= e($entry['storage_label']) ?></dd>
        <dt><?= e(t('Expires on')) ?></dt>
        <dd>
            <?= e($entry['expiry_label'] !== '' ? $entry['expiry_label'] : '—') ?>
            <?php if ($entry['status'] !== ''): ?>
                <span class="badge badge-<?= e($entry['status']) ?>"><?= e(status_label($entry['status'])) ?></span>
            <?php endif; ?>
        </dd>
    </dl>

    <section class="card">
        <h2><?= e(t('Remove full packages')) ?></h2>
        <form class="inline-form" method="post" action="<?= e($entryUrl) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="remove_packages">
            <label for="remove-count"><?= e(t('Number of packages')) ?></label>
            <input id="remove-count" name="count" type="number" required min="1" max="<?= (int) $entry['quantity'] ?>" step="1" value="1">
            <button type="submit" class="primary"><?= e(t('Remove')) ?></button>
        </form>
    </section>

    <section class="card">
        <h2><?= e(t('Remove part of a package')) ?></h2>
        <form class="inline-form" method="post" action="<?= e($entryUrl) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="remove_amount">
            <label for="remove-amount"><?= e(t('Amount to remove ({unit})', ['unit' => unit_label($entry['unit'])])) ?></label>
            <input id="remove-amount" name="amount" required inputmode="decimal" maxlength="12" aria-describedby="remove-amount-hint">
            <span class="unit"><?= e(unit_label($entry['unit'])) ?></span>
            <button type="submit" class="primary"><?= e(t('Remove')) ?></button>
        </form>
        <small id="remove-amount-hint"><?= e(t('Available: {amount}.', ['amount' => format_amount($entry['milli'] * $entry['quantity'], $entry['unit'])])) ?>
            <?= e(t('Whole packages are used first, then one package is opened.')) ?></small>
    </section>

    <section class="card">
        <h2><?= e(t('Move to another location')) ?></h2>
        <?php if ($otherLocations === []): ?>
            <p><?= e(t('Create another location first.')) ?> <a href="<?= e(app_url('locations.php')) ?>"><?= e(t('Locations')) ?></a></p>
        <?php else: ?>
        <form class="inline-form" method="post" action="<?= e($entryUrl) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="move">
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

    <section class="card">
        <h2><?= e(t('Change packaging or quantity')) ?></h2>
        <form class="form-grid" method="post" action="<?= e($entryUrl) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="edit">
            <div class="field">
                <label for="edit-container"><?= e(t('Container')) ?></label>
                <select id="edit-container" name="container" required>
                    <?= select_options(container_options($v['containers']), (string) $entry['container_id']) ?>
                </select>
            </div>
            <div class="field">
                <label for="edit-amount"><?= e(t('Amount per package')) ?></label>
                <div class="inline">
                    <input id="edit-amount" name="amount" required inputmode="decimal" maxlength="12"
                           value="<?= e(format_number($entry['milli'])) ?>">
                    <label for="edit-unit" class="visually-hidden"><?= e(t('Unit')) ?></label>
                    <select id="edit-unit" name="unit" required>
                        <?= select_options(unit_options(), $entry['unit']) ?>
                    </select>
                </div>
            </div>
            <div class="field">
                <label for="edit-quantity"><?= e(t('Quantity (number of packages)')) ?></label>
                <input id="edit-quantity" name="quantity" type="number" required min="1" max="<?= QUANTITY_MAX ?>" step="1"
                       value="<?= (int) $entry['quantity'] ?>">
            </div>
            <div class="field full actions-row">
                <button type="submit" class="primary"><?= e(t('Save')) ?></button>
            </div>
        </form>
    </section>
</main>
