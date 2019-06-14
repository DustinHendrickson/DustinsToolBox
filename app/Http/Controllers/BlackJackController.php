<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Deck;
use App\Http\Controllers\Player;

/**
 * Class BlackJackController
 * @package App\Http\Controllers
 */
class BlackJackController extends Controller
{
    /**
     * Player Index Reference
     * 0 = Dealer
     * 1 = Normal Player
     * 2 = AI Player
     *
     * @const array PLAYERS
     */
    private CONST PLAYERS = array(0,1,2);

    /**
     * @var
     */
    private $deck;

    /**
     * @var array
     */
    private $players = array();


    /**
     * Options:
     * "STATE_DEALING",
     * "STATE_PLAYER_TURN",
     * "STATE_AI_TURN",
     * "STATE_DEALER_TURN",
     * "STATE_GAME_OVER"
     *
     * @var string
     */
    private $current_game_state;


    /**
     * @param $arg_player_id
     * @param $arg_player_name
     */
    public function addPlayer($arg_player_id, $arg_player_name)
    {
        $this->players[$arg_player_id] = new Player($arg_player_name);
    }

    /**
     * @param $arg_player_id
     *
     * @return player
     */
    public function getPlayer($arg_player_id)
    {
        return $this->players[$arg_player_id];
    }

    /**
     * @return string
     */
    public function getCurrentGameState()
    {
        return $this->current_game_state;
    }

    /**
     * @param string $current_game_state
     */
    private function setCurrentGameState($current_game_state)
    {
        $this->current_game_state = $current_game_state;
    }

    /**
     * Starts a new game
     *
     * @param string $arg_player_name
     */
    public function setupNewGame($arg_player_name = "Player")
    {
        // Add our players.
        $this->addPlayer(0, "Dealer");
        $this->addPlayer(1, $arg_player_name);
        $this->addPlayer(2, "AI");

        // Setup a new Deck.
        $this->deck = new Deck();

        // Setup defaults
        $this->setCurrentGameState('STATE_DEALING');

        // Deal
        $this->deck->shuffle();
        $this->dealInitialCards();
        $this->advanceState();
    }

    /**
     * Advances the current game state based on the current state.
     */
    public function advanceState()
    {
        $new_state = "";
        switch($this->getCurrentGameState()) {
            case "STATE_DEALING":
                $new_state = "STATE_PLAYER_TURN";
                break;
            case "STATE_PLAYER_TURN":
                $new_state = "STATE_AI_TURN";
                if($this->getPlayer(2)->isPickedStay() == true) {
                    $new_state = "STATE_DEALER_TURN";
                }
                break;
            case "STATE_AI_TURN":
                $new_state = "STATE_DEALER_TURN";
                if($this->getPlayer(0)->isPickedStay() == true) {
                    $new_state = "STATE_PLAYER_TURN";
                }
                break;
            case "STATE_DEALER_TURN":
                $new_state = "STATE_PLAYER_TURN";
                // If player stays, we skip them and let the AI play.
                if($this->getPlayer(1)->isPickedStay() == true) {
                    $new_state = "STATE_AI_TURN";
                }
                break;
        }

        $this->setCurrentGameState($new_state);
    }

    /**
     * Deals a drawn card to a player
     *
     * @param int    $arg_player_id
     * @param string $arg_status
     */
    private function dealCard($arg_player_id, $arg_status = 'face_up')
    {
        // Give player a new card from the deck.
        $this->getPlayer($arg_player_id)->addToHand($this->deck->drawCard($arg_status));

        // Player has a new card, so let's re-calc their hand value.
        $this->getPlayer($arg_player_id)->calculateHandValue();
    }

    /**
     * Deals the initial amount of cards for a new game.
     */
    private function dealInitialCards()
    {
        $this->dealCard(0, 'face_down');
        $this->dealCard(1);
        $this->dealCard(2);
        $this->dealCard(0);
        $this->dealCard(1);
        $this->dealCard(2);
    }

    /**
     * Performs the action sent for the player specified.
     *
     * @param int $arg_player_id
     * @param string $arg_player_action
     */
    public function doPlayerAction($arg_player_id, $arg_player_action)
    {
        switch ($arg_player_action) {
            case "Hit Me":
                $this->dealCard($arg_player_id);
                break;
            case "Stay":
                $this->getPlayer($arg_player_id)->setPickedStay(true);
                break;
        }
    }

    /**
     * Performs a check that all players have picked Stay and to calculate the end of the game.
     */
    public function endGameCheck()
    {
        if(($this->getPlayer(0)->isPickedStay() == true && $this->getPlayer(1)->isPickedStay() == true && $this->getPlayer(2)->isPickedStay() == true) || ($this->getPlayer(0)->didPlayerBust() == true)
        ) {
            $this->setCurrentGameState("STATE_GAME_OVER");

            // Flip over any hidden cards for the Dealer
            $this->getPlayer(0)->flipCards();

            // Re-Calculate dealers hand with face up cards.
            $this->getPlayer(0)->calculateHandValue();
        }
    }

    /**
     * Get the winner of the current game.
     *
     * @return string
     */
    public function getEndGameStatus()
    {
        $winning_players = array();
        $non_busting_players = array();
        $highest_seen_total = $this->getHighestHandTotal();
        $return = "Winners: ";

        // Fill an array with non busting players.
        foreach(self::PLAYERS as $player) {
            if ($this->getPlayer($player)->didPlayerBust() == false) {
                $non_busting_players[] = $player;
            }
        }

        // Add players with the highest seen total, who have not busted to the winning array.
        foreach($non_busting_players as $player) {
            if($this->getPlayer($player)->getHandTotal() == $highest_seen_total) {
                $winning_players[] = $player;
            }
        }

        // Check if the Dealer is in the winning array, this overrides player ties with the dealer.
        $dealer_wins = array_search(0, $winning_players);

        // If the Dealer busted, all non busted players win!
        if($this->getPlayer(0)->didPlayerBust() == true) {
            $winning_players = $non_busting_players;
        }

        // Return the winning player names.
        if($dealer_wins === false) {
            foreach ($winning_players as $player) {
                $return .= $this->getPlayer($player)->getName() . ", ";
            }
        } else {
            $return .= $this->getPlayer(0)->getName();
        }

        // If everyone busted, nobody wins.
        if(empty($winning_players)) {
            $return = "Nobody";
        }

        return $return;

    }

    /**
     * Compares all players hands and returns the highest non busting hand value
     *
     * @return int
     */
    private function getHighestHandTotal()
    {
        $highest_seen_total = 0;

        foreach(self::PLAYERS as $player) {
            if($this->getPlayer($player)->getHandTotal() > $highest_seen_total && $this->getPlayer($player)->didPlayerBust() == false) {
                $highest_seen_total = $this->getPlayer($player)->getHandTotal();
            }
        }

        return $highest_seen_total;
    }
}
