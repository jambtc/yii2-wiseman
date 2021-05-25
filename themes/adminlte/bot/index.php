<?php
use app\assets\WisemanAsset;

WisemanAsset::register($this);

?>
<div class="container-fluid">
    <div class="row">
        <!-- Masthead-->
        <header class="masthead d-flex">
            <!-- Modal -->
            <div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content bg-transparent shadow-lg text-light">
                  <!-- <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div> -->
                  <div class="modal-body" id='ChatScroll'>
                      <div class="col-lg-12"  >
                          <ul class="ChatLog" id="ChatLog"></ul>
                      </div>

                  </div>
                  <div class="modal-footer">
                      <form id="form">
                          <input class="form-control ChatInput" type="text" id="message" />
                          <input value="false" id="interactive" type="hidden" />
                      </form>
                    </div>
                </div>
              </div>
            </div>

        </header>
    </div>
</div>
