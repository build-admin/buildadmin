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
