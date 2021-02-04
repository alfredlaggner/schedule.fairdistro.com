<?php

    namespace App\Console\Commands;

    use App\Margin;
    use Carbon\Carbon;
    use Illuminate\Console\Command;

    class makeNextSku extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'calc:makesku';

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

            $categories = [
                "Accessory",
                "Edible",
                "Vape",
                "Flower",
                "Tincture",
                "Topical",
                "Concentrate",
            ];

            $abbr = [
                "AC",
                "ED",
                "VA",
                "FL",
                "TI",
                "TP",
                "CT",
            ];
            for ($i = 0; $i < count($categories); $i++) {
                $sku = $abbr[$i];
                $products = Margin::where('category', 'like', $categories[$i])->get();
                $counter = 1;
                $sku_number = '';

                $this->info($products->count() . $categories[$i] . $sku);

                foreach ($products as $product) {
                    if ($counter < 10)
                        $sku_number = $sku . "0000" . $counter;
                    elseif ($counter < 100)
                        $sku_number = $sku . "000" . $counter;
                    elseif ($counter < 1000)
                        $sku_number = $sku . "00" . $counter;
                    elseif ($counter < 10000)
                        $sku_number = $sku . "0" . $counter;

                    //              $this->info($sku_number);

                    $this->info($sku_number);

                    Margin::find($product->id)->
                    update(['code' => $sku_number]);

                    $updated = $odoo->where('id', $product->ext_id)
                        ->update('product.product', ['default_code' => $sku_number]);

                    $counter++;
                }
            }
        }
    }
