<?php

namespace app\common\event;

use Exception;
use think\Request;
use think\facade\Db;
use think\facade\Log;
use app\admin\library\Auth;
use think\db\exception\PDOException;

class Security
{
    protected $listenAction = ['edit', 'del'];

    public function handle(Request $request)
    {
        $action = $request->action(true);
        if (!in_array($action, $this->listenAction) || (!$request->isPost() && !$request->isDelete())) {
            return true;
        }

        if ($action == 'del') {
            $dataIds = $request->param('ids');
            try {
                $recycle = \app\admin\model\DataRecycle::where('status', '1')
                    ->where('controller_as', $request->controllerPath)
                    ->find();
                if (!$recycle) {
                    return true;
                }

                $recycleData    = Db::table($recycle['data_table'])
                    ->whereIn($recycle['primary_key'], $dataIds)
                    ->select()->toArray();
                $recycleDataArr = [];
                $auth           = Auth::instance();
                $adminId        = $auth->isLogin() ? $auth->id : 123;
                foreach ($recycleData as $recycleDatum) {
                    $recycleDataArr[] = [
                        'admin_id'   => $adminId,
                        'recycle_id' => $recycle['id'],
                        'data'       => json_encode($recycleDatum, JSON_UNESCAPED_UNICODE),
                        'ip'         => $request->ip(),
                        'useragent'  => substr($request->server('HTTP_USER_AGENT'), 0, 255),
                    ];
                }
                if (!$recycleDataArr) {
                    return true;
                }
            } catch (PDOException $e) {
                Log::record('[ DataSecurity ]' . var_export($e, true), 'warning');
            } catch (Exception $e) {
                Log::record('[ DataSecurity ]' . var_export($e, true), 'warning');
            }

            // saveAll 方法自带事务
            $dataRecycleLogModel = new \app\admin\model\DataRecycleLog;
            if ($dataRecycleLogModel->saveAll($recycleDataArr) === false) {
                Log::record('[ DataSecurity ] Failed to recycle data:' . var_export($recycleDataArr, true), 'warning');
            }
            return true;
        }

        return true;
    }
}