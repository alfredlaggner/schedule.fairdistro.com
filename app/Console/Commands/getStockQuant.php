<?php

    namespace App\Console\Commands;

    use App\StockPicking;
    use Illuminate\Console\Command;

    class getStockQuant extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'calc:quant';

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
            $stock_quant = $odoo
                  ->where('create_date', '>=', date('Y-m-d', strtotime("- 1 day")))
                ->fields(
                    'id',
                    'display_name',
                    'origin',
                    'create_date',
                    'product_id',
                    'location_id',
                    'lot_id',
                    'quantity',
                    'reserved_quantity'
                )
                ->get('stock.quant');
dd($stock_quant);
            for ($i = 0; $i < count($stock_picking); $i++) {

                StockPicking::updateOrCreate(
                    [
                        'ext_id' => $stock_picking[$i]['id']
                    ],
                    [
                        'name' => $stock_picking[$i]['name'],
                        'date' => $stock_picking[$i]['date'],
                        'salesorder_number' => $stock_picking[$i]['origin'],
                        'product_id' => $stock_picking[$i]['product_id'][0],
                        'product_name' => $stock_picking[$i]['product_id'][1],
                    ]
                );
            }
        }
    }
