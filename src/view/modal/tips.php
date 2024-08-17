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
              <b>Current food list:</b> <?= $a['name'] ?> (<?= User::byId( $a['userId'] )->get('name') ?>)
            </h6>

            <table>
              <tr>
                <td valign="top" class="text-secondary small"><b>availability:&nbsp;</b></td>
                <td valign="top" class="text-secondary small"><?= $a['region'] ?></td>
              </tr>
              <tr>
                <td valign="top" class="text-secondary small"><b>vendors:</b></td>
                <td valign="top" class="text-secondary small"><?= trim($a['vendors']) ?></td>
              </tr>
            </table>

            <p class="mt-1 small">
              <a href="#disclaimer">See disclaimer</a>
            </p>

            <h6 class="fw-bold">Concept</h6>

            <p><?= $a['framework'] ?></p>
            <?= $a['conceptMisc'] ?>

            <h6 class="mt-3 fw-bold">Primary daily goals</h6>

            <p class="text-secondary">  <!-- because we can't handle too much daily: reach primary goals with a menu that has most nutrients -->
              A few primary daily goals for the goals bar on the edit tab. All nutrients:
              see nutrients tab in the evening.
            </p>

            <?= $a['goals'] ?>

            <h6 class="fw-bold">Sample menu</h6>

            <?= $a['sampleMenu'] ?>

            <h6 class="mt-3 fw-bold">Reference (inspired by)</h6>
              
            <table class="small">
              <?php foreach( $a['sources'] as $idx => $entry ): ?>
                <tr id="ref<?= $idx ?>">
                  <td class="pe-1">(<?= $idx ?>)</td>
                  <td>
                    <?php if( filter_var($entry['source'], FILTER_VALIDATE_URL)): ?>
                      <a href="<?= $entry['source'] ?>" target="_blank"><?= $entry['title'] ?></a>
                    <?php else: ?>
                      <?= $entry['title'] ?>
                    <?php endif; ?>
                    <?php if( ! empty($entry['sub'])): ?>
                      &gt; <?= $entry['sub'] ?>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </table>

            <p id="disclaimer" class="mt-3 small">
              <b>Disclaimer:</b> <?php require('misc/disclaimer.php') ?>
            </p>

          </div>
          <div id="tipsNutritionPane" class="tab-pane fade" role="tabpanel">

            <?php
            
              $tipsFile = 'data/bundles/Default_' . User::current('id') . '/tips.html';

              if( file_exists($tipsFile) && trim( file_get_contents($tipsFile)))
                require($tipsFile);
              else
                require('misc/tips_nutrition.php');
            
            ?>

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
