<?php

	namespace App\Console\Commands;

	use Illuminate\Console\Command;
	use App\InvoiceLine;
	use App\SaleInvoice;
	use App\SalesOrder;
	use App\Traits\CommissionTrait;

	class getInvoiceLines extends Command
	{
		use CommissionTrait;
		/**
		 * The name and signature of the console command.
		 *
		 * @var string
		 */
		protected $signature = 'odoo:invoice_lines  {arg1} {arg2}';

		/**
		 * The console command description.
		 *
		 * @var string
		 */
		protected $description = 'Import second after saleslines.Get all invoice lines from Odoo';

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

			$odoo = new \Edujugon\Laradoo\Odoo();
			$odoo = $odoo->connect();
			$invoice_lines = $odoo
				->where('create_date', '>=', date('Y-m-d', strtotime($retrieve_from)))
				//	->where('origin', '=', 'SO5447')
				//    ->where('id', '=', '1949')
				//   ->where('partner_id', '=', '3196')
				//	->where('create_date', '<=', date('Y-m-d', strtotime("-61 days")))
				//	->where('create_date', '>=', "2019-03-01")
				//	->where('create_date', '<=', date('Y-m-d', strtotime("2018-01-12")))
				//    ->limit(10)
				->fields(
					'id',
					'name',
					'invoice_id',
					'invoice_date',
					'partner_id',
					'product_id',
					'list_price',
					'origin',
					'price_unit',
					'price_subtotal_signed',
					'price_subtotal',
					'price_total',
					'quantity',
					'create_date',
					'invoice_type',
					'uom_id'
				)
				->get('account.invoice.line');
			//	   dd($invoice_lines);
			for ($i = 0; $i < count($invoice_lines); $i++) {

                InvoiceLine::updateOrCreate(
					[
						'ext_id' => $invoice_lines[$i]['id']
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

			$date = new \DateTime(); //this returns the current date time

			$invoice_lines = InvoiceLine::orderby('invoice_lines.order_id','desc')
				->select(
					'invoice_lines.*',
					'saleinvoices.margin as si_margin',
					'saleinvoices.product_margin as si_product_margin',
					'saleinvoices.brand_id as si_brand_id',
					'saleinvoices.brand as si_brand',
					'saleinvoices.commission as si_commission',
					'saleinvoices.comm_percent as si_comm_percent',
					'saleinvoices.comm_version as si_comm_version',
					'saleinvoices.comm_region as si_comm_region',
					'saleinvoices.invoice_date as si_invoice_date',
					'saleinvoices.code as si_code',
					'saleinvoices.code as si_code',
					'saleinvoices.sales_person_id as si_sales_person_id',
					'saleinvoices.cost as si_cost',
					'saleinvoices.qty_invoiced as si_qty_invoiced',
					'saleinvoices.qty_delivered as si_qty_delivered',
					'invoices.invoice_date as si_invoice_date',
					'invoices.state as si_invoice_state',
					'sales_persons.region as salesperson_region',

                    'margins.brand as brand_name',
                    'margins.category_full as brand_category_full',
                    'margins.category as si_category',
                    'margins.sub_category as si_subcategory'

				)
				->leftJoin('saleinvoices', function ($join) {
					$join->on('saleinvoices.order_id', '=', 'invoice_lines.order_id')
						->on('saleinvoices.product_id', '=', 'invoice_lines.product_id');
				})
                ->leftJoin('margins', 'margins.ext_id', '=', 'invoice_lines.product_id')
                ->leftJoin('invoices', 'invoices.ext_id', '=', 'invoice_lines.invoice_id')
				->leftJoin('sales_persons', 'sales_persons.sales_person_id', '=', 'invoice_lines.sales_person_id')
				->where('invoice_lines.create_date', '>=', date('Y-m-d', strtotime($retrieve_from)))
			//	->where('order_number', '=', 'SO4859')
		//				->limit(10)
				->get();
			$commission_version = 1;
      //      dd($invoice_lines->toarray());

			foreach ($invoice_lines as $invoice_line) {

				//		echo "brand_id= " . $invoice_line->si_brand_id;
				$commission_percent = $this->getCommission(round($invoice_line->si_margin, 0, PHP_ROUND_HALF_DOWN),
					$invoice_line->salesperson_region,
					$commission_version,
					$invoice_line->si_sales_person_id,
					$invoice_line->si_invoice_date);

				$commission = $invoice_line->price_subtotal * $commission_percent;

				if ($invoice_line->price_subtotal < 0) {
					$price_subtotal = 0;
					$commission = 0;
					$commission_percent = 0;
				} else {
					$price_subtotal = $invoice_line->price_subtotal;
				}

      //          $this->info($invoice_line->si_category .  " -> " . $invoice_line->si_subcategory);

				InvoiceLine::where('id', $invoice_line->id)
					->update([
						'margin' => $invoice_line->si_margin,
						'product_margin' => $invoice_line->si_product_margin,

						//		'commission' => $invoice_line->si_commission,
						'commission' => $commission,

						'brand_id' => $invoice_line->si_brand_id,
						'brand' => $invoice_line->si_brand,

						//			'comm_percent' => $invoice_line->si_comm_percent,
						'comm_percent' => $commission_percent,

						'comm_region' => $invoice_line->si_comm_region,
						'comm_version' => $invoice_line->si_comm_version,
						'category' => $invoice_line->si_category,
						'subcategory' => $invoice_line->si_subcategory,
						'code' => $invoice_line->si_code,
						'sales_person_id' => $invoice_line->si_sales_person_id,
						'cost' => $invoice_line->si_cost,
						'qty_invoiced' => $invoice_line->si_qty_invoiced,
						'qty_delivered' => $invoice_line->si_qty_delivered,
						'invoice_date' => $invoice_line->si_invoice_date,
						'invoice_state' => $invoice_line->si_invoice_state,
						'price_subtotal' => $price_subtotal
					]);
			}
			$this->info(date_format(date_create(), 'Y-m-d H:i:s'));

		}
	}
