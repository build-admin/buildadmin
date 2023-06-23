<?php

use think\migration\Migrator;

class Version200 extends Migrator
{
    public function up()
    {
        parent::up();
        $admin = $this->table('admin');
        if ($admin->hasColumn('loginfailure')) {
            $admin->renameColumn('loginfailure', 'login_failure')
                ->renameColumn('lastlogintime', 'last_login_time')
                ->renameColumn('lastloginip', 'last_login_ip')
                ->renameColumn('updatetime', 'update_time')
                ->renameColumn('createtime', 'create_time')
                ->changeColumn('update_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->changeColumn('create_time', 'biginteger', ['after' => 'update_time', 'limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }

        $adminGroup = $this->table('admin_group');
        if ($adminGroup->hasColumn('updatetime')) {
            $adminGroup->renameColumn('updatetime', 'update_time')
                ->renameColumn('createtime', 'create_time')
                ->changeColumn('update_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }

        $adminLog = $this->table('admin_log');
        if ($adminLog->hasColumn('createtime')) {
            $adminLog->renameColumn('createtime', 'create_time')
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }

        $attachment = $this->table('attachment');
        if ($attachment->hasColumn('createtime')) {
            $attachment->renameColumn('createtime', 'create_time')
                ->renameColumn('lastuploadtime', 'last_upload_time')
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->changeColumn('last_upload_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '最后上传时间'])
                ->save();
        }

        $captcha = $this->table('captcha');
        if ($captcha->hasColumn('createtime')) {
            $captcha->renameColumn('createtime', 'create_time')
                ->renameColumn('expiretime', 'expire_time')
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->changeColumn('expire_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '过期时间'])
                ->save();
        }

        $menuRule = $this->table('menu_rule');
        if ($menuRule->hasColumn('updatetime')) {
            $menuRule->renameColumn('updatetime', 'update_time')
                ->renameColumn('createtime', 'create_time')
                ->changeColumn('update_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }

        $securityDataRecycle = $this->table('security_data_recycle');
        if ($securityDataRecycle->hasColumn('updatetime')) {
            $securityDataRecycle->renameColumn('updatetime', 'update_time')
                ->renameColumn('createtime', 'create_time')
                ->changeColumn('update_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }

        $securityDataRecycleLog = $this->table('security_data_recycle_log');
        if ($securityDataRecycleLog->hasColumn('createtime')) {
            $securityDataRecycleLog->renameColumn('createtime', 'create_time')
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }

        $securitySensitiveData = $this->table('security_sensitive_data');
        if ($securitySensitiveData->hasColumn('updatetime')) {
            $securitySensitiveData->renameColumn('updatetime', 'update_time')
                ->renameColumn('createtime', 'create_time')
                ->changeColumn('update_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }

        $securitySensitiveDataLog = $this->table('security_sensitive_data_log');
        if ($securitySensitiveDataLog->hasColumn('createtime')) {
            $securitySensitiveDataLog->renameColumn('createtime', 'create_time')
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }

        $token = $this->table('token');
        if ($token->hasColumn('createtime')) {
            $token->renameColumn('createtime', 'create_time')
                ->renameColumn('expiretime', 'expire_time')
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->changeColumn('expire_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '过期时间'])
                ->save();
        }

        $userGroup = $this->table('user_group');
        if ($userGroup->hasColumn('createtime')) {
            $userGroup->renameColumn('updatetime', 'update_time')
                ->renameColumn('createtime', 'create_time')
                ->changeColumn('update_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }

        $userMoneyLog = $this->table('user_money_log');
        if ($userMoneyLog->hasColumn('createtime')) {
            $userMoneyLog->renameColumn('createtime', 'create_time')
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }

        $userRule = $this->table('user_rule');
        if ($userRule->hasColumn('createtime')) {
            $userRule->renameColumn('updatetime', 'update_time')
                ->renameColumn('createtime', 'create_time')
                ->changeColumn('update_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }

        $userScoreLog = $this->table('user_score_log');
        if ($userScoreLog->hasColumn('createtime')) {
            $userScoreLog->renameColumn('createtime', 'create_time')
                ->changeColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }

        $user = $this->table('user');
        if ($user->hasColumn('loginfailure')) {
            $user->renameColumn('lastlogintime', 'last_login_time')
                ->renameColumn('lastloginip', 'last_login_ip')
                ->renameColumn('loginfailure', 'login_failure')
                ->renameColumn('joinip', 'join_ip')
                ->renameColumn('jointime', 'join_time')
                ->renameColumn('updatetime', 'update_time')
                ->renameColumn('createtime', 'create_time')
                ->changeColumn('update_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->changeColumn('create_time', 'biginteger', ['after' => 'update_time', 'limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->save();
        }
    }
}
