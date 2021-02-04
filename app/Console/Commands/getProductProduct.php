<?php

namespace App\Console\Commands;

use App\Margin;
use App\ProductProduct;
use Illuminate\Console\Command;

class getProductProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:productproducts';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get product.product';
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

        $products = $odoo
            //      ->limit(5)
            //       ->where('id', '=', 700)
            ->fields(
                'id',
                'default_code',
                'barcode',
                'active',
                'display_name',
                'name',
                'product_id',
                'list_price',
                'standard_price',
                'categ_id',
                'qty_available',
                'sale_ok',
                'purchase_ok',
                'x_studio_case_qty',
                'x_studio_unit_size_unit',
                'x_studio_unit_size_temp',
                'x_studio_number_sold',
                'virtual_available',
                'incoming_qty',
                'available_threshold',
                'outgoing_qty',
                'weight'
            )
            ->get('product.product');
        //    \DB::table('products')->delete();

        for ($i = 0; $i < count($products); $i++) {
            $categ_temp = explode('/', $products[$i]['categ_id'][1]);
            $brand = 'no brand';
            $categ_temp = explode('/', $products[$i]['categ_id'][1]);
            $category = "no category";
            $sub_category = "";

            if (sizeof($categ_temp) == 2) {
                if (!in_array(" CBD ", $categ_temp, false)) {
                    $brand = str_replace(' ', '', $categ_temp[0]);
                    //                 echo "brand1 " . $brand;
                    $category = str_replace(' ', '', $categ_temp[1]);
                    //                               dd("categ1 " . $category);
                    if (sizeof($categ_temp) >= 3) {
                        $sub_category = str_replace(' ', '', $categ_temp[2]);
                    }
                }
            }
            if (sizeof($categ_temp) >= 3) {
                if (!in_array(" CBD ", $categ_temp, false)) {
                    $brand = str_replace(' ', '', $categ_temp[2]);
                    //                echo "brand2 " . $brand;
                    $category = str_replace(' ', '', $categ_temp[1]);
                    //              echo "categ2 " . $category;

                    if (sizeof($categ_temp) >= 4) {
//        dd($categ_temp);
                        $sub_category = str_replace(' ', '', $categ_temp[3]);
                    }
                }
            }
//dd( $products[$i]['categ_id']);
            ProductProduct:: updateOrCreate(
                [
                    'ext_id' => $products[$i]['id']
                ],
                [
                    'name' => $products[$i]['name'],
                    'code' => $products[$i]['default_code'],
                    'barcode' => $products[$i]['barcode'],
                    'is_active' => $products[$i]['active'] ? 1 : 0,
                    'cost' => $products[$i]['standard_price'],
                    'revenue' => $products[$i]['list_price'],
                    'margin' => 0,
                    'commission_percent' => 0,
                    'category_full' => $products[$i]['categ_id'][1],
                    'category_id' => $products[$i]['categ_id'][0],
                    'brand' => $brand,
                    'category' => $category,
                    'sub_category' => $sub_category,
                    'brand_id' => $products[$i]['categ_id'][0],
                    'quantity' => $products[$i]['qty_available'],
                    'sale_ok' => $products[$i]['sale_ok'],
                    'purchase_ok' => $products[$i]['purchase_ok'],
                    'units_sold' => $products[$i]['x_studio_number_sold'],
                    'units_forcasted' => $products[$i]['virtual_available'],
                    'incoming_qty' => $products[$i]['incoming_qty'],
                    'available_threshold' => $products[$i]['available_threshold'],
                    'list_price' => $products[$i]['list_price'],
                    'weight' => $products[$i]['weight'],
                    'uom' => $products[$i]['x_studio_unit_size_unit'],
                    'unit_size' => $products[$i]['x_studio_unit_size_temp'],
                    'case_qty' => $products[$i]['x_studio_case_qty'],
                ]
            );
            if ($products[$i]['active'] == 0) $this->info("not active!");

        }

    }
}
