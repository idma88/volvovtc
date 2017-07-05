<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class VtcMembers extends ActiveRecord{

    public $banned = false;

    public static function tableName(){
        return 'vtc_members';
    }

    public function rules(){
        return [
            [['user_id'], 'required'],
            [['user_id', 'can_lead', 'can_center', 'can_close', 'scores_total', 'scores_month', 'scores_other',
                'exam_driving', 'exam_3_cat', 'exam_2_cat', 'exam_1_cat', 'post_id', 'vacation_undefined'], 'integer'],
            [['vacation', 'start_date'], 'safe'],
            [['additional'], 'string', 'max' => 1024],
            [['user_id'], 'unique'],
        ];
    }

    public static function getMembers($get_bans = false){
        $members = array();
        $posts = array();
        $all_members = VtcMembers::find()->orderBy('post_id DESC, `scores_month` + `scores_other` DESC, scores_total DESC, start_date')->all();
        $positions = VtcPositions::find()->select(['id'])->all();
        foreach($positions as $position){
            $posts[] = $position->id;
        }
        foreach($all_members as $member){
            if(in_array($member->post_id, $posts)){
                $member->user_id = User::findOne($member->user_id);
                $position = VtcPositions::find()->select(['name'])->where(['id' => $member->post_id])->one();
                $member->post_id = $position->name;
                if($member->user_id->truckersmp != '' && $get_bans){
                    $member->banned = TruckersMP::isMemberBanned($member->user_id->truckersmp);
                }
                if($member->user_id->admin == '1') $members['Администрация'][] = $member;
                else $members[$member->post_id][] = $member;
            }
        }
        return $members;
    }

    public static function getAllMembers() {
        $members =  VtcMembers::find()->orderBy('start_date')->all();
        foreach($members as $member){
            $member->user_id = User::findOne($member->user_id);
        }
        return $members;
    }

    public static function fireMember($id){
        $member = VtcMembers::findOne($id);
        $user = User::findOne($member->user_id);
        $user->company = '';
        $user->save();
        return $member->delete() !== false;
    }

    public static function addScores($id, $scores, $target){
        $member = VtcMembers::findOne($id);
        if($target == 'month'){
            $member->scores_month = intval($member->scores_month) + intval($scores);
            $member->scores_total = intval($member->scores_total) + intval($scores);
        }elseif($target = 'other'){
            $member->scores_other = intval($member->scores_other) + intval($scores);
            $member->scores_total = intval($member->scores_total) + intval($scores);
        }
        if($member->update() !== false){
            Notifications::addNotification('Вам было начислено '. $scores . ' баллов!', $member->user_id);
            return ['other' => $member->scores_other, 'month' => $member->scores_month, 'total' => $member->scores_total];
        }
        return false;
    }

    public static function cleanVacations(){
        $members = VtcMembers::find()->where(['!=', 'vacation', ''])->all();
        foreach($members as $member){
            $vacation = new \DateTime($member->vacation);
            $now = new \DateTime();
            if($vacation < $now){
                $member->vacation = '';
                $member->save();
            }
        }
    }

    public static function zeroScores(){
        $members = VtcMembers::find()->all();
        foreach($members as $member){
            $member->scores_other = 0;
            $member->scores_month = 0;
            $member->update();
        }
    }

    public static function getBans($steamid64){
        $bans = array();
        foreach ($steamid64 as $uid => $steamid){
            $user = User::findOne($uid);
            $bans[$uid] = TruckersMP::isMemberBanned($user->truckersmp);
        }
        return $bans;
    }

}