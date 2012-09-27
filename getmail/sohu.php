<?php

define( "COOKIEJAR", tempnam( ini_get( "upload_tmp_dir" ), "cookie" ) ); 
class sohu
{
    public function getAddressList($username, $password)
    {       
        $name=explode('@', $username);
        $username=$name[0];
        $ch = curl_init();
        $encodeurl = "http://passport.sohu.com/sso/login.jsp?userid=".urlencode($username . "@sohu.com") . "&password=".md5($password)."&appid=1000&persistentcookie=0&s=".time()."343&b=2&w=1024&pwdtype=1";
        
        curl_setopt($ch, CURLOPT_URL, $encodeurl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIEJAR );
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $contents = curl_exec( $ch );
        if ( strpos( $contents, "success" ) === false )
        {
            return 0;
        }
       curl_setopt($ch, CURLOPT_URL, "http://mail.sohu.com/bapp/128/main#addressList");
       $contents = curl_exec($ch);
       if(false == $contents){ 
            curl_close( $ch );
            return false;
        }else{ 
            curl_close( $ch );
            return $this->parserContent($contents);
        }
    }
    function parserContent($content){
        preg_match_all("/var addresses = '(.*)';/Umsi",$content,$data);
        $numList= json_decode( $data[1][0]);
        $contactList = array();
        foreach ($numList->contact as $val){
            $obj = array();
            $obj['name'] = $val->nickname;
            //$obj['nickname'] = $val->pinyin;
            $obj['emailAddress'] = $val->email; 
            //if($obj['nickname']==""){
            //    $obj['nickname'] = $obj['name'];
            //}
            $contactList[] = $obj; 
        }
        if( count($contactList) == 0 ){
            return false;
        }else{
            return $contactList;
        }
    }
}

$test = new sohu();
$contects = $test->getAddressList('jerrywang4444@sohu.com', '123456');
print_r($contects);

?>
