<?php
declare(strict_types=1);

/* Consultas usadas pelas páginas públicas e pelo painel. */

/** Notícias publicadas, mais recentes primeiro, com a foto de capa. */
function published_news(int $limit, int $offset = 0): array
{
    $st = db()->prepare(
        'SELECT n.id, n.slug, n.title, n.body, n.published_at,
                (SELECT path FROM news_images i WHERE i.news_id = n.id ORDER BY sort_order, id LIMIT 1) AS cover
         FROM news n
         WHERE n.published_at IS NOT NULL
         ORDER BY n.published_at DESC, n.id DESC
         LIMIT ? OFFSET ?'
    );
    $st->execute([$limit, $offset]);
    return $st->fetchAll();
}

function published_news_count(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM news WHERE published_at IS NOT NULL')->fetchColumn();
}

function published_news_by_slug(string $slug): ?array
{
    $st = db()->prepare('SELECT id, slug, title, body, published_at FROM news WHERE slug = ? AND published_at IS NOT NULL');
    $st->execute([$slug]);
    $news = $st->fetch();
    if (!$news) {
        return null;
    }
    $news['images'] = images_of('news', (int) $news['id']);
    return $news;
}

/** Fotos de uma notícia ou produto, pela ordem. */
function images_of(string $type, int $id): array
{
    [$table, $column] = match ($type) {
        'news' => ['news_images', 'news_id'],
        'product' => ['product_images', 'product_id'],
    };
    $st = db()->prepare("SELECT id, path FROM $table WHERE $column = ? ORDER BY sort_order, id");
    $st->execute([$id]);
    return $st->fetchAll();
}

/** Primeiras linhas de um texto, para listas. */
function excerpt(string $text, int $length = 180): string
{
    $text = trim((string) preg_replace('/\s+/', ' ', $text));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    $cut = mb_substr($text, 0, $length);
    $space = mb_strrpos($cut, ' ');
    return rtrim(mb_substr($cut, 0, $space ?: $length), ' ,.;:') . '…';
}

/** A promoção de um produto está ativa hoje? */
function sale_active(array $product, ?DateTimeImmutable $today = null): bool
{
    if ($product['sale_price_cents'] === null) {
        return false;
    }
    $day = ($today ?? new DateTimeImmutable('today'))->format('Y-m-d');
    return ($product['sale_starts_on'] === null || $product['sale_starts_on'] <= $day)
        && ($product['sale_ends_on'] === null || $product['sale_ends_on'] >= $day);
}

/** Preço a cobrar hoje, em cêntimos. */
function current_price(array $product): int
{
    return sale_active($product) ? (int) $product['sale_price_cents'] : (int) $product['price_cents'];
}

/** Slug único na tabela indicada (acrescenta -2, -3... se já existir). */
function unique_slug(string $table, string $text): string
{
    if (!in_array($table, ['news', 'products'], true)) {
        throw new InvalidArgumentException('Tabela sem slug.');
    }
    $base = slugify($text);
    $slug = $base;
    $st = db()->prepare("SELECT 1 FROM $table WHERE slug = ?");
    for ($n = 2; ; $n++) {
        $st->execute([$slug]);
        if ($st->fetchColumn() === false) {
            return $slug;
        }
        $slug = $base . '-' . $n;
    }
}

/** Executa uma consulta para uma página pública; se a base de dados falhar, regista e usa o valor alternativo. */
function try_db(callable $query, mixed $fallback): mixed
{
    try {
        return $query();
    } catch (Throwable $ex) {
        error_log('ADMSC base de dados: ' . $ex->getMessage());
        return $fallback;
    }
}

/** "Sáb., 14 de março de 2026, 10:00–13:00" — texto da data de um evento. */
function event_when(array $e): string
{
    $days = ['seg.', 'ter.', 'qua.', 'qui.', 'sex.', 'sáb.', 'dom.'];
    $fmt = static function (string $ymd) use ($days): string {
        $d = new DateTimeImmutable($ymd);
        return $days[(int) $d->format('N') - 1] . ' ' . $d->format('j') . ' ' . mb_substr(MONTHS_PT[(int) $d->format('n') - 1], 0, 3) . ' ' . $d->format('Y');
    };
    $time = static fn (?string $t): string => $t === null ? '' : substr($t, 0, 5);

    $text = $fmt($e['starts_on']);
    if ($e['start_time'] !== null) {
        $text .= ', ' . $time($e['start_time']);
    }
    if ($e['ends_on'] !== null) {
        $text .= ' – ' . $fmt($e['ends_on']) . ($e['end_time'] !== null ? ', ' . $time($e['end_time']) : '');
    } elseif ($e['end_time'] !== null) {
        $text .= '–' . $time($e['end_time']);
    }
    return $text;
}

/** Eventos que tocam o intervalo [de, até] (datas Y-m-d), agrupados por dia. */
function events_by_day(string $from, string $to): array
{
    $st = db()->prepare(
        'SELECT id, title, location, starts_on, start_time, ends_on, end_time FROM events
         WHERE starts_on <= ? AND COALESCE(ends_on, starts_on) >= ?
         ORDER BY starts_on, start_time IS NOT NULL, start_time, id'
    );
    $st->execute([$to, $from]);
    $days = [];
    foreach ($st->fetchAll() as $event) {
        $day = new DateTimeImmutable(max($event['starts_on'], $from));
        $last = new DateTimeImmutable(min($event['ends_on'] ?? $event['starts_on'], $to));
        for (; $day <= $last; $day = $day->modify('+1 day')) {
            $days[$day->format('Y-m-d')][] = $event;
        }
    }
    return $days;
}

/** Próximos eventos (incluindo os que estão a decorrer hoje). */
function upcoming_events(int $limit): array
{
    $st = db()->prepare(
        'SELECT id, title, location, description, starts_on, start_time, ends_on, end_time FROM events
         WHERE COALESCE(ends_on, starts_on) >= ?
         ORDER BY starts_on, start_time IS NOT NULL, start_time, id
         LIMIT ?'
    );
    $st->execute([date('Y-m-d'), $limit]);  // data de Portugal (a ligação à BD está em UTC)
    return $st->fetchAll();
}
