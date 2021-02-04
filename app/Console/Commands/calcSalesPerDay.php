<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\SaleInvoice;
use App\SalesPerDay;

class calcSalesPerDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:salesperday';

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
		$products = SaleInvoice::
		select('*', \DB::raw('SUM(quantity) AS sumqty'))
			->groupBy('order_date')->groupBy('name')
			->get();

		\DB::table('sales_per_days')->delete();

		foreach ($products as $p) {
			$sp = new SalesPerDay;
			$sp->order_id = $p->order_id;
			$sp->order_date = $p->order_date;
			$sp->name = substr($p->name, 9);
			$sp->sku = substr($p->name, 0, 9);
			$sp->quantity = $p->sumqty;
			$sp->cost = $p->cost;
			$sp->margin = $p->margin;
			$sp->unit_price = $p->unit_price;
			$sp->sales_person_id = $p->sales_person_id;
			$sp->code = $p->code;
			$sp->created_at = $p->order_date;
			$sp->save();
		}
    }
}
