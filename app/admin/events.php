<?php
declare(strict_types=1);

/* Painel: eventos do calendário. Datas e horas são hora de Portugal. */

function events_routes(string $sub, string $method, array $base): void
{
    if ($sub === '/') {
        $all = db()->query('SELECT * FROM events ORDER BY starts_on, start_time IS NOT NULL, start_time, id')->fetchAll();
        $today = date('Y-m-d');
        $upcoming = array_values(array_filter($all, static fn (array $e) => ($e['ends_on'] ?? $e['starts_on']) >= $today));
        $past = array_reverse(array_values(array_filter($all, static fn (array $e) => ($e['ends_on'] ?? $e['starts_on']) < $today)));
        admin_render('events-list', $base + ['title' => 'Eventos', 'section' => 'eventos', 'upcoming' => $upcoming, 'past' => $past]);
    }

    if ($sub === '/novo') {
        if ($method === 'POST') {
            event_save(null, $base);
        }
        event_form($base, null, event_values([]));
    }

    if (preg_match('#^/(\d+)(/apagar)?$#', $sub, $m) !== 1) {
        return;
    }
    $st = db()->prepare('SELECT * FROM events WHERE id = ?');
    $st->execute([(int) $m[1]]);
    $event = $st->fetch() ?: null;
    if ($event === null) {
        return;
    }

    if (($m[2] ?? '') === '/apagar') {
        if ($method !== 'POST') {
            redirect('/admin/eventos/' . $event['id']);
        }
        db()->prepare('DELETE FROM events WHERE id = ?')->execute([$event['id']]);
        flash('ok', 'Evento «' . $event['title'] . '» apagado.');
        redirect('/admin/eventos');
    }

    if ($method === 'POST') {
        event_save($event, $base);
    }
    event_form($base, $event, event_values($event));
}

/** Valores do formulário a partir de uma linha da base de dados (ou vazios). */
function event_values(array $row): array
{
    $time = static fn (?string $t): string => $t === null ? '' : substr($t, 0, 5);
    return [
        'title' => $row['title'] ?? '',
        'starts_on' => $row['starts_on'] ?? '',
        'start_time' => $time($row['start_time'] ?? null),
        'ends_on' => $row['ends_on'] ?? '',
        'end_time' => $time($row['end_time'] ?? null),
        'location' => $row['location'] ?? '',
        'description' => $row['description'] ?? '',
    ];
}

function event_form(array $base, ?array $event, array $values, array $errors = []): never
{
    admin_render('event-form', $base + [
        'title' => $event ? 'Editar evento' : 'Novo evento',
        'section' => 'eventos',
        'event' => $event,
        'v' => $values,
        'errors' => $errors,
    ], $errors ? 422 : 200);
}

function event_save(?array $event, array $base): never
{
    $v = [
        'title' => post_string('title', 200),
        'starts_on' => post_string('starts_on', 10),
        'start_time' => post_string('start_time', 5),
        'ends_on' => post_string('ends_on', 10),
        'end_time' => post_string('end_time', 5),
        'location' => post_string('location', 200),
        'description' => post_string('description', 5000),
    ];

    $errors = [];
    if ($v['title'] === '') {
        $errors['title'] = 'Escreva o nome do evento.';
    }
    $startsOn = valid_date($v['starts_on']);
    if ($startsOn === null) {
        $errors['starts_on'] = 'Indique a data de início.';
    }
    $endsOn = $v['ends_on'] === '' ? null : valid_date($v['ends_on']);
    if ($v['ends_on'] !== '' && $endsOn === null) {
        $errors['ends_on'] = 'Data de fim inválida.';
    }
    $startTime = $v['start_time'] === '' ? null : valid_time($v['start_time']);
    if ($v['start_time'] !== '' && $startTime === null) {
        $errors['start_time'] = 'Hora inválida.';
    }
    $endTime = $v['end_time'] === '' ? null : valid_time($v['end_time']);
    if ($v['end_time'] !== '' && $endTime === null) {
        $errors['end_time'] = 'Hora inválida.';
    }
    if ($startsOn !== null && $endsOn !== null && $endsOn < $startsOn) {
        $errors['ends_on'] = 'A data de fim não pode ser antes da data de início.';
    }
    if ($startTime !== null && $endTime !== null && ($endsOn === null || $endsOn === $startsOn) && $endTime <= $startTime) {
        $errors['end_time'] = 'A hora de fim tem de ser depois da hora de início.';
    }
    if ($errors) {
        event_form($base, $event, $v, $errors);
    }

    if ($endsOn === $startsOn) {
        $endsOn = null;
    }
    $params = [$v['title'], $v['description'] ?: null, $v['location'] ?: null, $startsOn, $startTime, $endsOn, $endTime];
    if ($event === null) {
        db()->prepare(
            'INSERT INTO events (title, description, location, starts_on, start_time, ends_on, end_time, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([...$params, $base['admin']['id']]);
        flash('ok', 'Evento adicionado.');
    } else {
        db()->prepare(
            'UPDATE events SET title = ?, description = ?, location = ?, starts_on = ?, start_time = ?, ends_on = ?, end_time = ?
             WHERE id = ?'
        )->execute([...$params, $event['id']]);
        flash('ok', 'Alterações guardadas.');
    }
    redirect('/admin/eventos');
}

