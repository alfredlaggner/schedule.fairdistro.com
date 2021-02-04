<?php

namespace App\Console\Commands;


use App\Driver;
use App\SaleInvoice;
use App\Salesline;
use App\InvoiceLine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class saleslines extends Command
{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:invoicelines {arg1} {arg2}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import after saleslines and invoice_lines';

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


        $arg1 = $this->argument('arg1');
        $arg2 = $this->argument('arg2');
        $arg_1 = str_replace('/', '', $arg1);

        $retrieve_from = $arg_1 . ' ' . $arg2;
        $this->info($retrieve_from);

        //	dd(date('Y-m-d', strtotime("-10 days")));
        $queries = InvoiceLine::select(DB::raw('
			*,
			sales_persons.name as rep,
			customers.name as customer_name,
	        price_subtotal as amount,
            invoice_lines.name as name,
            invoice_lines.subcategory as subcategory,
            invoice_lines.category as category,
            invoice_lines.brand_id as brand_id,
            invoice_lines.ext_id as ext_id,
            product_products.category_full as product_category_full,
            invoice_lines.sales_person_id as invoice_lines_salesPerson_id,
            commissions_paids.paid_at as commissions_paids_paid_at,
            commissions_paids.created_at as  commissions_paids_created_at
            '))
            ->leftJoin('sales_persons', 'sales_persons.sales_person_id', '=', 'invoice_lines.sales_person_id')
            ->leftJoin('customers', 'customers.ext_id', '=', 'invoice_lines.customer_id')
            ->leftJoin('commissions_paids', 'commissions_paids.ext_id', '=', 'invoice_lines.ext_id')
            ->leftJoin('product_products', 'product_products.ext_id', '=', 'invoice_lines.product_id')
            ->where('invoice_lines.order_date', '>=', date('Y-m-d', strtotime($retrieve_from)))
            ->where('invoice_lines.sales_person_id', '>', 0)
            ->where('invoice_lines.margin', '>', -100)
            ->where('invoice_lines.margin', '<', 100)
            ->where('invoice_date', '!=', null)
            ->get();

        //	dd($queries->count());
        $cat_sub1 = '';
        $cat_sub2 = '';
        $cat_sub3 = '';
        $cat_sub4 = '';
        $cat_sub5 = '';

        foreach ($queries as $q) {
            $amount_tax = $q->amount * 0.24;
            $amount_total = $q->amount + $amount_tax;

            $cats = explode('/', $q->category_full);
            $this->info($q->category_full);

            $cat_length = sizeof($cats);
            $this->info($cat_length);
            if ($cat_length >= 1) {
                $cat_sub1 = trim($cats[0]);
            }
            if ($cat_length >= 2) {
                $cat_sub2 = trim($cats[1]);
            }
            if ($cat_length >= 3) {
                $cat_sub3 = trim($cats[2]);
            }
            if ($cat_length >= 4) {
                $cat_sub4 = trim($cats[3]);
                //    dd($cats);
            }
            if ($cat_length >= 5) {
                $cat_sub5 = trim($cats[4]);
            }


            Salesline::updateOrCreate(
                ['ext_id' => $q->ext_id],
                ['order_number' => $q->invoice_number,
                    'order_date' => $q->order_date,
                    'customer_name' => $q->customer_name,
                    'customer_id' => $q->customer_id,
                    'sales_person_id' => $q->invoice_lines_salesPerson_id,
                    'rep' => $q->rep,
                    'state' => $q->invoice_state,
                    'name' => $q->name,
                    'product_category' => $q->category,
                    'product_subcategory' => $q->subcategory,
                    'brand_id' => $q->brand_id,
                    'sku' => $q->code,
                    'brand_name' => $q->brand,
                    'qty_delivered' => $q->qty_delivered,
                    'quantity' => $q->quantity,
                    'qty_invoiced' => $q->qty_invoiced,
                    'amount_invoiced' => $q->amt_invoiced,
                    'amount_to_invoice' => $q->amt_to_invoice,
                    'unit_price' => $q->unit_price,
                    'cost' => $q->cost,
                    'commission' => $q->commission,
                    'comm_percent' => $q->comm_percent,
                    'amount_tax' => $amount_tax,
                    'amount_total' => $amount_total,
                    'amount_untaxed' => $q->amount,
                    'invoice_date' => $q->invoice_date,
                    'invoice_paid_at' => $q->commissions_paids_created_at,
                    'commission_paid_at' => $q->commissions_paids_paid_at,
                    'comm_region' => $q->comm_region,
                    'order_id' => $q->order_id,
                    'product_id' => $q->product_id,
                    'margin' => $q->margin,
                    'amount' => $q->amount,
                    'category_full' => $q->product_category_full,
                    'cat_sub1' => $cat_sub1,
                    'cat_sub2' => $cat_sub2,
                    'cat_sub3' => $cat_sub3,
                    'cat_sub4' => $cat_sub4,
                    'cat_sub5' => $cat_sub5
                ]);
            //       $this->info($q->invoice_lines_salesPerson_id);

        }
        /*        DB::table('saleslines')->delete();
                DB::table('saleslines')->insert($arr);*/
        return false;
    }
}
