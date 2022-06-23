<?php

namespace app\api\controller;

use ba\Date;
use think\facade\Db;
use app\api\model\UserScoreLog;
use app\api\model\UserMoneyLog;
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
        $sevenDays = Date::unixtime('day', -6);
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

    public function changePassword()
    {
        if ($this->request->isPost()) {
            $params = $this->request->only(['oldPassword', 'newPassword']);

            if (!$this->auth->checkPassword($params['oldPassword'])) {
                $this->error(__('Old password error'));
            }

            Db::startTrans();
            try {
                $validate = new AccountValidate();
                $validate->scene('changePassword')->check(['password' => $params['newPassword']]);
                $this->auth->getUser()->resetPassword($this->auth->id, $params['newPassword']);
                Db::commit();
            } catch (ValidateException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

            $this->auth->logout();
            $this->success(__('Password has been changed, please login again~'));
        }
    }

    public function integral()
    {
        $limit         = $this->request->request('limit');
        $integralModel = new UserScoreLog();
        $res           = $integralModel->where('user_id', $this->auth->id)
            ->order('createtime desc')
            ->paginate($limit);

        $this->success('', [
            'list'  => $res->items(),
            'total' => $res->total(),
        ]);
    }

    public function balance()
    {
        $limit      = $this->request->request('limit');
        $moneyModel = new UserMoneyLog();
        $res        = $moneyModel->where('user_id', $this->auth->id)
            ->order('createtime desc')
            ->paginate($limit);

        $this->success('', [
            'list'  => $res->items(),
            'total' => $res->total(),
        ]);
    }

}