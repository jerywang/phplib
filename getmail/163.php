<?php
/** 
 * @name Http163
 * 导出163邮箱联系人
 * @author Spring
 */    
header("Content-type: text/html; charset=utf-8");
define("COOKIEJAR1", tempnam("./assets/tmp", "c1_"));
define("COOKIEJAR2", tempnam("./assets/tmp", "c2_"));
define('TIMEOUT', 60);

class Http163{
   /**
    * @desc: login in the 163 mail box
    * @param string $username
    * @param string $password
    * @return int  //the login status
    */
    public function login($username, $password){
        // 登陆
		$url = 'http://reg.163.com/logins.jsp?type=1&url=http://entry.mail.163.com/coremail/fcg/ntesdoor2?lightweight%3D1%26verifycookie%3D1%26language%3D-1%26style%3D-1';
		$ch = curl_init ( $url );
		$referer_login = 'http://mail.163.com';
		// 返回结果存放在变量中，而不是默认的直接输出
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_HEADER, true );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 120 );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_REFERER, $referer_login );
		$fields_post = array (
				'username' => $username,
				'password' => $password,
				'verifycookie' => 1,
				'style' => - 1,
				'product' => 'mail163',
				'selType' => - 1,
				'secure' => 'on' 
		);
		$headers_login = array (
				'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11',
				'Referer' => 'http://www.163.com' 
		);
		$fields_string = '';
		foreach ( $fields_post as $key => $value ) {
			$fields_string .= $key . '=' . $value . '&';
		}
		$fields_string = rtrim ( $fields_string, '&' );
		curl_setopt ( $ch, CURLOPT_COOKIESESSION, true );
		// 关闭连接时，将服务器端返回的cookie保存在以下文件中
		curl_setopt ( $ch, CURLOPT_COOKIEJAR, COOKIEJAR1 );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers_login );
		curl_setopt ( $ch, CURLOPT_POST, 1024);
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields_string );
		curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
		$result = curl_exec ( $ch );
		//file_put_contents ( '163_login.txt', $result ); // 已经登录成功！
		curl_close ( $ch );
        if (strpos($result, "登录成功") === false){
            return 0;
        }
       
        return 1;
    }

    /**
    * @desc: get address list from mail box
    * @param string $username
    * @param string $password
    * @return array  //the address list
    */
    public function getAddressList($username, $password){
        if (!$this->login($username, $password)){
            return 0;
        }
        $header = $this->_getheader($username);
        if (!$header['sid']){
            return 0;
        }
       
        $ch = curl_init();
        //$url="http://twebmail.mail.163.com/js4/main.jsp?sid=".$header['sid']."#module=contact.ContactModule%7C%7B%22action%22%3A%22Welcome%22%2C%22_useCache%22%3Afalse%2C%22_nonExit%22%3Atrue%7D";
        $url='http://g4a30.mail.163.com/jy3/address/addrlist.jsp?sid='.$header['sid'].'&gid=all';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIEJAR2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11'));
	    //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //跟踪跳转
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	    $ret = curl_exec($ch);
	    curl_close($ch);
	    //file_put_contents('163_data.txt', $ret);
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
    * get cookie
    */
    public function _getheader($username)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://entry.mail.163.com/coremail/fcg/ntesdoor2?lightweight=1&verifycookie=1&language=-1&style=-1&username=".$username);
        curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIEJAR1);  //当前使用的cookie
        curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIEJAR2);   //服务器返回的新cookie
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
        $content=curl_exec($ch);
        //file_put_contents('163_header.txt', $content);
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

// $e163=new Http163();
// $elist = $e163->getAddressList('jerrywang4444', 'abcd1234');
// print_r($elist);
?>