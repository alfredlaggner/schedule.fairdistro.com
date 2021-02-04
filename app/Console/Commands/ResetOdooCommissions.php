<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetOdooCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:creset {sales_person_id?}{month?}';

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
     * @return int
     */
    public function handle()
    {

        $success = false;
        $odoo = new \Edujugon\Laradoo\Odoo();
        $odoo->connect();

        $sales_person_id = $this->argument('sales_person_id');
   //     $success = $odoo->where('user_id', (int)$sales_person_id)
        $success = $odoo->where('id','>=',1)->update('account.invoice', [
            'x_studio_commission' => 0.00,
            'x_studio_commission_percent' => 0.00,
            'x_studio_commission_paid' => '01-01-2000']);
        $this->info($sales_person_id);
        $this->info($success);

/*        $results = $odoo
            ->where( 'user_id', (int)$sales_person_id)
            ->where( 'x_studio_commission', '=',  0)
            ->fields(
                'user_id',
                'x_studio_commission',
                'x_studio_commission_percent'
            )
            ->get('account.invoice');*/

//dd($results);
    }
}
