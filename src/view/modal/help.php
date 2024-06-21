<div id="helpModal" class="modal info-modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="height: 100vh;">
    <div class="modal-content" style="height: 90%;">
<!--
      <div class="modal-header">
        <h6 class="modal-title">Help</h6>
        <button data-bs-dismiss="modal" class="btn-close" type="button"></button>
      </div>
-->
      <div class="modal-body small">

        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item" role="presentation">
            <a href="#helpPane" class="nav-link active py-1 px-2 small" data-bs-toggle="tab" role="tab">
              Help
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a href="#helpMiscPane" class="nav-link py-1 px-2 small" data-bs-toggle="tab" role="tab">
              Misc
            </a>
          </li>
          <li class="nav-item ms-auto" role="presentation">
            <button data-bs-dismiss="modal" class="btn-close" type="button"></button>
          </li>
        </ul>

        <div class="tab-content mt-3">
          <div id="helpPane" class="tab-pane fade show active" role="tabpanel">

            <?php require('misc/help.php') ?>

          </div>
          <div id="helpMiscPane" class="tab-pane fade" role="tabpanel">

            some content

          </div>
        </div>

      </div>
<!--
      <div class="modal-footer">
        <button data-bs-dismiss="modal" class="btn btn-sm btn-secondary" type="button">
          Close
        </button>
      </div>
-->
    </div>
  </div>
</div>
