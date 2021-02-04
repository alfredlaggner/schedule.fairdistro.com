<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\SaleInvoice;

class test_invoice_date extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'just testing';

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
        $date = new \DateTime(); //this returns the current date time
        $today = $date->format('Y-m-d');

        $sis = SaleInvoice::orderby('id', 'desc')
            // ->has('product')
            //     ->has('customer')
            ->has('invoice')
            ->where('created_at', '>=', date('Y-m-d', strtotime("- 3 months")))
            ->get();

        $i =0;
        foreach ($sis as $si) {

            $usi = SaleInvoice::find($si->id);

            //        $usi->product_margin = $si->product->margin;

            /*            if ($si->customer->tax_free) {
                            $usi->amt_to_invoice = $si->amt_to_invoice + ($si->amt_to_invoice * 0.24);
                            $usi->amt_invoiced = $si->amt_invoiced + ($si->amt_invoiced * 0.24);
                        }*/

            $usi->is_paid = $si->invoice->state == 'paid' ? True : False;
            $usi->invoice_date = $si->invoice->invoice_date;
            //     $usi->brand = $si->product->brand;
            //     $usi->brand_id = $si->product->category_id;
            $usi->save();
            $i++;
        }
    }
}
