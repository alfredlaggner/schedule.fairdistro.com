<?php

namespace App\Console\Commands;

use App\Invoice;
use App\SalesOrder;
use App\TenNinetyCommission;
use App\TenNinetyCommissionSalesOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class calcTenNinetyBiMonthly_org extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:xten_ninety_bi';

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
        $paidCommissionDateFrom = env('PAID_INVOICES_START_DATE', '2019-10-01');
        $timeFrame = [];
        $timeFrame = ['year' => 2020, 'months' => [Carbon::now()->month]];
        $timeFrame = ['year' => $year, 'months' => $month];
        $lastMonth = end($timeFrame['months']);
        $dateTo = substr(new Carbon($timeFrame['year'] . '-' . $lastMonth . '-01'), 0, 10);
        $lastDay = date('t', strtotime($dateTo));
        $dateTo = substr(new Carbon($timeFrame['year'] . '-' . $lastMonth . '-' . $lastDay), 0, 10);
        $dateTo = Carbon::now()->format('Y/m/d');




        $sales = Invoice::select(DB::raw('
                amount_untaxed as amount,
                invoice_date as invoice_date,
                invoices.sales_person_id as rep_id,
                sales_persons.is_ten_ninety as is_rep_id_ten_ninety,
                sales_persons.name as sales_persons_name,
                CONCAT(DATE_FORMAT(invoice_date,\'%Y-%m-\'),IF(DAY(invoice_date)<16,\'01\',\'15\')) + INTERVAL 0 DAY as bi_monthly,
                sum(amount_untaxed) as amount,
                count(sales_order) as count_amount
                '))
            ->leftJoin('sales_persons', 'invoices.sales_person_id', '=', 'sales_persons.sales_person_id')
            ->whereBetween('invoice_date', [$paidCommissionDateFrom, $dateTo])
            ->where('sales_persons.is_ten_ninety', '=', 1)
            ->where('invoices.state', '=', 'paid')
            ->orderBy('sales_persons.name')
            ->groupBy('bi_monthly')
            ->groupBy('invoices.sales_person_id')
            ->get();
        /*                EXTRACT(YEAR_MONTH FROM invoice_date) as summary_year_month,*/

        foreach ($sales as $sale) {
            //  dd($sale);
            $order_month = substr($sale->bi_monthly, 5, 2);
            $order_year = substr($sale->bi_monthly, 0, 4);
            $order_day = substr($sale->bi_monthly, 9, 2);


            if ($order_day == '01') {
                $order_half = 1;
            } else {
                $order_half = 2;
            }
            /*            $this->info($order_month);
                        $this->info($order_year);
                        $this->info($order_day);

                        dd('xxxx');
                        */

            TenNinetyCommission::updateOrCreate(
                ['month' => $order_month,
                    'year' => $order_year,
                    'half' => $order_half,
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
         //   $this->info('ten_ninety = ' . $ten_ninety->is_ten_ninety . 'id = ' . $id . ' commission= ' . $commission);

            TenNinetyCommission::find($id)->update(
                [
                    'percent' => $percent,
                    'commission' => $commission
                ]
            );
        }
        $sales = Invoice::select(DB::raw('*,
                sales_order,
                amount_untaxed as amount,
                invoices.sales_person_id as rep_id,
                sales_persons.is_ten_ninety as is_rep_id_ten_ninety,
                sales_persons.name as sales_persons_name,
                invoices.state as invoices_state
                '))
            ->leftJoin('sales_persons', 'invoices.sales_person_id', '=', 'sales_persons.sales_person_id')
            ->whereBetween('invoice_date', [$paidCommissionDateFrom, $dateTo])
            ->where('sales_persons.is_ten_ninety', '=', 1)
            ->where('invoices.state', '=', 'paid')
            ->get();
//dd($sales ->toarray());
        foreach ($sales as $sale) {
            $order_month = substr($sale->invoice_date, 5, 2);
            $order_year = substr($sale->invoice_date, 0, 4);
            $order_day = substr($sale->invoice_date, 8, 2);
    //        $this->info($order_day);
            if ($order_day < '16') {
                $order_half = 1;
            } else {
                $order_half = 2;
            }
            TenNinetyCommissionSalesOrder::updateOrCreate(
                ['month' => $order_month,
                    'year' => $order_year,
                    'half' => $order_half,
                    'rep_id' => $sale->rep_id,
                    'sales_order_id' => $sale->ext_id,
                ],
                [
                    'customer_id' => $sale->customer_id,
                    'amount_untaxed' => $sale->amount,
                    'invoice_date' => $sale->invoice_date,
                    'sales_order' => $sale->sales_order,
                    'commission' => $sale->amount * 0.06,
                    'is_ten_ninety' => $sale->is_rep_id_ten_ninety ? 1 : 0,
                    'is_paid' =>($sale->state == 'paid') ? 1 : 0
                ]
            );
            //  dd($sale);
            $order_month = substr($sale->bi_monthly, 5, 2);
            $order_year = substr($sale->bi_monthly, 0, 4);
            $order_day = substr($sale->bi_monthly, 9, 2);
        }
        $this->info($dateTo);
        $this->info(date_format(date_create(), 'U = Y-m-d H:i:s'));

    }
}
