
<ul id="foodList" class="list-group">
  <li class="food-item p-1 list-group-item d-flex"
      style="font-size: 1.5rem;"
  >
    <button class="btn btn-sm flex-fill" onclick="foodsCrl.saveDayEntriesBtnClick(event)">
      Save
    </button>
    <span id="uiMsg"></span>
    <button class="btn btn-sm flex-fill" onclick="foodsCrl.newEntryBtn(event)">
      New
    </button>
  </li>
  <?php foreach( $this->model->foods as $food => $entry): ?>
    <li class   = "food-item p-1 list-group-item"
        style   = "font-size: 1.5rem;"
        onclick = "foodsCrl.foodItemClick(event)"
        data-food       = "<?= $food ?>"
        data-calories   = "<?= $entry['calories'] ?>"
        data-nutritionalvalues = "<?= htmlspecialchars( json_encode( $entry['nutritionalValues'])) ?>"
        data-fattyacids = "<?= htmlspecialchars( json_encode( $entry['fattyAcids'])) ?>"
        data-aminoacids = "<?= htmlspecialchars( json_encode( $entry['aminoAcids'])) ?>"
        data-vitamins   = "<?= htmlspecialchars( json_encode( $entry['vitamins'])) ?>"
        data-minerals   = "<?= htmlspecialchars( json_encode( $entry['minerals'])) ?>"
        data-price      = "<?= $entry['price'] ?>"
    >   <!-- camelCase doesn't work here -->
      <?= $food ?>
    </li>
  <?php endforeach; ?>
</ul>
