<?php

namespace app\admin\controller\routine;

use Throwable;
use ba\Filesystem;
use app\common\library\Email;
use PHPMailer\PHPMailer\PHPMailer;
use app\common\controller\Backend;
use app\admin\model\Config as ConfigModel;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Config extends Backend
{
    /**
     * @var object
     * @phpstan-var ConfigModel
     */
    protected object $model;

    protected array $noNeedLogin = ['index'];

    protected array $filePath = [
        'appConfig'           => 'config/app.php',
        'webAdminBase'        => 'web/src/router/static/adminBase.ts',
        'backendEntranceStub' => 'app/admin/library/stubs/backendEntrance.stub',
    ];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new ConfigModel();
    }

    public function index(): void
    {
        $configGroup = get_sys_config('config_group');
        $config      = $this->model->order('weigh desc')->select()->toArray();

        $list           = [];
        $newConfigGroup = [];
        foreach ($configGroup as $item) {
            $list[$item['key']]['name']   = $item['key'];
            $list[$item['key']]['title']  = __($item['value']);
            $newConfigGroup[$item['key']] = $list[$item['key']]['title'];
        }
        foreach ($config as $item) {
            if (array_key_exists($item['group'], $newConfigGroup)) {
                $item['title']                  = __($item['title']);
                $list[$item['group']]['list'][] = $item;
            }
        }

        $this->success('', [
            'list'          => $list,
            'remark'        => get_route_remark(),
            'configGroup'   => $newConfigGroup ?? [],
            'quickEntrance' => get_sys_config('config_quick_entrance'),
        ]);
    }

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit(): void
    {
        $all = $this->model->select();
        foreach ($all as $item) {
            if ($item['type'] == 'editor') {
                $this->request->filter('clean_xss');
                break;
            }
        }
        if ($this->request->isPost()) {
            $this->modelValidate = false;
            $data                = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);

            $configValue = [];
            foreach ($all as $item) {
                if (array_key_exists($item->name, $data)) {
                    $configValue[] = [
                        'id'    => $item->id,
                        'type'  => $item->getData('type'),
                        'value' => $data[$item->name]
                    ];

                    // 自定义后台入口
                    if ($item->name == 'backend_entrance') {
                        $backendEntrance = get_sys_config('backend_entrance');
                        if ($backendEntrance == $data[$item->name]) continue;

                        if (!preg_match("/^\/[a-zA-Z0-9]+$/", $data[$item->name])) {
                            $this->error(__('Backend entrance rule'));
                        }

                        // 修改 adminBaseRoutePath
                        $adminBaseFilePath = Filesystem::fsFit(root_path() . $this->filePath['webAdminBase']);
                        $adminBaseContent  = @file_get_contents($adminBaseFilePath);
                        if (!$adminBaseContent) $this->error(__('Configuration write failed: %s', [$this->filePath['webAdminBase']]));

                        $adminBaseContent = str_replace("export const adminBaseRoutePath = '$backendEntrance'", "export const adminBaseRoutePath = '{$data[$item->name]}'", $adminBaseContent);
                        $result           = @file_put_contents($adminBaseFilePath, $adminBaseContent);
                        if (!$result) $this->error(__('Configuration write failed: %s', [$this->filePath['webAdminBase']]));

                        // 去除后台入口开头的斜杠
                        $oldBackendEntrance = ltrim($backendEntrance, '/');
                        $newBackendEntrance = ltrim($data[$item->name], '/');

                        // 设置应用别名映射
                        $appMap      = config('app.app_map');
                        $adminMapKey = array_search('admin', $appMap);
                        if ($adminMapKey !== false) {
                            unset($appMap[$adminMapKey]);
                        }
                        if ($newBackendEntrance != 'admin') {
                            $appMap[$newBackendEntrance] = 'admin';
                        }
                        $appConfigFilePath = Filesystem::fsFit(root_path() . $this->filePath['appConfig']);
                        $appConfigContent  = @file_get_contents($appConfigFilePath);
                        if (!$appConfigContent) $this->error(__('Configuration write failed: %s', [$this->filePath['appConfig']]));

                        $appMapStr = '';
                        foreach ($appMap as $newAppName => $oldAppName) {
                            $appMapStr .= "'$newAppName' => '$oldAppName', ";
                        }
                        $appMapStr = rtrim($appMapStr, ', ');
                        $appMapStr = "[$appMapStr]";

                        $appConfigContent = preg_replace("/'app_map'(\s+)=>(\s+)(.*)\/\/ 域名/s", "'app_map'\$1=>\$2$appMapStr,\n    // 域名", $appConfigContent);
                        $result           = @file_put_contents($appConfigFilePath, $appConfigContent);
                        if (!$result) $this->error(__('Configuration write failed: %s', [$this->filePath['appConfig']]));

                        // 建立API入口文件
                        $oldBackendEntranceFile = Filesystem::fsFit(public_path() . $oldBackendEntrance . '.php');
                        $newBackendEntranceFile = Filesystem::fsFit(public_path() . $newBackendEntrance . '.php');
                        if (file_exists($oldBackendEntranceFile)) @unlink($oldBackendEntranceFile);

                        if ($newBackendEntrance != 'admin') {
                            $backendEntranceStub = @file_get_contents(Filesystem::fsFit(root_path() . $this->filePath['backendEntranceStub']));
                            if (!$backendEntranceStub) $this->error(__('Configuration write failed: %s', [$this->filePath['backendEntranceStub']]));

                            $result = @file_put_contents($newBackendEntranceFile, $backendEntranceStub);
                            if (!$result) $this->error(__('Configuration write failed: %s', [$newBackendEntranceFile]));
                        }
                    }
                }
            }

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('edit');
                        $validate->check($data);
                    }
                }
                $result = $this->model->saveAll($configValue);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('The current page configuration item was updated successfully'));
            } else {
                $this->error(__('No rows updated'));
            }

        }
    }

    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data   = $this->excludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }
                $result = $this->model->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 发送邮件测试
     * @throws Throwable
     */
    public function sendTestMail(): void
    {
        $data = $this->request->post();
        $mail = new Email();
        try {
            $mail->Host       = $data['smtp_server'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $data['smtp_user'];
            $mail->Password   = $data['smtp_pass'];
            $mail->SMTPSecure = $data['smtp_verification'] == 'SSL' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $data['smtp_port'];

            $mail->setFrom($data['smtp_sender_mail'], $data['smtp_user']);

            $mail->isSMTP();
            $mail->addAddress($data['testMail']);
            $mail->isHTML();
            $mail->setSubject(__('This is a test email') . '-' . get_sys_config('site_name'));
            $mail->Body = __('Congratulations, receiving this email means that your email service has been configured correctly');
            $mail->send();
        } catch (PHPMailerException) {
            $this->error($mail->ErrorInfo);
        }
        $this->success(__('Test mail sent successfully~'));
    }
}