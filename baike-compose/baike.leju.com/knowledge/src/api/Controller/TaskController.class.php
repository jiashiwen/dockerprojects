<?php
/**
 * 系统异步任务控制器
 * @author Robert <yongliang1@leju.com>
 */
namespace api\Controller;

class TaskController extends BaseController {
	// 信息类型 字典
	const INFO_ERROR = 0;		// 错误
	const INFO_WARNING = 1;		// 警告
	const INFO_INFO = 2;			// 信息
	const INFO_DEBUG = 3;		// 调试
	// 任务类型 字典
	const ASYNCTASK = 0;	// 异步任务
	const CRONTAB = 1;		// 定时任务
	// 任务状态 字典
	const WAITING = 0;		// 未开始
	const RUNNING = 1;		// 执行中
	const SUCCESS = 2;		// 执行成功
	const FAILURE = 3;		// 执行失败
	// 任务守护进程状态码 字典
	const STOPED = 0;		// 停止服务
	const PAUSED = 1;		// 暂停服务
	const DEBUGING = 8;		// 运行服务 调试模式
	const STARTING = 9;		// 运行服务 正常模式

	// 缓存对象
	protected $Q = null;
	// 锁类型
	protected $_c = array(
		'lrt' => 'TASK:DAEMON:LOCKED',	// 存储服务启动时间 0或空时为未启动 string
		'st' => 'TASK:DAEMON:STATUS',	// 存储当前服务状态 string
		'i' => 'TASK:DAEMON:INFO',		// 存储当前服务信息 hash
		'q' => 'TASK:TASKS:QUEUE',		// 存储任务队列 list
		'ts' => 'TASK:TASKS:SUCCESS',	// 存储成功任务队列 list
		'tf' => 'TASK:TASKS:FAILURE',	// 存储失败任务队列 list
	);
	protected $_levels = array();
	protected $_types = array();
	// 任务守护进程启动时间
	protected $_LRT = 0;
	// 任务守护进程状态码
	protected $_status = 0;
	// 待执行队列
	protected $_tasks = array();
	// 执行完的任务序列
	protected $_done = array();

	// 服务信息缓存
	protected $_I = array(
		'mem_init' => 0,	// 初始内存占用
		'mem_usage' => 0,	// 内容使用量
		'max_time' => 0,	// 单任务最大执行时间
		// 'task_max' => 0,	// 最大任务并行数
		'task_total' => 0,	// 执行的任务总数
		'task_count' => 0,	// 当前任务数

		'time_running' => 0, // 服务启动时间
		'time_lastcycle' => 0, // 最后一次回收时间
		'time_cycle' => 3600, // 默认回收已完成的任务执行周期时长 单位秒 cfg
		'time_lastupdate' => 0, // 最后一次信息更新时间
		'time_update' => 1, // 默认信息更新周期时长 单位秒 cfg
	);

	protected function _init() {
		$this->_I['mem_init'] = memory_get_usage();
		// 初始化缓存队列
		$this->Q = S(C('REDIS'));
		// get Last Run Timestamp
		$this->_LRT = $this->Q->Get($this->_c['lrt']);

		$this->_levels = array(
			self::INFO_ERROR => 'ERROR',
			self::INFO_WARNING => 'WARNING',
			self::INFO_INFO => 'INFO',
			self::INFO_DEBUG => 'DEBUG',
		);
		$this->_types = array(
			self::ASYNCTASK=>'',
			self::CRONTAB=>'',
		);
	}

	public function __construct() {
		parent::__construct();
		$this->_init();
	}
	public function __destruct() {
		$cname = strtolower(CONTROLLER_NAME);
		$aname = strtolower(ACTION_NAME);
		if ( $cname=='task' && $aname=='daemon' ) {
			$this->_('Kill Daemon with '.$cname.'/'.$aname.'', false, self::INFO_DEBUG);
			$this->_reset();
		}
	}
	protected function _auto_shutdown() {
		$this->_reset();
	}
	public function _register_shutdown() {
		register_shutdown_function(array($this, '_auto_shutdown'));
		/*
		//使用ticks需要PHP 4.3.0以上版本
		declare(ticks = 1);
		//安装信号处理器
		pcntl_signal(SIGTERM, array($this, "_sig_handler"));
		pcntl_signal(SIGHUP,  array($this, "_sig_handler"));
		pcntl_signal(SIGINT,  array($this, "_sig_handler"));
		*/
	}
	/*
	//信号处理函数
	protected function _sig_handler($signo) {
	     switch ($signo) {
	        case SIGTERM:
	            // 处理kill
	            echo PHP_EOL.'kill';
	            exit;
	            break;
	        case SIGHUP:
	            //处理SIGHUP信号
	            break;
	        case SIGINT:
	            //处理ctrl+c
	            echo PHP_EOL.'ctrl+c';
	            exit;
	            break;
	        default:
	            // 处理所有其他信号
	     }
	}
	*/

	/**
	 * 获取服务信息数据
	 */
	public function info() {
		$I = $this->Q->hGetAll($this->_c['i']);
		if ( $I['time_running']>0 ) {
			foreach ( $I as $i => &$t ) {
				$t = intval($t);
			}
			$this->_($I, true, self::INFO_INFO);
		} else {
			// $longtime = formatQATimer(NOW_TIME - $this->_LRT);
			// $this->_(date('Y-m-d H:i:s', $longtime).'Daemon not running', true, self::INFO);
			$this->_(date('Y-m-d H:i:s').' Daemon not running', true, self::INFO_WARNING);
		}
	}

	/**
	 * 停止服务进程
	 */
	public function shutdown() {
		if ( $this->_LRT!=0 ) {
			$this->_reset();
			$this->_(date('Y-m-d H:i:s').' Shutdown success!', true, self::INFO_INFO);
		} else {
			$this->_(date('Y-m-d H:i:s').' Daemon not running!', true, self::INFO_INFO);
		}
	}

	/**
	 * 启动异步任务监听服务
	 */
	public function daemon() {
		ignore_user_abort(true);
		set_time_limit(0);
		ini_set('memory_limit', '512M'); // 设置内存限制
		ini_set('default_socket_timeout', -1);
		$this->_register_shutdown();
		// date_default_timezone_set('PRC'); // 切换到中国的时间
		// TODO get last info or init new info, when info.status == 0 do reset()
		if ( false ) {
			// $this->_reset();
		}

		if ( $this->_LRT!=0 ) {
			$this->_('Already run a daemon!', false, self::INFO_INFO);
			exit;
		}
		if ( !IS_CLI ) {
			$this->_('Starting Daemon...', true, self::INFO_INFO);
			fastcgi_finish_request();
		}

		$this->_LRT = NOW_TIME;
		$this->_I['time_running'] = NOW_TIME;
		$this->_I['time_lastupdate'] = NOW_TIME;
		$run = !!$this->Q->Set($this->_c['lrt'], NOW_TIME);

		$this->_status = self::STARTING;
		$this->Q->Set($this->_c['st'], $this->_status);

		while ( $this->_status!=self::STOPED ) {
			if ( $this->_status==self::PAUSED ) {
				continue;
			}
			// 检查异步任务服务状态，并进行状态处理
			// $this->_check_status();
			// 检查服务信息，并存储数据
			$this->_check_info();

			// 检查是否有异步任务调用
			if ( $ret = $this->_check_tasks() ) {
				$this->_task_result($ret);
			}
			$this->_cycle_tasks();
		}
		// STOPED 时, 清理缓存数据
		$this->_reset();
	}

	/**
	 * 重置服务状态
	 */
	protected function _reset() {
		$this->_LRT = 0;
		$this->_status = 0;
		foreach ( $this->_c as $_t => $key ) {
			$this->Q->Del($key);
		}
		return true;		
	}

	/**
	 * 检查调试信息
	 *
	 * 1. 检查内存占用情况
	 * 2. 更新服务信息数据到缓存
	 */
	protected function _check_info() {
		$_current_time = time();
		$mem_usage = memory_get_usage() - $this->_I['mem_init'];
		if ( $this->_I['mem_usage'] != $mem_usage ) {
			$this->_I['mem_usage'] = $mem_usage;
			$msg = 'mem usage : '.$this->_I['mem_usage'];
			$this->_($msg, true, self::INFO_DEBUG);
		}
		// 当前时间 对比 最后一次更新时间，如果超过设置的更新周期，就更新服务端缓存到中心缓存
		$_lastupdate_time = intval($this->_I['time_lastupdate']);
		$_update_val = intval($this->_I['time_update']);
		if ( $_current_time - $_lastupdate_time >= $_update_val ) {
			$this->_($_current_time, true, self::INFO_DEBUG);
			$this->_I['task_count'] = intval($this->Q->lSize($this->_c['q']));
			$this->_I['time_lastupdate'] = $_current_time;
			$this->Q->hMSet($this->_c['i'], $this->_I);

			$this->_check_status();
		}
	}

	/**
	 * 检查异步任务服务状态(配置)，并进行状态处理
	 */
	protected function _check_status() {
		// 检查缓存连接状态
		if ( $this->Q->ping()!='+PONG' ) {
			$this->Q->close();
			// unset($this->Q);
			$this->Q = S(C('REDIS'));
		}

		$c = array(
			$this->_c['lrt'],
			$this->_c['st'],
		);
		$cache = $this->Q->MGet($c);
		$this->_LRT = intval($cache[0]);
		// $LRT = intval($this->_LRT);
		$this->_status = intval($cache[1]);
	}


	protected function _check_tasks() {
		$ret = false;
		$task = $this->Q->lPop($this->_c['q']);
		if ( $task ) {
			// echo 'get 1 task from Queue', PHP_EOL,
			// 	 var_export($task, true), PHP_EOL;

			$taskid = $this->_parse_task($task);
			if ( $taskid ) {
				$ret = $this->_run_task($taskid);
			}
		}
		return $ret;
	}

	protected function _parse_task( $task ) {
		$taskid = md5($task);
		var_export('task id '.$taskid.PHP_EOL);
		$_task = json_decode($task, true);
		$_current_time = time();
		// 如果执行时间超过当前时间，将任务插回队尾，返回
		if ( $_task['t'] > $_current_time ) {
			$this->Q->rPush($this->_c['q'], $task);
			return false;
		}
		$urlinfo = parse_url($_task['a']);
		var_export($urlinfo);
		var_export('parse task...'.PHP_EOL);
		echo PHP_EOL;
		if ( false ) {
			$taskid = false;
		}
		$this->_tasks[$taskid] = array(
			's' => self::WAITING,	// 任务状态
			'o' => $_task,	// 任务参数
			'r' => null,	// 执行结果
			't' => 0,		// 执行时间
			'p' => false,	// 缓存状态 false 未存储 true 存储
		);

		return $taskid;
	}

	protected function _run_task( $taskid ) {
		$result = false;
		var_export('running...'.PHP_EOL);
		if ( !array_key_exists($taskid, $this->_tasks) ) {
			$this->_(date('Y-m-d H:i:s').' '.$taskid.' not exists', true, self::INFO_INFO);
			return false;
		}
		$T = & $this->_tasks[$taskid];
		$TO = & $T['o'];
		$R = array('status'=>false, 'error'=>'run error');
		if ( $TO['m']=='GET' ) {
			$T['s'] = self::RUNNING;
			$this->_(date('H:i:s').' '.$taskid.' GET ['.$TO['a'].']', true, self::INFO_INFO);
			$R = curl_get($TO['a'], $TO['q'], $TO['h'], 3);
		}
		if ( $TO['m']=='POST' ) {
			$T['s'] = self::RUNNING;
			$this->_(date('H:i:s').' '.$taskid.' POST ['.$TO['a'].']', true, self::INFO_INFO);
			$R = curl_post($TO['a'], $TO['f'], $TO['h'], 3);
		}
		$T['t'] = time();
		if ( $R['status'] ) {
			$R['result'] = json_decode($R['result'], true);
			$T['s'] = self::SUCCESS;
			$T['r'] = $R;
			$_tk = $this->_c['ts'];
			$result = true;
		} else {
			$T['s'] = self::FAILURE;
			$T['r'] = $R;
			$_tk = $this->_c['tf'];
		}
		$T['p'] = $this->Q->rPush($_tk, json_encode($T));
		$this->_done[$taskid] = & $T;
		$this->_I['task_total'] += 1;

		var_export($T);
		echo PHP_EOL;
		unset($TO);
		unset($this->_tasks[$taskid]);
		unset($T);
		return $result;
	}

	protected function _task_result( $result ) {
		echo 'task_result: ', var_export($result, true), PHP_EOL;
	}

	/**
	 * 清理任务队列
	 * 1. 将已完成并超时的任务从任务队列中清理出去
	 */
	protected function _cycle_tasks() {
		$_current_time = time();
		$i = 0;
		foreach ( $this->_done as $id => &$task ) {
			if ( $task['t']>0 && $_current_time-$task['t']>$this->_I['time_cycle'] ) {
				$i += 1;
				$this->_I['time_lastcycle'] = $_current_time;
				unset($this->_done[$id]);
			}
		}
		return $i;
	}

	/**
	 * 检查十分钟之内的下一个定时任务执行时间
	 */
	protected function _check_crontab() {

	}

	/**
	 * 自动匹配执行模式，并输出信息
	 *
	 */
	protected function _($msg, $status, $level) {
		$levels = &$this->_levels;
		if ( !in_array($level, $levels) ) { $level = self::INFO_DEBUG; }
		$_level = $levels[$level];

		if ( $status==false ) {
			// 显示错误信息
			if ( !IS_CLI ) {
				$this->ajax_return(array('s'=>false, 'l'=>$_level, 'msg'=>$msg));
			} else {
				if ( $level!=self::INFO_DEBUG || $this->_status==self::DEBUGING ) {
					$prefix = '['.$_level.'][FAIL]('.$this->_status.') ';
					echo $prefix, $msg, PHP_EOL;
				}
			}
		} else {
			// 显示成功信息
			if ( !IS_CLI ) {
				$this->ajax_return(array('s'=>true, 'l'=>$_level, 'msg'=>$msg));
			} else {
				if ( $level!=self::INFO_DEBUG || $this->_status==self::DEBUGING ) {
					$prefix = '['.$_level.'][SUCC]('.$this->_status.') ';
					echo $prefix, $msg, PHP_EOL;
				}
			}
		}
		return true;
	}

	/**
	 * 添加任务
	 */
	public function createTask() {
		echo '@createTask', PHP_EOL;
		// var_dump(I('post.'));
		$P = file_get_contents('php://input');
		if ( substr($P, 0, 1)!='{' ) {
			$this->_('task data fail', false, self::INFO_ERROR);
			exit;
		}
		$P = json_decode($P, true);
		if ( empty($P) || !isset($P['a']) || !isset($P['m']) ) {
			$this->_('task core data (a, m) not exists', false, self::INFO_ERROR);
			exit;
		}
		// $methods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTION', 'HEAD');
		$methods = array('GET', 'POST',);
		$method = strtoupper(trim($P['m']));
		if ( !in_array($method, $methods) ) {
			$this->_('task method error', false, self::INFO_ERROR);
			exit;
		}
		$ret = $this->Q->rPush($this->_c['q'], json_encode($P));
		$this->_('Add Task Success', !!$ret, self::INFO_INFO);
	}

	public function demoAddTask() {
		$api = 'http://ld.api.baike.leju.com/task/createTask';

		$method = 'GET';
		$headers = array(
		 	'Host: ld.api.baike.leju.com',
		);
		// $headers = array();
		$queries = array(
			'r' => rand(0,10),
		);
		$fields = array();
		$attime = 0;
		$callback = '';

		$data = array(
			'a' => 'http://192.168.99.99/Task/showdemo',
			'm' => $method,
			'h' => $headers,
			'q' => $queries,
			'f' => $fields,
			't' => $attime,
			'c' => $callback,
		);

		echo $api, PHP_EOL;
		print_r($data);
		$ret = curl_post($api, json_encode($data), array(), 3);
		var_export($ret);
		if ( $ret['status'] ) {
			$ret['result'] = json_decode($ret['result'], true);
		}
		var_export($ret);
	}
	public function index() {
		var_dump(IS_CLI, IS_CGI);
		echo "This is task", PHP_EOL;
	}
	public function index2() {
		echo "This is task2", PHP_EOL;
	}
	public function showdemo() {
		// if ( !IS_CLI ) {
		// 	fastcgi_finish_request();
		// }
		$r = I('get.r', 0, 'intval');
		if ( $r==5 ) {
			$this->_('run fail', false, self::INFO_ERROR);
		} else {
			$this->_('run success', false, self::INFO_INFO);
		}
	}
	public function formtest() {
		$form = I('post.');
		$form2= $_POST;
		print_r($form);
		echo '<hr>', PHP_EOL;
		print_r($form2);
	}
}