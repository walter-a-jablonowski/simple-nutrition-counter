<?php

// Grouped target-group picker (tabs = optgroups, groups = options).
// Args: selectId, options (from layout_target_options), optional extraClass,
// optional placeholder (adds an empty first option = no group picked).
// Each option carries data-tab so the client can pass tab + group to the server.

extract($args);

?>
<select id="<?= $selectId ?>" class="form-select <?= $extraClass ?? '' ?>">
  <?php if( ! empty($placeholder) ): ?>
    <option class="default" value="" selected><?= htmlspecialchars($placeholder) ?></option>
  <?php endif; ?>
  <?php foreach( $options as $tab => $groups ): ?>
    <optgroup label="<?= htmlspecialchars($tab) ?>">
      <?php foreach( $groups as $groupName => $label ): ?>
        <option data-tab  = "<?= htmlspecialchars($tab, ENT_QUOTES) ?>"
                value     = "<?= htmlspecialchars($groupName, ENT_QUOTES) ?>"><?= htmlspecialchars($label) ?></option>
      <?php endforeach; ?>
    </optgroup>
  <?php endforeach; ?>
</select>
