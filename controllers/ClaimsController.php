<?php

namespace app\controllers;

use app\models\ClaimsFired;
use app\models\ClaimsNickname;
use app\models\ClaimsRecruit;
use app\models\ClaimsVacation;
use app\models\FiredForm;
use app\models\NicknameForm;
use app\models\Notifications;
use app\models\RecruitForm;
use app\models\User;
use app\models\VacationForm;
use app\models\VtcMembers;
use Yii;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\Controller;

class ClaimsController extends Controller{

    public function actions(){
        return [
            'error' => ['class' => 'yii\web\ErrorAction'],
        ];
    }

    public function beforeAction($action){
        // getting user notifications
        if(!Yii::$app->user->isGuest){
            // online
            User::setUserActivity(Yii::$app->user->id);

            // notifications
            $has_unread = false;
            $notifications = Notifications::find()
                ->where(['uid' => Yii::$app->user->id])
                ->orderBy(['date' => SORT_DESC, 'status' => SORT_ASC])
                ->all();
            foreach ($notifications as $notification){
                if($notification->status == '0') {
                    $has_unread = true;
                    break;
                }
            }
            Yii::$app->view->params['notifications'] = $notifications;
            Yii::$app->view->params['hasUnreadNotifications'] = $has_unread;
        }
		if(Yii::$app->user->isGuest && $this->action->id != 'index'){
			return $this->redirect(['site/login']);
		}
        return parent::beforeAction($action);
    }

    public function actionIndex(){
        return $this->render('index', [
            'recruits' => ClaimsRecruit::getClaims(20),
            'fired' => ClaimsFired::getClaims(20),
            'vacation' => ClaimsVacation::getClaims(20),
            'nickname' => ClaimsNickname::getClaims(20)
        ]);
    }

    public function actionAdd(){
        if(Yii::$app->request->get('claim')){
            $claim = Yii::$app->request->get('claim');
            switch(Yii::$app->request->get('claim')){
                case 'recruit' : {
                    return $this->redirect(['site/recruit']);
                    break;
                }
                case 'dismissal' : {
                    $form = new FiredForm();
                    $render = 'add_fired_claim';
                    break;
                }
                case 'nickname' : {
                    $form = new NicknameForm();
                    $render = 'add_nickname_claim';
                    break;
                }
                case 'vacation' :
                default : {
                    $form = new VacationForm();
                    $render = 'add_vacation_claim';
                    break;
                }
            }
            if($form->load(Yii::$app->request->post()) && $form->validate()) {
                if($form->addClaim()){
                    return $this->redirect(['claims/index', '#' => $claim]);
                }else{
                    $form->addError('id','Возникла ошибка');
                }
            }
            if(User::isVtcMember()){
                return $this->render($render, [
                    'model' => $form
                ]);
            }else{
                return $this->redirect(['claims/index', '#' => $claim]);
            }
        }else{
            return $this->render('//site/error');
        }
    }

    public function actionEdit(){
        if(Yii::$app->request->get('claim') && Yii::$app->request->get('id')){
            $id = Yii::$app->request->get('id');
            $claim = str_replace('dismissal', 'fired', Yii::$app->request->get('claim'));
            $class = '\app\models\\'.ucfirst($claim).'Form';
            $form = new $class($id);
            if($form->load(Yii::$app->request->post()) && $form->validate()) {
                if($result = $form->editClaim($id)){
                    return $this->redirect(['claims/index', '#' => Yii::$app->request->get('claim')]);
                }else{
                    $form->addError('id','Возникла ошибка');
                }
            }
            // if admin or (claim user id = user id and status = 0)
            if((Yii::$app->user->id == $form->claim->user_id && $form->claim->status == '0') || User::isAdmin()){
                return $this->render('edit_'.$claim.'_claim', [
                    'model' => $form
                ]);
            }else{
                return $this->redirect(['claims/index', '#' => Yii::$app->request->get('claim')]);
            }
        }else{
            return $this->render('//site/error');
        }
    }

    public function actionApply(){
        if(Yii::$app->request->get('claim') && Yii::$app->request->get('id')){
			$claim = str_replace('dismissal', 'fired', Yii::$app->request->get('claim'));
			$class = '\app\models\\'.ucfirst($claim).'Form';
			$class::quickClaimApply(Yii::$app->request->get('id'));
			return $this->redirect(['claims/index', '#' => Yii::$app->request->get('claim')]);
        }else{
            return $this->render('//site/error');
        }
    }

    public function actionRemove(){
        if(User::isAdmin() && Yii::$app->request->get('claim') && Yii::$app->request->get('id')){
			$claim = str_replace('dismissal', 'fired', Yii::$app->request->get('claim'));
			$class = '\app\models\\'.ucfirst($claim).'Form';
			$class::deleteClaim(Yii::$app->request->get('id'));
			return $this->redirect(['claims/index', '#' => Yii::$app->request->get('claim')]);
        }else{
            return $this->render('//site/error');
        }
    }

    public static function countClaims($claims, $status = true){
        if(!$status){
            return count($claims);
        }else{
            $count = 0;
            foreach ($claims as $claim){
                if($claim->status == '0'){
                    $count++;
                }
            }
            return $count;
        }
    }

}