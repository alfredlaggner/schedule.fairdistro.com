<?php

namespace App\Console\Commands;

use App\InvoiceLine;
use Illuminate\Console\Command;
use CashIn;

class getCashIn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:cash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Odoo Cash';

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
		$cash_in = $odoo
			->fields(
				'id',
				'create_date',
				'display_name',
				'ref',
				'write_date'
			)
			->get('account.cashbox.line');

		dd($cash_in->count());
		for ($i = 0; $i < count($cash_in); $i++) {
			CashIn::updateOrCreate(
				[
					'ext_id' => $cash_in[$i]['id']
				],
				[
					'customer_id' => $invoice_lines[$i]['partner_id'][0],
					'created_at' => $invoice_lines[$i]['create_date'],
					'product_id' => $invoice_lines[$i]['product_id'][0],
					'invoice_id' => $invoice_lines[$i]['invoice_id'][0],
					'invoice_name' => $invoice_lines[$i]['invoice_id'][1],
					'order_number' => $invoice_lines[$i]['origin'],
					'invoice_number' => $invoice_lines[$i]['origin'],
					'order_id' => substr($invoice_lines[$i]['origin'], 2),
					'name' => $invoice_lines[$i]['name'],
					'quantity' => $invoice_lines[$i]['quantity'],
					'qty_invoiced' => $invoice_lines[$i]['quantity'],
					'unit_price' => $invoice_lines[$i]['price_unit'],
					'price_subtotal' => $invoice_lines[$i]['price_subtotal_signed'],
					'amt_invoiced' => $invoice_lines[$i]['price_subtotal_signed'],
					'price_total' => $invoice_lines[$i]['price_total'],
					'ext_id_unit' => $invoice_lines[$i]['uom_id'][1],

					'invoice_date' => $invoice_lines[$i]['create_date'],  // invoice date needs cleaning up
					'order_date' => $invoice_lines[$i]['create_date'],  // rename to invoice_id
					'create_date' => $invoice_lines[$i]['create_date'],  // rename to invoice_id
				]);

		}

	}
}
