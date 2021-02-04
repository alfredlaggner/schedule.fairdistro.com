<?php

    namespace App\Console\Commands;

    use App\AgedReceivablesTotal;
    use App\Invoice;
    use App\AgedReceivables;
    use App\InvoiceAmtCollect;
    use App\InvoiceLine;
    use App\SaleInvoice;
    use Carbon\Carbon;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;

    class calcAgedReceivables extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'calc:ar';

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Create Aged Invoices Table';

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

            $customers = Invoice::select(DB::raw("
							customer_id,
							customer_name,
							sales_person_id,
							sum(residual) as sum_residual
							"
            ))
     //           ->where('customer_id',1293)
                ->groupBy('customer_id')
                ->orderBy('customer_name')
                ->get();

            $data = [];
            DB::table('aged_receivables')->truncate();
            DB::table('aged_receivables_totals')->truncate();

            foreach ($customers as $customer) {
                $invoices = Invoice::select(DB::raw("*,
							name,
							invoices.id as invoice_id,
							sales_person_id,
							sales_person_name,
							age,
							invoice_date,
							residual,
							customer_name,
							CASE
								WHEN age <=0  THEN 'not today'
								WHEN age BETWEEN 1 and 7 THEN '1 - 7'
								WHEN age BETWEEN 8 and 14 THEN '8 - 14'
								WHEN age BETWEEN 15 and 30 THEN '15 - 30'
								WHEN age BETWEEN 31 and 60 THEN '31 - 60'
								WHEN age BETWEEN 61 and 90 THEN '61 - 90'
								WHEN age BETWEEN 91 and 120 THEN '91 - 120'
								WHEN age > 120 THEN 'Over 120'
								WHEN age IS NULL THEN 'Not Filled In (NULL)'
							END as age_range,
							CASE
								WHEN age <= 0 THEN 0
								WHEN age BETWEEN 1 and 7 THEN 1
								WHEN age BETWEEN 8 and 14 THEN 2
								WHEN age BETWEEN 15 and 30 THEN 3
								WHEN age BETWEEN 31 and 60 THEN 4
								WHEN age BETWEEN 61 and 90 THEN 5
								WHEN age BETWEEN 91 and 120 THEN 6
								WHEN age >= 120 THEN 7
								WHEN age IS NULL THEN 8
							END as age_rank
									"))
                    ->orderBy('customer_name')
                    ->orderBy('age_rank')
                    ->where('customer_id', '=', $customer->customer_id)
                    ->where('type', '=', 'out_invoice')
                    ->where('residual', '!=', 0)
                    ->get();

//dd($invoices->toarray());
                foreach ($invoices as $invoice) {
                    $rank = [];
                    for ($i = 0; $i < 9; $i++) {
                        if ($i == $invoice->age_rank) {
                            array_push($rank, $invoice->residual);
                        } else {
                            array_push($rank, '');
                        }
                    }
                    AgedReceivables::updateOrCreate(
                        [
                            'sales_order' => $invoice->sales_order
                        ],
                        [
                            'rep' => $invoice->sales_person_name,
                            'rep_id' => $invoice->sales_person_id,
                            'customer' => $invoice->customer_name,
                            'customer_id' => $invoice->customer_id,
                            'sales_order' => $invoice->sales_order,
                            'salesorder_id' => $invoice->ext_id,
                            'residual' => $invoice->residual,
                            'range0' => $rank[0],
                            'range1' => $rank[1],
                            'range2' => $rank[2],
                            'range3' => $rank[3],
                            'range4' => $rank[4],
                            'range5' => $rank[5],
                            'range6' => $rank[6],
                            'range7' => $rank[7],
                            'range8' => $rank[8]
                        ]);
                }
            }


            /*			calculate totals		*/

            $totals = AgedReceivables::select(DB::raw(
                "*,
                sum(range0) as sum_range0,
                sum(range1) as sum_range1,
                sum(range2) as sum_range2,
                sum(range3) as sum_range3,
                sum(range4) as sum_range4,
                sum(range5) as sum_range5,
                sum(range6) as sum_range6,
                sum(range7) as sum_range7,
                sum(range8) as sum_range8,
                sum(residual) as sum_residual
					"))
                ->groupBy('customer_id')
                ->get();
            //		dd($totals->toArray());
            foreach ($totals as $total) {
                AgedReceivablesTotal::updateOrCreate(
                    ['customer_id' => $total->customer_id],
                    [
                        'rep_id' => $total->rep_id,
                        'customer' => $total->customer,
                        'rep' => $total->rep,
                        'residual' => $total->sum_residual,
                        'range0' => $total->sum_range0,
                        'range1' => $total->sum_range1,
                        'range2' => $total->sum_range2,
                        'range3' => $total->sum_range3,
                        'range4' => $total->sum_range4,
                        'range5' => $total->sum_range5,
                        'range6' => $total->sum_range6,
                        'range7' => $total->sum_range7,
                        'range8' => $total->sum_range8,
                        'customer_total' =>
                            $total->sum_range0 +
                            $total->sum_range1 +
                            $total->sum_range2 +
                            $total->sum_range3 +
                            $total->sum_range4 +
                            $total->sum_range5 +
                            $total->sum_range6 +
                            $total->sum_range7 +
                            $total->sum_range8
                    ]);

            }
            /*calculate collected receivables*/

            $now = Carbon::now();
            $year = $now->year;
            $week = $now->weekOfYear;

            $year_week = $year . $week;
            $year_week = (int)($year_week);


        //    echo $year_week;

            $agedReceivablesTotals = AgedReceivablesTotal::all();
            foreach ($agedReceivablesTotals as $agedReceivablesTotal) {
                $amt_collect = InvoiceAmtCollect::where('customer_id', $agedReceivablesTotal->customer_id)
                    ->where('week', '>=', $year_week)
                    ->orderby('week', 'desc')
                    ->first();
                if ($amt_collect) {

                    $amount_collected = $amt_collect->saved_residual - $agedReceivablesTotal->customer_total;

                    InvoiceAmtCollect::find($amt_collect->id)
                        ->update([
                            'amt_collected' => $amount_collected,
                        ]);
                }
            }
/*            \Artisan::call('scout:flush App\\\AgedReceivables');
            \Artisan::call('scout:import App\\\AgedReceivables');
            \Artisan::call('scout:flush App\\\AgedReceivablesTotal');
            \Artisan::call('scout:import App\\\AgedReceivablesTotal');*/

            $this->info(date_format(date_create(), 'Y-m-d H:i:s'));
        }
    }
