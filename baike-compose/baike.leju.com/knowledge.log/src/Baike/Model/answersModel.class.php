<?php
namespace baike\Model;
use Think\Model;

class AnswersModel extends Model {

	public function addReply($reply) {
		$_id = $reply['question_id'];

		$ret1 = $this->data($reply)->add();
		$ret2 = $this->where(array('_id'=>$_id))->setInc('i_replies', 1);
		return true;
	}

}
