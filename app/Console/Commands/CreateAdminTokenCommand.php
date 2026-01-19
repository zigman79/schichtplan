<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdminTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an AdminToken';

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
     * @return int
     */
    public function handle()
    {
        dump(User::whereArbeitszeitAdmin(1)->first()->createToken($this->argument('name'),
            ['token:create'])->plainTextToken);
    }
}
