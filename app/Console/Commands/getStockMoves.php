<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\StockMove;

class getStockMoves extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:getstock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets stock moves';

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
            ->where('date', '>=', date('Y-m-d', strtotime("- 1 month")))
            ->fields(
                'id',
                'picking_id',
                'date',
                'reference',
                'product_id',
                'location_id',
                'location_dest_id',
                'qty_done',
                'lot_name',
                'product_barcode',
                'lot_id',
                'lot_name',
                'state',
                'location_processed'
            )
            ->get('stock.move.line');
        for ($i = 0; $i < count($stock_moves); $i++) {
//dd($stock_moves);
            StockMove::updateOrCreate(
                [
                    'ext_id' => $stock_moves[$i]['id']
                ],
                [
                    'sku' => substr($stock_moves[$i]['product_id'][1], 0, 9),
                    'product_id' => $stock_moves[$i]['product_id'][0],
                    'name' => substr($stock_moves[$i]['product_id'][1], 0),
                    'date' => $stock_moves[$i]['date'],
                    'picking_id' => $stock_moves[$i]['picking_id'][0],
                   'lot_name' => $stock_moves[$i]['lot_name'],
                    'reference' => $stock_moves[$i]['reference'],
                    'location' => $stock_moves[$i]['location_id'][1],
                    'location_dest' => $stock_moves[$i]['location_dest_id'][1],
                    'qty_done' => $stock_moves[$i]['qty_done'],
                    'state' => $stock_moves[$i]['state']
                ]
            );
        }
        $date1 = StockMove::orderby('id', 'asc')->first()->date;
        $date2 = StockMove::orderby('id', 'desc')->first()->date;

        $datetime1 = new \DateTime(date($date1));
        $datetime2 = new \DateTime(date($date2));
        $interval = $datetime1->diff($datetime2);
        $days = $interval->format('%r%a');

        session(['stockMoveDays' => $days]);
        $this->info(date_format(date_create(), 'Y-m-d H:i:s'));

    }
}
