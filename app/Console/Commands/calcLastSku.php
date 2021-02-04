<?php

namespace App\Console\Commands;

use App\Margin;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class calcLastSku extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:lastsku';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Odoo with next SKU numbers';

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
            "Concentrate",
            "Accessory",
            "Edible",
            "Vape",
            "Flower",
            "Tincture",
            "Topical",
        ];
        $abbr = [
            "CT",
            "AC",
            "ED",
            "VA",
            "FL",
            "TI",
            "TP",
        ];

        for ($i = 0; $i < count($categories); $i++) {
            $counter = 0;
/*            $this->info("$i = " . $i);
            $this->info("code = " . $categories[$i] );*/

            $last_sku = DB::table('products')
                ->orderby('code', 'desc')
                ->where('category', '=', $categories[$i])
                ->where('code', '!=', '0')
                ->first();
//                 $this->info("code = " . $last_sku->code );
//                $this->info("category = " . $last_sku->category );
//dd($last_sku);
            $no_skus = DB::table('products')->orderby('code', 'desc')
                ->where('category', '=', $categories[$i])
                ->where('code', '=', '0')
                ->get();

//            $this->info("count = " . $categories[$i] . " " . $no_skus->count());
            if ($no_skus->count() > 0) {
                $counter = intval(substr($last_sku->code, 2)) + 1;
                if ($counter == 0) {
                    $counter = 1;
                }
//                $this->info("counter= " . $counter);

                foreach ($no_skus as $no_sku) {
                    //      $this->info($no_sku->ext_id);
                    $sku = $abbr[$i];
                    $sku_number = '0';
                    if ($counter < 10)
                        $sku_number = $sku . "0000" . $counter;
                    elseif ($counter < 100)
                        $sku_number = $sku . "000" . $counter;
                    elseif ($counter < 1000)
                        $sku_number = $sku . "00" . $counter;
                    elseif ($counter < 10000)
                        $sku_number = $sku . "0" . $counter;

                    /*                    DB::table('products')->where('id', $no_sku->id)
                                            ->update(['code' => $sku_number,
                                            ]);*/

                    $this->info($no_sku->ext_id . " " . $sku_number);

              //      dd($last_sku);

                    $updated = $odoo->where('id', '=', $no_sku->ext_id)
                        ->update('product.template',
                            [
                                'default_code' => $sku_number,
                                'barcode' => $sku_number
                            ]);

                    $counter++;
                }
            }
        }

        $this->info(date_format(date_create(), 'Y-m-d H:i:s'));
    }
}
