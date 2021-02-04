<?php

namespace App\Console\Commands;

use App\Agedthreshold;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\SaleInvoice;
use Illuminate\Console\Command;
use App\Invoice;
use App\User;
use Carbon\Carbon;
use App\Notifications\InvoiceDue;
class getAccountingSalesOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all invoiced accounts';

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
        $orders = $odoo
            //			      ->where('origin','=','SO5383')
            ->fields(
                'id',
                'name',
                'display_name',
                'amount_untaxed',
                'amount_untaxed_signed',
                'residual',
                'date',
                'create_date',
                'date_invoice',
                'date_due',
                'partner_id',
                'user_id',
                'source_id',
                'number',
                'state',
                'reconciled',
                'origin',
                'type',
                'amount_total',
                'amount_tax',
                'amount_untaxed',
                'has_outstanding',
                'residual'
            )
            ->get('account.invoice');
        for ($i = 0; $i < count($orders); $i++) {

            if ($orders[$i]['date_invoice'] == '0000-00-00') {
                $invoice_date = '1900-01-01';
                $age = 0;
            } else {
                $invoice_date = $orders[$i]['date_invoice'];
                if ($orders[$i]['date_due'] < Carbon::now()) {
                    $date = Carbon::parse($orders[$i]['date_due']);
                    $age = $date->diffInDays(Carbon::now());
                } else {
                    $age = 0;
                }
            }

            Invoice:: updateOrCreate(
                [
                    'ext_id' => $orders[$i]['id']
                ],
                [
                    'name' => $orders[$i]['display_name'],
                    'state' => $orders[$i]['state'],
                    'reconciled' => $orders[$i]['reconciled'],
                    'invoice_date' => $invoice_date,
                    'invoice_status' => $orders[$i]['state'],
                    'due_date' => $orders[$i]['date_due'],
                    'sales_person_id' => $orders[$i]['user_id'][0],
                    'sales_person_name' => $orders[$i]['user_id'][1],
                    'customer_id' => $orders[$i]['partner_id'][0],
                    'customer_name' => $orders[$i]['partner_id'][1],
                    'sales_order' => $orders[$i]['origin'],
                    'amount_untaxed' => $orders[$i]['amount_untaxed'],
                    'amount_tax' => $orders[$i]['amount_total'] - $orders[$i]['amount_untaxed'],
                   	'type' => $orders[$i]['type'],
                    'amount_total' => $orders[$i]['amount_total'],
                    'amount_untaxed_signed' => $orders[$i]['amount_untaxed_signed'],
                    'has_outstanding' => $orders[$i]['has_outstanding'],
                    'residual' => $orders[$i]['residual'],
                    'age' => $age
                ]
            );

        }

        // keep only last version of multiple salesorders

        Schema::dropIfExists('invoices_unique');

        DB::statement('CREATE TABLE invoices_unique LIKE invoices');
        DB::statement('insert into invoices_unique (
						ext_id, name, sales_order, invoice_date, state, amount_tax) select ext_id, name, sales_order, max(invoice_date), state, amount_tax  from invoices
						group by sales_order, sales_person_id');

		$this->info(date_format(date_create(), 'Y-m-d H:i:s'));

    }
}


/*	insert into invoices_unique (
        ext_id, name, sales_order, invoice_date)

                        select ext_id, name, sales_order, max(invoice_date) as max_date from invoices
                         where invoice_date IS NOT NULL group by sales_order
*/

