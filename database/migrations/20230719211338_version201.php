<?php

use think\migration\Migrator;

class Version201 extends Migrator
{
    public function up()
    {
        parent::up();
        $user = $this->table('user');
        if ($user->hasIndex('email')) {
            $user->removeIndexByName('email')
                ->removeIndexByName('mobile')
                ->update();
        }
    }
}
