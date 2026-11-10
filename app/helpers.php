<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Lisbon');

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function asset(string $path): string
{
    $file = dirname(__DIR__) . '/public_html/assets/' . $path;
    $version = is_file($file) ? '?v=' . filemtime($file) : '';
    return '/assets/' . $path . $version;
}

function asset_exists(string $path): bool
{
    return is_file(dirname(__DIR__) . '/public_html/assets/' . $path);
}

function partial(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require __DIR__ . '/views/partials/' . $name . '.php';
}

/** Renders a page inside the base layout. */
function render(string $page, array $data = [], int $status = 200): void
{
    http_response_code($status);
    extract($data, EXTR_SKIP);
    ob_start();
    require __DIR__ . '/views/pages/' . $page . '.php';
    $content = ob_get_clean();
    require __DIR__ . '/views/layout/base.php';
}

/**
 * Builds a Monday-first month grid.
 *
 * @param array<string, mixed> $events events keyed by Y-m-d
 */
function calendar_month(?string $requested, array $events = []): array
{
    $months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $today = new DateTimeImmutable('today');
    $valid = is_string($requested) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $requested) === 1;
    $first = new DateTimeImmutable(($valid ? $requested : $today->format('Y-m')) . '-01');

    $offset = (int) $first->format('N') - 1;
    $days = (int) $first->format('t');
    $total = (int) ceil(($offset + $days) / 7) * 7;

    $cells = [];
    for ($i = 0; $i < $total; $i++) {
        $day = $i - $offset + 1;
        $inMonth = $day >= 1 && $day <= $days;
        $date = $inMonth ? $first->setDate((int) $first->format('Y'), (int) $first->format('n'), $day)->format('Y-m-d') : null;
        $cells[] = [
            'day' => $inMonth ? $day : null,
            'today' => $date === $today->format('Y-m-d'),
            'event' => $date !== null ? ($events[$date] ?? null) : null,
        ];
    }

    return [
        'label' => $months[(int) $first->format('n') - 1] . ' ' . $first->format('Y'),
        'prev' => $first->modify('-1 month')->format('Y-m'),
        'next' => $first->modify('+1 month')->format('Y-m'),
        'cells' => $cells,
    ];
}
