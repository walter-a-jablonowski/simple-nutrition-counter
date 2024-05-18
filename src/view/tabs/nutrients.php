this is an advanced feature

<div class="scrollable-list">
  <div id="">
<!--
    < ?php for( $i=0; $i < 4; $i++): ?>
      <div>Substance < ?= $i ?> <span id="< ?= $i ?>ProgressLabel">100/500</span></div>
      <div class="d-flex align-items-center mb-2">
        <div class="progress w-100" role="progressbar" style="margin-right: 20px;">
          <div id="< ?= $i ?>ProgressBar" class="progress-bar bg-success"  style="width: 80%;">80%</div>
        </div>
      </div>
    < ?php endfor; ?>
-->
    <?php foreach( $this->summary as $name => $val ): ?>
      <div class="mb-2">
        <div><?= $name ?></div>
        <div class="progress w-100" role="progressbar">
          <div id="<?= $name ?>ProgressBar" class="progress-bar bg-success" style="width: 80%;">
            <!-- < ?= $name ?> -->  <!-- breaks in 2 lines even with d-flex justify-content-between text-nowrap -->
            <span><?= $name ?> <span id="<?= $name ?>ProgressLabel">100 / <?= $val['ideal'] ?></span></span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
</div>
