<?php

namespace humhub\modules\sharebetween\models;

use Yii;
use yii\base\Model;

/**
 * @author Luke
 * @package humhub.modules_core.space.forms
 * @since 0.5
 */
class ShareForm extends Model
{

    /**
     * Field for Invite GUIDs
     *
     * @var type
     */
    public $spaces;
    public $users;

    public function rules()
    {
       
        return array(
            array(['users'], 'safe'),
            array(['spaces'], 'required'),
                //array('inviteExternal', 'checkInviteExternal'),
        );
    }

}
