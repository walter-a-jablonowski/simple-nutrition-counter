<div id="aboutModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
<!--
      <div class="modal-header">
        <h6 class="modal-title">About</h6>
        <button data-bs-dismiss="modal" class="btn-close" type="button"></button>
      </div>
-->
      <div class="modal-body small">

        <?= file_get_contents('misc/about.html') ?>

      </div>
      <div class="modal-footer">
        <button data-bs-dismiss="modal" class="btn btn-sm btn-secondary" type="button">
          Close
        </button>
      </div>
    </div>
  </div>
</div>
