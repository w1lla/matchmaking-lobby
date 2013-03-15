<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\MatchMakingLobby\Services;

class PlayerInfo
{
	const PLAYER_STATE_REPLACED = -5;
	const PLAYER_STATE_CANCEL = -4;
	const PLAYER_STATE_GIVE_UP = -3;
	const PLAYER_STATE_QUITTER = -2;
	const PLAYER_STATE_NOT_CONNECTED = -1;
	const PLAYER_STATE_CONNECTED = 1;

	/** @var PlayerInfo[] */
	static private $instances = array();

	/** @var string */
	public $login;

	/** @var float */
	public $ladderPoints;

	/** @var array */
	public $allies = array();

	/** @var int */
	public  $karma = 0;

	/** @var \DateTime */
	private $readySince = null;

	/** @var \DateTime */
	private $awaySince = null;

	/** @var Match */
	private $match = null;

	/** @var string */
	private $server = null;

	/**
	 * @param string $login
	 * @return PlayerInfo
	 */
	static function Get($login)
	{
		if(!isset(self::$instances[$login])) self::$instances[$login] = new PlayerInfo($login);

		return self::$instances[$login];
	}

	/**
	 * @return PlayerInfo[]
	 */
	static function GetReady()
	{
		$ready = array_filter(self::$instances, function($p)
			{
				return $p->isReady();
			});
		usort($ready, function($a, $b)
			{
				return $b->getWaitingTime() - $a->getWaitingTime();
			});
		return $ready;
	}

	/**
	 * Destroy players disconnected for more than 1 hour
	 */
	static function CleanUp()
	{
		$limit = new \DateTime('-1 hour');
		foreach(self::$instances as $login => $player)
			if($player->awaySince && $player->awaySince < $limit) unset(self::$instances[$login]);
	}

	private function __construct($login)
	{
		$this->login = $login;
	}

	/**
	 * @return bool
	 */
	function isReady()
	{
		return (bool) $this->readySince;
	}

	/**
	 * @return int
	 */
	function getWaitingTime()
	{
		return time() - $this->readySince->getTimestamp();
	}

	/**
	 * @param bool $ready
	 */
	function setReady($ready = true)
	{
		$this->readySince = $ready ? new \DateTime() : null;
	}

	/**
	 * @param bool $away
	 */
	function setAway($away = true)
	{
		$this->awaySince = $away ? new \DateTime() : null;
		$this->readySince = null;
	}

	/**
	 * @return bool
	 */
	function isAway()
	{
		return (bool) $this->awaySince;
	}
}

?>
