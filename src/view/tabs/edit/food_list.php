
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
        data-nutritionalvalues = "<?= htmlspecialchars( dump_json( $entry['nutriVal'])) ?>"
        data-fattyacids = "<?= htmlspecialchars( dump_json( $entry['fat'])) ?>"
        data-aminoacids = "<?= htmlspecialchars( dump_json( $entry['amino'])) ?>"
        data-vitamins   = "<?= htmlspecialchars( dump_json( $entry['vit'])) ?>"
        data-minerals   = "<?= htmlspecialchars( dump_json( $entry['min'])) ?>"
        data-secondary  = "<?= htmlspecialchars( dump_json( $entry['sec'])) ?>"
        data-price      = "<?= $entry['price'] ?>"
    >   <!-- ^ camelCase doesn't work here       ^ empty array as js obj, yml comp blanks behind colon -->
      <?= $food ?>
    </li>
  <?php endforeach; ?>
</ul>
