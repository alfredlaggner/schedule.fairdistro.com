<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class check_logs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:logs';

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

        $logs = $odoo
                ->limit(30)
            //       ->where('id', '=', 700)
            ->fields(
                'id',
                'create_date',
                'create_uid',
                'display_name',
                'write_date',
                'write_uid'
            )
            ->get('res.users.log');
        dd($logs);
    }
}
