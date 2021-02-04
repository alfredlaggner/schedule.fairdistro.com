<?php

namespace App\Console\Commands;

use App\Invoice;
use App\SalesOrder;
use App\TenNinetyCommission;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class calcTenNinety extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:ten_ninety';

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
        $month = [$now->month];
        $year = $now->year;
        $paidCommissionDateFrom = env('PAID_INVOICES_START_DATE', '2020-01-01');
        $timeFrame = [];
        $timeFrame = ['year' => 2020, 'months' => [Carbon::now()->month]];
        $timeFrame = ['year' => $year, 'months' => $month];
        $lastMonth = end($timeFrame['months']);
        $dateTo = substr(new Carbon($timeFrame['year'] . '-' . $lastMonth . '-01'), 0, 10);
        $lastDay = date('t', strtotime($dateTo));
        $dateTo = substr(new Carbon($timeFrame['year'] . '-' . $lastMonth . '-' . $lastDay), 0, 10);


        /*   just for once     */
      //  $dateTo = '2019-12-25';


        $sales = SalesOrder::select(DB::raw('
                sales_order,
                salesorders.salesperson_id,
                amount_untaxed,
                order_date,
                sales_person_id as rep_id,
                sales_persons.is_ten_ninety as is_rep_id_ten_ninety,
                sales_persons.name as sales_persons_name,
                EXTRACT(YEAR_MONTH FROM salesorders.order_date) as summary_year_month,
                sum(amount_untaxed) as amount,
                count(sales_order) as count_amount
                '))
            ->join('sales_persons', 'salesorders.salesperson_id', '=', 'sales_persons.sales_person_id')
            ->whereBetween('salesorders.order_date', [$paidCommissionDateFrom, $dateTo])
            ->where('salesperson_id', '>', 0)
            ->where('state', '=', 'sale')
            ->orderBy('sales_persons.name')
            ->groupBy('summary_year_month')
            ->groupBy('salesorders.salesperson_id')
            ->get();

           dd($sales->toArray());
        foreach ($sales as $sale) {
            //  dd($sale);
            $order_month = substr($sale->summary_year_month, 4, 2);
            $order_year = substr($sale->summary_year_month, 0, 4);
            TenNinetyCommission::updateOrCreate(
                ['month' => $order_month,
                    'year' => $order_year,
                    'rep_id' => $sale->rep_id,
                ],
                [
                    'volume' => $sale->amount,
                    'is_ten_ninety' => $sale->is_rep_id_ten_ninety ? 1 : 0
                ]
            );
        }
        $ten_ninetys = TenNinetyCommission::all();
        //  dd($ten_ninetys);
        foreach ($ten_ninetys as $ten_ninety) {
            $id = $ten_ninety->id;
            $volume = $ten_ninety->volume;
            $goal = $ten_ninety->goal;
            if ($ten_ninety->is_ten_ninety == 0) {
                $percent = 0.50;
            } else {
                if ($volume < 150000) {
                    $percent = 6.00;
                } else {
                    $percent = 7.00;
                }
            }
            if ($ten_ninety->is_ten_ninety) {
                $commission = ($volume * $percent) / 100;
            } else {
                if ($volume > $goal) {
                    $commission = ($volume * $percent) / 100;
                } else {
                    $commission = 0.00;
                }
            }

            TenNinetyCommission::find($id)->update(
                [
                    'percent' => $percent,
                    'commission' => $commission
                ]
            );
        }

        $ten_ninetys = TenNinetyCommission::get();
        foreach ($ten_ninetys as $ten_ninety) {
            $id = $ten_ninety->id;
            $volume = $ten_ninety->volume_collected;
            $percent = $ten_ninety->percent;
            if ($ten_ninety->is_ten_ninety) {
                $commission = ($volume * $percent) / 100;
                TenNinetyCommission::find($id)->update(
                    [
                        'commission' => $commission
                    ]
                );
            };
            //       $this->info($volume . ' * ' . $percent);

        }
        $this->info(date_format(date_create(), 'U = Y-m-d H:i:s'));

    }

    function keep_deleted_xxx()
    {
        $invoices = Invoice::select(DB::raw('
                sales_order,
                invoices.sales_person_id,
                amount_untaxed,
                payment_date,
                invoices.sales_person_id as rep_id,
                sales_persons.is_ten_ninety as is_rep_id_ten_ninety,
                sales_persons.name as sales_persons_name,
                EXTRACT(YEAR_MONTH FROM invoices.payment_date) as summary_year_month,
                sum(amount_untaxed) as amount,
                count(sales_order) as count_amount
                '))
            ->leftJoin('sales_persons', 'invoices.sales_person_id', '=', 'sales_persons.sales_person_id')
            ->whereMonth('payment_date', '=', 10)
            ->whereYear('payment_date', '=', $year)
            ->where('invoices.sales_person_id', '>', 0)
            //      ->where('state', '=', 'paid')
            ->orderBy('sales_persons.name')
            ->groupBy('summary_year_month')
            ->groupBy('invoices.sales_person_id')
            ->get();

        //    dd($invoices->toArray());
        foreach ($invoices as $invoice) {
            //  dd($invoice);
            //       $this->info($invoice->amount);
            $payment_month = substr($invoice->summary_year_month, 4, 2);
            $payment_year = substr($invoice->summary_year_month, 0, 4);
            TenNinetyCommission::updateOrCreate(
                ['month' => $payment_month,
                    'year' => $payment_year,
                    'rep_id' => $invoice->rep_id,
                ],
                [
                    'volume_collected' => $invoice->amount,
                ]
            );
        }
    }
}
