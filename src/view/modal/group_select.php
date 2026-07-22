<?php

// Grouped target-group picker (tabs = optgroups, groups = options).
// Args: selectId, options (from layout_target_options), optional extraClass.
// Each option carries data-tab so the client can pass tab + group to the server.

extract($args);

?>
<select id="<?= $selectId ?>" class="form-select <?= $extraClass ?? '' ?>">
  <?php foreach( $options as $tab => $groups ): ?>
    <optgroup label="<?= htmlspecialchars($tab) ?>">
      <?php foreach( $groups as $groupName => $label ): ?>
        <option data-tab  = "<?= htmlspecialchars($tab, ENT_QUOTES) ?>"
                value     = "<?= htmlspecialchars($groupName, ENT_QUOTES) ?>"><?= htmlspecialchars($label) ?></option>
      <?php endforeach; ?>
    </optgroup>
  <?php endforeach; ?>
</select>
