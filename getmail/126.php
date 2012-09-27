<?php
/**
 * @name Http126
 * 导出126邮箱联系人
 * @author Spring
 */
header("Content-type: text/html; charset=utf-8");
define("COOKIEJAR1", tempnam("./assets/tmp", "c1_"));
define("COOKIEJAR2", tempnam("./assets/tmp", "c2_"));
define('TIMEOUT', 60);

class Http126{

	/**
	 * @desc: login in the 126 mail box
	 * @param string $username
	 * @param string $password
	 * @return int  //the login status
	 */
	public $agent="Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11";
	
    public function login($username, $password){  
    	$url            = 'http://reg.163.com/login.jsp?type=1&product=mail126&url=http://entry.mail.126.com/cgi/ntesdoor?hid%3D10010102%26lightweight%3D1%26language%3D0%26style%3D-1';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, "https://reg.163.com/logins.jsp?type=1&product=mail126&url=http://entry.mail.126.com/cgi/ntesdoor?hid%3D10010102%26lightweight%3D1%26verifycookie%3D1%26language%3D0%26style%3D-1");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent' => $this->agent));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "username=".$username."&password=".$password);
        curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIEJAR1);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_login);
        curl_setopt($ch,CURLOPT_HEADER,true);        
        curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $str = curl_exec($ch);    
        //file_put_contents('./126_login.txt', $str); 
        //file_put_contents('./126_cookie.txt', COOKIEJAR1);
        curl_close($ch);
        
        /*=====开始第一次跳转=====*/
        preg_match('/http:\/\/passport.126.com(.*)loginyoudao=0/', $str, $url_c_1);//取得跳转地址
        $ch = curl_init($url_c_1[0]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent' => $this->agent));
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIEJAR1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIEJAR2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $result_check_1 = curl_exec($ch);
        //file_put_contents('126_login2.txt', $result_check_1);
        curl_close($ch);
        
        if (strpos($result_check_1, "登录成功") === false){            
            return 0;
        }        
        return 1;
    }
    
    /**
     * 获取邮箱通讯录-地址
     * @param $username
     * @param $password
     * @return array
     */
    public function getAddressList($username, $password){        
        if (!$this->login($username, $password)){
            return 0;
        }
        $header = $this->_getheader($username);
        if (!$header['sid']){
            return 0;
        }
        //开始进入模拟抓取
        $ch = curl_init();
        $url='http://twebmail.mail.126.com/jy3/address/addrlist.jsp?sid='.$header['sid'].'&gid=all';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIEJAR1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent' => $this->agent));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
        $ret = curl_exec($ch);
        curl_close($ch);
        //file_put_contents('126_data.txt', $ret);
        preg_match_all('/<td\s*class="Ibx_Td_addrEmail"><a[^>]+>[^>]+a><\/td>/',$ret,$mails);
        preg_match_all('/<td\s*class="Ibx_Td_addrName"><a[^>]+>[^>]+a><\/td>/',$ret,$names);
        //print_r($mails);print_r($names);
        foreach ($mails[0] as $k=>$v){
        	$r['email'] = strip_tags($v);
        	$r['name'] = strip_tags($names[0][$k]);
        	$res[] = $r;
        }
        return $res;
    }
    
    /**
    * Get Header info
    */
    public function _getheader($username){
    	$url="http://entry.mail.126.com/cgi/ntesdoor?username=".$username."&hid=10010102&lightweight=1&verifycookie=1&language=0&style=-1";
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    	curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIEJAR2);  //当前使用的cookie
    	curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIEJAR1);   //服务器返回的新cookie
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent' => $this->agent));
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_HEADER, true);
    	curl_setopt($ch, CURLOPT_NOBODY, true);
    	curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
    	$content=curl_exec($ch);
    	//file_put_contents('126_header.txt', $content);
    	preg_match_all('/Location:\s*(.*?)\r\n/i',$content,$regs);
    	$refer = $regs[1][0];
    	preg_match_all('/http\:\/\/(.*?)\//i',$refer,$regs);
    	$host = $regs[1][0];
    	preg_match_all("/sid=(.*)/i",$refer,$regs);
    	$sid = $regs[1][0];
    	
    	curl_close($ch);
    	return array('sid'=>$sid,'refer'=>$refer,'host'=>$host);
    }
}
// $e126=new Http126();
// $elist = $e126->getAddressList('jerrywang4444@126.com', 'abcd1234');
// print_r($elist);
?>