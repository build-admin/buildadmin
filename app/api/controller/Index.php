<?php

namespace app\api\controller;

use ba\Tree;
use Throwable;
use think\facade\Db;
use think\facade\Cookie;
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
     * 前台和会员中心的初始化请求
     * @throws Throwable
     */
    public function index()
    {
        $menus = [];
        if ($this->auth->isLogin()) {
            $rules     = [];
            $userMenus = $this->auth->getMenus();

            // 首页加载的规则，验权，但过滤掉会员中心菜单
            foreach ($userMenus as $item) {
                if ($item['type'] == 'menu_dir') {
                    $menus[] = $item;
                } else if ($item['type'] != 'menu') {
                    $rules[] = $item;
                }
            }
            $rules = array_values($rules);
        } else {
            // 若是从前台会员中心内发出的请求，要求必须登录，否则会员中心异常
            $requiredLogin = $this->request->get('requiredLogin/b', false);
            if ($requiredLogin) {
                $token = $this->request->server('HTTP_BA_USER_TOKEN', $this->request->request('ba-user-token', Cookie::get('ba-user-token') ?: false));

                // 若过期可触发过期判定
                $this->auth->init($token);

                // 未过期或无token，直接要求登录
                $this->error(__('Please login first'), [
                    'routePath' => '/user/login'
                ], 302);
            }

            $rules = Db::name('user_rule')
                ->where('status', '1')
                ->where('no_login_valid', 1)
                ->where('type', 'in', ['route', 'nav', 'button'])
                ->order('weigh', 'desc')
                ->select()
                ->toArray();
            $rules = Tree::instance()->assembleChild($rules);
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
            'userInfo'         => $this->auth->getUserInfo(),
            'rules'            => $rules,
            'menus'            => $menus,
        ]);
    }
}