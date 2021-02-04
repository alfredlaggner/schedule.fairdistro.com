<?php

    namespace App\Console\Commands;

    use App\StockMove;
    use App\StockPicking;
    use Illuminate\Console\Command;

    class getStockPicking extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'odoo:stockpicking';

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
            $stock_picking = $odoo
               ->where('date', '>=', date('Y-m-d', strtotime("- 30 days")))
                ->fields(
                    'id',
                    'name',
                    'display_name',
                    'origin',
                    'date',
                    'date_done',
                    'state',
                    'product_id',
                    'location_id',
                    'location_dest_id',
                    'state'
                )
                ->get('stock.picking');
            for ($i = 0; $i < count($stock_picking); $i++) {

                StockPicking::updateOrCreate(
                    [
                        'ext_id' => $stock_picking[$i]['id']
                    ],
                    [
                        'name' => $stock_picking[$i]['name'],
                        'date' => $stock_picking[$i]['date'],
                        'state' => $stock_picking[$i]['state'],
                        'date_done' => $stock_picking[$i]['date_done'],
                        'salesorder_number' => $stock_picking[$i]['origin'],
                        'product_id' => $stock_picking[$i]['product_id'][0],
                        'product_name' => $stock_picking[$i]['product_id'][1],
                    ]
                );
            }
            $this->info(date_format(date_create(), 'Y-m-d H:i:s'));

        }
    }
