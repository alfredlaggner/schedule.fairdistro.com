<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class getSalesPersons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fair:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle()
    {
        $odoo = new \Edujugon\Laradoo\Odoo();
        $odoo = $odoo->connect();

        $users = $odoo
            ->fields(
                'id',
                'name',
                'email',
                'contact_address',
            )
            ->get('res.users');
        for ($i = 0; $i < count($users); $i++) {

            User::updateOrCreate(
                ['email' => $users[$i]['email']],
                [
                    'name' => $users[$i]['name'],
                    'email' => $users[$i]['email'],
                    'address' => $users[$i]['contact_address'],
                ]
            );

        }


        $this->info(date_format(date_create(), 'Y-m-d H:i:s'));

    }

}
