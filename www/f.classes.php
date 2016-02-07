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

	/** type=string */
	var $turnPiece;

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

//////////////////////////////////////////////////////////////////////

/* operation */
class SetGame {
}

class SetGameOperator {

	public function operate($operation, $dao) {
		$game = F::setFromRequestParameters(new Game());
		if ($game->turn == 'Hellmuth') {
			$game->turn = 'Cris Stanza';
		}
		$dao->saveGame($game);
		echo 0;
	}

}

class SetGameDao extends D {
	
	public function saveGame($game) {
		return parent::update($game);
	}

}

?>
