<?php

namespace ba;

use Throwable;
use think\facade\Db;
use think\facade\Lang;
use think\facade\Config;

/**
 * 点选文字验证码类
 */
class ClickCaptcha
{
    /**
     * 验证码过期时间(s)
     * @var int
     */
    private int $expire = 600;

    /**
     * 可以使用的背景图片路径
     * @var array
     */
    private array $bgPaths = [
        'static/images/captcha/click/bgs/1.png',
        'static/images/captcha/click/bgs/2.png',
        'static/images/captcha/click/bgs/3.png',
    ];

    /**
     * 可以使用的字体文件路径
     * @var array
     */
    private array $fontPaths = [
        'static/fonts/zhttfs/SourceHanSansCN-Normal.ttf',
    ];

    /**
     * 验证点 Icon 映射表
     * @var array
     */
    private array $iconDict = [
        'aeroplane' => '飞机',
        'apple'     => '苹果',
        'banana'    => '香蕉',
        'bell'      => '铃铛',
        'bicycle'   => '自行车',
        'bird'      => '小鸟',
        'bomb'      => '炸弹',
        'butterfly' => '蝴蝶',
        'candy'     => '糖果',
        'crab'      => '螃蟹',
        'cup'       => '杯子',
        'dolphin'   => '海豚',
        'fire'      => '火',
        'guitar'    => '吉他',
        'hexagon'   => '六角形',
        'pear'      => '梨',
        'rocket'    => '火箭',
        'sailboat'  => '帆船',
        'snowflake' => '雪花',
        'wolf head' => '狼头',
    ];

    /**
     * 配置
     * @var array
     */
    private array $config = [
        // 透明度
        'alpha' => 36,
        // 中文字符集
        'zhSet' => '们以我到他会作时要动国产的是工就年阶义发成部民可出能方进在和有大这主中为来分生对于学级地用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所起政好十战无农使前等反体合斗路图把结第里正新开论之物从当两些还天资事队点育重其思与间内去因件利相由压员气业代全组数果期导平各基或月然如应形想制心样都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极已根共直团统式转别造切九你取西持总料连任志观调么山程百报更见必真保热委手改管处己将修支识象先老光专什六型具示复安带每东增则完风回南劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单坚据速防史拉世设达尔场织历花求传断况采精金界品判参层止边清至万确究书术状须离再目海权且青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿胜细影济白格效置推空配叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非亚磨族段算适讲按值美态易彪服早班麦削信排台声该击素张密害侯何树肥继右属市严径螺检左页抗苏显苦英快称坏移巴材省黑武培著河帝仅针怎植京助升王眼她抓苗副杂普谈围食源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功友限项余倒卷创律雨让骨远帮初皮播优占圈伟季训控激找叫云互跟粮粒母练塞钢顶策双留误础阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺版烈零室轻倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送侧润盖挥距触星松送获兴独官混纪依未突架宽冬章偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞哪旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶念兰映沟乙吗儒汽磷艰晶埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀摆贡呈劲财仪沉炼麻祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜脂庄擦险赞钟摇典柄辩竹谷乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼峰零柴簧午跳居尚秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑冰柬嘴啥饭塑寄赵喊垫丹渡耳虎笔稀昆浪萨茶滴浅拥覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷忽闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳塘燥泡袋朗喂铝软渠颗惯贸综墙趋彼届墨碍启逆卸航衣孙龄岭休借',
    ];

    /**
     * 构造方法
     * @param array $config 点击验证码配置
     * @throws Throwable
     */
    public function __construct(array $config = [])
    {
        $clickConfig  = Config::get('buildadmin.click_captcha');
        $this->config = array_merge($clickConfig, $this->config, $config);

        // 清理过期的验证码
        Db::name('captcha')->where('expire_time', '<', time())->delete();
    }

    /**
     * 创建图形验证码
     * @param string $id 验证码ID，开发者自定义
     * @return array 返回验证码图片的base64编码和验证码文字信息
     */
    public function creat(string $id): array
    {
        $imagePath  = Filesystem::fsFit(public_path() . $this->bgPaths[mt_rand(0, count($this->bgPaths) - 1)]);
        $fontPath   = Filesystem::fsFit(public_path() . $this->fontPaths[mt_rand(0, count($this->fontPaths) - 1)]);
        $randPoints = $this->randPoints($this->config['length'] + $this->config['confuse_length']);

        $lang = Lang::getLangSet();

        foreach ($randPoints as $v) {
            $tmp['size'] = rand(15, 30);
            if (isset($this->iconDict[$v])) {
                // 图标
                $tmp['icon']   = true;
                $tmp['name']   = $v;
                $tmp['text']   = $lang == 'zh-cn' ? "<{$this->iconDict[$v]}>" : "<$v>";
                $iconInfo      = getimagesize(Filesystem::fsFit(public_path() . 'static/images/captcha/click/icons/' . $v . '.png'));
                $tmp['width']  = $iconInfo[0];
                $tmp['height'] = $iconInfo[1];
            } else {
                // 字符串文本框宽度和长度
                $fontArea      = imagettfbbox($tmp['size'], 0, $fontPath, $v);
                $textWidth     = $fontArea[2] - $fontArea[0];
                $textHeight    = $fontArea[1] - $fontArea[7];
                $tmp['icon']   = false;
                $tmp['text']   = $v;
                $tmp['width']  = $textWidth;
                $tmp['height'] = $textHeight;
            }
            $textArr['text'][] = $tmp;
        }
        // 图片宽高和类型
        $imageInfo         = getimagesize($imagePath);
        $textArr['width']  = $imageInfo[0];
        $textArr['height'] = $imageInfo[1];
        // 随机生成验证点位置
        foreach ($textArr['text'] as &$v) {
            list($x, $y) = $this->randPosition($textArr['text'], $textArr['width'], $textArr['height'], $v['width'], $v['height'], $v['icon']);
            $v['x'] = $x;
            $v['y'] = $y;
            $text[] = $v['text'];
        }
        unset($v);
        // 创建图片的实例
        $image = imagecreatefromstring(file_get_contents($imagePath));
        foreach ($textArr['text'] as $v) {
            if ($v['icon']) {
                $this->iconCover($image, $v);
            } else {
                //字体颜色
                $color = imagecolorallocatealpha($image, 239, 239, 234, 127 - intval($this->config['alpha'] * (127 / 100)));
                // 绘画文字
                imagettftext($image, $v['size'], 0, $v['x'], $v['y'], $color, $fontPath, $v['text']);
            }
        }
        $nowTime         = time();
        $textArr['text'] = array_splice($textArr['text'], 0, $this->config['length']);
        $text            = array_splice($text, 0, $this->config['length']);
        Db::name('captcha')
            ->replace()
            ->insert([
                'key'         => md5($id),
                'code'        => md5(implode(',', $text)),
                'captcha'     => json_encode($textArr, JSON_UNESCAPED_UNICODE),
                'create_time' => $nowTime,
                'expire_time' => $nowTime + $this->expire
            ]);

        // 输出图片
        while (ob_get_level()) {
            ob_end_clean();
        }
        if (!ob_get_level()) ob_start();
        switch ($imageInfo[2]) {
            case 1:// GIF
                imagegif($image);
                $content = ob_get_clean();
                break;
            case 2:// JPG
                imagejpeg($image);
                $content = ob_get_clean();
                break;
            case 3:// PNG
                imagepng($image);
                $content = ob_get_clean();
                break;
            default:
                $content = '';
                break;
        }
        imagedestroy($image);
        return [
            'id'     => $id,
            'text'   => $text,
            'base64' => 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode($content),
            'width'  => $textArr['width'],
            'height' => $textArr['height'],
        ];
    }

    /**
     * 检查验证码
     * @param string $id    开发者自定义的验证码ID
     * @param string $info  验证信息
     * @param bool   $unset 验证成功是否删除验证码
     * @return bool
     * @throws Throwable
     */
    public function check(string $id, string $info, bool $unset = true): bool
    {
        $key     = md5($id);
        $captcha = Db::name('captcha')->where('key', $key)->find();
        if ($captcha) {
            // 验证码过期
            if (time() > $captcha['expire_time']) {
                Db::name('captcha')->where('key', $key)->delete();
                return false;
            }
            $textArr = json_decode($captcha['captcha'], true);
            list($xy, $w, $h) = explode(';', $info);
            $xyArr = explode('-', $xy);
            $xPro  = $w / $textArr['width'];// 宽度比例
            $yPro  = $h / $textArr['height'];// 高度比例
            foreach ($xyArr as $k => $v) {
                $xy = explode(',', $v);
                $x  = $xy[0];
                $y  = $xy[1];
                if ($x / $xPro < $textArr['text'][$k]['x'] || $x / $xPro > $textArr['text'][$k]['x'] + $textArr['text'][$k]['width']) {
                    return false;
                }
                $phStart = $textArr['text'][$k]['icon'] ? $textArr['text'][$k]['y'] : $textArr['text'][$k]['y'] - $textArr['text'][$k]['height'];
                $phEnd   = $textArr['text'][$k]['icon'] ? $textArr['text'][$k]['y'] + $textArr['text'][$k]['height'] : $textArr['text'][$k]['y'];
                if ($y / $yPro < $phStart || $y / $yPro > $phEnd) {
                    return false;
                }
            }
            if ($unset) Db::name('captcha')->where('key', $key)->delete();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 绘制Icon
     */
    protected function iconCover($bgImg, $iconImgData): void
    {
        $iconImage      = imagecreatefrompng(Filesystem::fsFit(public_path() . 'static/images/captcha/click/icons/' . $iconImgData['name'] . '.png'));
        $trueColorImage = imagecreatetruecolor($iconImgData['width'], $iconImgData['height']);
        imagecopy($trueColorImage, $bgImg, 0, 0, $iconImgData['x'], $iconImgData['y'], $iconImgData['width'], $iconImgData['height']);
        imagecopy($trueColorImage, $iconImage, 0, 0, 0, 0, $iconImgData['width'], $iconImgData['height']);
        imagecopymerge($bgImg, $trueColorImage, $iconImgData['x'], $iconImgData['y'], 0, 0, $iconImgData['width'], $iconImgData['height'], $this->config['alpha']);
        imagedestroy($iconImage);
        imagedestroy($trueColorImage);
    }

    /**
     * 随机生成验证点元素
     * @param int $length
     * @return array
     */
    public function randPoints(int $length = 4): array
    {
        $arr = [];
        // 文字
        if (in_array('text', $this->config['mode'])) {
            for ($i = 0; $i < $length; $i++) {
                $arr[] = mb_substr($this->config['zhSet'], mt_rand(0, mb_strlen($this->config['zhSet'], 'utf-8') - 1), 1, 'utf-8');
            }
        }

        // 图标
        if (in_array('icon', $this->config['mode'])) {
            $icon = array_keys($this->iconDict);
            shuffle($icon);
            $icon = array_slice($icon, 0, $length);
            $arr  = array_merge($arr, $icon);
        }

        shuffle($arr);
        return array_slice($arr, 0, $length);
    }

    /**
     * 随机生成位置布局
     * @param array $textArr 点位数据
     * @param int   $imgW    图片宽度
     * @param int   $imgH    图片高度
     * @param int   $fontW   文字宽度
     * @param int   $fontH   文字高度
     * @param bool  $isIcon  是否是图标
     * @return array
     */
    private function randPosition(array $textArr, int $imgW, int $imgH, int $fontW, int $fontH, bool $isIcon): array
    {
        $x = rand(0, $imgW - $fontW);
        $y = rand($fontH, $imgH - $fontH);
        // 碰撞验证
        if (!$this->checkPosition($textArr, $x, $y, $fontW, $fontH, $isIcon)) {
            $position = $this->randPosition($textArr, $imgW, $imgH, $fontW, $fontH, $isIcon);
        } else {
            $position = [$x, $y];
        }
        return $position;
    }

    /**
     * 碰撞验证
     * @param array $textArr 验证点数据
     * @param int   $x       x轴位置
     * @param int   $y       y轴位置
     * @param int   $w       验证点宽度
     * @param int   $h       验证点高度
     * @param bool  $isIcon  是否是图标
     * @return bool
     */
    public function checkPosition(array $textArr, int $x, int $y, int $w, int $h, bool $isIcon): bool
    {
        $flag = true;
        foreach ($textArr as $v) {
            if (isset($v['x']) && isset($v['y'])) {
                $flagX     = false;
                $flagY     = false;
                $historyPw = $v['x'] + $v['width'];
                if (($x + $w) < $v['x'] || $x > $historyPw) {
                    $flagX = true;
                }

                $currentPhStart = $isIcon ? $y : $y - $h;
                $currentPhEnd   = $isIcon ? $y + $v['height'] : $y;
                $historyPhStart = $v['icon'] ? $v['y'] : ($v['y'] - $v['height']);
                $historyPhEnd   = $v['icon'] ? ($v['y'] + $v['height']) : $v['y'];
                if ($currentPhEnd < $historyPhStart || $currentPhStart > $historyPhEnd) {
                    $flagY = true;
                }
                if (!$flagX && !$flagY) {
                    $flag = false;
                }
            }
        }
        return $flag;
    }
}