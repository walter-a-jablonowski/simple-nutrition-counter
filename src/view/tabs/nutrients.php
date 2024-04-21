this is an advanced feature

<div class="scrollable-list">
  <div id="">
    <?php for( $i=0; $i < 4; $i++): ?>
      <div>Substance <?= $i ?></div>  <!-- simple for now -->
      <div class="d-flex align-items-center mb-2">
        <div class="progress w-100" role="progressbar" style="margin-right: 20px;">
          <div id="<?= $i ?>ProgressBar" class="progress-bar bg-success" style="width: 80%;">80%</div>
        </div>
        <span id="<?= $i ?>ProgressLabel">100/500</span>
      </div>
    <?php endfor; ?>
  </div>
</div>
