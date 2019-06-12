<?php

namespace App\Http\Controllers;

use Storage;

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
     * @var array
     */
    private $base_deck = array();

    /**
     * @var array
     */
    private $current_deck = array();

    /**
     * @var array
     */
    private $player_hand = array();

    /**
     * @var array
     */
    private $player_hand_total = array();

    /**
     * @var array
     */
    private $player_visible_hand_total = array();

    /**
     * @var array
     */
    private $player_name = array();

    /**
     * @var array
     */
    private $player_picked_stay = array();

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
     * @return array
     */
    private function getBaseDeck()
    {
        return $this->base_deck;
    }

    /**
     * @param array
     */
    private function setBaseDeck($base_deck)
    {
        $this->base_deck = $base_deck;
    }

    /**
     * @return array
     */
    private function getCurrentDeck()
    {
        return $this->current_deck;
    }

    /**
     * @param array $current_deck
     */
    private function setCurrentDeck($current_deck)
    {
        $this->current_deck = $current_deck;
    }

    /**
     * @param int $player_id
     *
     * @return array
     */
    public function getPlayerHand($player_id)
    {
        return $this->player_hand[$player_id];
    }

    /**
     * @param int $player_id
     * @param int $card_position
     * @param string $key
     * @param mixed $value
     */
    private function setCardPropertyInPlayerHand($player_id, $card_position, $key, $value)
    {
        $this->player_hand[$player_id][$card_position][$key] = $value;
    }

    /**
     * @param int $player_id
     * @param array $card
     */
    private function addToPlayerHand($player_id, $card)
    {
        $this->player_hand[$player_id][] = $card;
    }

    /**
     * @param int $player_id
     *
     * @return int
     */
    private function getPlayerHandTotal($player_id)
    {
        return $this->player_hand_total[$player_id];
    }

    /**
     * @param int $player_id
     * @param int $player_hand_total
     */
    private function setPlayerHandTotal($player_id, $player_hand_total)
    {
        $this->player_hand_total[$player_id] = $player_hand_total;
    }

    /**
     * @param int $player_id
     * @param int $player_hand_total
     */
    private function addToPlayerHandTotal($player_id, $player_hand_total)
    {
        $this->player_hand_total[$player_id] += $player_hand_total;
    }

    /**
     * @param int $player_id
     *
     * @return int
     */
    public function getPlayerVisibleHandTotal($player_id)
    {
        return $this->player_visible_hand_total[$player_id];
    }

    /**
     * @param int $player_id
     * @param int $player_visible_hand_total
     */
    private function setPlayerVisibleHandTotal($player_id, $player_visible_hand_total)
    {
        $this->player_visible_hand_total[$player_id] = $player_visible_hand_total;
    }

    /**
     * @param int $player_id
     * @param int $player_visible_hand_total
     */
    private function addToPlayerVisibleHandTotal($player_id, $player_visible_hand_total)
    {
        $this->player_visible_hand_total[$player_id] += $player_visible_hand_total;
    }

    /**
     * @param int $player_id
     *
     * @return string
     */
    public function getPlayerName($player_id)
    {
        return $this->player_name[$player_id];
    }

    /**
     * @param int $player_id
     * @param string $player_name
     */
    private function setPlayerName($player_id, $player_name)
    {
        $this->player_name[$player_id] = $player_name;
    }

    /**
     * @param $player_id
     *
     * @return bool
     */
    public function getPlayerPickedStay($player_id)
    {
        return $this->player_picked_stay[$player_id];
    }

    /**
     * @param int $player_id
     * @param bool $player_picked_stay
     */
    private function setPlayerPickedStay($player_id, $player_picked_stay)
    {
        $this->player_picked_stay[$player_id] = $player_picked_stay;
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
        // Set player names
        $this->setPlayerName(0, "Dealer");
        $this->setPlayerName(1, $arg_player_name);
        $this->setPlayerName(2, "AI");

        // Setup array for the base deck if it's not set.
        if (empty($this->getBaseDeck())) {
            $deck_json = Storage::disk('local')->get('poker_deck.json');
            $deck_array = json_decode($deck_json, true);

            $this->setBaseDeck($deck_array);
        }

        // Setup defaults
        $this->setPlayerPickedStay(0, false);
        $this->setPlayerPickedStay(1, false);
        $this->setPlayerPickedStay(2, false);
        $this->setCurrentGameState('STATE_DEALING');

        // Deal
        $this->shuffleDeck();
        $this->dealInitialCards();
        $this->advanceState();
    }

    /**
     * Shuffles the current deck array
     */
    private function shuffleDeck()
    {
        if(!empty($this->getBaseDeck())) {
            $this->setCurrentDeck($this->getBaseDeck());
            shuffle($this->current_deck);
        }
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
                if($this->getPlayerPickedStay(2) == true) {
                    $new_state = "STATE_DEALER_TURN";
                }
                break;
            case "STATE_AI_TURN":
                $new_state = "STATE_DEALER_TURN";
                if($this->getPlayerPickedStay(0) == true) {
                    $new_state = "STATE_PLAYER_TURN";
                }
                break;
            case "STATE_DEALER_TURN":
                $new_state = "STATE_PLAYER_TURN";
                // If player stays, we skip them and let the AI play.
                if($this->getPlayerPickedStay(1) == true) {
                    $new_state = "STATE_AI_TURN";
                }
                break;
        }

        $this->setCurrentGameState($new_state);
    }

    /**
     * Draws a card from the deck array
     *
     * @param string $arg_status
     *
     * @return array
     */
    private function drawCard($arg_status = 'face_up')
    {
        // If the deck is empty, we'll shuffle it and reset.
        if(empty($this->getCurrentDeck())) {
            $this->shuffleDeck();
        }

        // Grab the first card from the top of the deck.
        $card = array_shift($this->current_deck);
        $card['status'] = $arg_status;

        return $card;
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
        $this->addToPlayerHand($arg_player_id, $this->drawCard($arg_status));

        // Player has a new card, so let's re-calc their hand value.
        $this->calculatePlayerHandValue($arg_player_id);
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
     * Calculates the requested players hand value
     * Ignores face_down cards for the visible total
     *
     * @param int $arg_player_id
     */
    private function calculatePlayerHandValue($arg_player_id)
    {
        // Clear the current total before we re-calculate.
        $this->setPlayerHandTotal($arg_player_id, 0);
        $this->setPlayerVisibleHandTotal($arg_player_id, 0);

        foreach($this->getPlayerHand($arg_player_id) as $card) {

            $card_value = $this->calculateCardValue($card);

            // Calculate total player hand value
            $this->addToPlayerHandTotal($arg_player_id, $card_value);

            // If a card is not face up we don't add it to the visible hand value we show to players.
            if($card['status'] == 'face_up') {
                $this->addToPlayerVisibleHandTotal($arg_player_id, $card_value);
            }
        }

        // Calc ACE Cards Values
        $this->calculateAceValues($arg_player_id);
    }

    /**
     * Calculates the value of each Ace after all hand values have been calculated.
     * Automatically picks the best option for the Ace value based on current hand values.
     *
     * @param int $arg_player_id
     */
    private function calculateAceValues($arg_player_id)
    {
        $current_aces_value = 0;

        foreach($this->getPlayerHand($arg_player_id) as $card) {

            $value = 0;

            // For each ace we check if the current hand value + any previous aces + using the current ace as an 11
            // would bust the player or not. If so, we set the value as 1.
            switch ($card['value'])
            {
                case "Ace":
                    if($this->getPlayerHandTotal($arg_player_id) + $current_aces_value + 11 > 21) {
                        $value = 1;
                    } else {
                        $value = 11;
                    }
                    break;
            }

            // Add to the total aces value to help calculate what the next ace's value should be if it's there.
            $current_aces_value += $value;

            if($card['status'] == 'face_up') {
                $this->addToPlayerVisibleHandTotal($arg_player_id, $value);
            }

            $this->addToPlayerHandTotal($arg_player_id, $value);
        }
    }

    /**
     * Used to convert named cards to a value.
     *
     * @param array $arg_card
     *
     * @return int
     */
    private function calculateCardValue($arg_card)
    {
        switch ($arg_card['value'])
        {
            case "Jack":
                $value = 10;
                break;
            case "King":
                $value = 10;
                break;
            case "Queen":
                $value = 10;
                break;
            case "Ace":
                $value = 0;
                break;
            default:
                $value = $arg_card['value'];
        }

        return $value;
    }

    /**
     * Checks if a player has busted or not
     *
     * @param int $arg_player_id
     *
     * @return bool
     */
    public function didPlayerBust($arg_player_id)
    {
        $total = $this->getPlayerHandTotal($arg_player_id);

        if ($total > 21) {
            return true;
        } else {
            return false;
        }
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
                $this->setPlayerPickedStay($arg_player_id,true);
                break;
        }
    }

    /**
     * Flips any face_down cards to a face_up state for the dealer.
     */
    private function flipDealerCards()
    {
        // Loop over each card in the dealers hand and change the status if it's face down.
        foreach($this->getPlayerHand(0) as $index => $card) {
            if ($card['status'] == 'face_down') {
                $this->setCardPropertyInPlayerHand(0,$index,'status','face_up');
            }
        }
    }

    /**
     * Performs a check that all players have picked Stay and to calculate the end of the game.
     */
    public function endGameCheck()
    {
        if(($this->getPlayerPickedStay(0) == true && $this->getPlayerPickedStay(1) == true && $this->getPlayerPickedStay(2) == true) || ($this->didPlayerBust(0) == true)
        ) {
            $this->setCurrentGameState("STATE_GAME_OVER");

            // Flip over any hidden cards for the Dealer
            $this->flipDealerCards();

            // Re-Calculate dealers hand with face up cards.
            $this->calculatePlayerHandValue(0);
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
            if ($this->didPlayerBust($player) == false) {
                $non_busting_players[] = $player;
            }
        }

        // Add players with the highest seen total, who have not busted to the winning array.
        foreach($non_busting_players as $player) {
            if($this->getPlayerHandTotal($player) == $highest_seen_total) {
                $winning_players[] = $player;
            }
        }

        // Check if the Dealer is in the winning array, this overrides player ties with the dealer.
        $dealer_wins = array_search(0, $winning_players);

        // If the Dealer busted, all non busted players win!
        if($this->didPlayerBust(0) == true) {
            $winning_players = $non_busting_players;
        }

        // Return the winning player names.
        if($dealer_wins === false) {
            foreach ($winning_players as $player) {
                $return .= $this->getPlayerName($player) . ", ";
            }
        } else {
            $return .= $this->getPlayerName(0);
        }

        // If everyone busted, nobody wins.
        if(empty($winning_players)) {
            $return = "Nobody";
        }

        return $return;

    }

    /**
     * Calculate which action the AI should pick.
     *
     * @param $arg_player_id
     *
     * @return string
     */
    public function getAIAction($arg_player_id)
    {
        if ($this->getPlayerHandTotal($arg_player_id) <= 16) {
            return "Hit Me";
        } else {
            return "Stay";
        }
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
            if($this->getPlayerHandTotal($player) > $highest_seen_total && $this->didPlayerBust($player) == false) {
                $highest_seen_total = $this->getPlayerHandTotal($player);
            }
        }

        return $highest_seen_total;
    }
}
