<?php

namespace App\Console\Commands;

use App\InvoiceLine;
use App\Salesline;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class count_sales_per_product extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:salescount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the number of sales per product';

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

        $queries = InvoiceLine::select(DB::raw('*,
                count(product_id) as count_product
                '))
            ->groupBy('product_id')
            ->orderBy('count_product', 'desc')
            ->get();

        foreach ($queries as $q) {
            if ($q->count_product) {
                $updated = $odoo->where('id', $q->product_id)
                    ->update('product.product', ['x_studio_number_sold' => $q->count_product]);

                $this->info($q->name . ": " . $q->count_product);
            }

        }
    }

    public function handle1()
    {
        $query = DB::table('customer_ext_ids')
            ->where('ext_id', '>', 0)
            ->get();

        foreach ($query as $q) {

            $a = \App\Customer::where('ext_id', $q->ext_id)->update(['api_id' => $q->api_id]);
        }

    }

}
