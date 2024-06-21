<!-- TASK: (advanced) sometimes it isn't the vendor url, no better place for url for now -->

<div class="mb-1 fw-bold d-flex justify-content-between align-items-center">
  <span>
    <?= htmlspecialchars($foodName) ?>
    <?php if( ! empty($data['vendor']) || ! empty($data['url'])): ?>
      <span class="fw-normal small">
        <?php if( ! empty($data['vendor']) && ! empty($data['url'])): ?>
          (<a href="<?= $data['url'] ?>" target="_blank" class="text-decoration-none"><?= $data['vendor'] ?></a>)
        <?php elseif( empty($data['vendor']) && ! empty($data['url'])): ?>
          (<a href="<?= $data['url'] ?>" target="_blank" class="text-decoration-none">url</a>)
        <?php elseif( ! empty($data['vendor']) && empty($data['url'])): ?>
          (<?= $data['vendor'] ?>)
        <?php endif; ?>
      </span>
    <?php endif; ?>
  </span>
  <?php if( $this->devMode ): ?>
    <i class="bi bi-pencil-square text-black"></i>  <!-- TASK: (advanced) or make all editable on typ -->
  <?php endif; ?>
</div>
