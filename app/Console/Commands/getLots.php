<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class getLots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:lots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get lots from Odoo';

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
        $stock_moves = $odoo
         //   ->where('sale_order_count', '>', 20)
            ->where( 'create_date', '>=',  date('Y-m-d', strtotime("-1 month")))

            //      ->limit(5)
            ->fields(
                'id',
                'name',
                'display_name',
                'product_id',
                'product_qty',
                'ref',
                'sale_order_count'
            )
            ->get('stock.production.lot');
        dd($stock_moves);
    }
}
