
<div class="scrollable-list">
  <div id="">
    <?php foreach( $this->model->nutrients as $groupShort => $group ): ?>
      <?php // TASK: add collapse ?>
      <?php foreach( $group as $short => $data ): ?>
        <div class      = "nutrients-entry mb-2"
            data-group = "<?= $groupShort ?>"
            data-short = "<?= $short ?>"
            data-ideal = "<?= $data['ideal'] ?>"
        >
          <div><?= $data['name'] ?></div>  <!-- TASK: or right align 80 / 100 -->
          <div class="progress w-100" role="progressbar">
            <div class="progress-bar bg-success" style="width: 0%;">
              <span><!-- < ?= $data['name'] ?> --><span class="progress-label">0 / <?= $val['ideal'] ?></span></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>
</div>
