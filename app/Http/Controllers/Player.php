<?php
/**
 * Created by PhpStorm.
 * User: dustin.hendrickson
 * Date: 6/14/2019
 * Time: 3:30 PM
 */

namespace App\Http\Controllers;


/**
 * Class Player
 * @package App\Http\Controllers
 */
class Player
{
    /**
     * @var array
     */
    private $hand = array();

    /**
     * @var int
     */
    private $hand_total = 0;

    /**
     * @var int
     */
    private $visible_hand_total = 0;

    /**
     * @var string
     */
    private $name = "";

    /**
     * @var bool
     */
    private $picked_stay = false;

    /**
     * @return array
     */
    public function getHand()
    {
        return $this->hand;
    }

    /**
     * @param object $card
     */
    public function addToHand($card)
    {
        $this->hand[] = $card;
    }

    /**
     * @return int
     */
    public function getHandTotal(): int
    {
        return $this->hand_total;
    }

    /**
     * @param int $hand_total
     */
    public function setHandTotal(int $hand_total): void
    {
        $this->hand_total = $hand_total;
    }

    /**
     * @param int $hand_total
     */
    public function addToHandTotal(int $hand_total): void
    {
        $this->hand_total += $hand_total;
    }

    /**
     * @return int
     */
    public function getVisibleHandTotal(): int
    {
        return $this->visible_hand_total;
    }

    /**
     * @param int $visible_hand_total
     */
    public function setVisibleHandTotal(int $visible_hand_total): void
    {
        $this->visible_hand_total = $visible_hand_total;
    }

    /**
     * @param int $visible_hand_total
     */
    public function addToVisibleHandTotal(int $visible_hand_total): void
    {
        $this->visible_hand_total += $visible_hand_total;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isPickedStay(): bool
    {
        return $this->picked_stay;
    }

    /**
     * @param bool $picked_stay
     */
    public function setPickedStay(bool $picked_stay): void
    {
        $this->picked_stay = $picked_stay;
    }

    function __construct($player_name)
    {
        $this->setName($player_name);
    }

    /**
     * Checks if a player has busted or not
     *
     * @return bool
     */
    public function didPlayerBust()
    {
        $total = $this->getHandTotal();

        if ($total > 21) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Flips any face_down cards to a face_up state for the dealer.
     */
    public function flipCards()
    {
        // Loop over each card in the players hand and change the status if it's face down.
        foreach($this->getHand() as $index => $card) {
            if ($card->getStatus() == 'face_down') {
                $card->setStatus('face_up');
            }
        }
    }

    /**
     * Calculate which action the AI should pick.
     *
     * @return string
     */
    public function getAIAction()
    {
        if ($this->getHandTotal() <= 16) {
            return "Hit Me";
        } else {
            return "Stay";
        }
    }

    /**
     * Calculates the requested players hand value
     * Ignores face_down cards for the visible total
     */
    public function calculateHandValue()
    {
        // Clear the current total before we re-calculate.
        $this->setHandTotal(0);
        $this->setVisibleHandTotal(0);

        foreach($this->getHand() as $card) {

            $card_value = $card->getConvertedValue();

            // Calculate total player hand value
            $this->addToHandTotal($card_value);

            // If a card is not face up we don't add it to the visible hand value we show to players.
            if($card->getStatus() == 'face_up') {
                $this->addToVisibleHandTotal($card_value);
            }
        }

        // Calc ACE Cards Values
        $this->calculateAceValues();
    }

    /**
     * Calculates the value of each Ace after all hand values have been calculated.
     * Automatically picks the best option for the Ace value based on current hand values.
     */
    public function calculateAceValues()
    {
        $current_aces_value = 0;

        foreach($this->getHand() as $card) {

            $value = 0;

            // For each ace we check if the current hand value + any previous aces + using the current ace as an 11
            // would bust the player or not. If so, we set the value as 1.
            switch ($card->getValue())
            {
                case "Ace":
                    if($this->getHandTotal() + $current_aces_value + 11 > 21) {
                        $value = 1;
                    } else {
                        $value = 11;
                    }
                    break;
            }

            // Add to the total aces value to help calculate what the next ace's value should be if it's there.
            $current_aces_value += $value;

            if($card->getStatus() == 'face_up') {
                $this->addToVisibleHandTotal($value);
            }

            $this->addToHandTotal($value);
        }
    }



}