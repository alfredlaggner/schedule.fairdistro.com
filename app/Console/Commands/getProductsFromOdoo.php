<?php

namespace App\Console\Commands;

use App\StockMove;
use App\SaleInvoice;
use App\SalesOrder;
use App\Customer;
use \App\Margin;
use \App\SalesPerDay;
use App\Traits\CommissionTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;

class getProductsFromOdoo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    use CommissionTrait;

    protected $signature = 'odoo:margin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulls all products from Odoo';

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
 //           ->where('default_code', '=', 'TI00035')
            ->fields(
                'id',
                'default_code',
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
                'x_studio_number_sold',
                'virtual_available',
                'incoming_qty',
                'available_threshold',
                'outgoing_qty',
                'weight',
                'image_small'
            )
            ->get('product.template');
        //      dd($products[0]['image']."<-image");
        \DB::table('margins')->delete();
        $this->update_table($products);

        $products = $odoo
            ->where('active', '=', false)
            ->fields(
                'id',
                'default_code',
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
                'x_studio_number_sold',
                'virtual_available',
                'incoming_qty',
                'available_threshold',
                'outgoing_qty',
                'weight',
                'image_small'
            )
            ->get('product.template');
        //     dd('$products');
        $this->update_table($products);

     //   $this->call('calc:lastsku');
        $this->info(date_format(date_create(), 'Y-m-d H:i:s'));

    }

    function display_image()
    {
        $images = Margin::where('code', '=', 'TI00035')->get();

        foreach ($images as $image) {
            dd ($image);
            echo '<img src="data:image/JPG;base64,' . $image->image . '"/>';
        }
    }

    function update_table($products)
    {

        for ($i = 0; $i < count($products); $i++) {
//dd( $products[$i]['categ_id']);

            $categ_temp = explode('/', $products[$i]['categ_id'][1]);
            $brand = 'no brand';
            $categ_temp = explode('/', $products[$i]['categ_id'][1]);
            $category = "no category";
            $sub_category = "";
            $this->info($products[$i]['categ_id'][1] . " size= " . (sizeof($categ_temp)));
            if (sizeof($categ_temp) == 2) {
                if (!in_array(" CBD ", $categ_temp, false)) {
                    $brand = str_replace(' ', '', $categ_temp[1]);
                    $category = str_replace(' ', '', $categ_temp[0]);
                    if (sizeof($categ_temp) >= 3) {
                        $sub_category = str_replace(' ', '', $categ_temp[2]);
                    }
                }
            }
            if (sizeof($categ_temp) >= 3) {
                if (!in_array(" CBD ", $categ_temp, false)) {
                    $brand = str_replace(' ', '', $categ_temp[2]);
                    $category = str_replace(' ', '', $categ_temp[1]);
                    if (sizeof($categ_temp) >= 4) {
//        dd($categ_temp);
                        $sub_category = str_replace(' ', '', $categ_temp[3]);
                    }
                }
            }
            $cost = 0;
            $gross_profit = 0;
            $margin = 0;
            $commission_percent = 0;
            $revenue = $products[$i]['list_price'];

            if ($revenue > 0.01) {
                $cost = $products[$i]['standard_price'];

                $gross_profit = $revenue - $cost;

                if ($gross_profit != 0 and $revenue != 0 and $cost != 0) {
                    $margin = bcmul('100', bcdiv($gross_profit, $revenue, 3), 3);
                } else {
                    $margin = 0;
                };
            } else {
                $margin = 0;
            }

            $commission_percent = $this->getCommission(round($margin, 0, PHP_ROUND_HALF_DOWN), 'N', 1, 0, '');

            Margin:: updateOrCreate(
                [
                    'ext_id' => $products[$i]['id']
                ],
                [
                    'name' => $products[$i]['name'],
                    'code' => $products[$i]['default_code'],
                    'is_active' => $products[$i]['active'] ? 1 : 0,
                    'cost' => $cost,
                    'revenue' => $revenue,
                    'margin' => $margin,
                    'commission_percent' => $commission_percent,
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
                    'image' => $products[$i]['image_small']
                ]
            );
       //     $this->display_image();
//         $this->info($products[$i]['name']);

        }


    }
}
