<?php
use app\assets\WisemanAsset;

WisemanAsset::register($this);

?>
<div class="container-fluid">
    <div class="row">
        <!-- Masthead-->
        <header class="masthead d-flex">
            <div class="container text-light my-auto">
                <div class="card bg-transparent shadow-lg p-3 mb-5 bg-white rounded float-right w-60" >
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="overflow-auto" id="log" style="max-height: 300px;"></div>
                            </div>
                            <div class="col-lg-6">
                                <form class="row" id="form">
                                    <input class="form-control " type="text" id="message" />
                                    </br>
                                    <input class="form-control " type="submit" />
                                    <input value="false" id="interactive" type="hidden" />
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overlay"></div>
        </header>
    </div>
</div>
