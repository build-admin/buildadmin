<?php

use think\migration\Migrator;
use Phinx\Db\Adapter\MysqlAdapter;

class Install extends Migrator
{

    public function change()
    {
        $this->admin();
        $this->adminGroup();
        $this->adminGroupAccess();
        $this->adminLog();
        $this->area();
        $this->attachment();
        $this->captcha();
        $this->config();
        $this->menuRule();
        $this->securityDataRecycle();
        $this->securityDataRecycleLog();
        $this->securitySensitiveData();
        $this->securitySensitiveDataLog();
        $this->testBuild();
        $this->token();
        $this->user();
        $this->userGroup();
        $this->userMoneyLog();
        $this->userRule();
        $this->userScoreLog();
        $this->crudLog();
    }

    public function admin()
    {
        if (!$this->hasTable('admin')) {
            $table = $this->table('admin', [
                'id'          => false,
                'comment'     => '管理员表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('username', 'string', ['limit' => 20, 'default' => '', 'comment' => '用户名'])
                ->addColumn('nickname', 'string', ['limit' => 50, 'default' => '', 'comment' => '昵称'])
                ->addColumn('avatar', 'string', ['limit' => 255, 'default' => '', 'comment' => '头像'])
                ->addColumn('email', 'string', ['limit' => 50, 'default' => '', 'comment' => '邮箱'])
                ->addColumn('mobile', 'string', ['limit' => 11, 'default' => '', 'comment' => '手机'])
                ->addColumn('loginfailure', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '登录失败次数'])
                ->addColumn('lastlogintime', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '上次登录时间'])
                ->addColumn('lastloginip', 'string', ['limit' => 50, 'default' => '', 'comment' => '上次登录IP'])
                ->addColumn('password', 'string', ['limit' => 32, 'default' => '', 'comment' => '密码'])
                ->addColumn('salt', 'string', ['limit' => 30, 'default' => '', 'comment' => '密码盐'])
                ->addColumn('motto', 'string', ['limit' => 255, 'default' => '', 'comment' => '签名'])
                ->addColumn('status', 'enum', ['values' => '0,1', 'default' => '1', 'comment' => '状态:0=禁用,1=启用'])
                ->addColumn('createtime', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addColumn('updatetime', 'integer', ['limit' => 10, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['username'], [
                    'unique' => true,
                ])
                ->create();
        }
    }

    public function adminGroup()
    {
        if (!$this->hasTable('admin_group')) {
            $table = $this->table('admin_group', [
                'id'          => false,
                'comment'     => '管理分组表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('pid', 'integer', ['comment' => '上级分组', 'default' => 0, 'signed' => false])
                ->addColumn('name', 'string', ['limit' => 100, 'default' => '', 'comment' => '组名'])
                ->addColumn('rules', 'text', ['null' => true, 'default' => null, 'comment' => '权限规则ID'])
                ->addColumn('status', 'enum', ['values' => '0,1', 'default' => '1', 'comment' => '状态:0=禁用,1=启用'])
                ->addColumn('updatetime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addColumn('createtime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->create();
        }
    }

    public function adminGroupAccess()
    {
        if (!$this->hasTable('admin_group_access')) {
            $table = $this->table('admin_group_access', [
                'id'         => false,
                'comment'    => '管理分组映射表',
                'row_format' => 'DYNAMIC',
                'collation'  => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('uid', 'integer', ['comment' => '管理员ID', 'signed' => false])
                ->addColumn('group_id', 'integer', ['comment' => '分组ID', 'signed' => false])
                ->addIndex(['uid'], [
                    'type' => 'BTREE',
                ])
                ->addIndex(['group_id'], [
                    'type' => 'BTREE',
                ])
                ->create();
        }
    }

    public function adminLog()
    {
        if (!$this->hasTable('admin_log')) {
            $table = $this->table('admin_log', [
                'id'          => false,
                'comment'     => '管理员日志表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('admin_id', 'integer', ['comment' => '管理员ID', 'default' => 0, 'signed' => false])
                ->addColumn('username', 'string', ['limit' => 20, 'default' => '', 'comment' => '管理员用户名'])
                ->addColumn('url', 'string', ['limit' => 1500, 'default' => '', 'comment' => '操作Url'])
                ->addColumn('title', 'string', ['limit' => 100, 'default' => '', 'comment' => '日志标题'])
                ->addColumn('data', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true, 'default' => null, 'comment' => '请求数据'])
                ->addColumn('ip', 'string', ['limit' => 50, 'default' => '', 'comment' => 'IP'])
                ->addColumn('useragent', 'string', ['limit' => 255, 'default' => '', 'comment' => 'User-Agent'])
                ->addColumn('createtime', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->create();
        }
    }

    public function area()
    {
        if (!$this->hasTable('area')) {
            $table = $this->table('area', [
                'id'          => false,
                'comment'     => '省份地区表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('pid', 'integer', ['comment' => '父id', 'null' => true, 'default' => null, 'signed' => false])
                ->addColumn('shortname', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => '简称'])
                ->addColumn('name', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => '名称'])
                ->addColumn('mergename', 'string', ['limit' => 255, 'null' => true, 'default' => null, 'comment' => '全称'])
                ->addColumn('level', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_TINY, 'null' => true, 'default' => null, 'comment' => '层级:1=省,2=市,3=区/县'])
                ->addColumn('pinyin', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => '拼音'])
                ->addColumn('code', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => '长途区号'])
                ->addColumn('zip', 'string', ['limit' => 100, 'null' => true, 'default' => null, 'comment' => '邮编'])
                ->addColumn('first', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'comment' => '首字母'])
                ->addColumn('lng', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'comment' => '经度'])
                ->addColumn('lat', 'string', ['limit' => 50, 'null' => true, 'default' => null, 'comment' => '纬度'])
                ->addIndex(['pid'], [
                    'type' => 'BTREE',
                ])
                ->create();
        }
    }

    public function attachment()
    {
        if (!$this->hasTable('attachment')) {
            $table = $this->table('attachment', [
                'id'          => false,
                'comment'     => '附件表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('topic', 'string', ['limit' => 20, 'default' => '', 'comment' => '细目'])
                ->addColumn('admin_id', 'integer', ['comment' => '上传管理员ID', 'default' => 0, 'signed' => false])
                ->addColumn('user_id', 'integer', ['comment' => '上传用户ID', 'default' => 0, 'signed' => false])
                ->addColumn('url', 'string', ['limit' => 255, 'default' => '', 'comment' => '物理路径'])
                ->addColumn('width', 'integer', ['comment' => '宽度', 'default' => 0, 'signed' => false])
                ->addColumn('height', 'integer', ['comment' => '高度', 'default' => 0, 'signed' => false])
                ->addColumn('name', 'string', ['limit' => 100, 'default' => '', 'comment' => '原始名称'])
                ->addColumn('size', 'integer', ['comment' => '大小', 'default' => 0, 'signed' => false])
                ->addColumn('mimetype', 'string', ['limit' => 100, 'default' => '', 'comment' => 'mime类型'])
                ->addColumn('quote', 'integer', ['comment' => '上传(引用)次数', 'default' => 0, 'signed' => false])
                ->addColumn('storage', 'string', ['limit' => 50, 'default' => '', 'comment' => '存储方式'])
                ->addColumn('sha1', 'string', ['limit' => 40, 'default' => '', 'comment' => 'sha1编码'])
                ->addColumn('createtime', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addColumn('lastuploadtime', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '最后上传时间'])
                ->create();
        }
    }

    public function captcha()
    {
        if (!$this->hasTable('captcha')) {
            $table = $this->table('captcha', [
                'id'          => false,
                'comment'     => '验证码表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'key',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('key', 'string', ['limit' => 32, 'default' => '', 'comment' => '验证码Key'])
                ->addColumn('code', 'string', ['limit' => 32, 'default' => '', 'comment' => '验证码(加密后)'])
                ->addColumn('captcha', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true, 'default' => null, 'comment' => '验证码数据'])
                ->addColumn('createtime', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addColumn('expiretime', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '过期时间'])
                ->create();
        }
    }

    public function config()
    {
        if (!$this->hasTable('config')) {
            $table = $this->table('config', [
                'id'          => false,
                'comment'     => '系统配置',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('name', 'string', ['limit' => 30, 'default' => '', 'comment' => '变量名'])
                ->addColumn('group', 'string', ['limit' => 30, 'default' => '', 'comment' => '分组'])
                ->addColumn('title', 'string', ['limit' => 50, 'default' => '', 'comment' => '变量标题'])
                ->addColumn('tip', 'string', ['limit' => 100, 'default' => '', 'comment' => '变量描述'])
                ->addColumn('type', 'string', ['limit' => 30, 'default' => '', 'comment' => '变量输入组件类型'])
                ->addColumn('value', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true, 'default' => null, 'comment' => '变量值'])
                ->addColumn('content', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true, 'default' => null, 'comment' => '字典数据'])
                ->addColumn('rule', 'string', ['limit' => 100, 'default' => '', 'comment' => '验证规则'])
                ->addColumn('extend', 'string', ['limit' => 255, 'default' => '', 'comment' => '扩展属性'])
                ->addColumn('allow_del', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '允许删除:0=否,1=是'])
                ->addColumn('weigh', 'integer', ['comment' => '权重', 'default' => 0])
                ->addIndex(['name'], [
                    'unique' => true,
                ])
                ->create();
        }
    }

    public function menuRule()
    {
        if (!$this->hasTable('menu_rule') && !$this->hasTable('admin_rule')) {
            $table = $this->table('menu_rule', [
                'id'          => false,
                'comment'     => '菜单和权限规则表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('pid', 'integer', ['comment' => '上级菜单', 'default' => 0, 'signed' => false])
                ->addColumn('type', 'enum', ['values' => 'menu_dir,menu,button', 'default' => 'menu', 'comment' => '类型:menu_dir=菜单目录,menu=菜单项,button=页面按钮'])
                ->addColumn('title', 'string', ['limit' => 50, 'default' => '', 'comment' => '标题'])
                ->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '规则名称'])
                ->addColumn('path', 'string', ['limit' => 100, 'default' => '', 'comment' => '路由路径'])
                ->addColumn('icon', 'string', ['limit' => 50, 'default' => '', 'comment' => '图标'])
                ->addColumn('menu_type', 'enum', ['values' => 'tab,link,iframe', 'null' => true, 'default' => null, 'comment' => '菜单类型:tab=选项卡,link=链接,iframe=Iframe'])
                ->addColumn('url', 'string', ['limit' => 255, 'default' => '', 'comment' => 'Url'])
                ->addColumn('component', 'string', ['limit' => 100, 'default' => '', 'comment' => '组件路径'])
                ->addColumn('keepalive', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '缓存:0=关闭,1=开启'])
                ->addColumn('extend', 'enum', ['values' => 'none,add_rules_only,add_menu_only', 'default' => 'none', 'comment' => '扩展属性:none=无,add_rules_only=只添加为路由,add_menu_only=只添加为菜单'])
                ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])
                ->addColumn('weigh', 'integer', ['comment' => '权重', 'default' => 0])
                ->addColumn('status', 'enum', ['values' => '0,1', 'default' => '1', 'comment' => '状态:0=禁用,1=启用'])
                ->addColumn('updatetime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addColumn('createtime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['pid'], [
                    'type' => 'BTREE',
                ])
                ->create();
        }
    }

    public function securityDataRecycle()
    {
        if (!$this->hasTable('security_data_recycle')) {
            $table = $this->table('security_data_recycle', [
                'id'          => false,
                'comment'     => '回收规则表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '规则名称'])
                ->addColumn('controller', 'string', ['limit' => 100, 'default' => '', 'comment' => '控制器'])
                ->addColumn('controller_as', 'string', ['limit' => 100, 'default' => '', 'comment' => '控制器别名'])
                ->addColumn('data_table', 'string', ['limit' => 100, 'default' => '', 'comment' => '对应数据表'])
                ->addColumn('primary_key', 'string', ['limit' => 50, 'default' => '', 'comment' => '数据表主键'])
                ->addColumn('status', 'enum', ['values' => '0,1', 'default' => '1', 'comment' => '状态:0=禁用,1=启用'])
                ->addColumn('updatetime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addColumn('createtime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->create();
        }
    }

    public function securityDataRecycleLog()
    {
        if (!$this->hasTable('security_data_recycle_log')) {
            $table = $this->table('security_data_recycle_log', [
                'id'          => false,
                'comment'     => '数据回收记录表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('admin_id', 'integer', ['comment' => '操作管理员', 'default' => 0, 'signed' => false])
                ->addColumn('recycle_id', 'integer', ['comment' => '回收规则ID', 'default' => 0, 'signed' => false])
                ->addColumn('data', 'text', ['null' => true, 'default' => null, 'comment' => '回收的数据'])
                ->addColumn('data_table', 'string', ['limit' => 100, 'default' => '', 'comment' => '数据表'])
                ->addColumn('primary_key', 'string', ['limit' => 50, 'default' => '', 'comment' => '数据表主键'])
                ->addColumn('is_restore', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '是否已还原:0=否,1=是'])
                ->addColumn('ip', 'string', ['limit' => 50, 'default' => '', 'comment' => '操作者IP'])
                ->addColumn('useragent', 'string', ['limit' => 255, 'default' => '', 'comment' => 'User-Agent'])
                ->addColumn('createtime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->create();
        }
    }

    public function securitySensitiveData()
    {
        if (!$this->hasTable('security_sensitive_data')) {
            $table = $this->table('security_sensitive_data', [
                'id'          => false,
                'comment'     => '敏感数据规则表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '规则名称'])
                ->addColumn('controller', 'string', ['limit' => 100, 'default' => '', 'comment' => '控制器'])
                ->addColumn('controller_as', 'string', ['limit' => 100, 'default' => '', 'comment' => '控制器别名'])
                ->addColumn('data_table', 'string', ['limit' => 100, 'default' => '', 'comment' => '对应数据表'])
                ->addColumn('primary_key', 'string', ['limit' => 50, 'default' => '', 'comment' => '数据表主键'])
                ->addColumn('data_fields', 'text', ['null' => true, 'default' => null, 'comment' => '敏感数据字段'])
                ->addColumn('status', 'enum', ['values' => '0,1', 'default' => '1', 'comment' => '状态:0=禁用,1=启用'])
                ->addColumn('updatetime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addColumn('createtime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->create();
        }
    }

    public function securitySensitiveDataLog()
    {
        if (!$this->hasTable('security_sensitive_data_log')) {
            $table = $this->table('security_sensitive_data_log', [
                'id'          => false,
                'comment'     => '敏感数据修改记录',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('admin_id', 'integer', ['comment' => '操作管理员', 'default' => 0, 'signed' => false])
                ->addColumn('sensitive_id', 'integer', ['comment' => '敏感数据规则ID', 'default' => 0, 'signed' => false])
                ->addColumn('data_table', 'string', ['limit' => 100, 'default' => '', 'comment' => '数据表'])
                ->addColumn('primary_key', 'string', ['limit' => 50, 'default' => '', 'comment' => '数据表主键'])
                ->addColumn('data_field', 'string', ['limit' => 50, 'default' => '', 'comment' => '被修改字段'])
                ->addColumn('data_comment', 'string', ['limit' => 50, 'default' => '', 'comment' => '被修改项'])
                ->addColumn('id_value', 'integer', ['comment' => '被修改项主键值', 'default' => 0])
                ->addColumn('before', 'text', ['null' => true, 'default' => null, 'comment' => '修改前'])
                ->addColumn('after', 'text', ['null' => true, 'default' => null, 'comment' => '修改后'])
                ->addColumn('ip', 'string', ['limit' => 50, 'default' => '', 'comment' => '操作者IP'])
                ->addColumn('useragent', 'string', ['limit' => 255, 'default' => '', 'comment' => 'User-Agent'])
                ->addColumn('is_rollback', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '是否已回滚:0=否,1=是'])
                ->addColumn('createtime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->create();
        }
    }

    public function testBuild()
    {
        if (!$this->hasTable('test_build')) {
            $table = $this->table('test_build', [
                'id'          => false,
                'comment'     => '知识库表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('title', 'string', ['limit' => 100, 'default' => '', 'comment' => '标题'])
                ->addColumn('keyword_rows', 'string', ['limit' => 100, 'default' => '', 'comment' => '关键词'])
                ->addColumn('content', 'text', ['null' => true, 'default' => null, 'comment' => '内容'])
                ->addColumn('views', 'integer', ['comment' => '浏览量', 'default' => 0, 'signed' => false])
                ->addColumn('likes', 'integer', ['comment' => '有帮助数', 'default' => 0, 'signed' => false])
                ->addColumn('dislikes', 'integer', ['comment' => '无帮助数', 'default' => 0, 'signed' => false])
                ->addColumn('note_textarea', 'string', ['limit' => 100, 'default' => '', 'comment' => '备注'])
                ->addColumn('status', 'enum', ['values' => '0,1', 'default' => '1', 'comment' => '状态:0=隐藏,1=正常'])
                ->addColumn('weigh', 'integer', ['comment' => '权重', 'default' => 0])
                ->addColumn('update_time', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addColumn('create_time', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->create();
        }
    }

    public function token()
    {
        if (!$this->hasTable('token')) {
            $table = $this->table('token', [
                'id'          => false,
                'comment'     => '用户Token表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'token',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('token', 'string', ['limit' => 50, 'default' => '', 'comment' => 'Token'])
                ->addColumn('type', 'string', ['limit' => 15, 'default' => '', 'comment' => '类型'])
                ->addColumn('user_id', 'integer', ['comment' => '用户ID', 'default' => 0, 'signed' => false])
                ->addColumn('createtime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addColumn('expiretime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '过期时间'])
                ->create();
        }
    }

    public function user()
    {
        if (!$this->hasTable('user')) {
            $table = $this->table('user', [
                'id'          => false,
                'comment'     => '会员表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('group_id', 'integer', ['comment' => '分组ID', 'default' => 0, 'signed' => false])
                ->addColumn('username', 'string', ['limit' => 32, 'default' => '', 'comment' => '用户名'])
                ->addColumn('nickname', 'string', ['limit' => 50, 'default' => '', 'comment' => '昵称'])
                ->addColumn('email', 'string', ['limit' => 50, 'default' => '', 'comment' => '邮箱'])
                ->addColumn('mobile', 'string', ['limit' => 11, 'default' => '', 'comment' => '手机'])
                ->addColumn('avatar', 'string', ['limit' => 255, 'default' => '', 'comment' => '头像'])
                ->addColumn('gender', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '性别:0=未知,1=男,2=女'])
                ->addColumn('birthday', 'date', ['null' => true, 'default' => null, 'comment' => '生日'])
                ->addColumn('money', 'integer', ['comment' => '余额', 'default' => 0, 'signed' => false])
                ->addColumn('score', 'integer', ['comment' => '积分', 'default' => 0, 'signed' => false])
                ->addColumn('lastlogintime', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '上次登录时间'])
                ->addColumn('lastloginip', 'string', ['limit' => 50, 'default' => '', 'comment' => '上次登录IP'])
                ->addColumn('loginfailure', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '登录失败次数'])
                ->addColumn('joinip', 'string', ['limit' => 50, 'default' => '', 'comment' => '加入IP'])
                ->addColumn('jointime', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '加入时间'])
                ->addColumn('motto', 'string', ['limit' => 255, 'default' => '', 'comment' => '签名'])
                ->addColumn('password', 'string', ['limit' => 32, 'default' => '', 'comment' => '密码'])
                ->addColumn('salt', 'string', ['limit' => 30, 'default' => '', 'comment' => '密码盐'])
                ->addColumn('status', 'string', ['limit' => 30, 'default' => '', 'comment' => '状态'])
                ->addColumn('updatetime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addColumn('createtime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['username'], [
                    'unique' => true,
                ])
                ->addIndex(['email'], [
                    'unique' => true,
                ])
                ->addIndex(['mobile'], [
                    'unique' => true,
                ])
                ->create();
        }
    }

    public function userGroup()
    {
        if (!$this->hasTable('user_group')) {
            $table = $this->table('user_group', [
                'id'          => false,
                'comment'     => '会员组表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '组名'])
                ->addColumn('rules', 'text', ['null' => true, 'default' => null, 'comment' => '权限节点'])
                ->addColumn('status', 'enum', ['values' => '0,1', 'default' => '1', 'comment' => '状态:0=禁用,1=启用'])
                ->addColumn('updatetime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addColumn('createtime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->create();
        }
    }

    public function userMoneyLog()
    {
        if (!$this->hasTable('user_money_log')) {
            $table = $this->table('user_money_log', [
                'id'          => false,
                'comment'     => '会员余额变动表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('user_id', 'integer', ['comment' => '会员ID', 'default' => 0, 'signed' => false])
                ->addColumn('money', 'integer', ['comment' => '变更余额', 'default' => 0])
                ->addColumn('before', 'integer', ['comment' => '变更前余额', 'default' => 0])
                ->addColumn('after', 'integer', ['comment' => '变更后余额', 'default' => 0])
                ->addColumn('memo', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])
                ->addColumn('createtime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->create();
        }
    }

    public function userRule()
    {
        if (!$this->hasTable('user_rule')) {
            $table = $this->table('user_rule', [
                'id'          => false,
                'comment'     => '会员菜单权限规则表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('pid', 'integer', ['comment' => '上级菜单', 'default' => 0, 'signed' => false])
                ->addColumn('type', 'enum', ['values' => 'route,menu_dir,menu,nav_user_menu,nav,button', 'default' => 'menu', 'comment' => '类型:route=路由,menu_dir=菜单目录,menu=菜单项,nav_user_menu=顶栏会员菜单下拉项,nav=顶栏菜单项,button=页面按钮'])
                ->addColumn('title', 'string', ['limit' => 50, 'default' => '', 'comment' => '标题'])
                ->addColumn('name', 'string', ['limit' => 50, 'default' => '', 'comment' => '规则名称'])
                ->addColumn('path', 'string', ['limit' => 100, 'default' => '', 'comment' => '路由路径'])
                ->addColumn('icon', 'string', ['limit' => 50, 'default' => '', 'comment' => '图标'])
                ->addColumn('menu_type', 'enum', ['values' => 'tab,link,iframe', 'default' => 'tab', 'comment' => '菜单类型:tab=选项卡,link=链接,iframe=Iframe'])
                ->addColumn('url', 'string', ['limit' => 255, 'default' => '', 'comment' => 'Url'])
                ->addColumn('component', 'string', ['limit' => 100, 'default' => '', 'comment' => '组件路径'])
                ->addColumn('no_login_valid', 'integer', ['signed' => false, 'limit' => MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '未登录有效:0=否,1=是'])
                ->addColumn('extend', 'enum', ['values' => 'none,add_rules_only,add_menu_only', 'default' => 'none', 'comment' => '扩展属性:none=无,add_rules_only=只添加为路由,add_menu_only=只添加为菜单'])
                ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])
                ->addColumn('weigh', 'integer', ['comment' => '权重', 'default' => 0])
                ->addColumn('status', 'enum', ['values' => '0,1', 'default' => '1', 'comment' => '状态:0=禁用,1=启用'])
                ->addColumn('updatetime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addColumn('createtime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['pid'], [
                    'type' => 'BTREE',
                ])
                ->create();
        }
    }

    public function userScoreLog()
    {
        if (!$this->hasTable('user_score_log')) {
            $table = $this->table('user_score_log', [
                'id'          => false,
                'comment'     => '会员积分变动表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('user_id', 'integer', ['comment' => '会员ID', 'default' => 0, 'signed' => false])
                ->addColumn('score', 'integer', ['comment' => '变更积分', 'default' => 0])
                ->addColumn('before', 'integer', ['comment' => '变更前积分', 'default' => 0])
                ->addColumn('after', 'integer', ['comment' => '变更后积分', 'default' => 0])
                ->addColumn('memo', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])
                ->addColumn('createtime', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->create();
        }
    }

    public function crudLog()
    {
        if (!$this->hasTable('crud_log')) {
            $table = $this->table('crud_log', [
                'id'          => false,
                'comment'     => 'CRUD记录表',
                'row_format'  => 'DYNAMIC',
                'primary_key' => 'id',
                'collation'   => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true])
                ->addColumn('table_name', 'string', ['limit' => 200, 'default' => '', 'comment' => '数据表名'])
                ->addColumn('table', 'text', ['null' => true, 'default' => null, 'comment' => '数据表数据'])
                ->addColumn('fields', 'text', ['null' => true, 'default' => null, 'comment' => '字段数据'])
                ->addColumn('status', 'enum', ['values' => 'delete,success,error,start', 'default' => 'start', 'comment' => '状态:delete=已删除,success=成功,error=失败,start=生成中'])
                ->addColumn('create_time', 'biginteger', ['signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->create();
        }
    }
}
