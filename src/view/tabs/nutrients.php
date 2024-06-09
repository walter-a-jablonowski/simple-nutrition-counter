
<div class="scrollable-list">

  <?php foreach( $this->model->nutrients as $groupShort => $group ): ?>

    <ul class="list-group">  <!-- TASK: alternative grid ins of list group -->

      <li class="list-group-item d-flex justify-content-between align-items-center">
        <a data-bs-toggle="collapse" href="#<?= $groupShort ?>Collapse" role="button">
          <?= $groupShort ?>  <!-- TASK: use collapse on whole elem ? -->
        </a>
        <span>v</span>        <!-- TASK -->
      </li>
      <li id="<?= $groupShort ?>Collapse" class="list-group-item collapse show">

        <?php foreach( $group as $short => $data ): ?>

          <div class     = "nutrients-entry mb-2"
              data-group = "<?= $groupShort ?>"
              data-short = "<?= $short ?>"
              data-ideal = "<?= $data['ideal'] ?>"
          >
            <div><?= $data['name'] ?></div>  <!-- TASK: or right align 80 / 100 -->
            <div class="progress w-100" role="progressbar">
              <div class="progress-bar bg-success" style="width: 0%;">
                <span><!-- < ?= $data['name'] ?> --><span class="progress-label">0 / <?= $data['ideal'] ?></span></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

      </li>
    </ul>
  <?php endforeach; ?>
</div>
