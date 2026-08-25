<!-- The nutrition advisor's panel, filled by AdvisorController.

     Its own modal rather than the agent overlay: that one is an aid to a spoken question,
     while this is a document with sections and per row actions, and it has to work with
     the voice agent switched off. See dev_info/Nutrition_Advisor_Plan.md -->

<div id="advisorModal" class="modal info-modal fade" tabindex="-1" aria-labelledby="advisorTitle">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">

      <div class="modal-header py-2">
        <h6 id="advisorTitle" class="modal-title">
          <i class="bi bi-lightbulb"></i>
          Nutrition advice
        </h6>

        <button id="advisorRefreshBtn" onclick="advisorCrl.refresh()" type="button"
                class = "btn btn-sm border-0 ms-auto me-2 text-body-secondary"
                title = "Ask again"
        >
          <i class="bi bi-arrow-clockwise"></i>
        </button>

        <button data-bs-dismiss="modal" class="btn-close" type="button" aria-label="Close"></button>
      </div>

      <div id="advisorBody" class="modal-body py-2">

        <!-- filled by js -->

      </div>

      <div class="modal-footer py-1">
        <p class="small text-body-secondary mb-0">
          <b>Disclaimer:</b> <?php require('misc/disclaimer.php'); ?>
        </p>
      </div>
    </div>
  </div>
</div>
