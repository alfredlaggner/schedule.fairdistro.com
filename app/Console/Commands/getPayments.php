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
use Carbon\Carbon;

class getPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:payments';

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

        $start = '2020-01-01';

        $current_month = Carbon::now()->month;

        $odoo = new \Edujugon\Laradoo\Odoo();
        $odoo = $odoo->connect();
        $payments = $odoo
                  ->where('payment_date', '>=', $start)
            ->where('state', '=', 'posted')
            ->where('partner_type', '=', 'customer')
            ->where('has_invoices', '=', true)
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
        for ($i = 0; $i < count($payments); $i++) {

            $invoices = $payments[$i]['invoice_ids'];
            $invoice_count = sizeof($invoices);
            $invoice_id = 0;
//dd($payments);
            for ($z = 0; $z < $invoice_count; $z++) {
                $invoice_id = $invoices[$z];

                Payment:: updateOrCreate(
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
        Payment::where('has_invoices', '=', false)->delete();

        Payment::leftJoin('invoices', 'invoices.ext_id', '=', 'payments.invoice_id')
            ->update([
                'payments.sales_person_id' => DB::raw('invoices.sales_person_id'),
                'payments.invoice_date' => DB::raw('invoices.invoice_date'),
                'payments.sales_order' => DB::raw('invoices.sales_order'),
                'payments.amount_untaxed' => DB::raw('invoices.amount_untaxed'),
                'payments.amount_taxed' => DB::raw('invoices.amount_total'),
                'payments.amount' => DB::raw('invoices.amount_untaxed'),
                'payments.tax' => DB::raw('invoices.amount_tax'),
                'payments.invoice_state' => DB::raw('invoices.state'),
                'payments.amount_due' => DB::raw('invoices.residual'),
            ]);


        $amount = 0.00;
        $payments = Payment::whereNotNull('invoice_date')
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
            Payment::where('id', $payment->id)->update([
                'year_invoiced' => Carbon::createFromFormat('Y-m-d', $payment->invoice_date)->year,
                'month_invoiced' => Carbon::createFromFormat('Y-m-d', $payment->invoice_date)->month,
                'commission' => $commission,
            ]);
        }

        $query = Invoice::select(
            '*',
            'payments.amount as payments_amount',
            'payments.payment_date as payments_payment_date',
            'payments.invoice_id as payments_invoice_id')
            ->join('payments', 'payments.invoice_id', '=', 'invoices.ext_id')
            ->where('invoices.invoice_date', '>=', '2020-01-01')
            ->orderBy('payments_payment_date', 'desc')
            ->get();

        foreach ($query as $q) {
//$this->info($q->invoice_date);
            Invoice::where('ext_id', $q->payments_invoice_id)
                ->update(['invoices.payment_amount' => $q->payments_amount,
                    'invoices.payment_date' => $q->payments_payment_date,
                    'invoices.payment_invoice_id' => $q->payments_invoice_id,]);
            /*            $this->info($q->payments_payment_date);
                        $this->info($q->payments_amount);*/
        }
    //    dd("halt");

        Payment::where('invoice_state', 'open')->delete();

        $this->info(date_format(date_create(), 'Y-m-d H:i:s'));

    }
}

