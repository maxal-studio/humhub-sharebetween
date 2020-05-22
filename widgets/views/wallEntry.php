<?php

use yii\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\widgets\WallEntry;
use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\content\widgets\WallEntryAddons;

//$user = $object->content->user;
$container = $object->content->container;
$sharedContent = $object->sharedContent->getPolymorphicRelation();

$addonOptions = [
    'object' => $object,
    'widgetOptions' => []
];
?>

<div class="panel panel-default wall_shared_content wall_<?php echo $object->getUniqueId(); ?>">
    <div class="panel-body">
        <div class="media">
            <?php echo \humhub\modules\content\widgets\WallEntryControls::widget(['object' => $object, 'wallEntryWidget' => $wallEntryWidget]); ?>

            <p>
                <?= Yii::t('SharebetweenModule.base', '{displayName} shared a {contentType}.', ['displayName' => Html::a($user->displayName, $user->getUrl(), ['style' => 'color: #e5c150']), 'contentType' => Html::a($sharedContent->getContentName(), $sharedContent->content->getUrl())]); ?>
            </p>

            <div class="content wall-content" id="wall_content_<?php echo $object->getUniqueId(); ?>">
                <?php echo $content; ?>
            </div>

            <div class="stream-entry-addons">
                <?php
                echo WallEntryAddons::widget($addonOptions);
                ?>
            </div>
        </div>
    </div>
</div>