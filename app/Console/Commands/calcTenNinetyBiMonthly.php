<?php

namespace App\Console\Commands;

use App\Invoice;
use App\SalesOrder;
use App\Payment;
use App\TenNinetyCommission;
use App\TenNinetyCommissionSalesOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class calcTenNinetyBiMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:1099';

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
        $dateTo = Carbon::now()->format('Y/m/d');

        $payments = Payment::select(DB::raw('*,
                invoices.sales_person_id as rep_id,
                sales_persons.is_ten_ninety as is_rep_id_ten_ninety,
                sales_persons.name as sales_persons_name,
                CONCAT(DATE_FORMAT(payments.payment_date,\'%Y-%m-\'),IF(DAY(payments.payment_date)<16,\'01\',\'15\')) + INTERVAL 0 DAY as bi_monthly,
                sum(amount) as amount,
                count(payments.sales_order) as count_amount
                '))
            ->leftJoin('invoices', 'invoices.ext_id', '=', 'payments.invoice_id')
            ->leftJoin('sales_persons', 'invoices.sales_person_id', '=', 'sales_persons.sales_person_id')
            ->whereBetween('payments.payment_date', [$paidCommissionDateFrom, $dateTo])
    //        ->where('sales_persons.is_ten_ninety', '=', 1)
            ->orderBy('sales_persons.name')
            ->groupBy('bi_monthly')
            ->groupBy('invoices.sales_person_id')
            ->get();

//dd($payments);


        foreach ($payments as $payment) {
            //  dd($payment);
            $order_month = substr($payment->bi_monthly, 5, 2);
            $order_year = substr($payment->bi_monthly, 0, 4);
            $order_day = substr($payment->bi_monthly, 9, 2);


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
                    'rep_id' => $payment->rep_id,
                ],
                [
                    'volume' => $payment->amount,
                    'rep' => $payment->sales_persons_name,
                    'is_ten_ninety' => $payment->is_rep_id_ten_ninety ? 1 : 0
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
        //    dd($paidCommissionDateFrom);
        $payments = Payment::select(DB::raw('*,
                invoices.sales_order,
                payments.sales_person_id as rep_id,
                sales_persons.is_ten_ninety as is_rep_id_ten_ninety,
                sales_persons.name as sales_persons_name,
                invoices.state as invoices_state,
                invoices.invoice_date as invoices_invoice_date
                '))
            ->leftJoin('invoices', 'invoices.ext_id', '=', 'payments.invoice_id')
            ->leftJoin('sales_persons', 'payments.sales_person_id', '=', 'sales_persons.sales_person_id')
            ->whereBetween('payments.payment_date', [$paidCommissionDateFrom, $dateTo])
               ->where('sales_persons.is_ten_ninety', '=', 1)
            ->orderBy('payments.payment_date')
            ->get();
        //     $this->info($paidCommissionDateFrom) ;
        //     echo $this->info($dateTo);
        //    dd($payments->count());
//$this->info($payments ->count());
        foreach ($payments as $payment) {
            $order_month = substr($payment->invoice_date, 5, 2);
            $order_year = substr($payment->invoice_date, 0, 4);
            $order_day = substr($payment->invoice_date, 8, 2);
                   $this->info($order_day);
            if ($order_day < '16') {
                $order_half = 1;
            } else {
                $order_half = 2;
            }
            TenNinetyCommissionSalesOrder::updateOrCreate(
                ['month' => $order_month,
                    'year' => $order_year,
                    'half' => $order_half,
                    'rep_id' => $payment->rep_id,
                    'sales_order_id' => $payment->ext_id,
                ],
                [
                    'customer_id' => $payment->customer_id,
                    'customer_name' => $payment->customer_name,
                    'amount_untaxed' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'invoice_date' => $payment->invoices_invoice_date,
                    'sales_order' => $payment->sales_order,
                    'commission' => $payment->amount * 0.06,
                    'is_ten_ninety' => $payment->is_rep_id_ten_ninety ? 1 : 0,
                    'rep' => $payment->sales_persons_name,
                ]
            );
            //  dd($payment);
            $order_month = substr($payment->bi_monthly, 5, 2);
            $order_year = substr($payment->bi_monthly, 0, 4);
            $order_day = substr($payment->bi_monthly, 9, 2);
        }
        $this->info("all");
        //  $this->info(date_format(date_create(), 'U = Y-m-d H:i:s'));

    }
}
