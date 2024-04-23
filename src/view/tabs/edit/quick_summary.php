<div class="row mt-2">  <!-- text-start text-left same for diff bs versions -->
  <div class="col ml-2 pl-1 pr-1 bg-secondary text-start text-left small">
    <b>H2O</b>
  </div>
  <div class="col pl-1 pr-1 bg-secondary text-start text-left small">
    kcal
  </div>
  <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left">
    <div class="align-split small"><span>Fat</span> <span>g</span></div>  <!-- align-split ai made class -->
  </div>
  <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left">
    <div class="align-split small"><span>Amino</span> <span>g</span></div>
  </div>
  <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left">
    <div class="align-split small"><span>Salt</span> <span>g</span></div>
  </div>
  <div class="col pl-1 pr-1 border-left bg-secondary text-start text-left small">
    <b>Price</b>
  </div>
  <div class="col mr-2 pl-1 text-end text-right" style="max-height: 20px;">

    <!-- <button class="btn btn-sm btn-light" onclick="foodsCrl.saveDayEntriesBtnClick(event)">
      Save
    </button> -->

    <!-- Try modal (or add in list as static entry -->


    <button class="btn btn-sm btn-light" onclick="foodsCrl.newEntryBtn(event)">
      New
    </button>

  </div>
</div>
<div class="row">
  <div id="h2oSum"      class="col ml-2 pl-1 pr-1 text-start text-left">0</div>
  <div id="caloriesSum" class="col pl-1 pr-1 text-start text-left">0</div>
  <div id="fatSum"      class="col pl-1 pr-1 text-end text-right">0</div>
  <div id="aminoSum"    class="col pl-1 pr-1 text-end text-right">0</div>
  <div id="saltSum"     class="col pl-1 pr-1 text-end text-right">0</div>
  <div id="priceSum"    class="col pl-1 pr-1 text-end text-right">0</div>
  <div class="col mr-2 pl-1 text-end text-right">
    &nbsp;
  </div>
</div>
