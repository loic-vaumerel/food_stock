<?php defined('FOOD_APP') || exit; ?>
<?php $form = $v['form']; ?>
<main class="content narrow">
    <div class="page-head">
        <h1><?= e(t('Add food')) ?></h1>
        <a href="<?= e(app_url('index.php')) ?>"><?= e(t('Back to stock')) ?></a>
    </div>

    <?php if ($v['locations'] === []): ?>
        <p class="empty"><?= e(t('There is no location yet.')) ?>
            <a href="<?= e(app_url('locations.php')) ?>"><?= e(t('Create a location')) ?></a></p>
    <?php else: ?>

    <?php if ($v['error'] !== null): ?>
        <p class="alert" role="alert"><?= e($v['error']) ?></p>
    <?php endif; ?>

    <form class="card form-grid" method="post" action="<?= e(app_url('stock_add.php')) ?>">
        <?= csrf_field() ?>

        <div class="field full">
            <label for="product"><?= e(t('Product')) ?></label>
            <input id="product" name="product" list="product-list" required autocomplete="off"
                   maxlength="<?= NAME_MAX_LENGTH ?>" value="<?= e($form['product']) ?>"
                   aria-describedby="product-hint">
            <datalist id="product-list">
                <?php foreach ($v['product_names'] as $name): ?>
                    <option value="<?= e((string) $name) ?>"></option>
                <?php endforeach; ?>
            </datalist>
            <small id="product-hint"><?= e(t('Pick an existing product or type a new name.')) ?></small>
        </div>

        <div class="field full">
            <label for="location"><?= e(t('Location')) ?></label>
            <select id="location" name="location" required>
                <?= select_options(location_options($v['locations']), $form['location'], 'Choose…') ?>
            </select>
        </div>

        <div class="field">
            <label for="container"><?= e(t('Container')) ?></label>
            <select id="container" name="container" required>
                <?= select_options(container_options($v['containers']), $form['container'], 'Choose…') ?>
            </select>
            <small><a href="<?= e(app_url('containers.php')) ?>"><?= e(t('New container type')) ?></a></small>
        </div>

        <div class="field">
            <label for="amount"><?= e(t('Amount per package')) ?></label>
            <div class="inline">
                <input id="amount" name="amount" required inputmode="decimal" maxlength="12"
                       value="<?= e($form['amount']) ?>" aria-describedby="amount-hint">
                <label for="unit" class="visually-hidden"><?= e(t('Unit')) ?></label>
                <select id="unit" name="unit" required>
                    <?= select_options(unit_options(), $form['unit']) ?>
                </select>
            </div>
            <small id="amount-hint"><?= e(t('kg and l: up to 3 decimals. g, ml and pieces: whole numbers.')) ?></small>
        </div>

        <div class="field">
            <label for="quantity"><?= e(t('Quantity (number of packages)')) ?></label>
            <input id="quantity" name="quantity" type="number" required min="1" max="<?= QUANTITY_MAX ?>" step="1"
                   value="<?= e($form['quantity']) ?>">
        </div>

        <div class="field">
            <label for="storage_date"><?= e(t('Storage date')) ?></label>
            <input id="storage_date" name="storage_date" type="date" required
                   min="<?= DATE_MIN ?>" max="<?= DATE_MAX ?>" value="<?= e($form['storage_date']) ?>">
        </div>

        <div class="field">
            <label for="expiry_date"><?= e(t('Expiry date (optional)')) ?></label>
            <input id="expiry_date" name="expiry_date" type="date"
                   min="<?= DATE_MIN ?>" max="<?= DATE_MAX ?>" value="<?= e($form['expiry_date']) ?>">
        </div>

        <div class="field full actions-row">
            <button type="submit" class="primary"><?= e(t('Add to stock')) ?></button>
            <a href="<?= e(app_url('index.php')) ?>"><?= e(t('Cancel')) ?></a>
        </div>
    </form>
    <?php endif; ?>
</main>
