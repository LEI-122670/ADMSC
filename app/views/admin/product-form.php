<?php
$variantRow = static function (string $key, array $row): void { ?>
      <li class="adm-variant">
        <input type="hidden" name="variants[<?= e($key) ?>][stock_was]" value="<?= e($row['stock_was']) ?>">
        <label class="fld">Tamanho
          <input type="text" name="variants[<?= e($key) ?>][label]" value="<?= e($row['label']) ?>" maxlength="40" placeholder="ex.: M ou Único">
        </label>
        <label class="fld">Stock
          <input type="number" name="variants[<?= e($key) ?>][stock]" value="<?= e($row['stock']) ?>" min="0" max="99999" step="1" inputmode="numeric">
        </label>
        <?php if ($row['id'] > 0) { ?>
        <label class="adm-check"><input type="checkbox" name="variants[<?= e($key) ?>][remove]"<?= $row['remove'] ? ' checked' : '' ?>> Remover</label>
        <?php } else { ?>
        <span></span>
        <?php } ?>
      </li>
<?php }; ?>
<div class="adm-head">
  <div>
    <a class="adm-back" href="/admin/loja">Loja</a>
    <h1><?= e($title) ?></h1>
  </div>
</div>
<?php if ($errors): ?><p class="flash flash--error" role="alert">Corrija os campos assinalados.</p><?php endif; ?>
<form class="adm-form" method="post" enctype="multipart/form-data" action="<?= $product ? '/admin/loja/' . (int) $product['id'] : '/admin/loja/novo' ?>">
  <?= csrf_field() ?>
  <section class="adm-sec">
    <label class="fld">Nome
      <input type="text" name="name" value="<?= e($v['name']) ?>" maxlength="160" required<?= field_attrs($errors, 'name') ?>>
      <?= field_error($errors, 'name') ?>
    </label>
    <label class="fld">Categoria <span class="hint">Opcional, ex.: Vestuário, Equipamento</span>
      <input type="text" name="category" value="<?= e($v['category']) ?>" maxlength="60" list="categorias">
    </label>
    <datalist id="categorias"><?php foreach ($categories as $cat): ?><option value="<?= e($cat) ?>"></option><?php endforeach; ?></datalist>
    <label class="fld">Descrição <span class="hint">Opcional</span>
      <textarea name="description"><?= e($v['description']) ?></textarea>
    </label>
    <label class="adm-check"><input type="checkbox" name="is_active"<?= $v['is_active'] ? ' checked' : '' ?>> Visível na loja</label>
  </section>

  <section class="adm-sec">
    <h2>Preço</h2>
    <div class="adm-grid">
      <label class="fld">Preço (€)
        <input type="text" name="price" value="<?= e($v['price']) ?>" inputmode="decimal" placeholder="0,00" required<?= field_attrs($errors, 'price') ?>>
        <?= field_error($errors, 'price') ?>
      </label>
    </div>
    <h3>Promoção <span class="hint">Opcional. Sem datas, fica ativa até a retirar.</span></h3>
    <div class="adm-grid adm-grid--3">
      <label class="fld">Preço promocional (€)
        <input type="text" name="sale_price" value="<?= e($v['sale_price']) ?>" inputmode="decimal" placeholder="0,00"<?= field_attrs($errors, 'sale_price') ?>>
        <?= field_error($errors, 'sale_price') ?>
      </label>
      <label class="fld">Desde
        <input type="date" name="sale_starts_on" value="<?= e($v['sale_starts_on']) ?>"<?= field_attrs($errors, 'sale_starts_on') ?>>
        <?= field_error($errors, 'sale_starts_on') ?>
      </label>
      <label class="fld">Até (inclusive)
        <input type="date" name="sale_ends_on" value="<?= e($v['sale_ends_on']) ?>"<?= field_attrs($errors, 'sale_ends_on') ?>>
        <?= field_error($errors, 'sale_ends_on') ?>
      </label>
    </div>
    <p class="hint">Para terminar uma promoção, apague o preço promocional e as datas.</p>
  </section>

  <section class="adm-sec">
    <h2>Tamanhos e stock</h2>
    <?= field_error($errors, 'variants') ?>
    <ul class="adm-variants" data-variants>
      <?php foreach ($v['variants'] as $key => $row) { $variantRow((string) $key, $row); } ?>
    </ul>
    <template data-variant-template><?php $variantRow('__KEY__', ['id' => 0, 'label' => '', 'stock' => '', 'stock_was' => '0', 'remove' => false]); ?></template>
    <button class="btn btn--line btn--sm" type="button" data-add-variant hidden>Adicionar tamanho</button>
    <noscript><p class="hint">Para adicionar mais tamanhos, guarde e volte a editar.</p></noscript>
  </section>

  <?php require __DIR__ . '/_photos.php'; ?>

  <div class="adm-submit">
    <button class="btn btn--red" type="submit"><?= $product ? 'Guardar alterações' : 'Adicionar produto' ?></button>
    <a class="adm-link" href="/admin/loja">Cancelar</a>
  </div>
</form>
<?php if ($product) { $deleteUrl = '/admin/loja/' . (int) $product['id'] . '/apagar'; $deleteLabel = 'Apagar produto'; require __DIR__ . '/_delete.php'; } ?>
