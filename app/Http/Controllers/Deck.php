<?php
/**
 * Created by PhpStorm.
 * User: dustin.hendrickson
 * Date: 6/14/2019
 * Time: 2:05 PM
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Card;
use Storage;

/**
 * Class Deck
 * @package App\Http\Controllers
 */
class Deck
{

    /**
     * @var array
     */
    private $base_deck = array();

    /**
     * @return array
     */
    private function getBaseDeck(): array
    {
        return $this->base_deck;
    }

    /**
     * @param array $base_deck
     */
    private function setBaseDeck(array $base_deck)
    {
        $this->base_deck = $base_deck;
    }

    /**
     * @param array $active_deck
     */
    private function setActiveDeck(array $active_deck)
    {
        $this->active_deck = $active_deck;
    }

    /**
     * @var array
     */
    private $active_deck = array();


    /**
     * Deck constructor.
     */
    function __construct()
    {
        // Grab our deck from the json file in Storage.
        $deck_json = Storage::disk('local')->get('poker_deck.json');
        $deck_array = json_decode($deck_json, true);

        $this->setBaseDeck($deck_array);
    }

    /**
     * Shuffles the active deck array
     */
    public function shuffle()
    {
        if(!empty($this->getBaseDeck())) {
            $this->setActiveDeck($this->getBaseDeck());
            shuffle($this->active_deck);
        }
    }

    /**
     * Draws a card from the deck array
     *
     * @param string $arg_status
     *
     * @return object
     */
    public function drawCard($arg_status = 'face_up')
    {
        // Grab the first card from the top of the deck.
        $card_info = array_shift($this->active_deck);
        $card = new Card($card_info['suit'], $card_info['value'], $arg_status);

        return $card;
    }


}