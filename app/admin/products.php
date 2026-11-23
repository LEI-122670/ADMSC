<?php
declare(strict_types=1);

/* Painel: produtos da loja, com tamanhos/stock, fotos e promoções. Preços em cêntimos. */

const STOCK_LOW = 3;

function products_routes(string $sub, string $method, array $base): void
{
    if ($sub === '/') {
        $products = db()->query(
            'SELECT p.*,
                    (SELECT COALESCE(SUM(stock), 0) FROM product_variants v WHERE v.product_id = p.id) AS stock_total,
                    (SELECT COUNT(*) FROM product_variants v WHERE v.product_id = p.id AND v.stock = 0) AS sold_out,
                    (SELECT path FROM product_images i WHERE i.product_id = p.id ORDER BY sort_order, id LIMIT 1) AS cover
             FROM products p
             ORDER BY p.is_active DESC, p.name'
        )->fetchAll();
        admin_render('products-list', $base + ['title' => 'Loja', 'section' => 'loja', 'products' => $products]);
    }

    if ($sub === '/novo') {
        if ($method === 'POST') {
            product_save(null, $base);
        }
        product_form($base, null, product_values([], []));
    }

    if (preg_match('#^/(\d+)(/apagar)?$#', $sub, $m) !== 1) {
        return;
    }
    $st = db()->prepare('SELECT * FROM products WHERE id = ?');
    $st->execute([(int) $m[1]]);
    $product = $st->fetch() ?: null;
    if ($product === null) {
        return;
    }

    if (($m[2] ?? '') === '/apagar') {
        if ($method !== 'POST') {
            redirect('/admin/loja/' . $product['id']);
        }
        // Quando houver encomendas, um produto já vendido deve ser ocultado em vez de apagado.
        $paths = array_column(images_of('product', (int) $product['id']), 'path');
        db()->prepare('DELETE FROM products WHERE id = ?')->execute([$product['id']]);
        array_map('delete_upload', $paths);
        flash('ok', 'Produto «' . $product['name'] . '» apagado.');
        redirect('/admin/loja');
    }

    if ($method === 'POST') {
        product_save($product, $base);
    }
    product_form($base, $product, product_values($product, product_variants((int) $product['id'])));
}

function product_variants(int $productId): array
{
    $st = db()->prepare('SELECT id, label, stock FROM product_variants WHERE product_id = ? ORDER BY sort_order, id');
    $st->execute([$productId]);
    return $st->fetchAll();
}

/** Valores do formulário a partir da base de dados. */
function product_values(array $p, array $variants): array
{
    $rows = [];
    foreach ($variants as $variant) {
        $rows['e' . $variant['id']] = ['id' => (int) $variant['id'], 'label' => $variant['label'], 'stock' => (string) $variant['stock'], 'stock_was' => (string) $variant['stock'], 'remove' => false];
    }
    if ($rows === []) {
        $rows['n0'] = ['id' => 0, 'label' => '', 'stock' => '', 'stock_was' => '0', 'remove' => false];
    }
    return [
        'name' => $p['name'] ?? '',
        'category' => $p['category'] ?? '',
        'description' => $p['description'] ?? '',
        'price' => money_input(isset($p['price_cents']) ? (int) $p['price_cents'] : null),
        'sale_price' => money_input(isset($p['sale_price_cents']) ? (int) $p['sale_price_cents'] : null),
        'sale_starts_on' => $p['sale_starts_on'] ?? '',
        'sale_ends_on' => $p['sale_ends_on'] ?? '',
        'is_active' => (bool) ($p['is_active'] ?? false),
        'variants' => $rows,
    ];
}

function product_form(array $base, ?array $product, array $values, array $errors = []): never
{
    admin_render('product-form', $base + [
        'title' => $product ? 'Editar produto' : 'Novo produto',
        'section' => 'loja',
        'product' => $product,
        'v' => $values,
        'errors' => $errors,
        'images' => $product ? images_of('product', (int) $product['id']) : [],
        'categories' => db()->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category <> '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN),
    ], $errors ? 422 : 200);
}

function product_save(?array $product, array $base): never
{
    $v = [
        'name' => post_string('name', 160),
        'category' => post_string('category', 60),
        'description' => post_string('description', 5000),
        'price' => post_string('price', 20),
        'sale_price' => post_string('sale_price', 20),
        'sale_starts_on' => post_string('sale_starts_on', 10),
        'sale_ends_on' => post_string('sale_ends_on', 10),
        'is_active' => isset($_POST['is_active']),
        'variants' => [],
    ];
    $errors = [];

    if ($v['name'] === '') {
        $errors['name'] = 'Escreva o nome do produto.';
    }
    $price = parse_money($v['price']);
    if ($price === null || $price === 0) {
        $errors['price'] = 'Indique o preço (ex.: 25,00).';
    }

    $salePrice = null;
    $saleStarts = null;
    $saleEnds = null;
    if ($v['sale_price'] !== '') {
        $salePrice = parse_money($v['sale_price']);
        if ($salePrice === null || $salePrice === 0) {
            $errors['sale_price'] = 'Preço promocional inválido.';
        } elseif ($price !== null && $salePrice >= $price) {
            $errors['sale_price'] = 'O preço promocional tem de ser mais baixo do que o preço normal.';
        }
    }
    if ($v['sale_starts_on'] !== '' && ($saleStarts = valid_date($v['sale_starts_on'])) === null) {
        $errors['sale_starts_on'] = 'Data inválida.';
    }
    if ($v['sale_ends_on'] !== '' && ($saleEnds = valid_date($v['sale_ends_on'])) === null) {
        $errors['sale_ends_on'] = 'Data inválida.';
    }
    if ($saleStarts !== null && $saleEnds !== null && $saleEnds < $saleStarts) {
        $errors['sale_ends_on'] = 'A promoção não pode acabar antes de começar.';
    }
    if ($salePrice === null && ($saleStarts !== null || $saleEnds !== null)) {
        $errors['sale_price'] = 'Indique o preço promocional (ou apague as datas).';
    }

    // Tamanhos: linhas existentes ("e<id>") e novas ("n<n>").
    $existing = $product ? array_column(product_variants((int) $product['id']), null, 'id') : [];
    $seen = [];
    $posted = is_array($_POST['variants'] ?? null) ? $_POST['variants'] : [];
    foreach ($posted as $key => $row) {
        if (!is_array($row) || preg_match('/^(e|n)(\d+)$/', (string) $key, $km) !== 1) {
            continue;
        }
        $id = $km[1] === 'e' ? (int) $km[2] : 0;
        if ($id > 0 && !isset($existing[$id])) {
            continue;
        }
        $label = mb_substr(trim((string) ($row['label'] ?? '')), 0, 40);
        $stock = trim((string) ($row['stock'] ?? ''));
        $remove = isset($row['remove']);
        if ($id === 0 && $label === '' && $stock === '') {
            continue;                                              // linha nova vazia
        }
        $v['variants'][$key] = ['id' => $id, 'label' => $label, 'stock' => $stock, 'stock_was' => (string) ($row['stock_was'] ?? '0'), 'remove' => $remove];
        if ($remove) {
            continue;
        }
        if ($label === '') {
            $errors['variants'] = 'Cada tamanho precisa de um nome (ex.: M ou Único).';
        } elseif (isset($seen[mb_strtolower($label)])) {
            $errors['variants'] = "O tamanho «{$label}» está repetido.";
        } elseif (preg_match('/^\d{1,5}$/', $stock) !== 1) {
            $errors['variants'] = "Indique o stock de «{$label}» (número inteiro, 0 ou mais).";
        }
        $seen[mb_strtolower($label)] = true;
    }
    if ($seen === [] && !isset($errors['variants'])) {
        $errors['variants'] = 'Adicione pelo menos um tamanho (se não tiver tamanhos, use «Único»).';
    }

    $files = uploaded_files('photos');
    $remove = $product ? posted_ids('remove_images') : [];
    $kept = $product ? count(images_of('product', (int) $product['id'])) - count($remove) : 0;
    if (($problem = photos_problem($files, max(0, $kept))) !== null) {
        $errors['photos'] = $problem;
    }

    if ($errors) {
        if ($files) {
            $errors['photos'] ??= 'As fotos escolhidas não foram guardadas. Volte a escolhê-las.';
        }
        if ($v['variants'] === []) {
            $v['variants'] = product_values([], [])['variants'];
        }
        product_form($base, $product, $v, $errors);
    }

    $stored = [];
    $removedPaths = [];
    $pdo = db();
    try {
        $pdo->beginTransaction();
        $params = [$v['name'], $v['description'] ?: null, $v['category'] ?: null, $price, $salePrice, $saleStarts, $saleEnds, $v['is_active'] ? 1 : 0];
        if ($product === null) {
            $pdo->prepare(
                'INSERT INTO products (slug, name, description, category, price_cents, sale_price_cents, sale_starts_on, sale_ends_on, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([unique_slug('products', $v['name']), ...$params]);
            $id = (int) $pdo->lastInsertId();
        } else {
            $id = (int) $product['id'];
            $pdo->prepare(
                'UPDATE products SET name = ?, description = ?, category = ?, price_cents = ?, sale_price_cents = ?,
                    sale_starts_on = ?, sale_ends_on = ?, is_active = ?
                 WHERE id = ?'
            )->execute([...$params, $id]);
            $removedPaths = detach_photos('product', $id, $remove);
        }
        save_variants($id, $v['variants']);
        attach_photos('product', $id, $files, $stored);
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        array_map('delete_upload', $stored);
        throw $ex;
    }
    array_map('delete_upload', $removedPaths);

    flash('ok', $product === null ? 'Produto adicionado.' : 'Alterações guardadas.');
    redirect('/admin/loja');
}

/**
 * Grava os tamanhos. O stock de tamanhos existentes é ajustado pela diferença
 * em relação ao valor que estava no formulário, para não apagar vendas feitas entretanto.
 */
function save_variants(int $productId, array $rows): void
{
    $pdo = db();
    $delete = $pdo->prepare('DELETE FROM product_variants WHERE id = ? AND product_id = ?');
    // Nomes temporários evitam conflitos ao trocar nomes entre tamanhos.
    $rename = $pdo->prepare("UPDATE product_variants SET label = CONCAT('~', id) WHERE id = ? AND product_id = ?");
    foreach ($rows as $row) {
        if ($row['id'] > 0) {
            ($row['remove'] ? $delete : $rename)->execute([$row['id'], $productId]);
        }
    }

    $update = $pdo->prepare(
        'UPDATE product_variants SET label = ?, sort_order = ?, stock = GREATEST(0, CAST(stock AS SIGNED) + ?)
         WHERE id = ? AND product_id = ?'
    );
    $insert = $pdo->prepare('INSERT INTO product_variants (product_id, label, stock, sort_order) VALUES (?, ?, ?, ?)');
    $order = 0;
    foreach ($rows as $row) {
        if ($row['remove']) {
            continue;
        }
        if ($row['id'] > 0) {
            $update->execute([$row['label'], $order++, (int) $row['stock'] - (int) $row['stock_was'], $row['id'], $productId]);
        } else {
            $insert->execute([$productId, $row['label'], (int) $row['stock'], $order++]);
        }
    }
}
