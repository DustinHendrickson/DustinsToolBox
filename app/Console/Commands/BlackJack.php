<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BlackJackController;

/**
 * Class BlackJack
 * @package App\Console\Commands
 */
class BlackJack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'play:blackjack';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts the Interactive BlackJack game.';

    /**
     * @var object
     */
    private $BlackJack;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param $player_name
     *
     */
    public function handle($player_name = "")
    {
        $this->clearScreen();

        // If a brand new game is started, let's grab the players name.
        if(empty($player_name)) {
            $player_name = $this->ask('What is your name?');
        }

        $this->clearScreen();

        // Start a new game.
        $this->BlackJack = new BlackJackController();
        $this->BlackJack->setupNewGame($player_name);

        // Display the initial hands for each player.
        $this->showAllPlayerHands();

        // Main Game Loop
        $this->gameLoop();

        // GAME ENDED
        $this->info($this->BlackJack->getEndGameStatus());

        // Ask for player input
        $picked_option = $this->choice('Would you like to play another game?', ['Yes', 'No'], 0);

        // Handle player input
        switch ($picked_option){
            case "Yes":
                $this->clearScreen();
                self::handle($player_name);
                break;
            case "No":
                $this->info("Thanks for playing!");
                break;
        }
    }

    private function gameLoop()
    {
        while($this->BlackJack->getCurrentGameState() != "STATE_GAME_OVER" ) {
            switch($this->BlackJack->getCurrentGameState()) {
                case "STATE_PLAYER_TURN":
                    $player_action = $this->getPlayerInput();
                    $this->BlackJack->doPlayerAction(1, $player_action);
                    $this->BlackJack->advanceState();
                    break;
                case "STATE_AI_TURN":
                    $player_action = $this->BlackJack->getAIAction(2);
                    $this->BlackJack->doPlayerAction(2, $player_action);
                    $this->BlackJack->advanceState();
                    break;
                case "STATE_DEALER_TURN":
                    $player_action = $this->BlackJack->getAIAction(0);
                    $this->BlackJack->doPlayerAction(0, $player_action);
                    $this->BlackJack->advanceState();
                    break;
            }

            // Check if endgame is ready
            $this->BlackJack->endGameCheck();

            // Clear the screen
            $this->clearScreen();

            // Display all users cards.
            $this->showAllPlayerHands();
        }
    }

    /**
     * Shows a selected players current hand.
     *
     * @param $arg_player_id
     */
    private function showPlayerHand($arg_player_id)
    {
        $headers = ['suit', 'value', 'status'];

        $obj_player_hand = $this->BlackJack->getPlayerHand($arg_player_id);
        $arr_player_hand = array();
        $card_array = array();

        // Hide face down cards.
        foreach($obj_player_hand as $index => $card) {
            $card_array['suit'] = $card->getVisibleSuit();
            $card_array['value'] = $card->getVisibleValue();
            $card_array['status'] = $card->getStatus();
            $arr_player_hand[] = $card_array;
        }

        // Display if a player has busted.
        if($this->BlackJack->didPlayerBust($arg_player_id)) {
            $this->error($this->BlackJack->getPlayerName($arg_player_id) . " BUSTED!");
        } else {
            $this->info($this->BlackJack->getPlayerName($arg_player_id) . "'s Hand...");
        }

        // Setup our table data for the player.
        $table_rows = $arr_player_hand;
        $table_rows[] = array('suit'=>'total','value'=>$this->BlackJack->getPlayerVisibleHandTotal($arg_player_id));

        // Display the players hand.
        $this->table($headers, $table_rows);

        // Add a space between each table.
        $this->line(PHP_EOL);

    }

    /**
     * Shows every players hand.
     */
    private function showAllPlayerHands()
    {
        $this->showPlayerHand(1);
        $this->showPlayerHand(2);
        $this->showPlayerHand(0);
    }

    /**
     * Get the action selection input from the live player.
     *
     * @param int $arg_player_id
     *
     * @return string
     */
    private function getPlayerInput($arg_player_id = 1)
    {
        // Display a selection of choices to the player if they have not busted.
        if($this->BlackJack->didPlayerBust($arg_player_id) == false) {
            $picked_option = $this->choice('What would you like to do?', ['Hit Me', 'Stay'], 0);
        } else {
            $picked_option = "Stay";
        }

        return $picked_option;
    }

    private function clearScreen()
    {
        // System command to clear the terminal screen.
        system('clear');
    }
}
