<?php
/**
 * 短彩邮服务逻辑
 * @author Robert <yongliang1@leju.com>
 */
namespace Common\Logic;

class EmsLogic {

	protected $instance = null;
	protected $config = null;
	protected $email = 'EMAIL_NOTICE';	// 邮件服务配置

	protected function initInstance() {
		if ( is_null($this->instance) ) {
			$this->instance = true;
			$this->config = C('EMS');
		}
	}

	public function sendMail($to, $title, $content) {
		$this->initInstance();
		$emsApp = & $this->config[$this->email];

		$mailData = array();
		$mailData['appname'] = $emsApp['name'];
		$mailData['appid'] = $emsApp['appid'];
		$mailData['nick'] = '知识百科管理系统管理员';
		$mailData['email'] = $to;
		$mailData['isReal'] = 0;
		$mailData['sendtime'] = '';
		$mailData['subject'] = $title;
		$mailData['content'] = $content;
		$mailData['num'] = 1;
		$mailData['format'] = 'json';
		$mailData['sign']= $this->getSign($mailData, $emsApp['key']);

		$api = 'http://ems.leju.com/api/mail/send';

		$result = curl_post($api, $mailData);
		return $result;
	}


	//数组系列化成字符串
	protected function getPostString(&$post) {
		$string = '';
		if(is_array($post)) {
			foreach($post as $item) {
				if(is_array($item))
					$string .= getPostString($item);
				else
					$string .= $item;
			}
		} else {
			$string = $post;
		}

		return $string;
	}
	//计算签名
	protected function getSign(&$data, $key) {
		$string = $this->getPostString($data);
		return md5($string.$key);
	}
}
