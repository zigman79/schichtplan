<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Utils\Telegram;
use Illuminate\Console\Command;

class SendTelegramMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:telegram {--message=} {--user=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a message to a Telegram user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $message = $this->option('message');

        $user = $this->option('user');

        if (!$message) {
            $message = $this->ask('What is the message?');
        }

        if (!$user) {
            $user = $this->ask('What is the user?');
            $user = User::where('name', $user)->orWhere('email', $user)->orWhere('id', $user)->first();
        }

        // confirm the user
        if (!$this->confirm('Send message to ' . $user->name . ' (' . $user->email . ')?')) {
            return Command::FAILURE;
        }

        // send the message
        Telegram::sendMessage($user->telegram_id, $message);

        return Command::SUCCESS;
    }
}
