<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/**
 * Renders a template inside the layout. Template names are validated against
 * a strict pattern and resolved in a fixed folder (no path traversal).
 *
 * @param array<string, mixed> $vars
 */
function render(string $template, array $vars = []): void
{
    $vars += [
        'title' => 'Food stock',
        'page' => 'home',
        'user' => current_user(),
        'flash' => flash_take(),
    ];
    $vars['body'] = render_to_string($template, $vars);
    echo render_to_string('layout', $vars);
}

/** @param array<string, mixed> $v */
function render_to_string(string $template, array $v): string
{
    ob_start();
    try {
        require template_path($template);
        return (string) ob_get_contents();
    } finally {
        ob_end_clean();
    }
}

function template_path(string $template): string
{
    if (preg_match('/^[a-z_]+$/', $template) !== 1) {
        throw new InvalidArgumentException('Invalid template name.');
    }
    return APP_ROOT . '/templates/' . $template . '.php';
}

/**
 * <option> list, every value and label escaped.
 *
 * @param list<array{0: string|int, 1: string}> $options [value, label]
 */
function select_options(array $options, string $selected, ?string $placeholder = null): string
{
    $html = $placeholder === null ? '' : '<option value="">' . e(t($placeholder)) . '</option>';
    foreach ($options as [$value, $label]) {
        $isSelected = (string) $value === $selected ? ' selected' : '';
        $html .= '<option value="' . e((string) $value) . '"' . $isSelected . '>' . e($label) . '</option>';
    }
    return $html;
}

/** Header navigation: page key => [path, English label (translated when shown)]. */
const NAV_PAGES = [
    'home' => ['index.php', 'Stock'],
    'locations' => ['locations.php', 'Locations'],
    'containers' => ['containers.php', 'Containers'],
];

/** @return list<array{0: int, 1: string}> */
function location_options(array $locations, ?int $excludeId = null): array
{
    $options = [];
    foreach ($locations as $location) {
        if ((int) $location['id'] !== $excludeId) {
            $options[] = [(int) $location['id'], (string) $location['name']];
        }
    }
    return $options;
}

/** @return list<array{0: int, 1: string}> */
function container_options(array $containers): array
{
    return array_map(fn (array $c): array => [(int) $c['id'], (string) $c['name']], $containers);
}

/** @return list<array{0: string, 1: string}> */
function unit_options(): array
{
    return array_map(fn (string $unit): array => [$unit, unit_label($unit)], UNITS);
}
