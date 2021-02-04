<?php

namespace App\Console\Commands;

use App\Margin;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class calcLastBarcode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:lastbarcode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Odoo with next Barcodes numbers';

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
        $odoo->where('default_code', '=', '')
            ->update('product.product',
                [
                    'default_code' => '0'
                ]);

        dd('done');


        $no_barcode = DB::table('products')->orderby('code', 'desc')
            ->where('code', '!=', '0')
            ->where('barcode', '=', '0')
            ->get();
        //    dd($no_barcode->count());
        if ($no_barcode->count() > 0) {
            foreach ($no_barcode as $no_barcode) {
                //      $this->info($no_barcode->ext_id);


                $codes = $odoo->where('id', '=', 3284)/*$no_barcode->ext_id*/
                ->fields('default_code', 'name', 'id')
                    ->get('product.product');
//dd($codes);
                $id = $codes[0]['id'];
                $name = $codes[0]['name'];
                $default_code = $codes[0]['default_code'];

                $this->info($id . " " . $default_code . ' ' . $name . ' ' . $default_code);
                //  dd('xx');
            }
            $default_code = "ED00030";
            $odoo->where('id', '=', 3284)
                ->update('product.product',
                    [
                        'barcode' => $default_code
                    ]);
            dd("xxx");
        }

        $this->info(date_format(date_create(), 'Y-m-d H:i:s'));
    }
}
