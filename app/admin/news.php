<?php
declare(strict_types=1);

/* Painel: notícias. A data de publicação é o momento em que se carrega em «Publicar». */

function news_routes(string $sub, string $method, array $base): void
{
    if ($sub === '/') {
        $news = db()->query(
            'SELECT n.id, n.title, n.published_at, n.updated_at,
                    (SELECT COUNT(*) FROM news_images i WHERE i.news_id = n.id) AS photos,
                    (SELECT path FROM news_images i WHERE i.news_id = n.id ORDER BY sort_order, id LIMIT 1) AS cover
             FROM news n
             ORDER BY n.published_at IS NULL DESC, n.published_at DESC, n.id DESC'
        )->fetchAll();
        admin_render('news-list', $base + ['title' => 'Notícias', 'section' => 'noticias', 'news' => $news]);
    }

    if ($sub === '/nova') {
        if ($method === 'POST') {
            news_save(null, $base);
        }
        news_form($base, null, ['title' => '', 'body' => '']);
    }

    if (preg_match('#^/(\d+)(/apagar)?$#', $sub, $m) !== 1) {
        return;
    }
    $st = db()->prepare('SELECT * FROM news WHERE id = ?');
    $st->execute([(int) $m[1]]);
    $news = $st->fetch() ?: null;
    if ($news === null) {
        return;
    }

    if (($m[2] ?? '') === '/apagar') {
        if ($method !== 'POST') {
            redirect('/admin/noticias/' . $news['id']);
        }
        $paths = array_column(images_of('news', (int) $news['id']), 'path');
        db()->prepare('DELETE FROM news WHERE id = ?')->execute([$news['id']]);
        array_map('delete_upload', $paths);
        flash('ok', 'Notícia «' . $news['title'] . '» apagada.');
        redirect('/admin/noticias');
    }

    if ($method === 'POST') {
        news_save($news, $base);
    }
    news_form($base, $news, ['title' => $news['title'], 'body' => $news['body']]);
}

function news_form(array $base, ?array $news, array $values, array $errors = []): never
{
    admin_render('news-form', $base + [
        'title' => $news ? 'Editar notícia' : 'Nova notícia',
        'section' => 'noticias',
        'news' => $news,
        'v' => $values,
        'errors' => $errors,
        'images' => $news ? images_of('news', (int) $news['id']) : [],
    ], $errors ? 422 : 200);
}

function news_save(?array $news, array $base): never
{
    $v = ['title' => post_string('title', 200), 'body' => post_string('body', 60000)];
    $action = in_array($_POST['action'] ?? '', ['publish', 'draft'], true) ? $_POST['action'] : 'publish';
    $files = uploaded_files('photos');
    $remove = $news ? posted_ids('remove_images') : [];
    $kept = $news ? count(images_of('news', (int) $news['id'])) - count($remove) : 0;

    $errors = [];
    if ($v['title'] === '') {
        $errors['title'] = 'Escreva o título.';
    }
    if ($v['body'] === '') {
        $errors['body'] = 'Escreva a descrição.';
    }
    if (($problem = photos_problem($files, max(0, $kept))) !== null) {
        $errors['photos'] = $problem;
    }
    if ($errors) {
        if ($files) {
            $errors['photos'] ??= 'As fotos escolhidas não foram guardadas. Volte a escolhê-las.';
        }
        news_form($base, $news, $v, $errors);
    }

    $stored = [];
    $removedPaths = [];
    $pdo = db();
    try {
        $pdo->beginTransaction();
        if ($news === null) {
            $pdo->prepare(
                'INSERT INTO news (slug, title, body, published_at, created_by)
                 VALUES (?, ?, ?, IF(? = \'publish\', NOW(), NULL), ?)'
            )->execute([unique_slug('news', $v['title']), $v['title'], $v['body'], $action, $base['admin']['id']]);
            $id = (int) $pdo->lastInsertId();
        } else {
            $id = (int) $news['id'];
            // Publicar mantém a data se já estava publicada; passar a rascunho retira-a.
            $pdo->prepare(
                'UPDATE news SET title = ?, body = ?,
                    published_at = IF(? = \'publish\', COALESCE(published_at, NOW()), NULL)
                 WHERE id = ?'
            )->execute([$v['title'], $v['body'], $action, $id]);
            $removedPaths = detach_photos('news', $id, $remove);
        }
        attach_photos('news', $id, $files, $stored);
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        array_map('delete_upload', $stored);
        throw $ex;
    }
    array_map('delete_upload', $removedPaths);

    $wasPublished = $news !== null && $news['published_at'] !== null;
    flash('ok', match (true) {
        $action === 'draft' => 'Notícia guardada como rascunho (não aparece no site).',
        $wasPublished => 'Alterações guardadas.',
        default => 'Notícia publicada.',
    });
    redirect('/admin/noticias');
}
