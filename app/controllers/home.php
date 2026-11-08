<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

function run_home_page(): void
{
    require_method(['GET', 'HEAD']);
    require_login();
    $filters = home_filters();
    render('home', [
        'title' => 'Stock',
        'page' => 'home',
        'filters' => $filters,
        'locations' => all_locations(),
        'groups' => stock_groups($filters['q'], $filters['location']),
    ]);
}

/** @return array{q: string, location: ?int} */
function home_filters(): array
{
    return [
        'q' => normalize_text(get_string('q', 400)),
        'location' => parse_id($_GET['location'] ?? null),
    ];
}
