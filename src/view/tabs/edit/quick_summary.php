<!-- 3 col for tab is col-6 col-md-4 col-xxl-2 -->
<div id="#quickSummary" class="row px-2">
  <div class="col-6 col-md-2 col-xxl-2 mt-2 px-1">
    <div style="background-color: #eb6864;">
      <div class="ps-1 fw-bold">kcal</div>
      <div id="caloriesSum" class="ps-1">0</div>
    </div>
  </div>
  <div class="col-6 col-md-2 col-xxl-2 mt-2 px-1">
    <div class="ps-1 bg-secondary text-white">Fat / Amino (2:1)</div>
    <div class="value ps-1"><span id="fatSum">0</span> / <span id="aminoSum">0</span> g</div>
  </div>
  <!-- (TASK) space saving quick summary design -->
<!-- 
  <div class="col-6 col-md-2 col-xxl-2 val">
    <div class="label">Carbs</div>
    <div class="value"><span id="carbsSum">0</span> g</div>
  </div>
-->
  <div class="col-6 col-md-2 col-xxl-2 mt-2 px-1">
    <div class="px-1 bg-secondary text-white">Carbs &le; 50g</div>
    <div class="value ps-1"><span id="carbsSum">0</span> g</div>
  </div>
  <div class="col-6 col-md-2 col-xxl-2 mt-2 px-1">
    <div class="px-1 bg-secondary text-white">Fibre &ge; 2 cups</div>
    <div class="value ps-1"><!-- <span id="fibreSum">0</span> --> g</div>
  </div>
  <div class="col-6 col-md-2 col-xxl-2 mt-2 px-1">
    <div class="px-1 bg-secondary text-white">Salt 4-6g</div>
    <div class="value ps-1"><span id="saltSum">0</span> g</div>
  </div>
  <div class="col-6 col-md-2 col-xxl-2 mt-2 px-1">
    <div class = "d-flex justify-content-center align-items-center"
         style = "background-color: #79fe28; font-size: 1.8rem;">
      <?= settings::get('currencySymbol') ?> <span id="priceSum">0</span>
    </div>
  </div>
</div>
