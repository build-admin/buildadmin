<?php

namespace app\api\controller;

use ba\Date;
use think\facade\Db;
use app\common\controller\Frontend;
use think\db\exception\PDOException;
use think\exception\ValidateException;
use app\api\validate\Account as AccountValidate;

class Account extends Frontend
{

    protected $model = null;

    public function initialize()
    {
        parent::initialize();
    }

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

    public function profile()
    {
        if ($this->request->isPost()) {
            $data = $this->request->only(['avatar', 'nickname', 'gender', 'birthday', 'motto']);
            if (!$data['birthday']) $data['birthday'] = null;
            
            Db::startTrans();
            try {
                $validate = new AccountValidate();
                $validate->scene('edit')->check($data);
                $this->auth->getUser()->where('id', $this->auth->id)->update($data);
                Db::commit();
            } catch (ValidateException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

            $this->success(__('Data updated successfully~'));
        }
    }

}