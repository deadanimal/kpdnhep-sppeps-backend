<?php 

namespace App\Http\Controllers;
  
use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use App\Http\Conversations\OnboardingConversation;

class BotManController extends Controller
{
  
    // public function __construct()
    // {
    //    $this->middleware('jwt.auth');
    // }

    public function handle()
    {
        $botman = app('botman');
        $botman->hears('{message}', function ($bot){
            $bot->startConversation(new OnboardingConversation);
        });
        $botman->listen();
    }
     //  $botman->hears('{message}', function($botman, $message) {
            //$this->askDetails($botman);


            // if ($message == 'hi') {
            //     $this->askName($botman);
            // }else{
            //     $botman->reply("write 'hi' for testing...");
            //     $question = Question::create('This is my question')
            //     ->addButton(Button::create('hi')->value('hi'))
            //     ->addButton(Button::create('no')->value('no'));
            
            //     $botman->reply($question);
            // }
  
      //  });
    
}
