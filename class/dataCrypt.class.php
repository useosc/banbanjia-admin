<?php
//加密解密

class WXBizDataCrypt
{
	private $sessionKey;

	/**
	 * 构造函数
	 * @param $sessionKey string 用户在小程序登录后获取的会话密钥
	 */
	public function __construct($sessionKey)
	{
		$this->sessionKey = $sessionKey;
	}


	/**
	 * 检验数据的真实性，并且获取解密后的明文.
	 * @param $encryptedData string 加密的用户数据
	 * @param $iv string 与用户数据一同返回的初始向量
     *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function decryptData( $encryptedData, $iv)
	{
		if (strlen($this->sessionKey) != 24) {
			return array(-41001, NULL);
		}
		$aesKey=base64_decode($this->sessionKey);
        
		if (strlen($iv) != 24) {
			return array(-41002, NULL);
		}
		$aesIV=base64_decode($iv);

		$aesCipher=base64_decode($encryptedData);

		$result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

		$dataObj=json_decode( $result );
		if( $dataObj  == NULL )
		{
			return array(-41003, NULL);
		}
        return array(0, $result);
	}

}