<?php

namespace app\api\controller;

use ba\Tree;
use Throwable;
use think\facade\Db;
use think\facade\Config;
use app\common\controller\Frontend;

class Index extends Frontend
{
    protected array $noNeedLogin = ['index'];

    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * 前台初始化请求
     * @throws Throwable
     */
    public function index()
    {
        if ($this->auth->isLogin()) {
            $rule = $this->auth->getMenus();

            // 首页加载的规则，验权，但过滤掉会员中心菜单
            foreach ($rule as $key => $item) {
                if (in_array($item['type'], ['menu_dir', 'menu'])) unset($rule[$key]);
            }
            $rule = array_values($rule);
        } else {
            $rule = Db::name('user_rule')
                ->where('status', '1')
                ->where('no_login_valid', 1)
                ->where('type', 'in', ['route', 'nav', 'button'])
                ->order('weigh', 'desc')
                ->select()
                ->toArray();
            $rule = Tree::instance()->assembleChild($rule);
        }

        $this->success('', [
            'site'             => [
                'siteName'     => get_sys_config('site_name'),
                'recordNumber' => get_sys_config('record_number'),
                'version'      => get_sys_config('version'),
                'cdnUrl'       => full_url(),
                'upload'       => get_upload_config(),
            ],
            'openMemberCenter' => Config::get('buildadmin.open_member_center'),
            'rules'            => $rule
        ]);
    }
}