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

	public function Game($id) {
		$this->id = $id;
	}

}

//////////////////////////////////////////////////////////////////////

/** operation */
class GetGame {

}

class GetGameOperator {

	public function operate($operation, $dao) {
		var $game = $dao::findGameById(1);
		echo '         '."\n".'0'."\n".'Cris Stanza';
		echo F::getFromInstance($game);
	}

}

class GetGameDao extends D {
	
	public function findGameById($id) {
		return base::findById(new Game($id));
	}
}

?>