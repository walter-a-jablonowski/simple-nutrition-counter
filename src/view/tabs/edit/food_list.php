
<ul id="foodList" class="list-group">
  <li class="food-item p-1 list-group-item d-flex"
      style="font-size: 1.5rem;"
  >
    <button class="btn btn-sm flex-fill" onclick="foodsCrl.saveDayEntriesBtnClick(event)">
      Save
    </button>
    <button class="btn btn-sm flex-fill" onclick="foodsCrl.newEntryBtn(event)">
      New
    </button>
  </li>
  <?php foreach( $this->model->foods as $food => $entry): ?>
    <li class   = "food-item p-1 list-group-item"
        style   = "font-size: 1.5rem;"
        onclick = "foodsCrl.foodItemClick(event)"
        data-food      = "<?= $food ?>"
        data-calories  = "<?= $entry['calories'] ?>"
        data-nutrients = "<?= htmlspecialchars( json_encode( $entry['nutrients'])) ?>"
    >
      <?= $food ?>
    </li>
  <?php endforeach; ?>
</ul>
