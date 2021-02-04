<?php

namespace App\Console\Commands;

use App\Agedthreshold;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\SaleInvoice;
use Illuminate\Console\Command;
use App\Invoice;
use App\User;
use App\Payment;
use App\PaymentAr;
use Carbon\Carbon;

class getPayments_AR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:payments_AR';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get invoice payments';

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

        $start = new Carbon(now());
        $start = substr($start->firstOfMonth(), 0, 10);

        $start = '2018-01-01';

        $current_month = Carbon::now()->month;

        $odoo = new \Edujugon\Laradoo\Odoo();
        $odoo = $odoo->connect();
        $payments = $odoo
                  ->where('payment_date', '>=', $start)
            ->where('state', '=', 'posted')
            ->where('partner_type', '=', 'customer')
            ->where('has_invoices', '=', true)
       //    ->where('id', '=', 9210)
            ->fields(
                'id',
                'name',
                'payment_date',
                'payment_type',
                'display_name',
                'amount',
                'invoice_ids',
                'partner_id',
                'partner_type',
                'multi',
                'move_name',
                'has_invoices',
                'payment_difference',
                'state'
            /*                'x_studio_field_8tRY0',
                            'x_studio_field_vOTbZ'*/
            )
            ->get('account.payment');

    //    dd($payments);

        for ($i = 0; $i < count($payments); $i++) {

            $invoices = $payments[$i]['invoice_ids'];
            $invoice_count = sizeof($invoices);
            $invoice_id = 0;
//dd($payments);
            for ($z = 0; $z < $invoice_count; $z++) {
                $invoice_id = $invoices[$z];

                PaymentAr:: updateOrCreate(
                    [
                        'ext_id' => $payments[$i]['id'],
                        'invoice_id' => $invoice_id,
                    ],
                    [
                        'year_paid' => Carbon::createFromFormat('Y-m-d', $payments[$i]['payment_date'])->year,
                        'month_paid' => Carbon::createFromFormat('Y-m-d', $payments[$i]['payment_date'])->month,
                        'name' => $payments[$i]['name'],
                        'display_name' => $payments[$i]['display_name'],
                        'state' => $payments[$i]['state'],
                        'payment_type' => $payments[$i]['payment_type'],
                        'payment_date' => $payments[$i]['payment_date'],
                        'payment_amount' => $payments[$i]['amount'],

                        /*                        'amount_taxed' => $payments[$i]['amount'],
                                                'amount_untaxed' => ($payments[$i]['amount']) / 1.27,*/
                        'invoice_count' => $invoice_count,
                        'customer_id' => $payments[$i]['partner_id'][0],
                        'customer_name' => $payments[$i]['partner_id'][1],
                        'customer_type' => $payments[$i]['partner_type'],
                        'multi' => $payments[$i]['multi'],
                        'move_name' => $payments[$i]['move_name'],
                        'has_invoices' => $payments[$i]['has_invoices'],
                        'payment_difference' => $payments[$i]['payment_difference'],
                    ]
                );

            }
        }
    //    PaymentAr::where('has_invoices', '=', false)->delete();

        PaymentAr::leftJoin('invoices', 'invoices.ext_id', '=', 'payment_ars.invoice_id')
            ->update([
                'payment_ars.sales_person_id' => DB::raw('invoices.sales_person_id'),
                'payment_ars.invoice_date' => DB::raw('invoices.invoice_date'),
                'payment_ars.sales_order' => DB::raw('invoices.sales_order'),
                'payment_ars.amount_untaxed' => DB::raw('invoices.amount_untaxed'),
                'payment_ars.amount_taxed' => DB::raw('invoices.amount_total'),
                'payment_ars.amount' => DB::raw('invoices.amount_untaxed'),
                'payment_ars.tax' => DB::raw('invoices.amount_tax'),
                'payment_ars.invoice_state' => DB::raw('invoices.state'),
                'payment_ars.amount_due' => DB::raw('invoices.residual'),
            ]);


        $amount = 0.00;
        $payments = PaymentAr::whereNotNull('invoice_date')
            ->where('payment_date', '>=', $start)
            ->get();
        foreach ($payments as $payment) {
            /*            if ($payment->payment_difference === 0.00) {
                            $amount_due = 0;
                        } else {
                            $amount_due = $payment->amount_taxed + $payment->payment_difference;
                        }*/

            $commission = $payment->commission;
            /*            if ($amount_due > 1.00) {
                            //    dd($amount_due);
                            $commission = 0.00;
                        }*/
            PaymentAr::where('id', $payment->id)->update([
                'year_invoiced' => Carbon::createFromFormat('Y-m-d', $payment->invoice_date)->year,
                'month_invoiced' => Carbon::createFromFormat('Y-m-d', $payment->invoice_date)->month,
                'commission' => $commission,
            ]);
        }


        $this->info(date_format(date_create(), 'Y-m-d H:i:s'));

    }
}

