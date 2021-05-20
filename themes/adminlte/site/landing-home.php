<?php
use yii\helpers\Url;
?>

<!-- Masthead-->
<header class="masthead d-flex">
    <div class="container text-light text-center my-auto">
        <div class="row">
            <div class="col-lg-5">
            </div>
            <div class="col-lg-4">
                <h1 class="mb-1">Io sono Wiseman</h1>
                <h3 class="mb-5"><em>Chiedimi ciò che vuoi</em></h3>
                <a class="btn btn-primary btn-xl js-scroll-trigger" href="<?= Url::to(['bot']) ?>">Iniziamo</a>
            </div>
            <div class="col-lg-3">
                <img class="img-fluid" src="/bundles/landing-page/assets/img/wiseman.png" >
            </div>
        </div>
    </div>
    <div class="overlay"></div>
</header>
