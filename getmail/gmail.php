<?php
/** 
 * @name Gmail
 * 导出gmail邮箱联系人
 * @author Spring
 */
define('TIMEOUT', 60);

class Gmail {
	
	/**
	 * @param string $username
     * @param string $password
     * @return mixed
	 */
	function getAddressList($username, $password) {
		$login_url = "https://www.google.com/accounts/ClientLogin";
		$fields = array (
				'Email' => $username,
				'Passwd' => $password,
				'service' => 'cp', //contact list service code
				'source' => 'test-google-contact-grabber',
				'accountType' => 'GOOGLE' 
		);
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $login_url );
		curl_setopt ( $curl, CURLOPT_POST, 1 );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $fields );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, TIMEOUT );
		$result = curl_exec ( $curl );
		$returns = array ();
		foreach ( explode ( "\n", $result ) as $line ) {
			$line = trim ( $line );
			if (! $line)
				continue;
			list ( $k, $v ) = explode ( "=", $line, 2 );
			$returns [$k] = $v;
		}
		curl_close ( $curl );
		//step 2: grab the contact list
		$feed_url = "http://www.google.com/m8/feeds/contacts/$username/full?alt=json&max-results=250";
		$header = array (
				'Authorization: GoogleLogin auth=' . $returns ['Auth'] 
		);
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $feed_url );
		curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, TIMEOUT );
		$result = curl_exec ( $curl );
		curl_close ( $curl );
		$data = json_decode ( $result, true );
		$list = array ();
		foreach ( $data ['feed'] ['entry'] as $entry ) {
			$list ['name'] = $entry ['title'] ['$t'];
			$list ['email'] = $entry ['gd$email'] [0] ['address'];
			$lists [] = $list;
		}
		return $lists;
	}
}

// error_reporting ( E_ALL );
// $username = "******@gmail.com";
// $password = "******";
// $gmail = new Gmail ();
// $result = $gmail->getAddressList ( $username, $password );
// print_r( $result );
?>