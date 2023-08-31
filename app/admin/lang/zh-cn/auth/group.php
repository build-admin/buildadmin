<?php
return [
    'Super administrator'                                                                                                 => '超级管理员',
    'No permission'                                                                                                       => '无权限',
    'You cannot modify your own management group!'                                                                        => '不能修改自己所在的管理组！',
    'You need to have all permissions of this group to operate this group~'                                               => '您需要拥有该分组的所有权限才可以操作该分组~',
    'You need to have all the permissions of the group and have additional permissions before you can operate the group~' => '您需要拥有该分组的所有权限且还有额外权限时，才可以操作该分组~',
    'Role group has all your rights, please contact the upper administrator to add or do not need to add!'                => '角色组拥有您的全部权限，请联系上级管理员添加或无需添加！',
    'Remark lang'                                                                                                         => '为保障系统安全，角色组本身的上下级关系仅供参考，系统的实际上下级划分是根据`权限多寡`来确定的，两位管理员的权限节点：相同被认为是`同级`、包含且有额外权限才被认为是`上级`，同级不可管理同级，上级可为下级分配自己拥有的权限节点；若有特殊情况管理员需转`上级`，可建立一个虚拟权限节点',
];
