<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Lisbon');

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* Email escapado que, se não couber, só parte a seguir ao @ (e não a meio do domínio). */
function email_html(string $email): string
{
    return str_replace('@', '@<wbr>', e($email));
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
 * @param (callable(string, string): array)|null $loadEvents recebe o 1.º e o último dia do mês; devolve eventos por dia (Y-m-d)
 */
function calendar_month(?string $requested, ?callable $loadEvents = null): array
{
    $months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $today = new DateTimeImmutable('today');
    $valid = is_string($requested) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $requested) === 1;
    $first = new DateTimeImmutable(($valid ? $requested : $today->format('Y-m')) . '-01');

    $offset = (int) $first->format('N') - 1;
    $days = (int) $first->format('t');
    $events = $loadEvents ? $loadEvents($first->format('Y-m-d'), $first->format('Y-m-t')) : [];
    $total = (int) ceil(($offset + $days) / 7) * 7;

    $cells = [];
    for ($i = 0; $i < $total; $i++) {
        $day = $i - $offset + 1;
        $inMonth = $day >= 1 && $day <= $days;
        $date = $inMonth ? $first->setDate((int) $first->format('Y'), (int) $first->format('n'), $day)->format('Y-m-d') : null;
        $cells[] = [
            'day' => $inMonth ? $day : null,
            'today' => $date === $today->format('Y-m-d'),
            'events' => $date !== null ? ($events[$date] ?? []) : [],
        ];
    }

    return [
        'label' => $months[(int) $first->format('n') - 1] . ' ' . $first->format('Y'),
        'prev' => $first->modify('-1 month')->format('Y-m'),
        'next' => $first->modify('+1 month')->format('Y-m'),
        'cells' => $cells,
    ];
}

function redirect(string $url, int $status = 303): never
{
    header('Location: ' . $url, true, $status);
    exit;
}

/** Texto para endereço: "Estágio de Verão 2026" → "estagio-de-verao-2026". */
function slugify(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'ç' => 'c',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ñ' => 'n', 'º' => 'o', 'ª' => 'a',
    ]);
    $text = trim((string) preg_replace('/[^a-z0-9]+/', '-', $text), '-');
    return substr($text, 0, 120) ?: 'item';
}

/** "12,50" / "12.5" / "12 €" → 1250 cêntimos; null se inválido. */
function parse_money(string $value): ?int
{
    $value = str_replace(['€', ' ', "\u{a0}"], '', trim($value));
    if (preg_match('/^(\d{1,6})(?:[.,](\d{1,2}))?$/', $value, $m) !== 1) {
        return null;
    }
    return (int) $m[1] * 100 + (int) str_pad($m[2] ?? '0', 2, '0');
}

/** 1250 → "12,50 €" */
function money(int $cents): string
{
    return number_format($cents / 100, 2, ',', ' ') . ' €';
}

/** 1250 → "12,50" (para campos de formulário). */
function money_input(?int $cents): string
{
    return $cents === null ? '' : number_format($cents / 100, 2, ',', '');
}

const MONTHS_PT = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

/** Data UTC da base de dados → hora de Portugal. */
function utc_to_local(string $utc): DateTimeImmutable
{
    return (new DateTimeImmutable($utc, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Europe/Lisbon'));
}

/** "23 de novembro de 2026" */
function date_long(DateTimeInterface $date): string
{
    return $date->format('j') . ' de ' . MONTHS_PT[(int) $date->format('n') - 1] . ' de ' . $date->format('Y');
}

/** Parágrafos a partir de texto simples (linhas em branco separam parágrafos). */
function paragraphs(string $text): string
{
    $html = '';
    foreach (preg_split('/\R{2,}/', trim($text)) ?: [] as $block) {
        if (trim($block) !== '') {
            $html .= '<p>' . nl2br(e(trim($block)), false) . '</p>';
        }
    }
    return $html;
}

/** Endereço público de um ficheiro em public_html/uploads/. */
function upload_url(string $relative): string
{
    return '/uploads/' . $relative;
}
