<div class="adm-head">
  <h1>Loja</h1>
  <a class="btn btn--red" href="/admin/loja/novo">Novo produto</a>
</div>
<?php if (!$products): ?>
<p class="adm-empty">Ainda não há produtos. A página da loja será construída na próxima fase; os produtos que adicionar aqui ficam prontos para ela.</p>
<?php else: ?>
<div class="adm-scroll">
  <table class="adm-table">
    <thead><tr><th scope="col"><span class="sr-only">Foto</span></th><th scope="col">Produto</th><th scope="col">Preço</th><th scope="col">Stock</th><th scope="col">Estado</th><th scope="col"><span class="sr-only">Ações</span></th></tr></thead>
    <tbody>
      <?php foreach ($products as $p): $onSale = sale_active($p); ?>
      <tr>
        <td class="adm-table__thumb"><?php if ($p['cover']): ?><img class="adm-thumb" src="<?= e(upload_url($p['cover'])) ?>" alt="" loading="lazy"><?php else: ?><span class="adm-thumb"></span><?php endif; ?></td>
        <td>
          <a class="adm-title" href="/admin/loja/<?= (int) $p['id'] ?>"><?= e($p['name']) ?></a>
          <?php if ($p['category']): ?><span class="adm-muted"><?= e($p['category']) ?></span><?php endif; ?>
        </td>
        <td class="adm-nowrap">
          <?php if ($onSale): ?>
          <s class="adm-muted"><?= e(money((int) $p['price_cents'])) ?></s> <strong><?= e(money((int) $p['sale_price_cents'])) ?></strong>
          <span class="adm-muted">Promoção<?= $p['sale_ends_on'] ? ' até ' . e((new DateTimeImmutable($p['sale_ends_on']))->format('d/m')) : '' ?></span>
          <?php else: ?>
          <?= e(money((int) $p['price_cents'])) ?>
          <?php if ($p['sale_price_cents'] !== null): ?><span class="adm-muted">Promoção fora do prazo</span><?php endif; ?>
          <?php endif; ?>
        </td>
        <td class="adm-nowrap">
          <?= (int) $p['stock_total'] ?>
          <?php if ((int) $p['sold_out'] > 0): ?><span class="adm-warn"><?= (int) $p['sold_out'] ?> <?= (int) $p['sold_out'] > 1 ? 'tamanhos esgotados' : 'tamanho esgotado' ?></span><?php endif; ?>
        </td>
        <td><span class="state<?= $p['is_active'] ? ' state--on' : '' ?>"><?= $p['is_active'] ? 'Visível' : 'Oculto' ?></span></td>
        <td class="adm-actions"><a class="adm-link" href="/admin/loja/<?= (int) $p['id'] ?>">Editar</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
