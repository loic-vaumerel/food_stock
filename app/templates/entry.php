<?php defined('FOOD_APP') || exit; ?>
<?php
$entry = $v['entry'];
$entryUrl = app_url('entry.php?id=' . $entry['id']);
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
        <h2><?= e(t('Edit details')) ?></h2>
        <form class="form-grid" method="post" action="<?= e($entryUrl) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="edit">
            <div class="field full">
                <label for="edit-name"><?= e(t('Name')) ?></label>
                <input id="edit-name" name="name" required maxlength="<?= NAME_MAX_LENGTH ?>"
                       value="<?= e($entry['product_name']) ?>">
            </div>
            <div class="field">
                <label for="edit-storage-date"><?= e(t('Storage date')) ?></label>
                <input id="edit-storage-date" name="storage_date" type="date" required
                       min="<?= DATE_MIN ?>" max="<?= DATE_MAX ?>" value="<?= e($entry['storage_date']) ?>">
            </div>
            <div class="field">
                <label for="edit-expiry-date"><?= e(t('Expiry date (optional)')) ?></label>
                <input id="edit-expiry-date" name="expiry_date" type="date"
                       min="<?= DATE_MIN ?>" max="<?= DATE_MAX ?>" value="<?= e($entry['expiry_date'] ?? '') ?>">
            </div>
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
