<?php defined('FOOD_APP') || exit; ?>
<?php $filters = $v['filters']; ?>
<main class="content">
    <div class="page-head">
        <h1><?= e(t('Stock')) ?></h1>
        <a class="button primary" href="<?= e(app_url('stock_add.php')) ?>"><?= e(t('Add food')) ?></a>
    </div>

    <form class="filters" method="get" action="<?= e(app_url('index.php')) ?>" role="search">
        <label for="q" class="visually-hidden"><?= e(t('Search a product')) ?></label>
        <input id="q" name="q" type="search" maxlength="100"
               placeholder="<?= e(t('Search a product…')) ?>" value="<?= e($filters['q']) ?>">
        <label for="location" class="visually-hidden"><?= e(t('Location')) ?></label>
        <select id="location" name="location">
            <option value=""><?= e(t('All locations')) ?></option>
            <?php foreach ($v['locations'] as $location): ?>
                <option value="<?= (int) $location['id'] ?>"<?= $filters['location'] === (int) $location['id'] ? ' selected' : '' ?>><?= e((string) $location['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="secondary"><?= e(t('Filter')) ?></button>
        <?php if ($filters['q'] !== '' || $filters['location'] !== null): ?>
            <a href="<?= e(app_url('index.php')) ?>"><?= e(t('Reset')) ?></a>
        <?php endif; ?>
    </form>

    <?php if ($v['locations'] === []): ?>
        <p class="empty"><?= e(t('There is no location yet.')) ?>
            <a href="<?= e(app_url('locations.php')) ?>"><?= e(t('Create a location')) ?></a></p>
    <?php elseif ($v['groups'] === []): ?>
        <p class="empty"><?= e(t('No stock matches.')) ?></p>
    <?php endif; ?>

    <?php foreach ($v['groups'] as $group): ?>
        <section class="location-group">
            <div class="group-head">
                <h2><?= e($group['name']) ?></h2>
                <a href="<?= e(app_url('stock_add.php?location=' . $group['id'])) ?>"><?= e(t('Add here')) ?></a>
            </div>
            <div class="table-wrap">
                <table class="stock">
                    <thead>
                    <tr>
                        <th scope="col"><?= e(t('Product')) ?></th>
                        <th scope="col"><?= e(t('Packaging')) ?></th>
                        <th scope="col" class="num"><?= e(t('Quantity')) ?></th>
                        <th scope="col"><?= e(t('Stored on')) ?></th>
                        <th scope="col"><?= e(t('Expires on')) ?></th>
                        <th scope="col"><span class="visually-hidden"><?= e(t('Actions')) ?></span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($group['entries'] as $entry): ?>
                        <tr<?= $entry['status'] !== '' ? ' class="row-' . e($entry['status']) . '"' : '' ?>>
                            <td><?= e($entry['product_name']) ?></td>
                            <td><?= e($entry['packaging']) ?></td>
                            <td class="num"><?= (int) $entry['quantity'] ?></td>
                            <td><?= e($entry['storage_label']) ?></td>
                            <td>
                                <?= e($entry['expiry_label']) ?>
                                <?php if ($entry['status'] !== ''): ?>
                                    <span class="badge badge-<?= e($entry['status']) ?>"><?= e(status_label($entry['status'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?= e(app_url('entry.php?id=' . $entry['id'])) ?>"><?= e(t('Manage')) ?></a>
                                <a href="<?= e(app_url('stock_add.php?product=' . $entry['product_id'] . '&location=' . $entry['location_id'])) ?>"><?= e(t('Other packaging')) ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endforeach; ?>
</main>
