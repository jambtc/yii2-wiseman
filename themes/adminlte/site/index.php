<div class="container-fluid">
    <div class="row">
        <?php if (Yii::$app->user->isGuest) : ?>
            <?= $this->render('landing-home') ?>
        <?php else: ?>
            <div class="col-lg-12">
                <?php
                echo $this->render('dashboard', [
                    'dataProvider'=>$dataProvider,
                    'userRequestsProvider' => $userRequestsProvider,
                ]);
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>
