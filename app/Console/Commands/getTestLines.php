<?php

	namespace App\Console\Commands;

	use Illuminate\Console\Command;
	use App\SaleInvoice;
	use App\TestLine;

	class getTestLines extends Command
	{
		/**
		 * The name and signature of the console command.
		 *
		 * @var string
		 */
		protected $signature = 'odoo:testlines';

		/**
		 * The console command description.
		 *
		 * @var string
		 */
		protected $description = 'Command description';

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
			$updated = $odoo->where('name', '99 Plus Outlet and Smoke Shop')
				->update('res.partner', ['create_uid' => '__export__.res_users_14_e05e6790']);
			
			$this->info($updated);

			//Rani
			/*

						$updated = $odoo->where('origin', 'SO1675')
							->update('account.invoice',['user_id' => 63]); //Rani
						$updated = $odoo->where('origin', 'SO1305')
							->update('account.invoice',['user_id' => 35]); //Matt

						$updated = $odoo->where('origin', 'SO4558')
							->update('account.invoice',['user_id' => 56]);
						$updated = $odoo->where('origin', 'SO3955')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO3987')
							->update('account.invoice',['user_id' => 61]); //Sebastien
						$updated = $odoo->where('origin', 'SO3962')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO4067')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO4031')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO4033')
							->update('account.invoice',['user_id' => 38]); //Bill
						$updated = $odoo->where('origin', 'SO4475')
							->update('account.invoice',['user_id' => 38]); //Bill
						$updated = $odoo->where('origin', 'SO3235')
							->update('account.invoice',['user_id' => 38]); //Bill
						$updated = $odoo->where('origin', 'SO4849')
							->update('account.invoice',['user_id' => 38]); //Bill
						$updated = $odoo->where('origin', 'SO4676')
							->update('account.invoice',['user_id' => 38]); //Bill
						$updated = $odoo->where('origin', 'SO4720')
							->update('account.invoice',['user_id' => 38]); //Bill
						$updated = $odoo->where('origin', 'SO4805')
							->update('account.invoice',['user_id' => 38]); //Bill
						$updated = $odoo->where('origin', 'SO1002')
							->update('account.invoice',['user_id' => 38]); //Bill
						$updated = $odoo->where('origin', 'SO3576')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO3754')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1448')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1447')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1945')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1743')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1742')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1721')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1721')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1721')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO2091')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1947')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1449')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1445')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1597')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1548')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1546')
							->update('account.invoice',['user_id' => 39]); //Blake



						$updated = $odoo->where('origin', 'SO1949')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1959')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO2158')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1864')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1868')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO2137')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO2184')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO2191')
							->update('account.invoice',['user_id' => 39]); //Blake

						$updated = $odoo->where('origin', 'SO2319')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO2238')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1333')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO2027')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1970')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO2329')
							->update('account.invoice',['user_id' => 52]); //Ky

						$updated = $odoo->where('origin', 'SO2400')
							->update('account.invoice',['user_id' => 56]); //Eli

						$updated = $odoo->where('origin', 'SO1628')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO2083')
							->update('account.invoice',['user_id' => 39]); //Blake

						$updated = $odoo->where('origin', 'SO1928')
							->update('account.invoice',['user_id' => 56]); //Eli

						$updated = $odoo->where('origin', 'SO2334')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1477')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO2239')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1643')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1279')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1822')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1352')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1334')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO2049')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO3253')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO3251')
							->update('account.invoice',['user_id' => 39]); //Blake

						$updated = $odoo->where('origin', 'SO2169')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO3236')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO850')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO851')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO2313')
							->update('account.invoice',['user_id' => 39]); //Blake

						$updated = $odoo->where('origin', 'SO1954')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1953')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1824')
							->update('account.invoice',['user_id' => 56]); //Eli

						$updated = $odoo->where('origin', 'SO1312')
							->update('account.invoice',['user_id' => 39]); //Blake

						$updated = $odoo->where('origin', 'SO2339')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO2293')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO2170')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO3273')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO3243')
							->update('account.invoice',['user_id' => 11]); //Karl

						$updated = $odoo->where('origin', 'SO2416')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO957')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1795')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1588')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO2103')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO2153')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1627')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1443')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1433')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1408')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1389')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1378')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1605')
							->update('account.invoice',['user_id' => 11]); //Karl

						$updated = $odoo->where('origin', 'SO1651')
							->update('account.invoice',['user_id' => 56]); //Eli
						$updated = $odoo->where('origin', 'SO1642')
							->update('account.invoice',['user_id' => 56]); //Eli

						$updated = $odoo->where('origin', 'SO1796')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1220')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO2094')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO2007')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1975')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1386')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO2089')
							->update('account.invoice',['user_id' => 11]); //Karl
						$updated = $odoo->where('origin', 'SO1460')
							->update('account.invoice',['user_id' => 11]); //Karl

						$updated = $odoo->where('origin', 'SO1791')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO2122')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1048')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO1986')
							->update('account.invoice',['user_id' => 39]); //Blake
						$updated = $odoo->where('origin', 'SO2022')
							->update('account.invoice',['user_id' => 39]); //Blake

						$updated = $odoo->where('origin', 'SO375')
							->update('account.invoice',['user_id' => 14]); //Uriel

						$updated = $odoo->where('origin', 'SO4030')
							->update('account.invoice',['user_id' => 56]); //Eli

						$updated = $odoo->where('origin', 'SO1613')
							->update('account.invoice',['user_id' => 38]); //Bill
						$updated = $odoo->where('origin', 'SO1611')
							->update('account.invoice',['user_id' => 38]); //Bill
			*/

			/*			$order_lines = $odoo
							->where('create_date', '>=', date('Y-m-d', strtotime("-200 days")))
							->limit(1)
							->fields(
								'id',
								'name',
								'price_subtotal',
								'product_uom_qty',
								'price_unit',
								'product_uom',
								'create_date',
								'order_partner_id',
								'product_id',
								'list_price',
								'order_id',
								'purchase_price',
								'salesman_id'
							)
							->get('sale.order.line');

						for ($i = 0; $i < count($order_lines); $i++) {
							$revenue = $order_lines[$i]['price_unit'];
							$cost = $order_lines[$i]['purchase_price'];
							$gross_profit = $revenue - $cost;
							$name_org = $order_lines[$i]['name'];
							$pos = strpos($name_org, ']');
							$name = substr($name_org, $pos + 2);
							$code = substr($name_org, 0, $pos + 2);

							$gross_profit = bcsub($revenue,$cost, 3);
							if ($gross_profit != 0 and $revenue != 0 and $cost != 0) {
								$margin = bcmul('100', bcdiv($gross_profit ,$revenue, 3), 3);
							} else {
								$margin = 0;
							};
							echo bcdiv('-3', '7') . '  test ';
							echo $cost . " <= cost  ";
							echo $revenue  . " <= revenue  ";
							echo $gross_profit  . " <= gross_profit  ";
							echo $margin . "  == Margin  ";

							TestLine::updateOrCreate(
								[
									'ext_id' => $order_lines[$i]['id']
								],
								[
									'ext_id_shipping' => $order_lines[$i]['order_partner_id'][0],
									'order_date' => $order_lines[$i]['create_date'],
									'created_at' => $order_lines[$i]['create_date'],
									'sales_person_id' => $order_lines[$i]['salesman_id'][0],
									'product_id' => $order_lines[$i]['product_id'][0],
									'order_id' => $order_lines[$i]['order_id'][0],
									'invoice_number' => $order_lines[$i]['order_id'][1],
									'name' => $name,
									'code' => $code,
									'quantity' => $order_lines[$i]['product_uom_qty'],
									'cost' => $order_lines[$i]['purchase_price'],
									'ext_id_unit' => $order_lines[$i]['product_uom'][1],
									'unit_price' => $order_lines[$i]['price_unit'],
									'margin' => $margin
								]);

						}
				*/
		}
	}
