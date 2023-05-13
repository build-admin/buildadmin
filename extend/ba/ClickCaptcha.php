<?php

namespace ba;

use think\facade\Db;
use think\db\exception\DbException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;

/**
 * 点选文字验证码类
 */
class ClickCaptcha
{
    /**
     * 验证码过期时间(s)
     */
    private $expire = 600;

    private $zhSet = '们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借';

    private $bgPathArr = [
        'static/images/click-captcha-bgs/1.jpg',
        'static/images/click-captcha-bgs/2.jpg',
        'static/images/click-captcha-bgs/3.jpg',
    ];

    private $fontPath = 'static/fonts/zhttfs/SourceHanSansCN-Normal.ttf';

    public function __construct()
    {
        // 清理过期的验证码
        Db::name('captcha')->where('expiretime', '<', time())->delete();
    }

    /**
     * 创建图形验证码
     * @param string $id 验证码ID，开发者自定义
     * @return array 返回验证码图片的base64编码和验证码文字信息
     */
    public function creat(string $id): array
    {
        $imagePath = path_transform(public_path() . $this->bgPathArr[rand(0, count($this->bgPathArr) - 1)]);
        $fontPath  = path_transform(public_path() . $this->fontPath);
        foreach ($this->randChars(8) as $v) {
            $fontSize = rand(15, 30);
            // 字符串文本框宽度和长度
            $fontArea          = imagettfbbox($fontSize, 0, $fontPath, $v);
            $textWidth         = $fontArea[2] - $fontArea[0];
            $textHeight        = $fontArea[1] - $fontArea[7];
            $tmp['text']       = $v;
            $tmp['size']       = $fontSize;
            $tmp['width']      = $textWidth;
            $tmp['height']     = $textHeight;
            $textArr['text'][] = $tmp;
        }
        // 图片宽高和类型
        $imageInfo         = getimagesize($imagePath);
        $textArr['width']  = $imageInfo[0];
        $textArr['height'] = $imageInfo[1];
        // 随机生成汉字位置
        foreach ($textArr['text'] as &$v) {
            list($x, $y) = $this->randPosition($textArr['text'], $textArr['width'], $textArr['height'], $v['width'], $v['height']);
            $v['x'] = $x;
            $v['y'] = $y;
            $text[] = $v['text'];
        }
        unset($v);
        // 创建图片的实例
        $image = imagecreatefromstring(file_get_contents($imagePath));
        foreach ($textArr['text'] as $v) {
            list($r, $g, $b) = $this->getImageColor($imagePath, $v['x'] + $v['width'] / 2, $v['y'] - $v['height'] / 2);
            // 字体颜色
            $color = imagecolorallocate($image, $r, $g, $b);
            // 阴影字体颜色
            $r           = $r > 127 ? 0 : 255;
            $g           = $g > 127 ? 0 : 255;
            $b           = $b > 127 ? 0 : 255;
            $shadowColor = imagecolorallocate($image, $r, $g, $b);
            // 绘画阴影
            imagettftext($image, $v['size'], 0, $v['x'] + 1, $v['y'], $shadowColor, $fontPath, $v['text']);
            imagettftext($image, $v['size'], 0, $v['x'], $v['y'] + 1, $shadowColor, $fontPath, $v['text']);
            imagettftext($image, $v['size'], 0, $v['x'] - 1, $v['y'], $shadowColor, $fontPath, $v['text']);
            imagettftext($image, $v['size'], 0, $v['x'], $v['y'] - 1, $shadowColor, $fontPath, $v['text']);
            imagettftext($image, $v['size'], 0, $v['x'] + 1, $v['y'] + 1, $shadowColor, $fontPath, $v['text']);
            imagettftext($image, $v['size'], 0, $v['x'] + 1, $v['y'] - 1, $shadowColor, $fontPath, $v['text']);
            imagettftext($image, $v['size'], 0, $v['x'] - 1, $v['y'] - 1, $shadowColor, $fontPath, $v['text']);
            imagettftext($image, $v['size'], 0, $v['x'] - 1, $v['y'] + 1, $shadowColor, $fontPath, $v['text']);
            // 绘画文字
            imagettftext($image, $v['size'], 0, $v['x'], $v['y'], $color, $fontPath, $v['text']);
        }
        // 删除汉字数组后面4个，实现图片上展示8个字，实际只需点击4个的效果
        $nowTime         = time();
        $textArr['text'] = array_splice($textArr['text'], 3, 4);
        $text            = array_splice($text, 3, 4);
        Db::name('captcha')
            ->replace()
            ->insert([
                'key'        => md5($id),
                'code'       => md5(implode(',', $text)),
                'captcha'    => json_encode($textArr, JSON_UNESCAPED_UNICODE),
                'createtime' => $nowTime,
                'expiretime' => $nowTime + $this->expire
            ]);

        // 输出图片
        if (ob_get_level()) ob_end_clean();
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
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function check(string $id, string $info, bool $unset = true): bool
    {
        $key     = md5($id);
        $captcha = Db::name('captcha')->where('key', $key)->find();
        if ($captcha) {
            // 验证码过期
            if (time() > $captcha['expiretime']) {
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
                if ($y / $yPro < $textArr['text'][$k]['y'] - $textArr['text'][$k]['height'] || $y / $yPro > $textArr['text'][$k]['y']) {
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
     * 随机生成中文汉字
     * @param int $length
     * @return array
     */
    private function randChars(int $length = 4): array
    {
        $str = [];
        for ($i = 0; $i < $length; $i++) {
            $str[] = mb_substr($this->zhSet, floor(mt_rand(0, mb_strlen($this->zhSet, 'utf-8') - 1)), 1, 'utf-8');
        }
        return $str;
    }

    /**
     * 随机生成位置布局
     * @param array $textArr 文字数据
     * @param int   $imgW    图片宽度
     * @param int   $imgH    图片高度
     * @param int   $fontW   文字宽度
     * @param int   $fontH   文字高度
     * @return array
     */
    private function randPosition(array $textArr, int $imgW, int $imgH, int $fontW, int $fontH): array
    {
        $x = rand(0, $imgW - $fontW);
        $y = rand($fontH, $imgH);
        // 碰撞验证
        if (!$this->checkPosition($textArr, $x, $y, $fontW, $fontH)) {
            $position = $this->randPosition($textArr, $imgW, $imgH, $fontW, $fontH);
        } else {
            $position = [$x, $y];
        }
        return $position;
    }

    /**
     * 碰撞验证
     * @param array $textArr 文字数据
     * @param int   $x
     * @param int   $y
     * @param int   $w
     * @param int   $h
     * @return bool
     */
    private function checkPosition(array $textArr, int $x, int $y, int $w, int $h): bool
    {
        $flag = true;
        foreach ($textArr as $v) {
            if (isset($v['x']) && isset($v['y'])) {
                //分别判断X和Y是否都有交集，如果都有交集，则判断为覆盖
                $flagX = true;
                if ($v['x'] > $x) {
                    if ($x + $w > $v['x']) {
                        $flagX = false;
                    }
                } else if ($x > $v['x']) {
                    if ($v['x'] + $v['width'] > $x) {
                        $flagX = false;
                    }
                } else {
                    $flagX = false;
                }
                $flagY = true;
                if ($v['y'] > $y) {
                    if ($y + $h > $v['y']) {
                        $flagY = false;
                    }
                } else if ($y > $v['y']) {
                    if ($v['y'] + $v['height'] > $y) {
                        $flagY = false;
                    }
                } else {
                    $flagY = false;
                }
                if (!$flagX && !$flagY) {
                    $flag = false;
                }
            }
        }
        return $flag;
    }

    /**
     * 获取图片某个定点上的主要色
     * @param string $img
     * @param int    $x
     * @param int    $y
     * @return array
     */
    private function getImageColor(string $img, int $x, int $y): array
    {
        list($imageWidth, $imageHeight, $imageType) = getimagesize($img);
        switch ($imageType) {
            case 1:// GIF
                $im = imagecreatefromgif($img);
                break;
            case 2:// JPG
                $im = imagecreatefromjpeg($img);
                break;
            case 3:// PNG
                $im = imagecreatefrompng($img);
                break;
        }
        if (!isset($im)) return [0, 0, 0];
        $rgb = imagecolorat($im, $x, $y);
        $r   = ($rgb >> 16) & 0xFF;
        $g   = ($rgb >> 8) & 0xFF;
        $b   = $rgb & 0xFF;
        return [$r, $g, $b];
    }
}