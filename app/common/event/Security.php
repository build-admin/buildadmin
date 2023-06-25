<?php

namespace app\common\event;

use Throwable;
use think\Request;
use think\facade\Db;
use think\facade\Log;
use app\admin\library\Auth;
use app\admin\model\SensitiveDataLog;
use app\admin\model\DataRecycle;
use app\admin\model\DataRecycleLog;
use app\admin\model\SensitiveData;

class Security
{
    protected array $listenAction = ['edit', 'del'];

    public function handle(Request $request): bool
    {
        $action = $request->action(true);
        if (!in_array($action, $this->listenAction) || (!$request->isPost() && !$request->isDelete())) {
            return true;
        }

        if ($action == 'del') {
            $dataIds = $request->param('ids');
            try {
                $recycle = DataRecycle::where('status', '1')
                    ->where('controller_as', $request->controllerPath)
                    ->find();
                if (!$recycle) {
                    return true;
                }

                $recycleData    = Db::name($recycle['data_table'])
                    ->whereIn($recycle['primary_key'], $dataIds)
                    ->select()
                    ->toArray();
                $recycleDataArr = [];
                $auth           = Auth::instance();
                $adminId        = $auth->isLogin() ? $auth->id : 0;
                foreach ($recycleData as $recycleDatum) {
                    $recycleDataArr[] = [
                        'admin_id'    => $adminId,
                        'recycle_id'  => $recycle['id'],
                        'data'        => json_encode($recycleDatum, JSON_UNESCAPED_UNICODE),
                        'data_table'  => $recycle['data_table'],
                        'primary_key' => $recycle['primary_key'],
                        'ip'          => $request->ip(),
                        'useragent'   => substr($request->server('HTTP_USER_AGENT'), 0, 255),
                    ];
                }
                if (!$recycleDataArr) {
                    return true;
                }

                // saveAll 方法自带事务
                $dataRecycleLogModel = new DataRecycleLog;
                if (!$dataRecycleLogModel->saveAll($recycleDataArr)) {
                    Log::record('[ DataSecurity ] Failed to recycle data:' . var_export($recycleDataArr, true), 'warning');
                }
            } catch (Throwable $e) {
                Log::record('[ DataSecurity ]' . $e->getMessage(), 'warning');
            }
            return true;
        }

        try {
            $sensitiveData = SensitiveData::where('status', '1')
                ->where('controller_as', $request->controllerPath)
                ->find();
            if (!$sensitiveData) {
                return true;
            }

            $sensitiveData = $sensitiveData->toArray();
            $dataId        = $request->param('id');
            $editData      = Db::name($sensitiveData['data_table'])
                ->field(array_keys($sensitiveData['data_fields']))
                ->where($sensitiveData['primary_key'], $dataId)
                ->find();
            if (!$editData) {
                return true;
            }

            $auth    = Auth::instance();
            $adminId = $auth->isLogin() ? $auth->id : 0;
            $newData = $request->post();
            foreach ($sensitiveData['data_fields'] as $field => $title) {
                if (isset($editData[$field]) && isset($newData[$field]) && $editData[$field] != $newData[$field]) {

                    /*
                     * 其他跳过规则可添加至此处
                     * 1. 如果字段名中包含 password，修改值为空则忽略，修改值不为空，则密码记录为 ******
                     */
                    if (stripos('password', $field) !== false) {
                        if (!$newData[$field]) {
                            continue;
                        } else {
                            $newData[$field] = "******";
                        }
                    }

                    $sensitiveDataLog[] = [
                        'admin_id'     => $adminId,
                        'sensitive_id' => $sensitiveData['id'],
                        'data_table'   => $sensitiveData['data_table'],
                        'primary_key'  => $sensitiveData['primary_key'],
                        'data_field'   => $field,
                        'data_comment' => $title,
                        'id_value'     => $dataId,
                        'before'       => $editData[$field],
                        'after'        => $newData[$field],
                        'ip'           => $request->ip(),
                        'useragent'    => substr($request->server('HTTP_USER_AGENT'), 0, 255),
                    ];
                }
            }

            if (!isset($sensitiveDataLog) || !$sensitiveDataLog) {
                return true;
            }

            $sensitiveDataLogModel = new SensitiveDataLog;
            if (!$sensitiveDataLogModel->saveAll($sensitiveDataLog)) {
                Log::record('[ DataSecurity ] Sensitive data recording failed:' . var_export($sensitiveDataLog, true), 'warning');
            }
        } catch (Throwable $e) {
            Log::record('[ DataSecurity ]' . $e->getMessage(), 'warning');
        }
        return true;
    }
}