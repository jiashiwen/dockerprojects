<?php
/**
 * SEO服务逻辑
 * @author Yangyang13 <yangyang13@leju.com>
 */
namespace Common\Logic;

class SeoLogic {
	public function __construct() {
		$this->_tag = (strpos($_SERVER['HTTP_HOST'], 'm.')!==false)  ? '手机乐居' : '乐居';
	}

	//百科&词条首页
	public function index($type="baike")
	{
		$seo = array();
		if($type=="baike")
		{
			$seo['seo_title'] = $seo['title'] = "乐居房产百科,房产词条-{$this->_tag}";
			$seo['keywords'] = '乐居房产百科,房产词条,房产知识,买房注意事项,购房指南';
			$seo['description'] = '乐居房产百科包含丰富的房地产知识，专业权威的房地产名词解释，房地产名人介绍，为您提供最新的买房新闻，房产政策，买房流程等购房知识最新信息，为业主购房支招，轻松了解房产专业内容。';
		}
		else
		{
			$seo['seo_title'] = $seo['title'] = "房产词条-{$this->_tag}";
			$seo['keywords'] = '房产词条,房产百科,房产知识,购房指南';
			$seo['description'] = '乐居房产词条包含丰富的房产各类词条知识，包括房产最热词条，房产最热词条，房产名人百科，房产机构百科。';
		}

		return $seo;
	}

	//词条列表
	//传值是名人或百科,不传是全部
	public function llist($cateid = '')
	{
		$seo = array();

		if($cateid !== '')
		{
			$name = array(0=>'名人',1=>'机构');

			$seo['title'] = "房产{$name[$cateid]}-房产百科-{$this->_tag}";
			$seo['keywords'] = "房产{$name[$cateid]},房产百科,房产知识,购房指南";
		}
		else
		{
			$seo['title'] = "乐居房产百科，房产词条-{$this->_tag}";
			$seo['keywords'] = '乐居房产百科,房产词条,房产知识,买房注意事项,购房指南';
		}
		$seo['description'] = '乐居房产百科包含丰富的房地产知识，专业权威的房地产名词解释，房地产名人介绍，为您提供最新的买房新闻，房产政策，买房流程等购房知识最新信息，为业主购房支招，轻松了解房产专业内容。';

		$seo['seo_title'] = &$seo['title'];
		return $seo;
	}

	//词条详情
	public function wiki_detail($title, $tags, $content) {
		$seo = array();

		$seo['title'] = "{$title}-房产词条-{$this->_tag}";
		$seo['seo_title'] = str_replace(array("'", '"'), array('',''), $title);
		$seo['keywords'] = str_replace(array("'", '"'), array('',''), implode(',', $tags));
		// $seo['keywords'] = "{$title},房产词条,房产知识,购房指南";
		$content = strip_tags($content);
		$content = str_replace(array("'", '"', ' ', PHP_EOL), array('','','',''), $content);
		$content = trim($content);
		//mb_substr(strip_tags($content), 0, 80, 'UTF-8');
		$seo['description'] = mystrcut($content, 100);
		return $seo;
	}

	//知识详情
	//column:栏目 -线隔开
	//words:关键词 ,号隔开
	public function knowledge_detail($title, $column, $tags, $content)
	{
		$seo = array();

		$seo['title'] = "{$title}-房产百科-{$this->_tag}";
		$seo['seo_title'] = str_replace(array("'", '"'), array('',''), $title);
		$seo['keywords'] = str_replace(array("'", '"',' '), array('','',','), $tags);
		// $seo['keywords'] = "{$title},{$tags}";
		$content = strip_tags($content);
		$content = str_replace(array("'", '"', ' '), array('','',''), $content);
		$content = trim(trim($content, '　'));
		$content = mystrcut($content, 100);
		//mb_substr(strip_tags($content), 0, 80, 'UTF-8');
		$seo['description'] = $content;

		return $seo;
	}

	//搜索结果页
	public function search($keyword)
	{
		$seo = array();

		$seo['seo_title'] = $seo['title'] = "{$keyword}-房产百科-{$this->_tag}";
		$seo['keywords'] = "{$keyword},房产百科,房产知识,购房指南";
		$seo['description'] = '乐居房产百科包含丰富的房地产知识，专业权威的房地产名词解释，房地产名人介绍，为您提供最新的买房新闻，房产政策，买房流程等购房知识最新信息，为业主购房支招，轻松了解房产专业内容。';

		return $seo;
	}

	//知识&词条标签页
	public function tag($name)
	{
		$seo = array();

		$seo['seo_title'] = $seo['title'] = "{$name}-房产百科-{$this->_tag}";
		$seo['keywords'] = "{$name},房产百科,房产知识,购房指南";
		$seo['description'] = '乐居房产百科包含丰富的房地产知识，专业权威的房地产名词解释，房地产名人介绍，为您提供最新的买房新闻，房产政策，买房流程等购房知识最新信息，为业主购房支招，轻松了解房产专业内容。';

		return $seo;
	}

	//知识栏目
	public function cate($l1, $l2 = false, $l3 = false)
	{
		$cate = array(
			//新房知识
			1 => array(
				0 => array(
					'title'=>"新房知识-新房百科-房产百科-{$this->_tag}",
					'keywords'=>'新房知识,新房知识大全,新房知识汇总',
					'description'=>'乐居新房知识栏目，为您提供最新最全的新房知识,包括准备买房、看房选房、认购新房、签约订房、贷款还款、收房验房、退房维权，产权归属等知识。',
				),
				//准备买房
				5 => array(
					0 => array(
						'title'=>"准备买房知识,买房知识大全,买房注意事项-房产百科-{$this->_tag}",
						'keywords'=>'买房准备知识,买房知识大全,买房注意事项',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的买房准备知识,买房知识大全,买房注意事项，包括买房资格，买房能力，买房政策，交易流程等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//购房资格
					6 => array(
						'title'=>"购房资格-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'购房资格,买房政策,买房条件',
						'description'=>'乐居房产百科为您提供新房百科知识，包括不同购房群体购房时需要具备哪些资格才可以购房，根据各地不同的政策进行不同的购房资格解读。',
					),
					//购房需求
					7 => array(
						'title'=>"购房需求-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'购房需求,买房需求,户型需求',
						'description'=>'乐居房产百科为您提供新房百科知识，包括不同购房群体购房需求不同，比如未婚族、已婚族、，对应不同的需求，购房人会选择公寓、loft、二居、三居等不同户型等。',
					),
					//资金评估
					8 => array(
						'title'=>"资金评估-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'买房资金评估,购房资格,买房能力',
						'description'=>'乐居房产百科为您提供新房百科知识，包括评估买房人的买房能力，包括：全款买房还是贷款买房，付首付的能力，月供的能力。',
					),
					//准备流程
					9 => array(
						'title'=>"准备流程-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'准备流程,买房流程,如何买房',
						'description'=>'乐居房产百科为您提供新房百科知识，包括购房前锁定心仪房源，网上或实地进行探房，准备个人购房的相关材料。',
					),
				),
				//看房选房
				10 => array(
					0 => array(
						'title'=>"看房选房知识,房产知识,看房注意事项,选房注意事项-房产百科-{$this->_tag}",
						'keywords'=>'看房选房知识,房产知识,看房注意事项,选房注意事项',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的看房选房知识,房产知识,看房注意事项,选房注意事项，包括选新房技巧，新房户型，建筑面积，面积分类等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//选房技巧
					11 => array(
						'title'=>"选房技巧-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'选房技巧,选房攻略,如何选房',
						'description'=>'乐居房产百科为您提供新房百科知识，包括选房技巧包括：开发商实力、房源的地理交通位置、房源周边的配套建设、房源周边的未来规划、房源的装修情况等。',
					),
					//建筑类型
					12 => array(
						'title'=>"建筑类型-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'建筑类型,买房分类,房型分类',
						'description'=>'乐居房产百科为您提供新房百科知识，包括包括建筑用途、建筑的结构类型，建筑用途是指商业住宅还是普通住宅，建筑的结构类型是指多层、高层、塔楼、板楼等。',
					),
					//面积户型
					13 => array(
						'title'=>"面积户型-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'面积户型介绍,买房户型,买房面积',
						'description'=>'乐居房产百科为您提供新房百科知识，包括面积：使用面积、套内面积、公摊面积、赠送面积，面积怎么计算？得房率、使用率以及使用率等计算；户型包括：户型优劣、户型采光、户型格局等。',
					),
					//楼层地段
					14 => array(
						'title'=>"楼层地段-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'楼层地段分析,楼层地段知识,楼层地段好坏',
						'description'=>'乐居房产百科为您提供新房百科知识，包括楼层包括：楼层优劣、楼层采光等；地段包括：如何选择地段？地段配套如何？等',
					),
				),
				//认购新房
				15 => array(
					0 => array(
						'title'=>"认购新房知识,新房购买,如何购买新房,购买新房知识-房产百科-{$this->_tag}",
						'keywords'=>'认购新房知识,新房购买,如何购买新房,购买新房知识',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的认购新房知识,新房购买,如何购买新房,购买新房等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//认购要求
					16 => array(
						'title'=>"认购要求-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'买房认购要求,认购注意事项,如何认购',
						'description'=>'乐居房产百科为您提供新房百科知识，包括签认购协议书需要注意哪些要求？认购协议书中有哪些条款购房人需要了解？',
					),
					//认购流程
					17 => array(
						'title'=>"认购流程-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'买房认购流程,认购注意事项,认购的流程',
						'description'=>'乐居房产百科为您提供新房百科知识，包括认购新房的具体流程？新房购买中有哪些注意事项？',
					),
				),
				//签约定房
				18 => array(
					0 => array(
						'title'=>"签约订房知识,签约买房,新房签约-房产百科-{$this->_tag}",
						'keywords'=>'签约订房知识,签约买房,新房签约',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的签约订房知识,签约买房,新房签约，包括选交定金，资金托管，签约注意事项等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//新房签约
					19 => array(
						'title'=>"新房签约-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'新房签约流程,新房签约注意事项',
						'description'=>'乐居房产百科为您提供新房百科知识，包括新房网签的流程和材料以及期房如何进行网签？签订合同中需要注意什么？',
					),
					//定金订金
					36 => array(
						'title'=>"定金订金-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'签约订金,签约定金,定金与订金区别',
						'description'=>'乐居房产百科为您提供新房百科知识，包括定金和订金如何区分？定金能否要回来？交了定金还需要注意什么？',
					),
				),
				//贷款还款
				20 => array(
					0 => array(
						'title'=>"贷款还款知识,贷款买房,房屋贷款,贷款还款方式-房产百科-{$this->_tag}",
						'keywords'=>'贷款还款知识,贷款买房,房屋贷款,贷款还款方式',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的贷款还款知识,贷款买房,房屋贷款,贷款还款方式，包括商业贷款知识，公积金贷款知识，个人贷款知识等。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//贷款政策
					21 => array(
						'title'=>"买房贷款政策,新房贷款-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'买房贷款政策,买房贷款知识,新房贷款',
						'description'=>'乐居房产百科为您提供新房百科知识，包括针对不同城市购房贷款中，涉及到的贷款所需资料、标准、要求、本地政策法规等相关知识',
					),
					//贷款流程
					22 => array(
						'title'=>"贷款流程-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'贷款流程,买房贷款细节,如何贷款买房',
						'description'=>'乐居房产百科为您提供新房百科知识，包括针对办理购房贷款的过程中，所涉及到的每个关键节点、所需资料、注意事项、环节流程等知识',
					),
					//还款方式
					23 => array(
						'title'=>"买房还款方式-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'买房还款方式,买房还款细节,如何还房贷',
						'description'=>'乐居房产百科为您提供新房百科知识，包括针对银行贷款还款的不同方式，分析不同还款方式的特点和利弊知识。',
					),
					//还款技巧
					24 => array(
						'title'=>"买房还款技巧新房知识-房产百科-{$this->_tag}",
						'keywords'=>'买房还款技巧,买房还款攻略,如何还款',
						'description'=>'乐居房产百科为您提供新房百科知识，包括针对还款方式的不同特点，为网友提供利用适合网友的还款安排，例如减少还款利息等。',
					),
				),
				//收房验房
				25 => array(
					0 => array(
						'title'=>"收房验房知识,收费验房注意事项,如何验房收房-房产百科-{$this->_tag}",
						'keywords'=>'收房验房知识,收费验房注意事项,如何验房收房',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的收房验房知识,收费验房注意事项,如何验房收房，包括验房攻略，物业，退房，落户问题等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//交房事项
					26 => array(
						'title'=>"交房事项-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'买房交房,交房注意事项,如何交房',
						'description'=>'乐居房产百科为您提供新房百科知识，包括针对交房时网友应该注意到的要点、可能准备资料等的相关知识',
					),
					//交房流程
					27 => array(
						'title'=>"交房流程-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'买房交房流程,买房如何交房,交房的流程',
						'description'=>'乐居房产百科为您提供新房百科知识，包括帮助网友了解交房时的每个节点的注意事项，所需手续和资料等知识',
					),
					//验房攻略
					28 => array(
						'title'=>"验房攻略-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'新房验房,验房攻略,验房注意事项',
						'description'=>'乐居房产百科为您提供新房百科知识，包括帮助网友了解在验房时应该注意的要点和信息，验房过程中的细节和验房方法。',
					),
					//物业管理
					32 => array(
						'title'=>"物业管理-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'物业管理,新房物业管理,物业管理事项',
						'description'=>'乐居房产百科为您提供新房百科知识，包括帮助网友了解收房时物业交付、入住等的手续和流程，了解物业费和日常物业管理等信息。',
					),
				),
				//退房维权
				29 => array(
					0 => array(
						'title'=>"退房维权知识,退房诉讼,退房违约金多少-房产百科-{$this->_tag}",
						'keywords'=>'退房维权知识,退房诉讼,退房违约金多少',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的退房维权知识,退房诉讼,退房违约金多少，包括退房流程，退房绿色，退房攻略等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//退房流程
					30 => array(
						'title'=>"退房流程-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'退房流程,新房如何退房,退房申请',
						'description'=>'乐居房产百科为您提供新房百科知识，包括帮助网友了解怎样的条件下可以申请退房、退房所需的手续、资料、办理过程等方面的知识',
					),
					//业主维权
					31 => array(
						'title'=>"业主维权-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'业主维权,业主如何维权,业主维权方式',
						'description'=>'乐居房产百科为您提供新房百科知识，包括帮助网友了解在发现项目有问题的时候，可以在怎样的情况下通过法律手段维护自身合法权益。',
					),
				),
				//产权归属
				33 => array(
					0 => array(
						'title'=>"产权归属知识,产权交易,产权维护-房产百科-{$this->_tag}",
						'keywords'=>'产权归属知识,产权交易,产权维护',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的产权归属知识,产权交易,产权维护，包括产权归属如何确定，产权归属证明等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//产权争议
					34 => array(
						'title'=>"产权争议,新房产权问题-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'产权争议,新房产权,产权问题',
						'description'=>'乐居房产百科为您提供新房百科知识，包括介绍关于房产归属的相关案例和规定。',
					),
					//房产证署名
					35 => array(
						'title'=>"房产证署名-新房知识-房产百科-{$this->_tag}",
						'keywords'=>'房产证署名要求,房产证署名权益。',
						'description'=>'乐居房产百科为您提供新房百科知识，包括介绍关于房产证署名的要求、权益、署名流程以及政策等方面的知识。',
					),
				),
			),
			//二手房知识
			2 => array(
				0 => array(
					'title'=>"二手房知识-房产百科-{$this->_tag}",
					'keywords'=>'二手房知识,二手房相关知识,二手房百科',
					'description'=>'乐居二手房知识栏目，为您提供最新最全的二手房知识,包括买房、卖方，租房等知识。',
				),
				//买房
				64 => array(
					0 => array(
						'title'=>"买二手房,买二手房注意事项,二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'买二手房,买二手房注意事项,二手房知识',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的买二手房,买二手房注意事项,二手房知识，包括买二手房需要注意什么，买二手房方法，买二手房交税等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//准备买房
					67 => array(
						'title'=>"准备买房-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'准备买房,买房准备工作,买房流程',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括全面系统的了解二手房楼市以及购房前的准备，购房前要缜密思考和规划，买房预算成本以及预算内的价格，了解相关的购房资格和相关政策。',
					),
					//选房看房
					68 => array(
						'title'=>"选房看房-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'选房看房,看房中介,看房经纪人',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括多渠道的获取房源信息，充足的真实房源信息可以提供更多的选择并且减少找房的精力，选择的中介和经纪人尤为重要。',
					),
					//双方审核
					69 => array(
						'title'=>"双方审核-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'买房审核,房屋产权,质量审核,买房交易',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括如何分辨房本真假，辨别房屋的产权，房屋的质量核验，房屋是否有做抵押，什么样的房子不能买等，你需要了解二手房交易前必要做的事情。',
					),
					//签约订房
					70 => array(
						'title'=>"签约订房-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'房产合同签订,签约合同,违约责任',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括需要明确首付、过户、户口迁移的约定时间，为你答疑解惑签订合同时的相关细节和政策，让买卖二手房签合同的那些事变得简单和清晰。',
					),
					//贷款流程/全款
					71 => array(
						'title'=>"贷款流程/全款-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'贷款流程,贷款交易流程,公积金贷款',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括二手房购买者一定要摸清交易流程，首套房、二套房如何认定，首付的相关新规定，公积金贷款、商贷和组合贷的区别，选择适合您的贷款方式和还款方法。',
					),
					//缴税/过户
					72 => array(
						'title'=>"缴税/过户-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'买房缴税,过户缴税,缴税注意事项',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括买卖二手房时涉及到的税收政策，交税流程、最新政策带来的影响以及交税时的注意事项，过户时需要注意的事项，减少纠纷顺利购房。',
					),
					//入住
					73 => array(
						'title'=>"入住-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'入住二手房,入住事项,入住常识',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括购入二手房后要了解的入住事项，明确物业的相关信息以及管理费用、取暖费用等相关费用；如有租户要签订租户协议，明确车位使用权等以及其他信息。',
					),
				),
				//卖房
				65 => array(
					0 => array(
						'title'=>"卖二手房,出售二手房注意事项,如何出售二手房-房产百科-{$this->_tag}",
						'keywords'=>'卖二手房,出售二手房注意事项,如何出售二手房',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的卖二手房,出售二手房注意事项,如何出售二手房，包括买二手房需要注意什么，买二手房方法，买二手房交税等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//准备卖房
					74 => array(
						'title'=>"卖房知识,卖房注意事项-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'卖房知识,买房注意事项,卖方政策',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括售房者需要了解最新的楼市行情，看懂房产政策，熟悉卖房的相关法律，了解定价技巧，以便顺利快速的卖出房子。',
					),
					//房屋核验
					75 => array(
						'title'=>"房屋核验-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'房屋核验,二手房交易注意事项,房屋核验内容',
						'description'=>'二手房的房产证可以说是二手房交易过程中的核心内容，售房者需要到相关部门对所卖房子进行评估核验，明确房子的面积等相关信息。',
					),
					//签订合同/定金
					76 => array(
						'title'=>"卖房签订合同,卖房定金-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'卖房签订合同,卖房定金,卖房合同',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括售房者与买家签订合同需要了解的相关法律法规，包括定金、付款方式、税费缴纳以及过户的办理，为你答疑解惑签订合同时的相关细节和政策。',
					),
					//收取房款
					77 => array(
						'title'=>"卖房房款,房款收取-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'卖房房款,房款收取,房款如何收取',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括售房者要了解订金和定金的区别、明确首付金额和贷款方式，已经还款时间，户口迁出等相关事宜，务必在合同中明确指出，以免出现不必要的纠纷。',
					),
					//过户交接
					78 => array(
						'title'=>"过户交接-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'过户交接,二手房过户,过户注意事项',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括售房者需要持相关的证件办理土地及建物的所有权转移以及抵押权等他项权利的消除或者更名，交房要在过户以后，以免带来损失。',
					),
					//物业交割
					79 => array(
						'title'=>"物业交割-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'物业交割,二手房物业问题,物业交割注意事项',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括交易双方事先要约定好合同的细节，售房者需要明确物业的相关信息以及管理费用、如有租户要签订租户协议，明确车位使用权等以及其他信息。',
					),
				),
				//租房
				66 => array(
					0 => array(
						'title'=>"租房知识,二手房出租,租房注意事项-房产百科-{$this->_tag}",
						'keywords'=>'租房知识,二手房出租,租房注意事项',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的租房知识,二手房出租,租房注意事项。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//准备租房
					80 => array(
						'title'=>"准备租房-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'租房,租房房源,租房攻略',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括租房者需要根据预算成本选择相关的地段和小区，充足的真实房源信息可以提供更多的选择并且减少找房的精力，选对中介和经纪人对租房者尤为重要。',
					),
					//选房看房
					81 => array(
						'title'=>"选房看房-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'看房注意细节,看房攻略,如何选房看房',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括租房约看房的时候注意细节，设施能否正常使用，房主的证件和房产证能否出示都是选房时最基本的要素，选房攻略让你火眼金睛看穿一切。',
					),
					//签约合同
					82 => array(
						'title'=>"签约合同-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'租房合同,租房签约,签约合同注意事项',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括确认是否与房主签约，确认所有与钱相关的事项并尽量写入租赁合同，交接房屋内所有物品并列入合同，注意签约的细节以免让你的财产受损失。',
					),
					//退房流程
					83 => array(
						'title'=>"退房流程-二手房知识-房产百科-{$this->_tag}",
						'keywords'=>'租房退房,退房注意事项,租房如何退房',
						'description'=>'乐居房产百科为您提供二手房百科知识，包括租房的最后阶段，就是退房。让您在租房的最后阶段，愉快而圆满的结束一次租房过程。',
					),
				),
			),
			//家居知识
			3 => array(
				// 一级分类的设置
				0 => array(
					'title'=>"家居知识-房产百科-{$this->_tag}",
					'keywords'=>'家居知识,家居百科,家居常识',
					'description'=>'乐居家居知识栏目，为您提供最新最全的家居知识,包括设计、主材，攻略等知识。',
				),
				//设计
				37 => array(
					0 => array(
						'title'=>"家居知识,家居设计,家居常识-房产百科-{$this->_tag}",
						'keywords'=>'家居知识,家居设计,家居常识',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的家居知识,家居设计,家居常识，包括家居设计效果，家居设计大全，家居设计方案等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//风格
					38 => array(
						'title'=>"家居设计风格-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'家居设计风格,家居设计,设计风格',
						'description'=>'乐居房产百科为您提供家居设计风格百科知识，从建筑风格衍生出多种室内设计风格，根据设计师和业主审美和爱好的不同，又有各种不同的幻化体。风格栏目主要汇集家装设计各种风格知识。',
					),
					//案例
					39 => array(
						'title'=>"家居设计案例-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'家居设计案例,家居设计,设计案例鉴赏',
						'description'=>'乐居房产百科为您提供家居设计案例百科知识，各种装修案例汇集，给业主更多选择与灵感。家居知识，购房无忧尽在乐居房产百科！',
					),
					//攻略
					40 => array(
						'title'=>"家居设计攻略-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'家居设计攻略,家居设计,设计攻略',
						'description'=>'乐居房产百科为您提供家居设计攻略百科知识，针对家装设计的支招，小贴士，让业主根据推荐找到属于自己的家居装修风格。。家居知识，购房无忧尽在乐居房产百科！',
					),
				),
				//主材
				41 => array(
					0 => array(
						'title'=>"家居主材,主材挑选注意事项,主材分类-房产百科-{$this->_tag}",
						'keywords'=>'家居主材,主材挑选注意事项,主材分类',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的家居主材,主材挑选注意事项,主材分类，包括如何挑选家居主材，主材有哪些，主材价格等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//地板
					42 => array(
						'title'=>"地板知识-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'地板知识,家居主材,地板分析',
						'description'=>'乐居房产百科为您提供家居地板百科知识，包括地板的选购攻略、安装与保养知识。实木地板等级、环保等级、耐磨等级等知识。家居知识，购房无忧尽在乐居房产百科！',
					),
					//瓷砖
					43 => array(
						'title'=>"瓷砖知识-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'瓷砖知识,家居主材,瓷砖选购',
						'description'=>'乐居房产百科为您提供家居瓷砖百科知识，包括瓷砖的选购攻略、安装与保养知识。瓷砖的分类，瓷砖的日常保养及清洗、保养实用技巧等。家居知识，购房无忧尽在乐居房产百科！',
					),
					//木门
					44 => array(
						'title'=>"木门知识-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'木门知识,家居主材,木门选购',
						'description'=>'乐居房产百科为您提供家居木门百科知识，包括木门的选购攻略、安装与保养知识。木门，顾名思义，即木制的门。按照材质、工艺及用途可以分为很多种类。广泛适用于民、商用建筑及住宅。家居知识，购房无忧尽在乐居房产百科！',
					),
					//橱柜
					45 => array(
						'title'=>"橱柜知识-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'橱柜知识,家居主材,橱柜选购',
						'description'=>'乐居房产百科为您提供家居木门百科知识，包括橱柜的选购攻略、安装与保养知识。橱柜是指厨房中存放厨具以及做饭操作的平台。使用明度较高的色彩搭配，由五大件组成，柜体，门板，五金件，台面，电器。',
					),
					//卫浴
					46 => array(
						'title'=>"卫浴知识-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'卫浴知识,家居主材,卫浴选购',
						'description'=>'乐居房产百科为您提供家居卫浴百科知识，包括卫浴产品（花洒、洁具等）的选购攻略、安装与保养知识。卫浴是供居住者便溺、洗浴、盥洗等日常公共卫生活动的空间。一般指卫浴用品。家居知识，购房无忧尽在乐居房产百科！',
					),
					//集成吊顶
					47 => array(
						'title'=>"集成知识-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'集成吊顶知识,家居主材,集成吊顶选购',
						'description'=>'乐居房产百科为您提供家居集成吊顶百科知识，包括阳台吊顶、卫生间、厨房吊顶等的选购攻略、安装与保养知识。家居知识，购房无忧尽在乐居房产百科！',
					),
					//灯具
					48 => array(
						'title'=>"灯具知识-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'灯具知识,家居主材,灯具选购',
						'description'=>'乐居房产百科为您提供家居灯具百科知识，包括各种灯具的选购攻略、安装知识。灯具，是指能透光、分配和改变光源光分布的器具，包括除光源外所有用于固定和保护光源所需的全部零部件，以及与电源连接所必需的线路附件。家居知识，购房无忧尽在乐居房产百科！',
					),
					//开关插座
					49 => array(
						'title'=>"开关知识-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'开关插座知识,家居主材,开关插座选购',
						'description'=>'乐居房产百科为您提供家居开关插座百科知识，包括各种开关插座的选购攻略、安装知识。家居知识，购房无忧尽在乐居房产百科！',
					),
					//涂料
					50 => array(
						'title'=>"涂料知识-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'涂料知识,家居主材,涂料选购',
						'description'=>'乐居房产百科为您提供家居涂料百科知识，包括涂料的选购攻略、色彩搭配知识。涂料一般由四种基本成分：成膜物质（树脂、乳液）、颜料（包括体质颜料）、溶剂和添加剂（助剂）。家居知识，购房无忧尽在乐居房产百科！',
					),
				),
				//施工
				51 => array(
					0 => array(
						'title'=>"家装施工,施工方案,施工流程-房产百科-{$this->_tag}",
						'keywords'=>'家装施工,施工方案,施工流程',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的家装施工,施工方案,施工流程，包括家居装修施工流程，家居装修施工注意事项等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
				),
				//攻略
				58 => array(
					0 => array(
						'title'=>"家居攻略,家居实用攻略-房产百科-{$this->_tag}",
						'keywords'=>'家居攻略,家居实用攻略',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的家居攻略,家居实用攻略。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//导购
					59 => array(
						'title'=>"家居导购-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'家居导购,家居攻略,家居选购技巧',
						'description'=>'乐居房产百科为您提供家居导购百科知识，包括各种装修材料的导购攻略，靠谱的推荐让消费者明晰各类产品的功能进而更好根据自己的需要选择合适的产品。家居知识，购房无忧尽在乐居房产百科！',
					),
					//测评
					60 => array(
						'title'=>"家居测评-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'家居测评,家居攻略,家居测评方法',
						'description'=>'乐居房产百科为您提供家居测评百科知识，包括专业的家居产品评测、建材产品评测、家具产品评测等评测信息。家居知识，购房无忧尽在乐居房产百科！',
					),
				),
				//风水
				61 => array(
					0 => array(
						'title'=>"家居风水,家居风水禁忌大全,家居风水知识大全-房产百科-{$this->_tag}",
						'keywords'=>'家居风水,家居风水禁忌大全,家居风水知识大全',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的家居风水,家居风水禁忌大全,家居风水知识大全，包括家居风水布局摆设，家居风水禁忌等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//风水知识
					62 => array(
						'title'=>"风水知识-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'风水知识,家居风水,买房风水',
						'description'=>'乐居房产百科为您提供家居风水知识百科知识，包括住宅风水包括住宅选址、外部风水环境、庭院风水、门、主、灶风水，婚姻风水等方面。家居知识，购房无忧尽在乐居房产百科！',
					),
					//风水禁忌
					63 => array(
						'title'=>"风水禁忌-家居知识-房产百科-{$this->_tag}",
						'keywords'=>'风水禁忌,家居风水,家居风水禁忌',
						'description'=>'乐居房产百科为您提供家居风水禁忌百科知识，包括房屋装修中需要注意的风水方面禁忌。家居知识，购房无忧尽在乐居房产百科！',
					),
				),
			),
			//装修知识
			4 => array(
				// 一级分类的设置
				0 => array(
					'title'=>"装修知识-房产百科-{$this->_tag}",
					'keywords'=>'装修知识,装修常识,装修百科',
					'description'=>'乐居家居知识栏目，为您提供最新最全的家居知识,包括拆改、水电改造、防水、瓦木、油漆、验收等知识。',
				),
				//瓦木
				88 => array(
					0 => array(
						'title'=>"瓦木知识,装修知识,装修知识大全-房产百科-{$this->_tag}",
						'keywords'=>'瓦木知识,装修知识,装修知识大全',
						'description'=>'乐居网新房知识准备买房栏目，为您提供最新最全的瓦木知识,装修知识,装修知识大全，包括瓦木验收标准,验收注意事项等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
				),
				//施工
				84 => array(
					0 => array(
						'title'=>"家装施工,施工方案,施工流程-装修知识-房产百科-{$this->_tag}",
						'keywords'=>'家装施工,施工方案,施工流程',
						'description'=>'乐居新房知识准备买房栏目，为您提供最新最全的家装施工,施工方案,施工流程，包括家居装修施工流程，家居装修施工注意事项等知识。买房无忧，购房宝典尽在乐居房产百科！',
					),
					//拆改
					85 => array(
						'title'=>"拆改知识-装修知识-房产百科-{$this->_tag}",
						'keywords'=>'拆改知识,家居施工,拆改注意事项',
						'description'=>'乐居房产百科为您提供家居拆改百科知识，包括进场，拆墙，砌墙等方面的知识。【主体拆改知识】教你如何进行主体拆改等内容。装修知识，购房无忧尽在乐居房产百科！',
					),
					//水电改造
					86 => array(
						'title'=>"水电改造知识-装修知识-房产百科-{$this->_tag}",
						'keywords'=>'水电改造,家居施工,水电改造注意事项',
						'description'=>'乐居房产百科为您提供家居水电改造百科知识，包括凿线槽，水电改造并验收等方面的知识。根据装修配置，家庭人口，生活习惯，审美观念对原有开发商使用的水路，电路全部或部分更换的装修工序。水电改造又分为水路改造和电路改造。装修知识，购房无忧尽在乐居房产百科！',
					),
					//防水
					87 => array(
						'title'=>"防水-装修知识-房产百科-{$this->_tag}",
						'keywords'=>'防水,家居施工,施工防水',
						'description'=>'乐居房产百科为您提供家居防水百科知识，包括封埋线槽隐蔽水电改造工程，做防水工程，卫生间、厨房地面做24小时闭水试验等方面的知识。装修知识，购房无忧尽在乐居房产百科！',
					),
					//油漆
					89 => array(
						'title'=>"油漆-装修知识-房产百科-{$this->_tag}",
						'keywords'=>'油漆,家居施工,油漆选购',
						'description'=>'乐居房产百科为您提供家居油漆百科知识，包括墙面基层处理、墙面粉刷涂料等方面的知识。装修知识，购房无忧尽在乐居房产百科！',
					),
					//瓦木
					90 => array(
						'title'=>"瓦木-装修知识-房产百科-{$this->_tag}",
						'keywords'=>'瓦木,家居施工,瓦木选购',
						'description'=>'乐居房产百科为您提供家居瓦木百科知识，包括卫生间、厨房贴墙地面瓷砖，木工进场，吊天花板，石膏角线等方面的知识。装修知识，购房无忧尽在乐居房产百科！',
					),
					//验收
					91 => array(
						'title'=>"验收-装修知识-房产百科-{$this->_tag}",
						'keywords'=>'验收,家居施工,施工验收',
						'description'=>'乐居房产百科为您提供家居验收百科知识，包括整体施工验收，主材安装完毕验收等方面的知识。装修知识，购房无忧尽在乐居房产百科！',
					),
				),
			),
		);

		if ( isset($cate[$l1]) ) {
			// 是否存在指定的二级栏目
			if(isset($cate[$l1][$l2])) {
				// 是否存在指定的一级栏目
				if(isset($cate[$l1][$l2][$l3])) {
					$cate[$l1][$l2][$l3]['cateid'] = $l3;
					return $cate[$l1][$l2][$l3];
				}
				$cate[$l1][$l2][0]['cateid'] = $l2;
				return $cate[$l1][$l2][0];
			}
				$cate[$l1][0]['cateid'] = $l1;
			return $cate[$l1][0];
		} else {
			// 所有栏目指定存在问题，使用首页的打底数据
			return $this->index();
		}
	}
}