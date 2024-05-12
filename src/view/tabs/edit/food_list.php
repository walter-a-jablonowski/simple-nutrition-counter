
<ul id="foodList" class="list-group">
  <li class="food-item p-1 list-group-item d-flex"
      style="font-size: 1.5rem;"
  >
    <button class="btn btn-sm flex-fill" onclick="foodsCrl.saveDayEntriesBtnClick(event)">
      Save
    </button>
    <span id="uiMsg"></span>
    <button class="btn btn-sm flex-fill" onclick="foodsCrl.newEntryBtn(event)">
      Enter manually ...
    </button>
  </li>
  <?php foreach( $this->model->foods as $food => $entry): ?>
    <li class   = "food-item p-1 list-group-item"
        style   = "font-size: 1.5rem;"
        onclick = "foodsCrl.foodItemClick(event)"
        data-food       = "<?= $food ?>"
        data-calories   = "<?= $entry['calories'] ?>"
        data-nutritionalvalues = "<?= htmlspecialchars( json_encode( $entry['nutriVal'], JSON_FORCE_OBJECT)) ?>"
        data-fattyacids = "<?= htmlspecialchars( json_encode( $entry['fat'],   JSON_FORCE_OBJECT)) ?>"
        data-aminoacids = "<?= htmlspecialchars( json_encode( $entry['amino'], JSON_FORCE_OBJECT)) ?>"
        data-vitamins   = "<?= htmlspecialchars( json_encode( $entry['vit'],   JSON_FORCE_OBJECT)) ?>"
        data-minerals   = "<?= htmlspecialchars( json_encode( $entry['min'],   JSON_FORCE_OBJECT)) ?>"
        data-secondary  = "<?= htmlspecialchars( json_encode( $entry['sec'],   JSON_FORCE_OBJECT)) ?>"
        data-price      = "<?= $entry['price'] ?>"
    >   <!-- ^ camelCase doesn't work here                                     ^ empty array as js obj -->
      <?= $food ?>
    </li>
  <?php endforeach; ?>
</ul>
