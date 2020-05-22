<?php

namespace humhub\modules\sharebetween\controllers;

use Yii;
use yii\db\Expression;
use humhub\modules\content\models\Content;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\space\models\Space;
use humhub\modules\sharebetween\models\ShareForm;
use humhub\modules\sharebetween\models\Share;
use humhub\modules\space\models\Membership;
use yii\helpers\ArrayHelper;
use humhub\modules\space\widgets\Image;
use humhub\modules\user\models\User;
use yii\helpers\Url;

class ShareController extends \humhub\components\Controller
{
    const USER_SPACES_CACHE_KEY = 'userSpaces_';
    const STATUS_MEMBER = 3;

    public function actionIndex()
    {
        $model = new ShareForm();
        $content = Content::findOne(['id' => Yii::$app->request->get('id')]);

        if (!$content->canView()) {
            throw new \yii\web\HttpException('400', 'Permission denied!');
        }

        if (Yii::$app->request->isPost) {
            $type = Yii::$app->request->get('type');

            //Check if is already shared
            if ($content->object_model == "humhub\modules\sharebetween\models\Share") {
                $shareRelation = Share::findOne(['id' => $content->object_id]);
                $sharedContent = Content::findOne(['id' => $shareRelation->content_id]);

                if ($sharedContent !== null) {
                    $content = $sharedContent;
                } else {
                    return $this->renderAjax('failed');
                }
            }

            //Post to my profile
            if ($type == "self") {
                Share::create($content, Yii::$app->user->getIdentity());
                return $this->renderAjax('success');
            } else if ($type == "group") { //POst to a group of users
                if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                    //Selected user space ids
                    $selected_spaces = Yii::$app->request->post()['ShareForm']['spaces'];

                    //Get spaces by id
                    $getSelectedMemberships = Membership::findByUser(Yii::$app->user->getIdentity())->andWhere(['in', 'id', $selected_spaces])->all();
                    foreach ($getSelectedMemberships as $membership) {
                        //Share content to space
                        Share::create($content, $membership->space);
                    }

                    return $this->renderAjax('success');
                }
            } else { // Type Is missing or unknown
                return $this->renderAjax('failed');
            }
        }
        return $this->renderAjax('index', ['content' => $content, 'model' => $model]);
    }


    //Get followed
    public function actionFollowerList()
    {
        $query = User::find();
        $query->leftJoin('user_follow', 'user.id=user_follow.user_id AND object_model=:userClass AND user_follow.object_id=:userId', [':userClass' => User::class, ':userId' => Yii::$app->user->id]);
        $query->joinWith('profile');
        $query->orderBy(['user_follow.id' => SORT_DESC]);
        $query->andWhere(['IS NOT', 'user_follow.id', new Expression('NULL')]);
        $query->active();

        return $query;
    }

    public function actionFollowedUsersList()
    {
        $query = User::find();
        $query->leftJoin('user_follow', 'user.id=user_follow.object_id AND object_model=:userClass AND user_follow.user_id=:userId', [':userClass' => User::class, ':userId' => Yii::$app->user->id]);
        $query->joinWith('profile');
        $query->orderBy(['user_follow.id' => SORT_DESC]);
        $query->andWhere(['IS NOT', 'user_follow.id', new Expression('NULL')]);
        $query->active();

        return $query;
    }


    //Get Users by query name
    public function actionGetUsers()
    {
        if (strlen(Yii::$app->request->get('query')) >= 1) {
            if (Yii::$app->request->isAjax) {
                $query = Yii::$app->request->get('query');

                $results = array();
                $followed = $this->actionFollowerList()
                    ->andWhere([
                        'or',
                        ['like', 'profile.firstname', $query],
                        ['like', 'profile.lastname', $query]
                    ])
                    ->limit(10)
                    ->all();
                foreach ($followed as $user) {
                    $results[] = [
                        'id' => $user->id,
                        'guid' => $user->guid,
                        'text' => $user->getDisplayName(),
                        'image' => $user->getProfileImage()->getUrl()
                    ];
                }

                return $this->asJson($results);
            }
        }
    }

    //Get Membership Query
    private function getMembershipQuery($userId)
    {
        $orderSetting = Yii::$app->getModule('space')->settings->get('spaceOrder');
        $orderBy = 'name ASC';
        if ($orderSetting != 0) {
            $orderBy = 'last_visit DESC';
        }

        $query = Membership::find()->joinWith('space')->orderBy($orderBy);
        $query->where([
            'user_id' => $userId,
            'space_membership.status' => self::STATUS_MEMBER
        ]);

        return $query;
    }

    //Get User Spaces by query name
    private function getUserSpacesByQueryName($userId = '', $query = '', $limit = 10, $cached = true)
    {
        if ($userId === '') {
            $userId = Yii::$app->user->id;
        }

        $cacheId = self::USER_SPACES_CACHE_KEY . $userId;

        $spaces = Yii::$app->cache->get($cacheId);
        if ($spaces === false || !$cached) {
            $spaces = [];

            //Get Memberships
            $getMemberships = $this->getMembershipQuery($userId)->andWhere(['like', 'space.name', $query])->all();

            foreach ($getMemberships as $membership) {
                $spaces[] = $membership->space;
            }
            Yii::$app->cache->set($cacheId, $spaces);
        }

        return $spaces;
    }

    //Get groups by query name
    public function actionGetGroups()
    {
        if (strlen(Yii::$app->request->get('query')) >= 1) {
            if (Yii::$app->request->isAjax) {
                //Find User Spaces
                $spaces = array();
                $results = array();
                foreach ($this->getUserSpacesByQueryName(Yii::$app->user->id, Yii::$app->request->get('query')) as $space) {
                    $spaces[] = ArrayHelper::toArray($space);
                    $results[] = [
                        'id' => $space->id,
                        'guid' => $space->guid,
                        'text' => $space->getDisplayName(),
                        'image' => $space->getProfileImage()->getUrl()
                    ];
                }

                return $this->asJson($results);
            }
        }
    }
}
