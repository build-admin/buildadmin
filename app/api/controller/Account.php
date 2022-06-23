<?php

namespace app\api\controller;

use ba\Date;
use think\facade\Db;
use app\common\controller\Frontend;

class Account extends Frontend
{

    public function overview()
    {
        $sevenDays = Date::unixtime('day', -7);
        $score     = $money = $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[$i]    = date("Y-m-d", $sevenDays + ($i * 86400));
            $tempToday0  = strtotime($days[$i]);
            $temptoday24 = strtotime('+1 day', $tempToday0) - 1;
            $score[$i]   = Db::name('user_score_log')
                ->where('user_id', $this->auth->id)
                ->where('createtime', 'BETWEEN', $tempToday0 . ',' . $temptoday24)
                ->sum('score');
            $money[$i]   = Db::name('user_money_log')
                ->where('user_id', $this->auth->id)
                ->where('createtime', 'BETWEEN', $tempToday0 . ',' . $temptoday24)
                ->sum('money');
        }

        $this->success('', [
            'days'  => $days,
            'score' => $score,
            'money' => $money,
        ]);
    }

}