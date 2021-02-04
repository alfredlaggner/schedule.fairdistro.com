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
use App\Notifications\InvoiceDue;

class payments_wrong extends Command
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

        $odoo = new \Edujugon\Laradoo\Odoo();
        $odoo = $odoo->connect();
        $payments = $odoo
            ->where('payment_date', '>=', date('Y-m-d', strtotime("2018-03-01")))
            ->where('partner_type', '=', 'customer')
            ->where('has_invoices', '=', true)
            ->where('state', '=', 'posted')
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
                'state',
/*                'x_studio_field_8tRY0',
                'x_studio_field_vOTbZ'*/
            )
            ->get('account.payment');
    //       dd($payments);
        for ($i = 0; $i < count($payments); $i++) {
            if ($payments[$i]['invoice_ids']) {
                $invoice_id = $payments[$i]['invoice_ids'][0];
            } else {
                $invoice_id = 0;
            }
            Payment:: updateOrCreate(
                [
                    'ext_id' => $payments[$i]['id']
                ],
                [
                    'name' => $payments[$i]['name'],
                    'display_name' => $payments[$i]['display_name'],
                    'state' => $payments[$i]['state'],
                    'payment_type' => $payments[$i]['payment_type'],
                    'payment_date' => $payments[$i]['payment_date'],
                    'amount' => $payments[$i]['amount'],
                    'invoice_ids' => $invoice_id,
                    'customer_id' => $payments[$i]['partner_id'][0],
                    'customer_name' => $payments[$i]['partner_id'][1],
                    'customer_type' => $payments[$i]['partner_type'],
                    'multi' => $payments[$i]['multi'],
                    'move_name' => $payments[$i]['move_name'],
                    'has_invoices' => $payments[$i]['has_invoices'],
                    'payment_difference' => $payments[$i]['payment_difference'],
/*                    'sales_person_id' => $payments[$i]['x_studio_field_vOTbZ']
                    'invoice_id' => $payments[$i]['x_studio_field_8tRY0'],*/
                ]
            );

        }

        $query = Invoice::select(
            'payments.amount as payments_amount',
            'payments.invoice_ids as payments_invoice_id',
            'payments.payment_date as payments_payment_date',
            'payments.invoice_ids as payments_invoice_id')
            ->leftJoin('payments', 'payments.invoice_ids', '=', 'invoices.ext_id')
            ->get();

        foreach ($query as $q) {

            Invoice::where('ext_id', $q->payments_invoice_id)
                ->update([
                    'invoices.payment_amount' => $q->payments_amount,
                    'invoices.payment_date' => $q->payments_payment_date,
                    'invoices.payment_invoice_id' => $q->payments_invoice_id,
                ]);
/*            $this->info($q->payments_payment_date);
            $this->info($q->payments_amount);*/
        }
        $this->info(date_format(date_create(), 'Y-m-d H:i:s'));

    }
}
