<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class update_odoo_products extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:update_products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update product.product model with additional values';

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

        $query = DB::table('product_imports')
            //     ->where('category', '=', 'Cartridge')
            ->get();

        foreach ($query as $q) {

            $updated = $odoo->where('id', '=', $q->ext_id)
                ->update('product.template', [
                                  'x_studio_sample' => $q->sample,
         /*           'x_studio_field_WSEir' => $q->sc_cart != 'FALSE' ? $q->sc_cart : '0',
                    'x_studio_field_95Dvf' => $q->sc_tincture != 'FALSE' ? $q->sc_tincture : '0',
                    'x_studio_subcategory_1' => $q->sc_dropper != 'FALSE' ? $q->sc_dropper : '0',
                    'x_studio_subcategory_2' => $q->sc_flower != 'FALSE' ? $q->sc_flower : '0',
                    'x_studio_subcategory_3' => $q->sc_edible != 'FALSE' ? $q->sc_edible : '0',
                    'x_studio_subcategory_4' => $q->sc_preroll != 'FALSE' ? $q->sc_preroll : '0',
                    'x_studio_subcategory_5' => $q->sc_beverage != 'FALSE' ? $q->sc_beverage : '0',
                    'x_studio_subcategory_6' => $q->sc_pills != 'FALSE' ? $q->sc_pills : '0',
                    'x_studio_subcategory_7' => $q->sc_suppository != 'FALSE' ? $q->sc_suppository : '0',
                    'x_studio_subcategory_8' => $q->sc_plants != 'FALSE' ? $q->sc_plants : '0',
                    'x_studio_subcategory_9' => $q->sc_topical != 'FALSE' ? $q->sc_topical : '0',
                    'x_studio_subcategory' => $q->sc_liquids != 'FALSE' ? $q->sc_liquids : '0',
                    'x_studio_total_terps' => $q->total_terps != null ? $q->total_terps : '',
                    'x_studio_species' => $q->species != 'FALSE' ? $q->species : '',
                    'x_studio_unit_size_unit' => $q->unit_size_unit != null ? $q->unit_size_unit : '',
                    'x_studio_unit_size_1' => $q->unit_size != null ? $q->unit_size : '',
                    'x_studio_total_cannabinoids' => $q->total_cannabinoids != null ? $q->total_cannabinoids : '',
                    'x_studio_total_thc' => $q->total_thc != null ? $q->total_thc : '',
                    'x_studio_case_qty' => $q->case_quantity != null ? $q->case_quantity : '',
                    'x_studio_total_cbd' => $q->total_cbd != null ? $q->total_cbd : '',
                    'x_studio_batch_id' => $q->batch_id != null ? $q->batch_id : '',
                    'x_studio_supplier' => $q->supplier != null ? $q->supplier : '',
                    'x_studio_product_name' => $q->product_name,
                    'x_studio_marketing_description' => $q->marketing_description,
                    'x_studio_category' => $q->category,
                    /*                   'x_studio_field_WSEir' => $q->sc_cart != 'FALSE' ? $q->sc_cart : 0,
                                       'x_studio_field_95Dvf' => $q->sc_tincture != 'FALSE' ? $q->sc_tincture : 0,
                                       'x_studio_subcategory_1' => $q->sc_dropper != 'FALSE' ? $q->sc_dropper : 0,
                                       'x_studio_subcategory_2' => $q->sc_flower != 'FALSE' ? $q->sc_flower : 0,
                                       'x_studio_subcategory_3' => $q->sc_edible != 'FALSE' ? $q->sc_edible : 0,
                                       'x_studio_subcategory_4' => $q->sc_preroll != 'FALSE' ? $q->sc_preroll : 0,
                                       'x_studio_subcategory_5' => $q->sc_beverage != 'FALSE' ? $q->sc_beverage : 0,
                                       'x_studio_subcategory_6' => $q->sc_pills != 'FALSE' ? $q->sc_pills : 0,
                                       'x_studio_subcategory_7' => $q->sc_suppository != 'FALSE' ? $q->sc_suppository : 0,
                                       'x_studio_subcategory_8' => $q->sc_plants != 'FALSE' ? $q->sc_plants : 0,
                                       'x_studio_subcategory_9' => $q->sc_topical != 'FALSE' ? $q->sc_topical : 0,
                                       'x_studio_subcategory' => $q->sc_liquids != 'FALSE' ? $q->sc_liquids : 0,
                   */
                ]);

            if ($updated) {
                $this->info($q->ext_id . ' ' . $q->category);
                $this->info($q->sc_tincture);
            } else {
                $this->info($q->ext_id . ' Not updated ');
            }

        }
        $products = $odoo
                   ->where('sample', '=', 1)
      //      ->where('x_studio_field_95Dvf', '!=', '0')
            ->fields(
                'id',
                'x_studio_sample'
            )
            ->get('product.template');
        dd($products);
        /*        'x_studio_subcategory_2' => $q->sc_misc != 'FALSE' ? $q->sc_misc : 0,
                            'x_studio_subcategory_2' => $q->sc_concentrate != 'FALSE' ? $q->sc_concentrate : 0,*/

    }
}
