<?php defined('FOOD_APP') || exit; ?>
<?php $url = app_url('users.php'); ?>
<?php $me = (int) $v['user']['id']; ?>
<main class="content narrow users-page">
    <h1><?= e(t('Users')) ?></h1>

    <?php if ($v['error'] !== null): ?>
        <p class="alert" role="alert"><?= e($v['error']) ?></p>
    <?php endif; ?>

    <form class="card" method="post" action="<?= e($url) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <h2><?= e(t('New user')) ?></h2>
        <div class="form-grid">
            <div>
                <label for="new-email"><?= e(t('E-mail')) ?></label>
                <input id="new-email" name="email" type="email" required maxlength="<?= EMAIL_MAX_LENGTH ?>" autocomplete="off">
            </div>
            <div>
                <label for="new-password"><?= e(t('Password')) ?></label>
                <input id="new-password" name="password" type="password" required maxlength="<?= PASSWORD_MAX_LENGTH ?>" autocomplete="new-password">
            </div>
        </div>
        <div class="actions-row">
            <button type="submit" class="primary"><?= e(t('Add')) ?></button>
        </div>
    </form>

    <?php if ($v['users'] === []): ?>
        <p class="empty"><?= e(t('There is no user yet.')) ?></p>
    <?php else: ?>
    <ul class="card list">
        <?php foreach ($v['users'] as $user): ?>
            <?php $id = (int) $user['id']; $active = (int) $user['is_active'] === 1; ?>
            <li class="user-row">
                <!-- One field per line. The Save button below belongs to this form through form="...". -->
                <form id="user-edit-<?= $id ?>" class="user-fields" method="post" action="<?= e($url) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <label for="user-email-<?= $id ?>"><?= e(t('E-mail')) ?></label>
                    <input id="user-email-<?= $id ?>" name="email" type="email" required maxlength="<?= EMAIL_MAX_LENGTH ?>"
                           value="<?= e((string) $user['email']) ?>">
                    <label for="user-password-<?= $id ?>"><?= e(t('New password (leave empty to keep)')) ?></label>
                    <input id="user-password-<?= $id ?>" name="password" type="password" maxlength="<?= PASSWORD_MAX_LENGTH ?>"
                           autocomplete="new-password">
                </form>
                <div class="user-actions">
                    <!-- Status and its toggle stay together on one line (nowrap group). -->
                    <div class="user-state">
                        <span class="muted">
                            <?= e($active ? t('Active') : t('Inactive')) ?><?= $id === $me ? ' ' . e(t('(you)')) : '' ?>
                        </span>
                        <?php if ($id !== $me): ?>
                            <form method="post" action="<?= e($url) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="<?= $active ? 'deactivate' : 'activate' ?>">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <button type="submit" class="<?= $active ? 'danger' : 'primary' ?>"><?= e($active ? t('Deactivate') : t('Reactivate')) ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="user-buttons">
                    <button type="submit" form="user-edit-<?= $id ?>" class="primary"><?= e(t('Save')) ?></button>
                    <?php if ($id !== $me): ?>
                        <form method="post" action="<?= e($url) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button type="submit" class="danger"><?= e(t('Delete')) ?></button>
                        </form>
                    <?php endif; ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</main>
