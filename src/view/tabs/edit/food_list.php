
<ul id="foodList" class="list-group">
  <?php foreach( $this->model->foods as $food => $entry): ?>
    <li class   = "food-item p-1 list-group-item d-flex justify-content-between align-items-center"
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
