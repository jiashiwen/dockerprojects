<?php

/**
 * 计算分页显示数据结构的方法
 * @param  int	$page     当前页码
 * @param  int	$total    记录集合中的记录总数
 * @param  int	$pagesize 每页显示的记录数量
 * @param  array  $opts     配置参数
 * @return array           分页信息结果集合
 */
function pager ( $page, $total, $pagesize, $opts=array() ) {
	$_totalpage = intval(ceil($total/$pagesize));
	$pager = array(
		'page' => $page,
		'pagesize' => $pagesize,
		'total' => intval($total),
		'count' => $_totalpage,
		'prev' => ( $page > 1 ) ? $page-1 : 1,
		'next' => ( $page < $_totalpage ) ? $page+1 : $_totalpage,
		'first' => 1,
		'last' => $_totalpage,
		'list' => array(),
		'template' => isset($opts['linkstring']) ? $opts['linkstring'] : '#',
		'spline' => isset($opts['spline']) ? $opts['spline'] : '...',
	);
	$opts['number'] = isset($opts['number']) ? intval($opts['number']) : 5;
	$opts['number'] = in_array($opts['number'], array(5,7,9) ) ?  $opts['number'] : 5;
	$_fix = ( $opts['number'] + 1 ) / 2 - 1;

	isset($opts['var']) && $pager['var'] = $opts['var'];
	isset($opts['jump']) && $pager['jump'] = !!$opts['jump'];
	isset($opts['linkopts']) && $pager['linkopts'] = $opts['linkopts'];

	$_start = $page - $_fix;
	$_end = $page + $_fix;

	if ( $_totalpage > $opts['number'] ) {
		if ( $page > $_fix + 1 ) {
			$pager['sp_before'] = true;
		}
		if ( $page < $_totalpage - $_fix ) {
			$pager['sp_after'] = true;
		}
	}

	if ( $page < $_fix + 1 ) {
		$_end = ( $_totalpage < $opts['number'] ) ? $_totalpage : $opts['number'];
	}
	if ( $page > $_totalpage - $_fix ) {
		$_start = ( $_totalpage < $opts['number'] ) ? 1 : $_totalpage - $opts['number'];
	}
	$_start = ( $_start < 1 ) ? 1 : $_start;
	$_end = ( $_end > $_totalpage ) ? $_totalpage : $_end;

	if ( $page > $_totalpage ) {
		$pager['page'] = $_totalpage;
	}

	$_opts_filter = array('first', 'last', 'prev', 'next');
	foreach ( $_opts_filter as $_i => $_opt ) {
		if ( !isset($opts[$_opt]) || $opts[$_opt]==false ) {
			unset($pager[$_opt]);
		} else {
		    if ($_totalpage > 1)
            {
                if ($page == 1 && in_array($_opt,array('first','prev')))
                {
                    $pager[$_opt] = 'javascript:;';
                }
                elseif ($_totalpage == $page && in_array($_opt,array('last','next')))
                {
                   $pager[$_opt] = 'javascript:;';
                }
                else
                {
                    $pager[$_opt] = str_replace('#', $pager[$_opt], $pager['template']);
                }
            }
            else
            {
                $pager[$_opt] = 'javascript:;';
            }
		}
	}

	for ( $_idx=$_start; $_idx<=$_end; $_idx++ ) {
		array_push($pager['list'], array(
			'num' => $_idx,
			'url' => str_replace('#', $_idx, $pager['template']),
		));
	}
	return $pager;
}

/**
 * 问答 PC 端 时间显示格式化处理
 */
function formatQATimer( $time=NOW_TIME ) {
	$year_ts = strtotime(date('Y-01-01 00:00:00', NOW_TIME));
	if ( $time < $year_ts ) {
		$result = date('Y年m月d日', $time);
	} else {
		// 同一年中的提问
		$ago_var = NOW_TIME - $time;
		if ( $ago_var < 60 ) {
			$result = '刚刚';
		} else if ( $ago_var < 3600 ) {
			$result = intval($ago_var/60).'分钟之前';
		} else if ( $ago_var < 43200 ) {
			$result = intval($ago_var/3600).'小时之前';
		} else {
			$result = date('m月d日', $time);
		}
	}
	return $result;
}
function formatQASearchTitle ($string='', $find='') {
	if ( trim($string)=='' || trim($find)=='' ) {
		return $string;
	}
	$result = str_replace($find, '<em>'.$find.'</em>', $string);
	return $result;
}
/**
 * 问答 PC 端 用户昵称显示格式化处理
 */
function formatUsernick( $usernick='', $anonymous=0, $default='乐居网友' ) {
	$usernick = trim($usernick);
	$anonymous = intval($anonymous);
	if ( $anonymous ) {
		$result = '匿名网友';
	} else {
		$result = $usernick == '' ? $default : $usernick;
	}
	return $result;
}
/**
 * 移动端与PC版自动适配检测
 */
function autofit ( $jump = false, $type='baike' ) {
	if ( !isset($_SERVER['HTTP_USER_AGENT']) || !$jump ) {
		return false;
	}

	$useragent = $_SERVER['HTTP_USER_AGENT'];
	$origin_host = $_SERVER['HTTP_HOST'];
	$origin_url = $_SERVER['REQUEST_URI'];
	$detected = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
	$device = 'pc';
	if( $detected ) {
		// show mobile
		$device = 'touch';
	} else {
		$device = 'pc';
	}

	$target = url('index', array(), $device, $type);
	$target_host = substr($target, 7, -1);
	if ( $origin_host !== $target_host ) {
		$detected = true;
		$target = 'http://' . $target_host . $origin_url;

		if ( $jump!==false ) {
			header('Location: '.$target);
			exit;
		}
	}
	/*
	var_dump(array(
		'useragent' => $useragent,
		'diff' => $detected,
		'autojump' => $jump,
		'device' => $device,
		'origin_host' => $origin_host,
		'target_host' => $target_host,
		'origin_url' => $origin_url,
		'target' => $target,
	));
	*/
	return $target;
}


/**
* 根据 房首埋的 cookie 城市数据
* @param $device 设备类型
* @return string 对应的城市名称 
*/
function getCookieLocation ( $device='pc' ) {
	$ip = get_client_ip();
	$default = array( 'city_cn'=>'北京', 'city_en'=>'bj', 'ip'=>$ip );

	if ( $device=='pc' ) {
		$city_en = strtolower(trim(cookie('M_CITY')));
	} else {
		$city_en = strtolower(trim(cookie('B_CITY')));
	}
	if ( $city_en=='bj' ) {
		return $default;
	}

	$cities = C('CITIES.ALL');
	// 接口返回的城市名称不属于业务城市范围，自动改为默认城市 
	if (!array_key_exists($city_en, $cities) ) {
		$ret = $default;
	} else {
		$ret['city_cn'] = $cities[$city_en]['cn'];
		$ret['city_en'] = $city_en;
		$ret['ip'] = $ip;
	}
	return $ret;
}
/**
* 根据 ip 地址获取城市名称 
* @param $ip string ip 地址 
* @param $default string 默认城市名称 
* @return string ip 地址对应的城市名称 
*/
function getIPLocation($ip, $default=array()) {
	if ( empty($default) ) {
		$default = array( 'city_cn'=>'北京', 'city_en'=>'bj' );
	}
	$default['ip'] = $ip;
	if ($ip=='127.0.0.1') {
		return $default;
	}

	$api = 'http://ip.house.sina.com.cn/iplookup.php';
	$data = array('ip'=>$ip);
	$return = curl_get($api, $data);
	if ($return['status'] ) {
		$result = json_decode($return['result'], true);
		$city_en = $result['info']['city_en'];
		if ($result['info']['city']!='' ) {
			$result = $result['info']['city'];
		} else if ($result['info']['country']!='' ) {
			$result = $result['info']['country'];
		} else if ($result['info']['province']!='' ) {
			$result = $result['info']['province'];
		} else {
			$result = $result['area'];
		}

		$cities = C('CITIES.ALL');
		// 接口返回的城市名称不属于业务城市范围，自动改为默认城市 
		if (!array_key_exists($city_en, $cities) ) {
			$ret = $default;
		}
		else
		{
			$ret['city_cn'] = $result;
			$ret['city_en'] = $city_en;
		}
	} else {
		$ret = $default;
	}

	$ret['ip'] = $ip;
	return $ret;
}

/**
 * 从用户接口获取用户信息
 */
function getMemberInfo( $uid ) {
	$uid = intval($uid);
	if ( $uid<=0 ) {
		return false;
	}

	$token = sha1(crypt("&{$uid}",'BiOnline'));
	$uid = base64_encode(substr(md5($uid),0,8).base64_encode($uid).substr(md5($uid),10,4));

	$api = 'http://my.leju.com/web/sso/userinfo';
	$data = array('u'=>$uid, 'token'=>$token);
	$result = curl_post($api, $data);
	if ( $result['status']==true ) {
		return json_decode($result['result'], true);
	} else {
		return false;
	}
}


/**
 * 从字符串中获取 Utf-8 的汉字
 */
function fetchChinese( $str ) {
	$ret = preg_match_all("/[\x{4e00}-\x{9fff}]+/u", $str, $matches);
	if ( $ret===0 ) {
		return false;
	}
	$str = implode('', $matches[0]);
	return $str;
}

/**
 * 从标签系统中获取根据访问量统计的标签排行数据
 * @provider : [戚树春-php-1640](qq://1162699939)
 */
function getHotTags( $num=10, $day=7) {
	$url = 'http://admin.tag.leju.com/api/api/get_tags_with_click';
	$q = array(
		'key' => '5ed33f7008771c9d49e3716aeaeca581',
		'day' => $day,
		'limit' => $num,
		'own' => 'true',
	);
	$ret = curl_get($url, $q);
	$result = array();
	if ( $ret['status'] ) {
		$ret = json_decode($ret['result'], true);
		$result = $ret['data']['tags'];
	}
	return $result;
}


/**
 * 提交GET请求，curl方法
 * @param string  $url       请求url地址
 * @param mixed   $data      GET数据,数组或类似id=1&k1=v1
 * @param array   $header    头信息
 * @param int     $timeout   超时时间
 * @param int     $port      端口号
 * @return array             请求结果,
 *                            如果出错,返回结果为array('error'=>'','result'=>''),
 *                            未出错，返回结果为array('result'=>''),
 */
function curl_get($url, $data = array(), $header = array(), $timeout = 5, $port = 80)
{
	$ch = curl_init();
	if (!empty($data)) {
		$data = is_array($data)?http_build_query($data): $data;
		$url .= (strpos($url,'?')?  '&': "?") . $data;
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_POST, 0);
	//curl_setopt($ch, CURLOPT_PORT, $port);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

	$result = array('status'=>true);
	$result['result'] = curl_exec($ch);
	if (0 != curl_errno($ch)) {
		$result['status'] = false;
		$result['error']  = curl_error($ch);
		$result['code']  = curl_errno($ch);
	}
	curl_close($ch);
	return $result;
}


/**
 * 提交POST请求，curl方法
 * @param string  $url       请求url地址
 * @param mixed   $data      POST数据,数组或类似id=1&k1=v1
 * @param array   $header    头信息
 * @param int     $timeout   超时时间
 * @param int     $port      端口号
 * @return string            请求结果,
 *                            如果出错,返回结果为array('error'=>'','result'=>''),
 *                            未出错，返回结果为array('result'=>''),
 */
function curl_post($url, $data = array(), $header = array(), $timeout = 5, $port = 80)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	//curl_setopt($ch, CURLOPT_PORT, $port);
	!empty ($header) && curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	// var_dump('@curl_post', $url, $data,$header, $timeout,$port);
	if ( is_array($data) ) {
		// echo 'as a array data post', PHP_EOL;
	}
	if ( is_string($data) && !empty($data) && substr($data, 0, 1)=='{' && json_decode($data, true) ) {
		// echo 'as a json data post', PHP_EOL;
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	}
	$result = array('status'=>true);
	$result['result'] = curl_exec($ch);

	if (0 != curl_errno($ch)) {
		$result['status'] = false;
		$result['error']  = curl_error($ch);
		$result['code']  = curl_errno($ch);
	}
	curl_close($ch);

	return $result;
}

/**
 * 数据加密
 * @author yangyang13@leju.com
 * @param string $str 需加密的字符串
 */
function encode($str)
{
	$str_encode = base64_encode(substr(md5($str),10,6).base64_encode($str).substr(md5($str),20,6));
	return $str_encode;
}

/**
 * 数据解密
 * @author yangyang13@leju.com
 * @param string $str 需解密的字符串
 */
function decode($str)
{
	$str_decode = base64_decode(substr(base64_decode($str),6,-6));
	return $str_decode;
}

/**
* 可以统计中文字符串长度的函数
* @param $str 要计算长度的字符串
* @param $type 计算长度类型，0(默认)表示一个中文算一个字符，1表示一个中文算两个字符
*
*/
function abslength($str)
{
    if(empty($str)){
        return 0;
    }
    if(function_exists('mb_strlen')){
        return mb_strlen($str,'utf-8');
    }
    else {
        preg_match_all("/./u", $str, $ar);
        return count($ar[0]);
    }
}

/**
 * 判断字符长度，一个中文占1位，半角英文点0.5位
 */
function chineseLength( $str,$charset='utf-8' ){  
	if($charset=='utf-8') {
		$str = iconv('utf-8','gb2312//TRANSLIT', $str);  
	}
	$num = strlen($str);
	$cnNum = 0;
	for ( $i=0; $i<$num; $i++ ) {
		if ( ord(substr($str,$i+1,1))>127 ){
			$cnNum++;
			$i++;
		}
	}
	$enNum = $num-($cnNum*2);
	$number = ($enNum/2)+$cnNum;
	return ceil($number);
}  


function set_ajax_output($out_header=false) {
	if ( !defined('AJAX_OUTPUT') ) {
		define('AJAX_OUTPUT', true);
	}
	if ( $out_header ) {
		header('Content-Type: text/json');
	}
	return true;
}

function set_xml_output($out_header=false) {
	if ( !defined('AJAX_OUTPUT') ) {
		define('AJAX_OUTPUT', true);
	}
	if ( $out_header ) {
		header('Content-Type: text/xml;');
	}
	return true;
}

/**
 * ajax返回成功格式支持json
 * @param mixed $result
 * @return string
 */
function ajax_succ($info = '')
{
	set_ajax_output(true);
	die(
		json_encode(
			array(
				'status' => 'succ',
				'info' => $info,
			)
		)
	);
}

/**
 * ajax返回错误格式支持json
 * @param mixed $result
 * @return string
 */
function ajax_error($reason = '')
{
	set_ajax_output(true);
	die(
		json_encode(
			array(
				'status' => 'fail',
				'reason' => $reason,
			)
		)
	);
}


/**
 * 通用调试方法
 * @param $msg string 问题点描述
 * @param $ret array 问题信息列表
 * @param $mode string 调试信息详细模式 lite精简模式 full完整模式
 * @return bool 返回 true
 */
function debug($msg, $ret=null, $mode='full', $_debug=false) {
	if ( $_debug===false ) {
		$_debug = defined('APP_DEBUG') ? constant('APP_DEBUG') : false;
	}

	if ( $_debug === true ) {
		$debug = array(
			'code' => 'DEBUG',
			'msg' => &$msg,
		);
		if ( $ret ) {
			if ( $mode == false ) {
				$debug['trace'] = false;
			} else {
				$trace = debug_backtrace();
				if ( $mode == 'lite' ) {
					array_shift($trace);
					$filters = array(
						'file'=>'','line'=>'',
						'function'=>'','class'=>'',
						'type'=>'',/*'args'=>'',*/
					);
					foreach ( $trace as $i => &$item ) {
						$item = array_intersect_key($item, $filters);
					}
				}
				$debug['trace'] = &$trace;
			}
			$debug['ret'] = &$ret;
		}
		\Think\Log::record(var_export($debug, true));
	}
	return true;
}

/**
 * 获取前一天的零点到23：59：59
 * @return array time
 */
function getDayTime()
{
	$begin = strtotime(date('Y-m-d',strtotime('-1 day')));
	$end = strtotime(date('Y-m-d',time())) - 1;
	return array('begin'=>$begin,'end'=>$end);
}

/**
 * 乐居图库图片剪裁方法
 * @param $img string 图片地址
 * @param $width int 转换后的宽度
 * @param $height int 转换后的高度
 * @param $type string 使用的转换类型
 * 			smart 智能剪裁，先按最小边进行缩放后再居中剪裁
 * 			scale 仅缩放，按指定宽高进行图片缩放
 */
function changeImageSize($img, $width, $height, $type='smart') {
	$types = array(
		'smart' => 'cm:WIDTH:X:HEIGHT:',
		'scale' => 's:WIDTH:X:HEIGHT:',
	);
	if ( !array_key_exists($type, $types) ) {
		return false;
	}

	// 验证图片是否支持动态缩放
	$hosts = array(
		'src.leju.com',
	);
	$info = parse_url($img);
	$img_domain = isset($info['host']) ? $info['host'] : '';
	if ( !in_array($img_domain, $hosts) ) {
		return $img;
	}

	// 按乐居图库规则处理图片
	$info = explode('_', $img);
	$base = array(array_shift($info));
	// 获取文件扩展名
	$ext = explode('.', array_pop($info));
	array_push($info, $ext[0]);
	$ext = count($ext)>0 ? array_pop($ext) : false;
	$retains = array('p', 'mk', 'os', 'c', 's', 'rt');
	foreach ( $info as $_i => $seg ) {
		foreach ( $retains as $_r => $key ) {
			$len = strlen($key);
			if ( $key == substr($seg, 0, $len) ) {
				array_push($base, $seg);
				unset($retains[$_r]);
			}
		}
	}
	$current_type = $types[$type];
	$placeholder = array(':WIDTH:', ':HEIGHT:');
	$replace_set = array(strval($width), strval($height));
	array_push($base, str_replace($placeholder, $replace_set, $current_type));
	$result = implode('_', $base) . ( $ext ? '.'.$ext : '');

	return $result;
}
/**
 * 乐居图库图片批量处理方法, 通过文章内容，对内容中图库的图片进行统一剪裁处理
 * @param $content string 文章html内容
 * @param $width int 转换后的宽度
 * @param $height int 转换后的高度
 * @param $type string 使用的转换类型
 * 			smart 智能剪裁，先按最小边进行缩放后再居中剪裁
 * 			scale 仅缩放，按指定宽高进行图片缩放
 */
function changeImagesSize($content, $width, $height, $type='smart') {
	$pattern = '/<img\s*.*src="(?P<img>http:\/\/src\.leju\.com\/.+)"\s*.*>/U';
	//$p = '/<header.*>/U';
	$ret = preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
	if ( count($matches) > 0 ) {
		$_holder = $_replace = array();
		foreach ( $matches as $_i => $match ) {
			if ( !isset($match['img']) ) {
				continue;
			}
			$new = changeImageSize($match['img'], $width, $height, $type);
			if ( !$new ) {
				continue;
			}
			array_push($_holder, $match['img']);
			array_push($_replace, $new);
		}
		$content = str_replace($_holder, $_replace, $content);
	}
	return $content;
}

/**
 * 移动端知识、百科详情页中相关内容的更多链接
 */
function getMore($city = null)
{
	if(empty($city))
	{
		$city = cookie('B_CITY') ? cookie('B_CITY') : 'bj';
	}

	$return = array(
		'news' => "http://m.leju.com/touch/news/s/{$city}/toutiao/",
		'house' => "http://m.leju.com/house/{$city}/s/"
	);
	return $return;
}

/**
 * 设置跨域
 */
function setCorss ( $origin = '*' ) {
	$request_method = strtoupper($_SERVER['REQUEST_METHOD']);
	if ($request_method === 'OPTIONS') {
		header('Access-Control-Allow-Origin: '.$origin);
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		header('Access-Control-Allow-Headers: APPID, SECRET, TOKEN');

		header('Access-Control-Max-Age: 1728000');
		header('Content-Type: text/plain charset=UTF-8');
		header('Content-Length: 0',true);

		header('status: 204');
		header('HTTP/1.0 204 No Content');
	}

	if ($request_method === 'POST') {
		header('Access-Control-Allow-Origin: '.$origin);
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	}

	if ($request_method === 'GET') {
		header('Access-Control-Allow-Origin: '.$origin);
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	}
	return true;
}

function clear_all($area_str){ //过滤成纯文本用于显示
    if ($area_str!=''){
        $area_str = trim($area_str); //清除字符串两边的空格
        $area_str = strip_tags($area_str,""); //利用php自带的函数清除html格式
        $area_str = str_replace("&nbsp;","",$area_str);

        $area_str = preg_replace("/   /","",$area_str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
        $area_str = preg_replace("/
/","",$area_str);
        $area_str = preg_replace("/
/","",$area_str);
        $area_str = preg_replace("/
/","",$area_str);
        $area_str = preg_replace("/ /","",$area_str);
        $area_str = preg_replace("/  /","",$area_str);  //匹配html中的空格
        $area_str = trim($area_str); //返回字符串
    }
    return $area_str;
}

//过滤一些输入体
function filterInput(&$str){
	$str = preg_replace('/[\[\]<>,\.\'"\(\)]|(alert)|select|(update)|(delete)|(insert)/i', '', $str);
	return $str;
}

/*
 * 截取精确字符串长度
 */
function mystrcut ( $string , $length , $etc = '...' )
{
	$result	 = '';
	$string	 = html_entity_decode( trim( strip_tags( $string ) ) , ENT_QUOTES , 'UTF-8' );
	$strlen	 = strlen( $string );

	for ( $i = 0; (($i < $strlen) && ($length > 0) ); $i++ )
	{
		$number = strpos( str_pad( decbin( ord( substr( $string , $i , 1 ) ) ) , 8 , '0' , STR_PAD_LEFT ) , '0' );
		if ( $number )
		{
			if ( $length < 1.0 )
			{
				break;
			}
			$result .= substr( $string , $i , $number );
			$length -= 1.0;
			$i += $number - 1;
		}
		else
		{
			$result .= substr( $string , $i , 1 );
			$length -= 0.5;
		}
	}

	$result = htmlspecialchars( $result , ENT_QUOTES , 'UTF-8' );

	if ( $i < $strlen )
	{
		$result .= $etc;
	}
	return $result;
}

/**
 * @description 2013-1-9 by hongwang@leju.com
 * @param $string
 * @param bool $low 安全别级低
 * @return $string
 */
function clean_xss ( $html )
{
	$html=trim($html);
	$pattern=array(
		"'<\?php[^>]*[\?>]?'si",
		"'<script[^>]*?>.*?</script>'si",
		"'<style[^>]*?>.*?</style>'si",
		"'<frame[^>]*?>'si",
		"'<iframe[^>]*?>.*?</iframe>'si",
		"'<link[^>]*?>'si",
	);
	$replace = array_fill(0, count($pattern), '');
	return preg_replace($pattern,$replace,$html);
}

/**
 * 验证并转换时间格式
 */
function vaild_datestr ( $dtstr, $format='Y-m-d H:i:s', $offset=false ) {
	$time = strtotime($dtstr);
	if ( $time ) {
		if ( $offset !== false ) {
			$time = strtotime($offset, $time);
		}
		$time = date($format, $time);
	}
	return $time;
}

/**
 * 自动生成 url
 *
 */
function url( $page='index', $opts=array(), $type='touch', $mod='baike', $detected=false ) {
	$detected = true;
	$default = '#';
	$url = \Common\Logic\UrlLogic::getInstance($detected);
	$action = $type.ucfirst($mod).ucfirst($page);
	// for compation controller device var
	$type = $type=='pc' ? 'pc' : 'touch';
	$R = $url->setBase($type, $mod);
	if ( method_exists($url, $action) ) {
		// return $default;
	}
	$href = call_user_func_array(array($url, $action), $opts);
	return $href;
}

/**
 * 审计日志 helper 快速处理
 *
 */
function actMsg( $record ) {
	$logger = D('Adminlogs', 'Model', 'Common');
	return $logger->helper($record);
}

/**
 * 添加兼容配置
 */
if ( !function_exists('fastcgi_finish_request') ) {
	function fastcgi_finish_request() {}
}
