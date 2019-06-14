<?php
/**
 * Created by PhpStorm.
 * User: dustin.hendrickson
 * Date: 6/14/2019
 * Time: 2:06 PM
 */

namespace App\Http\Controllers;


/**
 * Class Card
 * @package App\Http\Controllers
 */
class Card
{
    /**
     * @var string
     */
    private $suit = "";
    /**
     * @var string
     */
    private $value = "";
    /**
     * @var string
     */
    private $status = "";

    /**
     * @return string
     */
    public function getSuit()
    {
        return $this->suit;
    }

    /**
     * @param string $suit
     */
    public function setSuit(string $suit)
    {
        $this->suit = $suit;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * Card constructor.
     *
     * @param $arg_suit
     * @param $arg_value
     * @param $arg_status
     */
    function __construct($arg_suit, $arg_value, $arg_status)
    {
        $this->suit = $arg_suit;
        $this->value = $arg_value;
        $this->status = $arg_status;
    }

    /**
     * @return string
     */
    public function getVisibleValue()
    {
        if ($this->status == 'face_up') {
            return $this->value;
        } else {
            return "?";
        }
    }

    /**
     * @return string
     */
    public function getVisibleSuit()
    {
        if ($this->status == 'face_up') {
            return $this->suit;
        } else {
            return "?";
        }
    }

    /**
     * Used to convert named cards to a value.
     *
     *
     * @return int
     */
    public function getConvertedValue()
    {
        switch ($this->value)
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
                $value = $this->value;
        }

        return $value;
    }


}