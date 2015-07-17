<?php

class Game {

	/** type=string */
	var $board;

	/** type=numeric&id=true */
	var $id;

	/** type=numeric */
	var $status;

	/** type=string */
	var $turn;

	public function Game($id=null) {
		$this->id = $id;
	}

}

//////////////////////////////////////////////////////////////////////

/* operation */
class GetGame {

}

class GetGameOperator {

	public function operate($operation, $dao) {
		$game = $dao->findGameById(1);
		echo F::getFromInstance($game);
	}

}

class GetGameDao extends D {
	
	public function findGameById($id) {
		return parent::findById(new Game($id));
	}

	public function rowToObjectTransformer($row) {
		return parent::setFromResultSet($row, new Game());
	}

}

?>