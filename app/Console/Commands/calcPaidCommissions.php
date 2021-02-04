<?php

namespace App\Console\Commands;

use App\CommissionsPaid;
use App\TenNinetyPaid;
use App\InvoiceLine;
use App\Salesline;
use Illuminate\Console\Command;

class calcPaidCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:paid_commissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds invoices that got paid';

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
        $paid_invoices = Salesline::where('invoice_date', '>=', date('Y-m-d', strtotime(env('PAID_INVOICES_START_DATE', '2019-06-01'))))
            ->join('sales_persons', 'sales_persons.sales_person_id', '=', 'saleslines.sales_person_id')
            ->where('sales_persons.is_ten_ninety', '=', false)
            ->where('state', '=', 'paid')
            ->get();

        foreach ($paid_invoices as $paid_invoice) {
            CommissionsPaid::updateOrCreate(
                ['ext_id' => $paid_invoice->ext_id],
                ['order_number' => $paid_invoice->order_number]);
        }

        $paid_invoices = Salesline::where('invoice_date', '>=', date('Y-m-d', strtotime(env('PAID_INVOICES_START_DATE', '2019-06-01'))))
            ->leftJoin('sales_persons', 'sales_persons.sales_person_id', '=', 'saleslines.sales_person_id')
            ->where('sales_persons.is_ten_ninety', '=', true)
            ->where('state', '=', 'paid')
            ->get();
//dd($paid_invoices);
        foreach ($paid_invoices as $paid_invoice) {
            TenNinetyPaid::updateOrCreate(
                ['ext_id' => $paid_invoice->ext_id],
                ['order_number' => $paid_invoice->order_number]);
        }
    }
}
