<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

?><div id="tipsModal" class="modal info-modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="height: 100vh;">
    <div class="modal-content" style="height: 90%;">
<!--
      <div class="modal-header">
        <h6 class="modal-title">Tips</h6>
        <button data-bs-dismiss="modal" class="btn-close" type="button"></button>
      </div>
-->
      <div class="modal-body">

        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item" role="presentation">
            <a href="#tipsMenuPane" class="nav-link active py-1 px-2 small" data-bs-toggle="tab" role="tab">
              Menu
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a href="#tipsNutritionPane" class="nav-link py-1 px-2 small" data-bs-toggle="tab" role="tab">
              Nutrition tips
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a href="#tipsAppPane" class="nav-link py-1 px-2 small" data-bs-toggle="tab" role="tab">
              App tips
            </a>
          </li>
          <!-- TASK -->
<!-- 
          <li class="nav-item" role="presentation">
            <a href="#tipsMissionPane" class="nav-link py-1 px-2 small" data-bs-toggle="tab" role="tab">
              Mission
            </a>
          </li>
-->
          <li class="nav-item ms-auto" role="presentation">
            <button data-bs-dismiss="modal" class="btn-close" type="button"></button>
            <!-- TASK: Btn design alternatives -->
<!-- 
            <button type="button" class="border-0" data-bs-dismiss="modal">
              <i class="bi bi-x"></i>
            </button>
 -->
            <!-- <i class="bi bi-x-circle"></i> -->
<!-- 
            <button type="button" class="btn btn-sm btn-outline-secondary p-1 py-0" style="    font-size: .8em;" data-bs-dismiss="modal">
              Close
            </button>
          </li>
-->
        </ul>

        <div class="tab-content mt-3">
          <div id="tipsMenuPane" class="tab-pane fade show active" role="tabpanel">

            <?php

              // $a = Yaml::parse( file_get_contents('data/bundles/Default_JaneDoe@example.com-24080101000000/-this.yml'));
              $a = Yaml::parse( file_get_contents('data/bundles/Default_' . User::current('id') . '/-this.yml'));

            ?>

            <h6>
              <b>Current food list:</b> <?= $a['name'] ?> (<?= $a['userId'] ?>)
            </h6>

            <table class="mt-2" style="border: 0;">
              <tr>
                <td valign="top" class="text-secondary small"><b>availability:&nbsp;</b></td>
                <td valign="top" class="text-secondary small"><?= $a['region'] ?></td>
              </tr>
              <tr>
                <td valign="top" class="text-secondary small"><b>vendors:</b></td>
                <td valign="top" class="text-secondary small"><?= trim($a['vendors']) ?></td>
              </tr>
            </table>

            <p class="mt-2">
              <?= $a['spec'] ?>
            </p>

            <p class="mt-2">
              Reference

              <ul class="no-indent">
                <?php foreach( $a['sources'] as $idx => $entry ): ?>
                  <li>
                    <?php if( filter_var( $entry['source'], FILTER_VALIDATE_URL)): ?>
                      <a href="<?= $entry['source'] ?>"><?= $entry['title'] ?></a>
                    <?php else: ?>
                      <?= $entry['title'] ?>
                    <?php endif; ?>
                    <?php if (!empty($entry['sub'])): ?>
                      &gt; <?= $entry['sub'] ?>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </p>

          </div>
          <div id="tipsNutritionPane" class="tab-pane fade" role="tabpanel">

            <?php require('misc/tips_nutrition.php') ?>

          </div>
          <div id="tipsAppPane" class="tab-pane fade" role="tabpanel">

            <?php require('misc/tips_app.php') ?>

          </div>
          <div id="tipsMissionPane" class="tab-pane fade" role="tabpanel">

            <?php require('misc/tips_mission.php') ?>

          </div>
        </div>

      </div>
<!--
      <div class="modal-footer">
        <button data-bs-dismiss="modal" class="btn btn-sm btn-secondary" type="button">
          Close
        </button>
      </div>
-->
    </div>
  </div>
</div>
