<?php
class HBOX {
    private $pkey = null;
    private $heybox_id = null;
    private $phone = null;
    private $pass = null;
    
    function __construct($pkey, $heybox_id, $phone = null, $pass = null) {
        $this->pkey = $pkey;
        $this->heybox_id = $heybox_id;
        $this->phone = $phone;
        $this->pass = $pass;
    }

    // 做每日任务
    public function dailyTask() {
        $tasks = $this->taskList();
        if($tasks)
        {
            foreach($tasks as $task)
            {
                if("finish" != $task['state'])
                {
                    print_r($task);
                    sleep(mt_rand(5, 10));
                    if(false !== strpos($task['title'], "签到"))
                        self::sign();
                    else if(false !== strpos($task['title'], "分享头条"))
                        self::shareArticle();
                    else if(false !== strpos($task['title'], "分享评论"))
                        self::shareComment();
                    else if(false !== strpos($task['title'], "点赞"))
                        self::like();
                    else
                        file_put_contents("error.log", json_encode($task) . PHP_EOL, FILE_APPEND);
                }
            }
        }
        // self::sign();
        // sleep(mt_rand(5, 10));
        self::shareArticle();
        sleep(mt_rand(5, 10));
        self::shareComment();
        sleep(mt_rand(5, 10));
        self::like();
        self::dataReport("99");
        self::dataReport("100");
    }

    // 检测pkey有效性
    public function checkPkey() {
        $http = new EasyHttp();
        $time = time();
        $hkey = self::getHkey('/account/get_socks_params', $time);
        $response = $http->request("https://api.xiaoheihe.cn/account/get_socks_params/?heybox_id={$this->heybox_id}&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.118&_time={$time}&hkey={$hkey}&channel=heybox_yingyongbao", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,           //	超时的秒数
            'redirection' => 0,       //	最大重定向次数
            'httpversion' => '1.1',   //	1.0/1.1
            'user-agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 ApiMaxJia/1.0",
            'blocking' => true,       //	是否阻塞
            'headers' => array(
                "Referer" => "http://api.maxjia.com/",
                "cookie" => "pkey={$this->pkey}"
            ),    //	header信息
            'cookies' => null,        //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,      //	是否压缩
            'decompress' => true,     //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        $ret = json_decode($response['body'], true);
        // print_r($ret);
        if($ret['status'] == "relogin")
            return 401;
        else if($ret['status'] == "ok")
            return 200;
        else if($ret['status'] == "failed" && $ret['msg'] == "你的账号已被限制访问，如有疑问请与管理员联系")
            return 403;
        else return 404;
    }

    /**
     * 登录操作
     * 
     * @param $file pkey存储文件
     * 
     * @return status 登录状态
     * */
    public function login($file = null) {
        $http = new EasyHttp();
        $time = time();
        $hkey = self::getHkey('/account/login', $time);
        $response = $http->request("https://api.xiaoheihe.cn/account/login/?heybox_id=-1&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.114&_time={$time}&hkey={$hkey}&channel=heybox_yingyongbao", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 ApiMaxJia/1.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Referer" => "http://api.maxjia.com/",
                "Content-Type" => "application/x-www-form-urlencoded"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            'body' => array(
                'phone_num' => HeyBoxEncrypt::encrypt_a($this->phone),
                'pwd' => HeyBoxEncrypt::encrypt_a($this->pass)
            ),
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        print_r(json_decode($response['body'], true));
        $this->pkey = json_decode($response['body'], true)['result']['pkey'];
        if($file)file_put_contents($file, $this->pkey);
        return json_decode($response['body'], true)['status'];
    }

    // 任务列表
    public function taskList() {
        $http = new EasyHttp();
        $time = time();
        $hkey = self::getHkey('/task/list', $time);
        $response = $http->request("https://api.xiaoheihe.cn/task/list/?heybox_id={$this->heybox_id}&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.114&_time={$time}&hkey={$hkey}&channel=heybox_yingyongbao", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 ApiMaxJia/1.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Referer" => "http://api.maxjia.com/",
                "Content-Type" => "application/x-www-form-urlencoded",
                "cookie" => "pkey={$this->pkey}"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        $ret = json_decode($response['body'], true);
        if($ret['status'] == 'ok')
            return $ret['result']['task_list'][0]['tasks'];
        else return false;
    }

    // 文章分享
    private function shareArticle() {
        $http = new EasyHttp();
        $time = time();
        $hkey = self::getHkey('/task/shared', $time);
        $response = $http->request("https://api.xiaoheihe.cn/task/shared/?h_src=bmV3c19mZWVkc18tMQ%3D%3D&shared_type=normal&heybox_id={$this->heybox_id}&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.114&_time={$time}&hkey={$hkey}&channel=heybox_yingyongbao", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 ApiMaxJia/1.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Referer" => "http://api.maxjia.com/",
                "cookie" => "pkey={$this->pkey}"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        // print_r(json_decode($response['body'], true));
    }

    // 每日签到
    private function sign() {
        $http = new EasyHttp();
        $time = time();
        $hkey = self::getHkey('/task/sign', $time);
        $response = $http->request("https://api.xiaoheihe.cn/task/sign/?heybox_id={$this->heybox_id}&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.114&_time={$time}&hkey={$hkey}&channel=heybox_yingyongbao", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 ApiMaxJia/1.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Referer" => "http://api.maxjia.com/",
                "cookie" => "pkey={$this->pkey}"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        // print_r(json_decode($response['body'], true));
    }

    // 评论分享
    private function shareComment() {
        $http = new EasyHttp();
        $time = time();
        $hkey = self::getHkey('/task/shared', $time);
        $response = $http->request("https://api.xiaoheihe.cn/task/shared/?shared_type=BBSComment&heybox_id={$this->heybox_id}&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.114&_time={$time}&hkey={$hkey}&channel=heybox_yingyongbao", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 ApiMaxJia/1.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Referer" => "http://api.maxjia.com/",
                "cookie" => "pkey={$this->pkey}"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        print_r(json_decode($response['body'], true));
    }

    /** 获取文章id & h_src
     *
     * @return array[link_ids, h_src]
     * */
    private function articleList() {
        $http = new EasyHttp();
        $time = time();
        $hkey = self::getHkey('/bbs/app/feeds', $time);
        $response = $http->request("https://api.xiaoheihe.cn/bbs/app/feeds?pull=1&use_history=0&lastval=1596019195272&heybox_id={$this->heybox_id}&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.114&_time={$time}&hkey={$hkey}&channel=heybox_yingyongbao", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 ApiMaxJia/1.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Referer" => "http://api.maxjia.com/",
                "cookie" => "pkey={$this->pkey}"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        $body = json_decode($response['body'], true);
        return array(
            'link_ids' => $body['result']['link_ids'],
            'h_src' => $body['result']['links'][0]['h_src']
        );
    }

    // 点两篇文章的赞
    private function like() {
        $http = new EasyHttp();
        $time = time();
        $hkey = self::getHkey('/bbs/app/profile/award/link', $time);
        $articles = self::articleList();
        $i = 0;
        foreach ($articles['link_ids'] as $value) {
            $i++;
            if ($i > 2)
                break;
            $response = $http->request("https://api.xiaoheihe.cn/bbs/app/profile/award/link?h_src={$articles['h_src']}&index=1&heybox_id={$this->heybox_id}&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.114&_time={$time}&hkey={$hkey}&channel=heybox_yingyongbao", array(
                'method' => 'POST',        //	GET/POST
                'timeout' => 5,            //	超时的秒数
                'redirection' => 0,        //	最大重定向次数
                'httpversion' => '1.1',    //	1.0/1.1
                'user-agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 ApiMaxJia/1.0",
                'blocking' => true,        //	是否阻塞
                'headers' => array(
                    "Referer" => "http://api.maxjia.com/",
                    "cookie" => "pkey={$this->pkey}",
                    "Content-Type" => "application/x-www-form-urlencoded"
                ),    //	header信息
                'cookies' => null,    //	关联数组形式的cookie信息
                'body' => "link_id={$value}&award_type=1",
                'compress' => false,    //	是否压缩
                'decompress' => true,    //	是否自动解压缩结果
                'sslverify' => true,
                'stream' => false,
                'filename' => null        //	如果stream = true，则必须设定一个临时文件名
            ));
            // print_r(json_decode($response['body'], true));
        }
    }
    
    // roll Room
    public function rollRoom(){
        $rooms = $this->getRollList();
        $cnt = 0;
        foreach($rooms as $value)
        {
            $cnt++;
            sleep(mt_rand(5, 10));
            $this->doRoll($value);
            if($cnt >= 5)break;
        }
    }
    
    // 赠楼列表
    private function getRollList() {
        $http = new EasyHttp();
        $time = time();
        $hkey = self::getHkey('/store/get_all_active_roll_room', $time);
        
        // 价值最高 无密码  /store/get_all_active_roll_room/?filter_passwd=1&page_type=home&sort_types=price&offset=0&limit=30&heybox_id=22047552&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.118&_time=1596205378&hkey=90d90c68d8&channel=heybox_yingyongbao
        
        $response = $http->request("https://api.xiaoheihe.cn/store/get_all_active_roll_room/?filter_passwd=1&page_type=home&sort_types=roll&offset=0&limit=15&heybox_id={$this->heybox_id}&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.114&_time={$time}&hkey={$hkey}&channel=heybox_yingyongbao", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 ApiMaxJia/1.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Referer" => "http://api.maxjia.com/"
                // "cookie" => "pkey={$this->pkey}"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        // print_r(json_decode($response['body'], true)['result']['rooms']);
        $t = json_decode($response['body'], true)['result']['rooms'];
        $rooms = array();
        foreach($t as $value)
        {
            if(!isset($value['joined']))
                $rooms[] = $value['room_id'];
        }
        // print_r($rooms);
        return $rooms;
    }

    // Roll Room
    private function doRoll($roomId) {
        $http = new EasyHttp();
        $time = time();
        $hkey = self::getHkey('/store/join_roll_room', $time);

        $room = array("room_id" => $roomId);
        $data = HeyBoxEncrypt::encrypt_b(json_encode($room), 'uxorce02');
        $key = HeyBoxEncrypt::encrypt_a("uxorce02");
        $sid = md5($key . $time) . md5($data);
        
        self::dataReport("13");
        
        $response = $http->request("https://api.xiaoheihe.cn/store/join_roll_room/?time_={$time}&heybox_id={$this->heybox_id}&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.118&_time={$time}&hkey={$hkey}&channel=heybox_yingyongbao", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 ApiMaxJia/1.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Referer" => "http://api.maxjia.com/",
                "cookie" => "pkey={$this->pkey}",
                "Content-Type" => "application/x-www-form-urlencoded"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            'body' => array(
                "data" => $data,
                "key" => $key,
                "sid" => $sid
            ),
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        // print_r(json_decode($response['body'], true));
    }
    
    // 取得hkey
    private static function getHkey($path, $time) {
        if ($path[strlen($path) - 1] != '/')
            $path .= '/';
        $str = "bfhdkud_time=";

        $temp = $path . $str . $time;
        // echo $temp;
        $mdstring = md5($temp);
        $t = str_replace("a", "app", $mdstring);
        $t = str_replace("0", "app", $t);
        $md5 = md5($t);

        $hkey = substr($md5, 0, 10);

        // echo $hkey;
        return $hkey;
    }

    // 提示更新
    public function getNotify() {
        $http = new EasyHttp();
        $time = time();
        $hkey = self::getHkey('/bbs/notify/list', $time);
        $articles = self::articleList();
        
        $response = $http->request("https://api.xiaoheihe.cn/bbs/notify/list?offset=0&limit=1&heybox_id={$this->heybox_id}&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.118&_time={$time}&hkey={$hkey}&channel=heybox_yingyongbao", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 ApiMaxJia/1.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Referer" => "http://api.maxjia.com/",
                "cookie" => "pkey={$this->pkey}"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        // print_r(json_decode($response['body'], true));
        $ret = json_decode($response['body'], true);
        if(    $ret['status'] === "ok"
            && $ret['result'][0]['obj_type'] === "coupon"
            && time() - $ret['result'][0]['create_at'] <= 60 * 6)
        {
            $coupon = $ret['result'][0];
            echo "\r\n奖品";
            $msg_arr = array(
                'task_name' => '小黑盒中奖',
                'status' => "{$coupon['title']} <br /><img src=\"{$coupon['img']}\">"
            );
            //发送邮件
            send_email('jiyecafe@qq.com', $msg_arr);
        }
    }
    /*
    {"events":[{"event_id":"178","time":"1597368748","value":36723}]}
    
    点进rool房（type:13）==》{"source":"[{\"pageID\":\"24\"},{\"pageID\":\"12\"}]"}
    参加roll房（type:13）==》{"source":"[{\"pageID\":\"24\"},{\"action\":\"1\",\"pageID\":\"25\"}]"}
    */
    public function dataReport($type){
        $http = new EasyHttp();
        $time = time();
        $hkey = self::getHkey('/account/data_report', $time);
        
        $arr = array(
            "13" => array(
                "source" => array(
                    "pageID" => 24,
                    array(
                        "action" => 1,
                        "pageID" => 12
                        )
                    )
                ),
            // {"events":[{"event_id":"173","time":"1597370779","type":"show"}]}
            "99" => array(
                "events" => array(
                    "event_id" => 173,
                    "time" => $time,
                    "type" => "show"
                    )
                ),
            // {"events":[{"event_id":"180","time":"1597370780","value":1251}]}
            "100" => array(
                "events" => array(
                    "event_id" => 180,
                    "time" => $time,
                    "value" => "1251"
                    )
                )
            );
        $data = HeyBoxEncrypt::encrypt_a2(json_encode($arr[$type]), 'U|b2-Wj>~.@**3Y8');
        $key = HeyBoxEncrypt::encrypt_a("U|b2-Wj>~.@**3Y8");
        $sid = md5($key . $time) . md5($data);
        
        $response = $http->request("https://api.xiaoheihe.cn/account/data_report/?type={$type}&time_={$time}&heybox_id={$this->heybox_id}&imei=6302c1f48d4c38a6&os_type=Android&os_version=6.0&version=1.3.118&_time={$time}&hkey={$hkey}&channel=heybox_yingyongbao", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 ApiMaxJia/1.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Referer" => "http://api.maxjia.com/",
                "cookie" => "pkey={$this->pkey}"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            'body' => array(
                "data" => $data,
                "key" => $key,
                "sid" => $sid
            ),
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        print_r(json_decode($response['body'], true));
        $ret = json_decode($response['body'], true);
        
    }
}

/*
    ////////////////////登录请求数据：//////////////////////
    'body' => array(
        'phone_num' => HeyBoxEncrypt::encrypt_a($this->phone),
        'pwd' => HeyBoxEncrypt::encrypt_a($this->pass)
    ),
    
    
    //////////////////roll room请求数据：/////////////////////
    $room = array("room_id" => $roomId);
    $data = HeyBoxEncrypt::encrypt_b(json_encode($room), "uxorce02");
    $key = HeyBoxEncrypt::encrypt_a("uxorce02");
    $sid = md5($key . $time) . md5($data);
*/
// 解密算法
class HeyBoxEncrypt {
    /**
     *  加密算法a[ta.a]
     *
     * @param $originalData 要加密的原始数据
     *
     * @return String 加密后的数据
     */
    public static function encrypt_a($originalData) {
        $publicKeyFilePath = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDZgjVwAiKTjZ55nG+mW6r3TSU4\nECvNYqDMIS/bhCj2QaH5GI/KZb2TBp+CBvUj9SLFnmJQ0kzHzHoGZCQ88VevCffF\n7JePGF9cmKQqotlfTKbV4oxV5iLz7JSG6b/Vg7AXtrTolNtWsa8HiB0tI0YClYaQ\nlOXm4UxLeSxQwSFETwIDAQAB
-----END PUBLIC KEY-----";
        extension_loaded('openssl') or die('php需要openssl扩展支持');
        $publicKey = openssl_pkey_get_public($publicKeyFilePath);
        ($publicKey) or die('公钥不可用');
        if (openssl_public_encrypt($originalData, $encryptData, $publicKey)) {
            return base64_encode($encryptData);
        } else {
            die('加密失败');
        }
    }
    public static function encrypt_a2($originalData, $key) {
        extension_loaded('openssl') or die('php需要openssl扩展支持');
        return base64_encode(openssl_encrypt(gzencode($originalData), 'AES', $key, OPENSSL_RAW_DATA, "abcdefghijklmnop"));
    }

    /**
     *  加密算法b[ta.b]
     *
     * @param $originalData 要加密的原始数据
     * @param $key 密钥
     *
     * @return String 加密后的数据
     */
    public static function encrypt_b($originalData, $key) {
        extension_loaded('openssl') or die('php需要openssl扩展支持');
        return base64_encode(openssl_encrypt(gzencode($originalData), 'DES-CBC', $key, OPENSSL_RAW_DATA, "abcdefgh"));
    }
    
    /**
     * hkey生成算法
     * 
     * @param $path 请求路径（/???/???  ||  /???/???/）
     * @param $time 时间戳（秒数）
     * 
     * @return string hkey
    */
    public static function getHkey($path, $time) {
        if($path[strlen($path) - 1] != '/')
            $path .= '/';
        $str = "bfhdkud_time=";

        $temp = $path . $str . $time;
        $mdstring = md5($temp);
        $t = str_replace("a", "app", $mdstring);
        $t = str_replace("0", "app", $t);
        $md5 = md5($t);

        $hkey = substr($md5, 0, 10);
        return $hkey;
    }
}
