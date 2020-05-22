<?php

/**
 * ShareBetween
 *
 * @package humhub.modules.sharebetween
 * @since 1.0
 * @author Dajan Vulaj
 */

use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use \humhub\modules\sharebetween\assets\Assets;
use humhub\modules\space\models\Space;
use yii\helpers\Url;

Assets::register($this);


$this->registerJsConfig('sharebetween', [
    "groups" => [
        'searchGroupsUrl' =>  yii\helpers\Url::to(['/sharebetween/share/get-groups']),
        'searchUsersUrl' =>  yii\helpers\Url::to(['/sharebetween/share/get-users']),
        'retriveUserObjects' =>  yii\helpers\Url::to(['/sharebetween/share/get-message-objects']),
        'user_id' =>  Yii::$app->user->id
    ]
]);

?>
<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel"><?= Yii::t('SharebetweenModule.base', '<strong>Share</strong> content'); ?></h4>
        </div>
        <div class="modal-body">

            <br />
            <div class="text-center">
                <ul id="tabs" class="nav nav-tabs tabs-center" data-tabs="tabs">
                    <li class="active tab-internal">
                        <a href="#profile" data-toggle="tab"><?= Yii::t('SharebetweenModule.base', 'On your profile'); ?></a>
                    </li>
                    <li class="tab-external">
                        <a href="#space" data-toggle="tab"><?= Yii::t('SharebetweenModule.base', 'On space'); ?></a>
                    </li>
                </ul>
            </div>
            <br />

            <div class="tab-content">
                <div class="tab-pane active" id="profile">
                    <?php $form = ActiveForm::begin(); ?>
                    <?= Yii::t('SharebetweenModule.base', 'Share this content directly on your profile.'); ?>
                    <br /><br />

                    <div class="modal-footer">

                        <?= \humhub\widgets\AjaxButton::widget([
                            'label' => Yii::t('SharebetweenModule.base', 'Share'),
                            'ajaxOptions' => [
                                'type' => 'POST',
                                'beforeSend' => new JsExpression('function(){ setModalLoader(); }'),
                                'success' => new JsExpression('function(html){ $("#globalModal").html(html); humhub.modules.sharebetween.checkResponse();}'),
                                'url' => yii\helpers\Url::to(['/sharebetween/share/index', 'id' => $content->id, 'type' => 'self']),
                            ],
                            'htmlOptions' => [
                                'class' => 'btn btn-primary'
                            ]
                        ]);
                        ?>
                        <button type="button" class="btn btn-primary" data-dismiss="modal">
                            <?= Yii::t('SharebetweenModule.base', 'Close'); ?>
                        </button>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
                <div class="tab-pane" id="space">
                    <?php $form = ActiveForm::begin(); ?>
                    <?= Yii::t('SharebetweenModule.base', 'To share this content with other spaces, please type their names below to find and pick them.'); ?>
                    <br /><br />

                    <?= $form->field($model, 'spaces')->dropDownList([], ['id' => 'spaces_select2', 'multiple' => 'multiple'])->label(false); ?>
                    <div class="modal-footer">
                        <?= \humhub\widgets\AjaxButton::widget([
                            'label' => Yii::t('SharebetweenModule.base', 'Share'),
                            'ajaxOptions' => [
                                'type' => 'POST',
                                'beforeSend' => new JsExpression('function(){ setModalLoader(); }'),
                                'success' => new JsExpression('function(html){ $("#globalModal").html(html); humhub.modules.sharebetween.checkResponse();}'),
                                'url' => yii\helpers\Url::to(['/sharebetween/share/index', 'id' => $content->id, 'type' => 'group']),
                            ],
                            'htmlOptions' => [
                                'class' => 'btn btn-primary'
                            ]
                        ]);
                        ?>
                        <button type="button" onClick='humhub.modules.sharebetween.unload()' class="btn btn-primary" data-dismiss="modal">
                            <?= Yii::t('SharebetweenModule.base', 'Close'); ?>
                        </button>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
                <?= \humhub\widgets\LoaderWidget::widget(['id' => 'invite-loader', 'cssClass' => 'loader-modal hidden']); ?>

            </div>

        </div>

    </div>

</div>

<script type="text/javascript">
    // Shake modal after wrong validation
    <?php if ($model->hasErrors()) : ?>
        $('.modal-dialog').removeClass('fadeIn');
        $('.modal-dialog').addClass('shake');

        // check if there is an error at the second tab
        <?php if ($model->hasErrors('inviteExternal')) : ?>
            // show tab external tab
            $('#tabs a:last').tab('show');
        <?php endif; ?>

    <?php endif; ?>

    $('.tab-internal a').on('shown.bs.tab', function(e) {
        $('#invite_tag_input_field').focus();
    });

    $('.tab-external a').on('shown.bs.tab', function(e) {
        $('#email_invite').focus();
    });
</script>