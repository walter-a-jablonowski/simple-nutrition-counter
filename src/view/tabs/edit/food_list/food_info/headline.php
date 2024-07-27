<?php

extract($args);

$data = $this->model->get("foods.$foodName");

?>

<!-- TASK: (advanced) sometimes it isn't the vendor url, no better place for url for now -->

<div class="mb-1 fw-bold">
  <?= htmlspecialchars($foodName) ?>
  <?php if( ! empty($data['vendor']) || ! empty($data['url'])): ?>
    <span class="fw-normal small">
      <?php if( ! empty($data['vendor']) && ! empty($data['url'])): ?>
        (<a href="<?= self::iif( is_string($data['url']), $data['url'], $data['url'][0]) ?>" target="_blank" class="text-decoration-none">
          <?= $data['vendor'] ?>
        </a>)
      <?php elseif( empty($data['vendor']) && ! empty($data['url'])): ?>
        (<a href="<?= self::iif( is_string($data['url']), $data['url'], $data['url'][0]) ?>" target="_blank" class="text-decoration-none">
          url
        </a>)
      <?php elseif( ! empty($data['vendor']) && empty($data['url'])): ?>
        (<?= $data['vendor'] ?>)
      <?php endif; ?>
    </span>
  <?php endif; ?>
  <?php if( config::get('devMode') ): ?>
    &nbsp;&nbsp;&nbsp;<i class="bi bi-pencil-square text-black"></i>  <!-- TASK: (advanced) or make all editable on typ -->
  <?php endif; ?>
</div>

<!-- Badges -->

<?php if( ! empty($data['acceptable'])): ?>
  <span class="badge bg-<?= self::iif( $data['acceptable'] == 'less', 'danger', 'warning') ?>">
    <?= self::iif( $data['acceptable'] == 'less', 'less good', 'occasionally') ?>
  </span>
<?php endif; ?>
<span class="badge bg-<?= self::iif( ! empty($data['certificates']['bio']), 'success', 'secondary') ?>">
  <?= self::iif( ! empty($data['certificates']['bio']), 'bio', '<s>bio</s>') ?>  <!-- TASK: non working -->
</span>
<span class="badge bg-<?= self::iif( ! empty($data['certificates']['vegan']), 'success', 'secondary') ?>">
  <?= self::iif( ! empty($data['certificates']['vegan']), 'vegan', '<s>vegan</s>') ?>
</span>
<?php if( ! empty($data['certificates']['oekotest'])): ?>
  <span class="badge bg-info">oekotest: <?= $data['certificates']['oekotest'] ?></span>
<?php endif; ?>
<?php if( ! empty($data['certificates']['NutriScore'])): ?>
  <span class="badge bg-info">NutriScore: <?= $data['certificates']['NutriScore'] ?></span>
<?php endif; ?>

<!-- High fat ... -->
<!-- TASK: add -->

<?php if( $data['nutritionalValues']['fat'] > config::get('highIntake.fat')): ?>
  <span class="badge bg-danger">fatty</span>
<?php endif; ?>

<!-- Gluten and similar from ingredients list -->
<!-- TASK: maybe also add a flag gluten: true in food data where you can add it manually -->
<!-- TASK: (advanced) add high calcium ... -->

<?php if( ! empty($data['ingredients'])): ?>
  <?php foreach( config::get('substances.gluten') as $s ): ?>
    <?php if( stripos( $data['ingredients'], $s) !== false): ?>
      <span class="badge bg-danger">gluten</span>
    <?php break; endif; ?>
  <?php endforeach; ?>
<?php endif; ?>
<?php if( ! empty($data['ingredients'])): ?>
  <?php foreach( config::get('substances.lactose') as $s ): ?>
    <?php if( stripos( $data['ingredients'], $s) !== false): ?>
      <span class="badge bg-danger">lactose</span>
    <?php break; endif; ?>
  <?php endforeach; ?>
<?php endif; ?>
