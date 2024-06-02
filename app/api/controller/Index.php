<?php

namespace app\api\controller;

use ba\Tree;
use Throwable;
use think\facade\Db;
use think\facade\Config;
use app\common\controller\Frontend;
use app\common\library\token\TokenExpirationException;

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
    public function index(): void
    {
        $menus = [];
        if ($this->auth->isLogin()) {
            $rules     = [];
            $userMenus = $this->auth->getMenus();

            // 首页加载的规则，验权，但过滤掉会员中心菜单
            foreach ($userMenus as $item) {
                if ($item['type'] == 'menu_dir') {
                    $menus[] = $item;
                } elseif ($item['type'] != 'menu') {
                    $rules[] = $item;
                }
            }
            $rules = array_values($rules);
        } else {
            // 若是从前台会员中心内发出的请求，要求必须登录，否则会员中心异常
            $requiredLogin = $this->request->get('requiredLogin/b', false);
            if ($requiredLogin) {

                // 触发可能的 token 过期异常
                try {
                    $token = get_auth_token(['ba', 'user', 'token']);
                    $this->auth->init($token);
                } catch (TokenExpirationException) {
                    $this->error(__('Token expiration'), [], 409);
                }

                $this->error(__('Please login first'), [
                    'type' => $this->auth::NEED_LOGIN
                ], $this->auth::LOGIN_RESPONSE_CODE);
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