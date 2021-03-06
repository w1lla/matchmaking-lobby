<?php
/**
 * @version     $Revision: $:
 * @author      $Author: $:
 * @date        $Date: $:
 */

namespace ManiaLivePlugins\MatchMakingLobby\Windows;

use ManiaLib\Gui\Elements;

class StartSoloMatch extends \ManiaLive\Gui\Window
{

	/** @var Elements\Bgs1 */
	protected $background;
	
	/** @var Elements\Label */
	protected $label;
	
	/** @var Elements\Label */
	protected $transferLabel;
	
	/** @var Elements\Label */
	protected $cancelLabel;

	/** @var \ManiaLive\Gui\Controls\Frame */
	protected $players;
	
	/** @var Elements\Label */
	protected $dico = array();
	
	protected $time;

	protected function onConstruct()
	{
		$this->background = new Elements\Quad(320, 142);
		$this->background->setAlign('center', 'center');
		$this->background->setImage('http://static.maniaplanet.com/manialinks/lobbies/background.png',true);
		$this->addComponent($this->background);
		
		$this->label = new Elements\Label(200, 20);
		$this->label->setPosY(47);
		$this->label->setAlign('center', 'center2');
		$this->label->setStyle(\ManiaLib\Gui\Elements\Label::TextRaceMessageBig);
		$this->label->setTextid('text');
		$this->label->setId('info-label');
		$this->label->setTextSize(7);
		$this->addComponent($this->label);
		
		$this->transferLabel = clone $this->label;
		$this->transferLabel->setPosY(47);
		$this->transferLabel->setTextColor(null);
		$this->transferLabel->setTextid('transferText');
		$this->transferLabel->setId('transfer-label');
		$this->addComponent($this->transferLabel);
		
		$this->cancelLabel = new Elements\Label(200);
		$this->cancelLabel->setPosY(-47);
		$this->cancelLabel->setAlign('center', 'center2');
		$this->cancelLabel->setStyle(\ManiaLib\Gui\Elements\Label::TextRaceMessageBig);
		$this->cancelLabel->setTextColor('AAA');
		$this->cancelLabel->setTextid('cancel');
		$this->cancelLabel->setId('cancel-label');
		$this->cancelLabel->setTextSize(7);
		$this->addComponent($this->cancelLabel);

		$layout = new \ManiaLib\Gui\Layouts\Column();
		$layout->setMarginHeight(2);
		
		$this->players = new \ManiaLive\Gui\Controls\Frame();
		$this->players->setLayout($layout);
		$this->players->setPosition(0, 33);
		$this->addComponent($this->players);
	}
	
	function set(array $players, $time)
	{
		$this->time = $time;
		
		$playerCard = new \ManiaLivePlugins\MatchMakingLobby\Controls\PlayerDetailed();
		$playerCard->setAlign('center');
		
		foreach($players as $player)
		{
			$playerCard->nickname = $player->nickname;
			$playerCard->zone = $player->zone;
			$playerCard->rank = $player->rank;
			$playerCard->avatarUrl = 'file://Avatars/'.$player->login.'/Default';
			$playerCard->countryFlagUrl = $player->zoneFlag;
			$playerCard->echelon = $player->echelon;
			$this->players->addComponent(clone $playerCard);
		}
		
	}
	
	protected function onDraw()
	{
		$this->posZ = 5;
		$countdown = (int)$this->time;
		
		\ManiaLive\Gui\Manialinks::appendScript(<<<MANIASCRIPT
#RequireContext CMlScript
#Include "MathLib" as MathLib
#Include "TextLib" as TextLib
main()
{
	declare Integer countdownTime = CurrentTime;
	declare Integer countdownTimeLeft = $countdown;
	declare Boolean waiting = False;
	declare CMlLabel label <=> (Page.MainFrame.GetFirstChild("info-label") as CMlLabel);
	declare CMlLabel label2 <=> (Page.MainFrame.GetFirstChild("transfer-label") as CMlLabel);
	declare CMlLabel label3 <=> (Page.MainFrame.GetFirstChild("cancel-label") as CMlLabel);
	declare Text labelText = label.Value;
	label.SetText(TextLib::Compose(labelText, TextLib::ToText(countdownTimeLeft)));
	label2.Hide();
	label3.Hide();

	while(True)
	{
		if(countdownTimeLeft > 0 && CurrentTime - countdownTime > 1000)
		{
			countdownTime = CurrentTime;
			countdownTimeLeft = countdownTimeLeft - 1;
			label.SetText(TextLib::Compose(labelText, TextLib::ToText(countdownTimeLeft)));
		}
		else if(countdownTimeLeft <= 0)
		{
			label2.Show();
			label.Hide();
			waiting = True;
		}
		yield;
	}
}
MANIASCRIPT
		);
		
		\ManiaLive\Gui\Manialinks::appendXML(
		\ManiaLivePlugins\MatchMakingLobby\Utils\Dictionary::getInstance()->getManialink(array(
				'text' => 'launchMatch',
				'transferText' => 'transfer',
				'cancel' => 'cancel',
		)));
	}
	
	protected function secureNicknames(array $array)
	{
		return array_map(function ($e) { return '$<'.$e.'$>'; }, $array);
	}
	

}

?>
