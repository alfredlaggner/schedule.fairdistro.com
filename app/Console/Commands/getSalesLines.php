<?php

	namespace App\Console\Commands;

	use Illuminate\Console\Command;
	use App\SaleInvoice;
	use App\Traits\CommissionTrait;
	use Illuminate\Support\Facades\DB;

	class getSalesLines extends Command
	{
		/**
		 * The name and signature of the console command.
		 *
		 * @var string
		 */
		use CommissionTrait;

		protected $signature = 'odoo:saleslines {arg1?} {arg2?}';

		/**
		 * The console command description.
		 *
		 * @var string
		 */
		protected $description = 'import first';

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


			$retrieve_from = date('Y-m-d', strtotime(today()));

			if ($this->argument('arg1') AND $this->argument('arg2')) {
				$arg1 = $this->argument('arg1');
				$arg2 = $this->argument('arg2');
				$arg_1 = str_replace('/', '', $arg1);
				$retrieve_from = $arg_1 . ' ' . $arg2;
			}

//dd($retrieve_from);

			$this->info($retrieve_from);

			$odoo = new \Edujugon\Laradoo\Odoo();
			$odoo = $odoo->connect();
			$order_lines = $odoo
				//	->where('create_date', '>=', date('Y-m-d', strtotime($retrieve_from)))
				->where('write_date', '>=', date('Y-m-d', strtotime($retrieve_from)))
				//	->where('order_id', 'SO6353')
				->fields(
					'id',
					'write_date',
					'name',
					'price_subtotal',
					'move_ids',
					'invoice_lines',
					'untaxed_amount_to_invoice',
					'untaxed_amount_invoiced',
					'invoice_status',
					'margin',
					'qty_invoiced',
					'qty_to_invoice',
					'qty_delivered',
					'product_uom_qty',
					'price_unit',
					'product_uom',
					'create_date',
					'order_partner_id',
					'product_id',
					'order_partner_id',
					'list_price',
					'order_id',
					'purchase_price',
					'salesman_id'
				)
				->get('sale.order.line');

			$this->info($retrieve_from);

//			dd($order_lines);

			//	dd($order_lines);
			$name = '';
			$code = '';
			$margin = 0;

			SaleInvoice::where('order_date', '>=', date('Y-m-d', strtotime($retrieve_from)))->delete();
//dd("test");

			for ($i = 0; $i < count($order_lines); $i++) {


			//	$this->info( $order_lines[$i]['write_date']);



				$name_org = $order_lines[$i]['name'];
				$pos = strpos($name_org, ']');
				$code = '';
				if ($pos) {
					$name = substr($name_org, $pos + 2);
					$code = substr($name_org, 0, $pos + 2);
				} else {
					$name = $name_org;
				}

				$revenue = $order_lines[$i]['price_unit'];
				if ($revenue > 0.01) {
					$cost = $order_lines[$i]['purchase_price'];

					$gross_profit = bcsub($revenue, $cost, 3);

					if ($gross_profit != 0 and $revenue != 0 and $cost != 0) {
						$margin = bcmul('100', bcdiv($gross_profit, $revenue, 3), 3);
						// dd($margin);
					} else {
						$margin = 0;
					};
				} else {
					$margin = 0;
				}
				SaleInvoice::updateOrCreate(
					[
						'ext_id' => $order_lines[$i]['id']
					],
					[
						'ext_id_shipping' => $order_lines[$i]['order_partner_id'][0],
						'order_date' => $order_lines[$i]['create_date'],
						'created_at' => $order_lines[$i]['create_date'],
						'sales_person_id' => $order_lines[$i]['salesman_id'][0],
						'product_id' => $order_lines[$i]['product_id'][0],
						'order_id' => substr($order_lines[$i]['order_id'][1], 2),
						'customer_id' => $order_lines[$i]['order_partner_id'][0],
						'invoice_number' => $order_lines[$i]['order_id'][1],
						'name' => $name,
						'code' => $code,
						'quantity' => $order_lines[$i]['product_uom_qty'],
						'cost' => $order_lines[$i]['purchase_price'],
						'ext_id_unit' => $order_lines[$i]['product_uom'][1],
						'unit_price' => $order_lines[$i]['price_unit'],
						'margin' => $margin,
						'amt_to_invoice' => $order_lines[$i]['untaxed_amount_to_invoice'] / 1.24,
						'amt_invoiced' => $order_lines[$i]['untaxed_amount_invoiced'] / 1.24,
						'price_subtotal' => $order_lines[$i]['price_subtotal'],
						'invoice_status' => $order_lines[$i]['invoice_status'],
						'odoo_margin' => $order_lines[$i]['margin'],
						'qty_invoiced' => $order_lines[$i]['qty_invoiced'],
						'qty_to_invoice' => $order_lines[$i]['qty_to_invoice'],
						'qty_delivered' => $order_lines[$i]['qty_delivered'],
					]);

			}

			$date = new \DateTime(); //this returns the current date time
			$today = $date->format('Y-m-d');

			/*        $latestInvoices = DB::table('invoices_unique')
						->select('sales_order', DB::raw('MAX(invoice_date) as last_invoice_date'))
						->groupBy('sales_order');*/

			$sis = SaleInvoice::select('saleinvoices.ext_id as eext_id',

				'saleinvoices.sales_person_id as ssales_person_id',
				'saleinvoices.margin',
				'saleinvoices.name as xname',
				'saleinvoices.price_subtotal as subtotal',
				'saleinvoices.amt_invoiced as invoiced',
				'saleinvoices.amt_to_invoice  as to_invoice',
				'saleinvoices.id as sid',
				'margins.brand as mbrand',
				'margins.brand_id as mbrand_id',
				'invoices_unique.invoice_date as iinvoice_date',
				'invoices_unique.state as iinvoice_state',
				'invoices_unique.amount_tax as aamount_tax',
				'invoices_unique.state as iinvoice_state',
				'brands.name as brand_name',
				'brands.category_full as brand_category_full',
				'brands.category as brand_category',
				'brands.subcategory as brand_subcategory',
				'sales_persons.region as salesperson_region'

			)
				->leftJoin('margins', 'margins.ext_id', '=', 'saleinvoices.product_id')
				->leftJoin('invoices_unique', 'invoices_unique.sales_order', 'like', 'saleinvoices.invoice_number')
				->leftJoin('brands', 'brands.ext_id', '=', 'saleinvoices.brand_id')
				->leftJoin('sales_persons', 'sales_persons.sales_person_id', '=', 'saleinvoices.sales_person_id')
				->where('saleinvoices.order_date', '>=', date('Y-m-d', strtotime($retrieve_from)))
				->orderby('saleinvoices.id', 'desc')
				->get();
			$commission_version = 1;

			foreach ($sis as $si) {

				$brand = $si->mbrand;
				$sid = $si->sid;
				$is_paid = $si->iinvoice_state == 'paid' ? True : False;
				$invoice_date = $si->iinvoice_date;

			//	$this->info($invoice_date);

				$brand_name = $si->brand_name;

				$commission_percent = $this->getCommission(round($si->margin, 0, PHP_ROUND_HALF_DOWN), $si->salesperson_region, $commission_version, $si->ssales_person_id, $si->iinvoice_date);

				if (!$si->invoiced and !$si->to_invoice) {
					$commission = $si->subtotal * $commission_percent;
				} else {
					$commission = ($si->invoiced + $si->to_invoice) * $commission_percent;
				}

		//		$this->info($commission);

				$brand_subcategory = $si->brand_subcategory;
				$brand_category = $si->brand_category;

				if (intval($si->aamount_tax) === 0) {
					if ($si->to_invoice > 0) {
						$si->to_invoice = $si->to_invoice + ($si->to_invoice * 0.24);
					}
					if ($si->invoiced > 0) {
						$si->invoiced = $si->invoiced + ($si->invoiced * 0.24);
					}
				}

        //        $this->info($si->mbrand_id .  " -> " . $brand_name . " or " . $brand_category);

				SaleInvoice::where('id', $sid)
					->update([
						'brand' => $si->mbrand,
						'brand_id' => $si->mbrand_id,
						'is_paid' => $is_paid,
						'invoice_date' => $invoice_date,
						'category' => $brand_category,
						'subcategory' => $brand_subcategory,
						'commission' => $commission,
						'comm_percent' => $commission_percent,
						'comm_region' => $si->salesperson_region,
						'amt_to_invoice' => $si->to_invoice,
						'amt_invoiced' => $si->invoiced,
					]);
				// }
			}
			$this->info(date_format(date_create(), 'U = Y-m-d H:i:s'));

		}
	}
