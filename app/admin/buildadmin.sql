/*
 BuildAdmin Install SQL
 Date: 2022-05-13
*/

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for `__PREFIX__admin`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin`;
CREATE TABLE `__PREFIX__admin` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `username` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '用户名',
    `nickname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '昵称',
    `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '头像',
    `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '邮箱',
    `mobile` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '手机',
    `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '登录失败次数',
    `lastlogintime` int(10) DEFAULT NULL COMMENT '登录时间',
    `lastloginip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '登录IP',
    `password` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '密码',
    `salt` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '密码盐',
    `motto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '签名',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员表';

-- ----------------------------
-- Records of __PREFIX__admin
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__admin` VALUES ('1', 'admin', 'Admin', '', 'admin@buildadmin.com', '18888888888', '0', '1652166427', '127.0.0.1', 'dc82034ba4108148cbefd980b6b63371', 'kWlGDm9qAVB8MjbX', '', '1645876529', '1652166427', '1');
COMMIT;

-- ----------------------------
-- Table structure for `__PREFIX__admin_group`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin_group`;
CREATE TABLE `__PREFIX__admin_group` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分组',
    `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '组名',
    `rules` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '权限规则ID',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理分组表';

-- ----------------------------
-- Records of __PREFIX__admin_group
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__admin_group` VALUES ('1', '0', '超级管理组', '*', '1645876529', '1647805864', '1');
INSERT INTO `__PREFIX__admin_group` VALUES ('2', '1', '一级管理员', '1,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,77,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76', '1645876529', '1658197123', '1');
INSERT INTO `__PREFIX__admin_group` VALUES ('3', '2', '二级管理员', '21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43', '1645876529', '1658197143', '1');
INSERT INTO `__PREFIX__admin_group` VALUES ('4', '3', '三级管理员', '55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75', '1645876529', '1658197162', '1');
COMMIT;

-- ----------------------------
-- Table structure for `__PREFIX__admin_group_access`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin_group_access`;
CREATE TABLE `__PREFIX__admin_group_access` (
    `uid` int(10) unsigned NOT NULL COMMENT '管理员ID',
    `group_id` int(10) unsigned NOT NULL COMMENT '分组ID',
    UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
    KEY `uid` (`uid`),
    KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理权限分组表';

-- ----------------------------
-- Records of __PREFIX__admin_group_access
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__admin_group_access` VALUES ('1', '1');
COMMIT;

-- ----------------------------
-- Table structure for `__PREFIX__admin_log`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin_log`;
CREATE TABLE `__PREFIX__admin_log` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
    `username` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '管理员用户名',
    `url` varchar(1500) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '操作Url',
    `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '日志标题',
    `data` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求数据',
    `ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'IP',
    `useragent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'User-Agent',
    `createtime` int(10) DEFAULT NULL COMMENT '操作时间',
    PRIMARY KEY (`id`),
    KEY `name` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员日志表';

-- ----------------------------
-- Table structure for `__PREFIX__area`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__area`;
CREATE TABLE `__PREFIX__area` (
    `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `pid` int(10) DEFAULT NULL COMMENT '父id',
    `shortname` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '简称',
    `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '名称',
    `mergename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '全称',
    `level` tinyint(4) DEFAULT NULL COMMENT '层级:1=省,2=市,3=区/县',
    `pinyin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '拼音',
    `code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '长途区号',
    `zip` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮编',
    `first` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '首字母',
    `lng` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '经度',
    `lat` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '纬度',
    PRIMARY KEY (`id`),
    KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='地区表';

-- ----------------------------
-- Table structure for `__PREFIX__attachment`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__attachment`;
CREATE TABLE `__PREFIX__attachment` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `topic` varchar(20) NOT NULL DEFAULT '' COMMENT '细目',
    `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传管理员ID',
    `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传用户ID',
    `url` varchar(255) NOT NULL DEFAULT '' COMMENT '物理路径',
    `width` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '宽度',
    `height` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '高度',
    `name` varchar(100) NOT NULL DEFAULT '' COMMENT '原始名称',
    `size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '大小',
    `mimetype` varchar(100) NOT NULL DEFAULT '' COMMENT 'mime类型',
    `quote` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传(引用)次数',
    `storage` varchar(50) NOT NULL DEFAULT '' COMMENT '存储方式',
    `sha1` varchar(40) NOT NULL DEFAULT '' COMMENT 'sha1编码',
    `createtime` int(10) unsigned DEFAULT NULL COMMENT '上传时间',
    `lastuploadtime` int(10) unsigned DEFAULT NULL COMMENT '最后上传时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='附件表';

-- ----------------------------
-- Table structure for `__PREFIX__captcha`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__captcha`;
CREATE TABLE `__PREFIX__captcha` (
    `key` varchar(32) NOT NULL DEFAULT '' COMMENT '验证码Key',
    `code` varchar(32) NOT NULL DEFAULT '' COMMENT '验证码(加密后的,用于验证)',
    `captcha` varchar(6) NOT NULL DEFAULT '' COMMENT '验证码(供UniApp安卓二次生成图片)',
    `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
    `expiretime` int(10) unsigned DEFAULT NULL COMMENT '过期时间',
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='验证码表';

-- ----------------------------
-- Table structure for `__PREFIX__config`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__config`;
CREATE TABLE `__PREFIX__config` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '变量名',
    `group` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '分组',
    `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '变量标题',
    `tip` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '变量描述',
    `type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '类型:string,number,radio,checkbox,switch,textarea,array,datetime,date,select,selects',
    `value` text COLLATE utf8mb4_unicode_ci COMMENT '变量值',
    `content` text COLLATE utf8mb4_unicode_ci COMMENT '字典数据',
    `rule` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '验证规则',
    `extend` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '扩展属性',
    `allow_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '允许删除:0=否,1=是',
    `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置';

-- ----------------------------
-- Records of __PREFIX__config
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__config` VALUES ('1', 'config_group', 'basics', 'Config group', '', 'array', '[{\"key\":\"basics\",\"value\":\"Basics\"},{\"key\":\"mail\",\"value\":\"Mail\"},{\"key\":\"config_quick_entrance\",\"value\":\"Config Quick entrance\"}]', null, 'required', '', '0', '-1');
INSERT INTO `__PREFIX__config` VALUES ('2', 'site_name', 'basics', 'Site Name', '站点名称', 'string', '站点名称', null, 'required', '', '0', '99');
INSERT INTO `__PREFIX__config` VALUES ('3', 'record_number', 'basics', 'Record number', '域名备案号', 'string', '渝ICP备8888888号-1', null, '', '', '0', '0');
INSERT INTO `__PREFIX__config` VALUES ('4', 'version', 'basics', 'Version number', '系统版本号', 'string', 'v1.0.0', null, 'required', '', '0', '0');
INSERT INTO `__PREFIX__config` VALUES ('5', 'time_zone', 'basics', 'time zone', '', 'string', 'Asia/Shanghai', null, 'required', '', '0', '0');
INSERT INTO `__PREFIX__config` VALUES ('6', 'no_access_ip', 'basics', 'No access ip', '禁止访问站点的ip列表,一行一个', 'textarea', '', null, '', '', '0', '0');
INSERT INTO `__PREFIX__config` VALUES ('7', 'smtp_server', 'mail', 'smtp server', '', 'string', 'smtp.qq.com', null, '', '', '0', '99');
INSERT INTO `__PREFIX__config` VALUES ('8', 'smtp_port', 'mail', 'smtp port', '', 'string', '465', null, '', '', '0', '0');
INSERT INTO `__PREFIX__config` VALUES ('9', 'smtp_user', 'mail', 'smtp user', '', 'string', '', null, '', '', '0', '0');
INSERT INTO `__PREFIX__config` VALUES ('10', 'smtp_pass', 'mail', 'smtp pass', '', 'string', '', null, '', '', '0', '0');
INSERT INTO `__PREFIX__config` VALUES ('11', 'smtp_verification', 'mail', 'smtp verification', '', 'select', 'SSL', '{\"SSL\":\"SSL\",\"TLS\":\"TLS\"}', '', '', '0', '0');
INSERT INTO `__PREFIX__config` VALUES ('12', 'smtp_sender_mail', 'mail', 'smtp sender mail', '', 'string', '', null, 'email', '', '0', '0');
INSERT INTO `__PREFIX__config` VALUES ('13', 'config_quick_entrance', 'config_quick_entrance', 'Config Quick entrance', '', 'array', '[{\"key\":\"\\u6570\\u636e\\u56de\\u6536\\u89c4\\u5219\\u914d\\u7f6e\",\"value\":\"\\/admin\\/security\\/dataRecycle\"},{\"key\":\"\\u654f\\u611f\\u6570\\u636e\\u89c4\\u5219\\u914d\\u7f6e\",\"value\":\"\\/admin\\/security\\/sensitiveData\"}]', null, '', '', '0', '0');
COMMIT;

-- ----------------------------
-- Table structure for `__PREFIX__menu_rule`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__menu_rule`;
CREATE TABLE `__PREFIX__menu_rule` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级菜单',
    `type` enum('menu_dir','menu','button') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menu' COMMENT '类型:menu_dir=菜单目录,menu=菜单项,button=页面按钮',
    `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
    `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
    `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路由路径',
    `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
    `menu_type` enum('tab','link','iframe') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '菜单类型:tab=选项卡,link=链接,iframe=Iframe',
    `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Url',
    `component` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组件路径',
    `keepalive` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '缓存:0=关闭,1=开启',
    `extend` enum('none','add_rules_only','add_menu_only') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT '扩展属性:none=无,add_rules_only=只添加为路由,add_menu_only=只添加为菜单',
    `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
    `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重(排序)',
    `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `pid` (`pid`),
    KEY `weigh` (`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='菜单和权限规则表';

-- ----------------------------
-- Records of __PREFIX__menu_rule
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__menu_rule` VALUES ('1', '0', 'menu', '控制台', 'dashboard/dashboard', 'dashboard', 'fa fa-dashboard', 'tab', '', '/src/views/backend/dashboard.vue', '1', 'none', 'remark_text', '999', '1', '1651926966', '1646889188');
INSERT INTO `__PREFIX__menu_rule` VALUES ('2', '0', 'menu_dir', '权限管理', 'auth', 'auth', 'fa fa-group', null, '', '', '0', 'none', '', '100', '1', '1648948034', '1645876529');
INSERT INTO `__PREFIX__menu_rule` VALUES ('3', '2', 'menu', '角色组管理', 'auth/group', 'auth/group', 'fa fa-group', 'tab', '', '/src/views/backend/auth/group/index.vue', '1', 'none', '', '99', '1', '1648162157', '1646927597');
INSERT INTO `__PREFIX__menu_rule` VALUES ('4', '3', 'button', '查看', 'auth/group/index', '', '', null, '', '', '0', 'none', '', '99', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('5', '3', 'button', '添加', 'auth/group/add', '', '', null, '', '', '0', 'none', '', '99', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('6', '3', 'button', '编辑', 'auth/group/edit', '', '', null, '', '', '0', 'none', '', '99', '1', '1648065864', '1647806129');
INSERT INTO `__PREFIX__menu_rule` VALUES ('7', '3', 'button', '删除', 'auth/group/del', '', '', null, '', '', '0', 'none', '', '99', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('8', '2', 'menu', '管理员管理', 'auth/admin', 'auth/admin', 'el-icon-UserFilled', 'tab', '', '/src/views/backend/auth/admin/index.vue', '1', 'none', '', '98', '1', '1648067239', '1647549566');
INSERT INTO `__PREFIX__menu_rule` VALUES ('9', '8', 'button', '查看', 'auth/admin/index', '', '', null, '', '', '0', 'none', '', '98', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('10', '8', 'button', '添加', 'auth/admin/add', '', '', null, '', '', '0', 'none', '', '98', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('11', '8', 'button', '编辑', 'auth/admin/edit', '', '', null, '', '', '0', 'none', '', '98', '1', '1648065864', '1647806129');
INSERT INTO `__PREFIX__menu_rule` VALUES ('12', '8', 'button', '删除', 'auth/admin/del', '', '', null, '', '', '0', 'none', '', '98', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('13', '2', 'menu', '菜单规则管理', 'auth/menu', 'auth/menu', 'el-icon-Grid', 'tab', '', '/src/views/backend/auth/menu/index.vue', '1', 'none', '', '97', '1', '1648133759', '1645876529');
INSERT INTO `__PREFIX__menu_rule` VALUES ('14', '13', 'button', '查看', 'auth/menu/index', '', '', null, '', '', '0', 'none', '', '97', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('15', '13', 'button', '添加', 'auth/menu/add', '', '', null, '', '', '0', 'none', '', '97', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('16', '13', 'button', '编辑', 'auth/menu/edit', '', '', null, '', '', '0', 'none', '', '97', '1', '1648065864', '1647806129');
INSERT INTO `__PREFIX__menu_rule` VALUES ('17', '13', 'button', '删除', 'auth/menu/del', '', '', null, '', '', '0', 'none', '', '97', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('18', '13', 'button', '快速排序', 'auth/menu/sortable', '', '', null, '', '', '0', 'none', '', '97', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('19', '2', 'menu', '管理员日志管理', 'auth/adminLog', 'auth/adminLog', 'el-icon-List', 'tab', '', '/src/views/backend/auth/adminLog/index.vue', '1', 'none', '', '96', '1', '1648067241', '1647963918');
INSERT INTO `__PREFIX__menu_rule` VALUES ('20', '19', 'button', '查看', 'auth/adminLog/index', '', '', null, '', '', '0', 'none', '', '96', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('21', '0', 'menu_dir', '会员管理', 'user', 'user', 'fa fa-drivers-license', null, '', '', '0', 'none', '', '95', '1', '1648947448', '1648049553');
INSERT INTO `__PREFIX__menu_rule` VALUES ('22', '21', 'menu', '会员管理', 'user/user', 'user/user', 'fa fa-user', 'tab', '', '/src/views/backend/user/user/index.vue', '1', 'none', '', '94', '1', '1648255019', '1648049712');
INSERT INTO `__PREFIX__menu_rule` VALUES ('23', '22', 'button', '查看', 'user/user/index', '', '', null, '', '', '0', 'none', '', '94', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('24', '22', 'button', '添加', 'user/user/add', '', '', null, '', '', '0', 'none', '', '94', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('25', '22', 'button', '编辑', 'user/user/edit', '', '', null, '', '', '0', 'none', '', '94', '1', '1648065864', '1647806129');
INSERT INTO `__PREFIX__menu_rule` VALUES ('26', '22', 'button', '删除', 'user/user/del', '', '', null, '', '', '0', 'none', '', '94', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('27', '21', 'menu', '会员分组管理', 'user/group', 'user/group', 'fa fa-group', 'tab', '', '/src/views/backend/user/group/index.vue', '1', 'none', '', '93', '1', '1648067248', '1648051141');
INSERT INTO `__PREFIX__menu_rule` VALUES ('28', '27', 'button', '查看', 'user/group/index', '', '', null, '', '', '0', 'none', '', '93', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('29', '27', 'button', '添加', 'user/group/add', '', '', null, '', '', '0', 'none', '', '93', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('30', '27', 'button', '编辑', 'user/group/edit', '', '', null, '', '', '0', 'none', '', '93', '1', '1648065864', '1647806129');
INSERT INTO `__PREFIX__menu_rule` VALUES ('31', '27', 'button', '删除', 'user/group/del', '', '', null, '', '', '0', 'none', '', '93', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('32', '21', 'menu', '会员规则管理', 'user/rule', 'user/rule', 'fa fa-th-list', 'tab', '', '/src/views/backend/user/rule/index.vue', '1', 'none', '', '92', '1', '1648067247', '1648051207');
INSERT INTO `__PREFIX__menu_rule` VALUES ('33', '32', 'button', '查看', 'user/rule/index', '', '', null, '', '', '0', 'none', '', '92', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('34', '32', 'button', '添加', 'user/rule/add', '', '', null, '', '', '0', 'none', '', '92', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('35', '32', 'button', '编辑', 'user/rule/edit', '', '', null, '', '', '0', 'none', '', '92', '1', '1648065864', '1647806129');
INSERT INTO `__PREFIX__menu_rule` VALUES ('36', '32', 'button', '删除', 'user/rule/del', '', '', null, '', '', '0', 'none', '', '92', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('37', '32', 'button', '快速排序', 'user/rule/sortable', '', '', null, '', '', '0', 'none', '', '92', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('38', '21', 'menu', '会员余额管理', 'user/moneyLog', 'user/moneyLog', 'el-icon-Money', 'tab', '', '/src/views/backend/user/moneyLog/index.vue', '0', 'none', '', '91', '1', '1648437356', '1648052587');
INSERT INTO `__PREFIX__menu_rule` VALUES ('39', '38', 'button', '查看', 'user/moneyLog/index', '', '', null, '', '', '0', 'none', '', '91', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('40', '38', 'button', '添加', 'user/moneyLog/add', '', '', null, '', '', '0', 'none', '', '91', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('41', '21', 'menu', '会员积分管理', 'user/scoreLog', 'user/scoreLog', 'el-icon-Discount', 'tab', '', '/src/views/backend/user/scoreLog/index.vue', '1', 'none', '', '90', '1', '1648067246', '1648052689');
INSERT INTO `__PREFIX__menu_rule` VALUES ('42', '41', 'button', '查看', 'user/scoreLog/index', '', '', null, '', '', '0', 'none', '', '90', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('43', '41', 'button', '添加', 'user/scoreLog/add', '', '', null, '', '', '0', 'none', '', '90', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('44', '0', 'menu_dir', '常规管理', 'routine', 'routine', 'fa fa-cogs', null, '', '', '0', 'none', '', '89', '1', '1648133739', '1645876529');
INSERT INTO `__PREFIX__menu_rule` VALUES ('45', '44', 'menu', '系统配置', 'routine/config', 'routine/config', 'el-icon-Tools', 'tab', '', '/src/views/backend/routine/config/index.vue', '1', 'none', '', '88', '1', '1648781089', '1648053389');
INSERT INTO `__PREFIX__menu_rule` VALUES ('46', '45', 'button', '查看', 'routine/config/index', '', '', null, '', '', '0', 'none', '', '88', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('47', '45', 'button', '编辑', 'routine/config/edit', '', '', null, '', '', '0', 'none', '', '88', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('48', '44', 'menu', '附件管理', 'routine/attachment', 'routine/attachment', 'fa fa-folder', 'tab', '', '/src/views/backend/routine/attachment/index.vue', '1', 'none', 'remark_text', '87', '1', '1648067228', '1647105410');
INSERT INTO `__PREFIX__menu_rule` VALUES ('49', '48', 'button', '查看', 'routine/attachment/index', '', '', null, '', '', '0', 'none', '', '87', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('50', '48', 'button', '编辑', 'routine/attachment/edit', '', '', null, '', '', '0', 'none', '', '87', '1', '1648065864', '1647806129');
INSERT INTO `__PREFIX__menu_rule` VALUES ('51', '48', 'button', '删除', 'routine/attachment/del', '', '', null, '', '', '0', 'none', '', '87', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('52', '44', 'menu', '个人资料', 'routine/adminInfo', 'routine/adminInfo', 'fa fa-user', 'tab', '', '/src/views/backend/routine/adminInfo.vue', '1', 'none', '', '86', '1', '1648067229', '1645876529');
INSERT INTO `__PREFIX__menu_rule` VALUES ('53', '52', 'button', '查看', 'routine/adminInfo/index', '', '', null, '', '', '0', 'none', '', '86', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('54', '52', 'button', '编辑', 'routine/adminInfo/edit', '', '', null, '', '', '0', 'none', '', '86', '1', '1648065864', '1647806129');
INSERT INTO `__PREFIX__menu_rule` VALUES ('55', '0', 'menu_dir', '数据安全管理', 'security', 'security', 'fa fa-shield', null, '', '', '0', 'none', '', '85', '1', '1649853629', '1648948025');
INSERT INTO `__PREFIX__menu_rule` VALUES ('56', '55', 'menu', '数据回收站', 'security/dataRecycleLog', 'security/dataRecycleLog', 'fa fa-database', 'tab', '', '/src/views/backend/security/dataRecycleLog/index.vue', '1', 'none', '', '84', '1', '1651603319', '1648948283');
INSERT INTO `__PREFIX__menu_rule` VALUES ('57', '56', 'button', '查看', 'security/dataRecycleLog/index', '', '', null, '', '', '0', 'none', '', '84', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('58', '56', 'button', '删除', 'security/dataRecycleLog/del', '', '', null, '', '', '0', 'none', '', '84', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('59', '56', 'button', '还原', 'security/dataRecycleLog/restore', '', '', null, '', '', '0', 'none', '', '84', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('60', '56', 'button', '查看详情', 'security/dataRecycleLog/info', '', '', null, '', '', '0', 'none', '', '84', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('61', '55', 'menu', '敏感数据修改记录', 'security/sensitiveDataLog', 'security/sensitiveDataLog', 'fa fa-expeditedssl', 'tab', '', '/src/views/backend/security/sensitiveDataLog/index.vue', '1', 'none', '', '83', '1', '1649112262', '1649059604');
INSERT INTO `__PREFIX__menu_rule` VALUES ('62', '61', 'button', '查看', 'security/sensitiveDataLog/index', '', '', null, '', '', '0', 'none', '', '83', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('63', '61', 'button', '删除', 'security/sensitiveDataLog/del', '', '', null, '', '', '0', 'none', '', '83', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('64', '61', 'button', '回滚', 'security/sensitiveDataLog/rollback', '', '', null, '', '', '0', 'none', '', '83', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('65', '61', 'button', '查看详情', 'security/sensitiveDataLog/info', '', '', null, '', '', '0', 'none', '', '83', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('66', '55', 'menu', '数据回收规则管理', 'security/dataRecycle', 'security/dataRecycle', 'fa fa-database', 'tab', '', '/src/views/backend/security/dataRecycle/index.vue', '1', 'none', '在此定义需要回收的数据，实现数据自动统一回收', '82', '1', '1651603319', '1648948215');
INSERT INTO `__PREFIX__menu_rule` VALUES ('67', '66', 'button', '查看', 'security/dataRecycle/index', '', '', null, '', '', '0', 'none', '', '82', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('68', '66', 'button', '添加', 'security/dataRecycle/add', '', '', null, '', '', '0', 'none', '', '82', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('69', '66', 'button', '编辑', 'security/dataRecycle/edit', '', '', null, '', '', '0', 'none', '', '82', '1', '1648065864', '1647806129');
INSERT INTO `__PREFIX__menu_rule` VALUES ('70', '66', 'button', '删除', 'security/dataRecycle/del', '', '', null, '', '', '0', 'none', '', '82', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('71', '55', 'menu', '敏感字段规则管理', 'security/sensitiveData', 'security/sensitiveData', 'fa fa-expeditedssl', 'tab', '', '/src/views/backend/security/sensitiveData/index.vue', '1', 'none', '在此定义需要保护的敏感字段，随后系统将自动监听该字段的修改操作，并提供了敏感字段的修改回滚功能', '81', '1', '1649112263', '1649005119');
INSERT INTO `__PREFIX__menu_rule` VALUES ('72', '71', 'button', '查看', 'security/sensitiveData/index', '', '', null, '', '', '0', 'none', '', '81', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('73', '71', 'button', '添加', 'security/sensitiveData/add', '', '', null, '', '', '0', 'none', '', '81', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('74', '71', 'button', '编辑', 'security/sensitiveData/edit', '', '', null, '', '', '0', 'none', '', '81', '1', '1648065864', '1647806129');
INSERT INTO `__PREFIX__menu_rule` VALUES ('75', '71', 'button', '删除', 'security/sensitiveData/del', '', '', null, '', '', '0', 'none', '', '81', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('76', '0', 'menu', 'BuildAdmin', 'buildadmin/buildadmin', 'buildadmin', 'local-logo', 'link', 'https://doc.buildadmin.com', '', '0', 'none', '', '0', '0', '1651926977', '1648947396');
INSERT INTO `__PREFIX__menu_rule` VALUES ('77', '45', 'button', '添加', 'routine/config/add', '', '', null, '', '', '0', 'none', '', '88', '1', '1655375826', '1655375812');
INSERT INTO `__PREFIX__menu_rule` VALUES ('78', '0', 'menu', '模块市场', 'moduleStore/moduleStore', 'moduleStore', 'el-icon-GoodsFilled', 'tab', '', '/src/views/backend/module/index.vue', '1', 'none', '', '86', '1', '1661317584', '1661317424');
INSERT INTO `__PREFIX__menu_rule` VALUES ('79', '78', 'button', '查看', 'moduleStore/moduleStore/index', '', '', null, '', '', '0', 'none', '', '1', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('80', '78', 'button', '安装', 'moduleStore/moduleStore/install', '', '', null, '', '', '0', 'none', '', '2', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('81', '78', 'button', '调整状态', 'moduleStore/moduleStore/changeState', '', '', null, '', '', '0', 'none', '', '3', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('82', '78', 'button', '卸载', 'moduleStore/moduleStore/uninstall', '', '', null, '', '', '0', 'none', '', '4', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('83', '78', 'button', '更新', 'moduleStore/moduleStore/update', '', '', null, '', '', '0', 'none', '', '5', '1', '1648065864', '1647806112');
INSERT INTO `__PREFIX__menu_rule` VALUES ('84', '0', 'menu', 'CRUD代码生成', 'crud/crud', 'crud/crud', 'fa fa-code', 'tab', '', '/src/views/backend/crud/index.vue', '1', 'none', '', '80', '1', '1668848266', '1668848266');
INSERT INTO `__PREFIX__menu_rule` VALUES ('85', '84', 'button', '查看', 'crud/crud/index', '', '', null, '', '', '0', 'none', '', '3', '1', '1668848809', '1668848770');
INSERT INTO `__PREFIX__menu_rule` VALUES ('86', '84', 'button', '生成', 'crud/crud/generate', '', '', null, '', '', '0', 'none', '', '2', '1', '1668848809', '1668848770');
INSERT INTO `__PREFIX__menu_rule` VALUES ('87', '84', 'button', '删除', 'crud/crud/delete', '', '', null, '', '', '0', 'none', '', '1', '1', '1668848921', '1668848921');
COMMIT;

-- ----------------------------
-- Table structure for `__PREFIX__security_data_recycle`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__security_data_recycle`;
CREATE TABLE `__PREFIX__security_data_recycle` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` varchar(50) NOT NULL DEFAULT '' COMMENT '规则名称',
    `controller` varchar(100) NOT NULL DEFAULT '' COMMENT '控制器',
    `controller_as` varchar(100) NOT NULL DEFAULT '' COMMENT '控制器别名',
    `data_table` varchar(100) NOT NULL DEFAULT '' COMMENT '对应数据表',
    `primary_key` varchar(50) NOT NULL DEFAULT '' COMMENT '数据表主键',
    `status` enum('1','0') NOT NULL DEFAULT '0' COMMENT '状态:0=禁用,1=启用',
    `updatetime` int(10) unsigned DEFAULT NULL COMMENT '修改时间',
    `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='回收规则表';

-- ----------------------------
-- Records of __PREFIX__security_data_recycle
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__security_data_recycle` VALUES ('1', '管理员', 'auth/Admin.php', 'auth/admin', 'admin', 'id', '1', '1648958789', '1648958789');
INSERT INTO `__PREFIX__security_data_recycle` VALUES ('2', '管理员日志', 'auth/AdminLog.php', 'auth/adminlog', 'admin_log', 'id', '1', '1648967082', '1648958964');
INSERT INTO `__PREFIX__security_data_recycle` VALUES ('3', '菜单规则', 'auth/Menu.php', 'auth/menu', 'menu_rule', 'id', '1', '1648959494', '1648959494');
INSERT INTO `__PREFIX__security_data_recycle` VALUES ('4', '系统配置项', 'routine/Config.php', 'routine/config', 'config', 'id', '1', '1648959518', '1648959510');
INSERT INTO `__PREFIX__security_data_recycle` VALUES ('5', '会员', 'user/User.php', 'user/user', 'user', 'id', '1', '1649097966', '1648959540');
INSERT INTO `__PREFIX__security_data_recycle` VALUES ('6', '数据回收规则', 'security/DataRecycle.php', 'security/datarecycle', 'security_data_recycle', 'id', '1', '1648965759', '1648959655');
COMMIT;

-- ----------------------------
-- Table structure for `__PREFIX__security_data_recycle_log`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__security_data_recycle_log`;
CREATE TABLE `__PREFIX__security_data_recycle_log` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作管理员',
    `recycle_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '回收规则ID',
    `data` text COMMENT '回收的数据',
    `data_table` varchar(100) NOT NULL DEFAULT '' COMMENT '数据表',
    `primary_key` varchar(50) NOT NULL DEFAULT '' COMMENT '数据表主键',
    `is_restore` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已还原:0=否,1=是',
    `ip` varchar(50) NOT NULL DEFAULT '' COMMENT '操作者IP',
    `useragent` varchar(255) NOT NULL DEFAULT '' COMMENT 'User Agent',
    `createtime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据回收记录表';

-- ----------------------------
-- Table structure for `__PREFIX__security_sensitive_data`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__security_sensitive_data`;
CREATE TABLE `__PREFIX__security_sensitive_data` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `name` varchar(50) NOT NULL DEFAULT '' COMMENT '规则名称',
    `controller` varchar(100) NOT NULL DEFAULT '' COMMENT '控制器',
    `controller_as` varchar(100) NOT NULL DEFAULT '' COMMENT '处理后的控制器名',
    `data_table` varchar(100) NOT NULL DEFAULT '' COMMENT '对应数据表',
    `primary_key` varchar(50) NOT NULL DEFAULT '' COMMENT '数据表主键字段',
    `data_fields` text NOT NULL COMMENT '敏感数据字段',
    `status` enum('1','0') NOT NULL DEFAULT '0' COMMENT '状态:0=关闭,1=启用',
    `updatetime` int(10) unsigned DEFAULT NULL COMMENT '修改时间',
    `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='敏感数据表';

-- ----------------------------
-- Records of __PREFIX__security_sensitive_data
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__security_sensitive_data` VALUES ('1', '管理员数据', 'auth/Admin.php', 'auth/admin', 'admin', 'id', '{\"username\":\"用户名\",\"mobile\":\"手机\",\"password\":\"密码\",\"status\":\"状态\"}', '1', '1649047890', '1649045180');
INSERT INTO `__PREFIX__security_sensitive_data` VALUES ('2', '会员数据', 'user/User.php', 'user/user', 'user', 'id', '{\"username\":\"用户名\",\"mobile\":\"手机号\",\"password\":\"密码\",\"status\":\"状态\",\"email\":\"邮箱地址\"}', '1', '1649058989', '1649045243');
INSERT INTO `__PREFIX__security_sensitive_data` VALUES ('3', '管理员权限', 'auth/Group.php', 'auth/group', 'admin_group', 'id', '{\"rules\":\"权限规则ID\"}', '1', '1649047866', '1649047271');
COMMIT;

-- ----------------------------
-- Table structure for `__PREFIX__security_sensitive_data_log`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__security_sensitive_data_log`;
CREATE TABLE `__PREFIX__security_sensitive_data_log` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '管理员',
    `sensitive_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '敏感数据规则ID',
    `data_table` varchar(100) NOT NULL DEFAULT '' COMMENT '所在数据表',
    `primary_key` varchar(50) NOT NULL DEFAULT '' COMMENT '数据表主键',
    `data_field` varchar(50) NOT NULL DEFAULT '' COMMENT '被修改字段',
    `data_comment` varchar(50) NOT NULL DEFAULT '' COMMENT '被修改项',
    `id_value` varchar(11) NOT NULL DEFAULT '' COMMENT '被修改项主键值',
    `before` text COMMENT '修改前',
    `after` text COMMENT '修改后',
    `ip` varchar(50) NOT NULL DEFAULT '' COMMENT '操作者IP',
    `useragent` varchar(255) NOT NULL DEFAULT '' COMMENT 'User Agent',
    `is_rollback` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已回滚:0=否,1=是',
    `createtime` int(10) unsigned DEFAULT NULL COMMENT '修改时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='敏感数据修改记录';

-- ----------------------------
-- Table structure for `__PREFIX__test_build`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__test_build`;
CREATE TABLE `__PREFIX__test_build` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
    `keyword_rows` varchar(100) NOT NULL DEFAULT '' COMMENT '关键词',
    `content` text NOT NULL COMMENT '内容',
    `views` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览量',
    `likes` mediumint(9) unsigned NOT NULL DEFAULT '0' COMMENT '有帮助数',
    `dislikes` mediumint(9) unsigned NOT NULL DEFAULT '0' COMMENT '无帮助数',
    `note_textarea` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
    `status` enum('1','0') NOT NULL DEFAULT '1' COMMENT '状态:0=隐藏,1=正常',
    `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
    `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
    `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='知识库表';

-- ----------------------------
-- Table structure for `__PREFIX__token`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__token`;
CREATE TABLE `__PREFIX__token` (
    `token` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Token',
    `type` varchar(15) NOT NULL COMMENT '类型',
    `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    `expiretime` int(10) DEFAULT NULL COMMENT '过期时间',
    PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户Token表';

-- ----------------------------
-- Table structure for `__PREFIX__user`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user`;
CREATE TABLE `__PREFIX__user` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分组ID',
    `username` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '用户名',
    `nickname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '昵称',
    `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '邮箱地址',
    `mobile` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '手机号',
    `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '头像',
    `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别:0=未知,1=男,2=女',
    `birthday` date DEFAULT NULL COMMENT '生日',
    `money` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '余额',
    `score` int(10) NOT NULL DEFAULT '0' COMMENT '积分',
    `lastlogintime` int(10) DEFAULT NULL COMMENT '上次登录时间',
    `lastloginip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '登录IP',
    `loginfailure` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
    `joinip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '加入IP',
    `jointime` int(10) DEFAULT NULL COMMENT '加入时间',
    `motto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '签名',
    `password` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '密码',
    `salt` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '密码盐',
    `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '状态',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `username` (`username`),
    KEY `email` (`email`),
    KEY `mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员表';

-- ----------------------------
-- Records of __PREFIX__user
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__user` VALUES ('1', '1', 'user', 'User', 'user@buildadmin.com', '18888888888', '', '2', '2022-05-13', '0', '0', '1648156017', '127.0.0.1', '0', '', '1648156017', '', '', '', 'enable', '1650731874', '1648156017');
COMMIT;

-- ----------------------------
-- Table structure for `__PREFIX__user_group`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user_group`;
CREATE TABLE `__PREFIX__user_group` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '组名',
    `rules` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '权限节点',
    `status` enum('1','0') COLLATE utf8mb4_unicode_ci DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员组表';

-- ----------------------------
-- Records of __PREFIX__user_group
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__user_group` VALUES ('1', '默认分组', '*', '1', '1648167137', '1648167095');
COMMIT;

-- ----------------------------
-- Table structure for `__PREFIX__user_money_log`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user_money_log`;
CREATE TABLE `__PREFIX__user_money_log` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
    `money` int(10) NOT NULL DEFAULT '0' COMMENT '变更余额',
    `before` int(10) NOT NULL DEFAULT '0' COMMENT '变更前余额',
    `after` int(10) NOT NULL DEFAULT '0' COMMENT '变更后余额',
    `memo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
    `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员余额变动表';

-- ----------------------------
-- Table structure for `__PREFIX__user_rule`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user_rule`;
CREATE TABLE `__PREFIX__user_rule` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级菜单',
    `type` enum('route','menu_dir','menu') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menu' COMMENT '类型:route=路由,menu_dir=菜单目录,menu=菜单项',
    `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
    `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则名称',
    `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '路由路径',
    `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标',
    `menu_type` enum('tab','link','iframe') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tab' COMMENT '菜单类型:tab=选项卡,link=链接,iframe=Iframe',
    `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Url',
    `component` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组件路径',
    `extend` enum('none','add_rules_only','add_menu_only') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT '扩展属性:none=无,add_rules_only=只添加为路由,add_menu_only=只添加为菜单',
    `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
    `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重(排序)',
    `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT '状态:0=禁用,1=启用',
    `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
    `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `pid` (`pid`),
    KEY `weigh` (`weigh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员权限规则表';

-- ----------------------------
-- Records of __PREFIX__user_rule
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__user_rule` VALUES ('1', '0', 'route', '前台', 'api', 'api', 'el-icon-HomeFilled', 'tab', '', '', 'none', '', '99', '1', '1655867308', '1648156017');
INSERT INTO `__PREFIX__user_rule` VALUES ('2', '0', 'menu_dir', '我的账户', 'account', 'account', 'fa fa-user-circle', 'tab', '', '', 'none', '', '98', '1', '1655970295', '1648156017');
INSERT INTO `__PREFIX__user_rule` VALUES ('3', '2', 'menu', '账户概览', 'account/overview', 'account/overview', 'fa fa-home', 'tab', '', '/src/views/frontend/user/account/overview.vue', 'none', '', '99', '1', '1655879438', '1655820267');
INSERT INTO `__PREFIX__user_rule` VALUES ('4', '2', 'menu', '个人资料', 'account/profile', 'account/profile', 'fa fa-user-circle-o', 'tab', '', '/src/views/frontend/user/account/profile.vue', 'none', '', '98', '1', '1655972096', '1655820365');
INSERT INTO `__PREFIX__user_rule` VALUES ('5', '2', 'menu', '修改密码', 'account/changePassword', 'account/changePassword', 'fa fa-shield', 'tab', '', '/src/views/frontend/user/account/changePassword.vue', 'none', '', '97', '1', '1655980365', '1655820461');
INSERT INTO `__PREFIX__user_rule` VALUES ('6', '2', 'menu', '积分记录', 'account/integral', 'account/integral', 'fa fa-tag', 'tab', '', '/src/views/frontend/user/account/integral.vue', 'none', '', '96', '1', '1655985356', '1655820507');
INSERT INTO `__PREFIX__user_rule` VALUES ('7', '2', 'menu', '余额记录', 'account/balance', 'account/balance', 'fa fa-money', 'tab', '', '/src/views/frontend/user/account/balance.vue', 'none', '', '95', '1', '1655985373', '1655820593');
COMMIT;

-- ----------------------------
-- Table structure for `__PREFIX__user_score_log`
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__user_score_log`;
CREATE TABLE `__PREFIX__user_score_log` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
    `score` int(10) NOT NULL DEFAULT '0' COMMENT '变更积分',
    `before` int(10) NOT NULL DEFAULT '0' COMMENT '变更前积分',
    `after` int(10) NOT NULL DEFAULT '0' COMMENT '变更后积分',
    `memo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '备注',
    `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员积分变动表';