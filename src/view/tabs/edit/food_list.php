
<ul id="foodList" class="list-group">
  <!-- style   = "font-size: 1.5rem;"-->
  <li class="food-item p-1 list-group-item d-flex">
    <button class="btn btn-sm flex-fill" onclick="foodsCrl.saveDayEntriesBtnClick(event)">
      Save
    </button>
    <span id="uiMsg"></span>
    <button class="btn btn-sm flex-fill" onclick="foodsCrl.newEntryBtn(event)">
      Enter manually ...
    </button>
  </li>
  <!-- style   = "font-size: 1.5rem;"-->
  <?php foreach( $this->modelView->foods as $food => $entry): ?>
  <?php
          // if( strpos( $food, 'Salt') !== false )  // DEBUG
          // if( $food === '1.38g Salt' )
          //   $debug = 'halt';
  ?>
    <li class   = "food-item p-1 list-group-item"
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
