<?php

  use Symfony\Component\Yaml\Yaml;
  use Symfony\Component\Yaml\Exception\ParseException;

?>

<?php if( User::current()->has('myStrategy.headline')): ?>

  <div class="row mt-2">
    <div class="col-12">
    
      <div class="px-1" style="border: 1px solid #bbb; background-color: #ffff88;"
           data-bs-toggle = "modal"
           data-bs-target = "#infoModal"
           data-title     = "&#x3C;span class=&#x22;fs-4&#x22;&#x3E;<?= User::current()->get('myStrategy.headline') ?>&#x3C;/span&#x3E;"
           data-source    = "#myStrategyData"
      >
    
        <?= User::current()->get('myStrategy.headline') ?>
        
        <?php if( User::current()->has('myStrategy.content')): ?>
        
          <button type="button" class="border-0 p-1 bg-transparent">
            <i class="bi bi-info-circle icon-circle"></i>
          </button>
          <div id="myStrategyData" class="d-none">
            <span class="fs-5"><?= User::current()->get('myStrategy.content') ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

<?php endif; ?>

<?php

  $a = Yaml::parse( file_get_contents('data/bundles/Default_' . User::current('id') . '/-this.yml'));

?>

<!-- 3 col for tab is col-6 col-md-4 col-xxl-2 -->
<div id="quickSummary" class="row px-2">
  <div class="col-6 col-md-2 col-xxl-2 mt-1 px-1">
    <div style="background-color: #eb6864;" data-bs-toggle="modal" data-bs-target="#tipsModal">
      <div class="ps-1 fw-bold text-nowrap overflow-hidden">kcal <?= $a['primaryGoals']['calories']['label'] ?></div>
      <div class="ps-1">
        <span id="caloriesSum">0</span>
        in
        <span id="timeSum">00:00</span>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-2 col-xxl-2 mt-1 px-1" data-bs-toggle="modal" data-bs-target="#tipsModal">
    <div class="ps-1 bg-secondary text-white text-nowrap overflow-hidden">Fat / Amino <?= $a['primaryGoals']['fat:amino']['label'] ?></div>
    <div class="value ps-1 text-nowrap overflow-hidden">
      <span id="fatSum">0</span> / <span id="aminoSum">0</span> g
    </div>
  </div>
  <!-- (TASK) space saving quick summary design -->
<!-- 
  <div class="col-6 col-md-2 col-xxl-2 val">
    <div class="label">Carbs</div>
    <div class="value"><span id="carbsSum">0</span> g</div>
  </div>
-->
  <div class="col-6 col-md-2 col-xxl-2 mt-1 px-1" data-bs-toggle="modal" data-bs-target="#tipsModal">
    <div class="px-1 bg-secondary text-white text-nowrap overflow-hidden">Carbs <?= $a['primaryGoals']['carbs']['label'] ?> (sugar)</div>
    <div class="value ps-1">
      <span id="carbsSum">0</span> g (<span id="sugarSum">0</span>)
    </div>
  </div>
  <div class="col-6 col-md-2 col-xxl-2 mt-1 px-1" data-bs-toggle="modal" data-bs-target="#tipsModal">
    <div class="px-1 bg-secondary text-white text-nowrap overflow-hidden">Fibre <?= $a['primaryGoals']['fibre']['label'] ?></div>
    <div class="value ps-1"><span id="fibreSum">0</span> g</div>
  </div>
  <div class="col-6 col-md-2 col-xxl-2 mt-1 px-1" data-bs-toggle="modal" data-bs-target="#tipsModal">
    <div class="px-1 bg-secondary text-white text-nowrap overflow-hidden">Salt <?= $a['primaryGoals']['salt']['label'] ?></div>
    <div class="value ps-1"><span id="saltSum">0</span> g</div>
  </div>
  <div class="col-6 col-md-2 col-xxl-2 mt-1 px-1">
    <div class = "d-flex justify-content-center align-items-center"
         style = "background-color: #79fe28; font-size: 1.8rem;">
      <?= settings::get('currencySymbol') ?> <span id="priceSum">0</span>
    </div>
  </div>
</div>
