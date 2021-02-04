<?php

    namespace App\Console\Commands;

    use App\SalesOrder;
    use App\TenNinetyCommission;
    use Carbon\Carbon;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;

    class calcExtraCommission extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'calc:extra_commission';

        /*
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
            $now = Carbon::now();
            $month = $now->month;
            $year = $now->year;
            $queries = SalesOrder::select(DB::raw('
                sales_order,
                salesorders.salesperson_id,
                amount_untaxed,
                order_date,
                sales_person_id as rep_id,
                sales_persons.name as sales_persons_name,
                EXTRACT(YEAR_MONTH FROM salesorders.order_date) as summary_year_month,
                sum(amount_untaxed) as amount,
                count(sales_order) as count_amount
                '))
                ->leftJoin('sales_persons', 'salesorders.salesperson_id', '=', 'sales_persons.sales_person_id')
                ->whereMonth('order_date', '=', 11)
                ->whereYear('order_date', '=', $year)
                ->where('salesperson_id', '!=', 0)
                ->where('state', '=', 'sale')
                ->orderBy('sales_persons.name')
                ->orderBy('salesorders.sales_order', 'desc')
                ->groupBy('summary_year_month')
                ->groupBy('salesorders.salesperson_id')
                ->get();

            //    dd($queries->toArray());
            foreach ($queries as $query) {
                $order_month = substr($query->summary_year_month, 4, 2);
                $order_year = substr($query->summary_year_month, 0, 4);
                TenNinetyCommission::updateOrCreate(
                    [
                        'month' => $order_month,
                        'year' => $order_year,
                        'rep_id' => $query->rep_id
                    ],
                    [
                        'volume' => $query->amount,
                    ]
                );
            }
            $queries = TenNinetyCommission::get();
            foreach ($queries as $query) {
                $id = $query->id;
                $a = $query->volume;
                $b = $query->goal;

                if ($a > 0 and $b > 0) {
                    TenNinetyCommission::find($id)->update(
                        [
                            'percent_diff' => 100 * ($a - $b) / (($a + $b) / 2)
                        ]
                    );
                }
                if ($a > 100000) {
                    TenNinetyCommission::find($id)->update(
                        [
                            'commission' => ($a * 0.5) / 100,
                        ]
                    );
                }
            }
        }
    }
