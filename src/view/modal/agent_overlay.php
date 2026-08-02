<!-- The voice agent's own overlay, filled by AgentOverlayController.

     Deliberately not the confirm or info dialog: those are fixed forms with a message and
     buttons, while this one swaps its whole body per content type and stays open across a
     spoken conversation. See dev_info/Voice_Logging_Plan.md -->

<div id="agentOverlay" class="modal fade" tabindex="-1" aria-labelledby="agentOverlayTitle">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 id="agentOverlayTitle" class="modal-title">
          <!-- filled by js -->
        </h6>
        <button data-bs-dismiss="modal" class="btn-close" type="button" aria-label="Close"></button>
      </div>
      <div id="agentOverlayBody" class="modal-body py-2">

        <!-- filled by js -->

      </div>
    </div>
  </div>
</div>
