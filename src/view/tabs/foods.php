<div class="row">
  <div class="col">

    <textarea id="foods" class="form-control" wrap="off" style="font-family: monospace; font-size: 15px;" rows="18"
    ><?= $this->model->foodsTxt ?></textarea>

    <button onclick="foodsCrl.saveFoodsBtnClick(event)" class="btn btn-sm btn-primary mt-2">Save</button>
    <span id="uiMsg"></span>

  </div>
</div>
