<?php

// src/Controller/FeedController.php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Team;
use App\Entity\Player;
use App\Entity\Event;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class FeedController extends AbstractController
{
	private $em;


    /**
     * Game update listener
     * @Route("/listener", name="listener")
     */
    public function listener(Request $request)
    {
	    $this->em = $this->getDoctrine()->getManager();

	    $type = $request->get("type");
	    $matches = $request->get("matches");

	    foreach ($matches as $gameData) {
	    	$game = $this->updateOrCreateGame($gameData);

	    	if(isset($gameData->players))
	    		$this->updatePlayers($game, $gameData->players);

	    	if(isset($gameData->events))
	    		$this->updatePlayers($game, $gameData->events);

	    	$this->em->flush();
	    }

	    echo 'Success!';
    }

    /**
     * Create or update the game information in database
     * @param array
     * @return object Game
     */
    private function updateOrCreateGame($gameData)
    {
	    $game = $this->em->getRepository(Game::class)->findOneBy(['externalId' => $gameData->id]);

	    if(!$game){
	        $game = new Game();
	        $game->setExternalId($gameData->id);
	    }

	    $team = $this->em->getRepository(Team::class)->findOneBy(['externalId' => $gameData->teamId]);

	    $game->setPlace();
	    $game->setDatetime();
	    $game->setEnded();
	    $game->setResult($gameData->result[0].':'.$gameData->result[0]); // Adversary result always after colon
	    $game->setTeam($team);

	    $this->em->persist();

	    return $game;
    }

    /**
     * Create or update the game players information in database
     * @param object Game
     * @param array
     */
    private function updatePlayers($game, $playersData)
    {
    	foreach ($playersData as $playerData) {
	    	$player = $this->em->getRepository(Player::class)->findOneBy(['externalId' => $playerData]);
	    	if(!$game->contains($player)){
		        $game->addPlayer($player);
		    }
    	}

    	return;
    }

    /**
     * Create or update the game events information in database (goals, penalties, cards, etc.)
     * @param object Game
     * @param array
     */
    private function updateEvents($game, $eventsData)
    {
    	foreach ($eventsData as $eventData) {
	    	$eventType = $this->em->getRepository(EventType::class)->findOneBy(['name' => $eventData->type]);
	    	$player = $this->em->getRepository(Player::class)->findOneBy(['externalId' => $eventData->player]);
	    	$event = $this->em->getRepository(Event::class)->findOneBy([
	    		'game' => $game->getId(),
	    		'datetime' => $eventData->datetime,
	    		'player' => $player->getId()
	    	]);

	    	if (!$event) {
		        $event = new Event();
		        $event->setGame($game);
		        $event->setPlayer($player);
		        $event->setDatetime($eventData->datetime);
		        $event->setEventType($eventType);

		        if($eventType->getId() == 3) { // 1 is Goal
		        	$sms = array();

		        	$sms['adversary'] = $game->getTeam()->getName();
		        	$sms['player'] = $player->getName();
		        	$sms['time'] = $event->getDatetime()->format('H:i:s');

		        	// // SMS feature request
		        	// sendByEmail(json_encode($sms));
		        }	
		    }
    	}

    	return;
    }
}