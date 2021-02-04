<?php

namespace App\Console\Commands;

use App\EmployeeBonus;
use App\InvoiceLine;
use App\Salesline;
use App\SalesPerson;
use App\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class employee_bonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:bonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate Employee Bonus';

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
        $current_month = (Carbon::now()->month - 1);
        $current_year = (Carbon::now()->year);
        //    $current_month = 6;
        $sps = SalesPerson::where('is_ten_ninety', false)->get();
     //   $sps = SalesPerson::get();
        foreach ($sps as $sp) {
            EmployeeBonus::updateOrCreate(
                [
                    'sales_person_id' => $sp->sales_person_id,
                    'month' => $current_month,
                    'year' => $current_year
                ],
                [
                    'sales_person_name' => $sp->name,
                ]
            );
        }
        $payments = Payment::whereNotNull('invoice_date')
            ->select('*', 'payments.id as payment_id')
            ->leftJoin('sales_persons', 'sales_persons.sales_person_id', '=', 'payments.sales_person_id')
            ->where('sales_persons.is_ten_ninety', false)
            ->where('month_paid', $current_month)
            ->where('year_paid', $current_year)
            ->orderby('payments.id')
            ->get();
        //    dd($payments->toArray());
        foreach ($payments as $payment) {
            if ($payment->invoice_date >= env('BONUS_START')) {
                $bonus = EmployeeBonus::where('month', $payment->month_invoiced)
                    ->where('year', $payment->year_invoiced)
                    ->where('sales_person_id', $payment->sales_person_id)
                    ->first();

                //       dd($bonus);

                if ($bonus) {
                    if ($bonus->bonus > 0) {
                        $bonus_percent = $bonus->bonus;
                    } else {
                        $bonus_percent = $bonus->base_bonus;
                    }
                    if ($payment->amount_due > 1) {
                        $commission = 0;
                    } else {
                        $commission = $bonus_percent * $payment->amount;
                    }

                    $this->info("id= " . $payment->payment_id);
                    $this->info($bonus_percent);
                    $this->info("commission= " . $commission);
                    Payment::where('id', $payment->payment_id)
                        ->update([
                            'comm_percent' => $bonus_percent,
                            'commission' => $commission,
                            'comm_paid_at' => $bonus->comm_paid_at,
                        ]);
                    $this->write_to_odoo($payment, $bonus_percent);
                }
            } elseif ($payment->invoice_date < env('BONUS_START')) {
                if ($payment->sales_person_id != 73) {
                    $sales_line = Salesline::select(DB::raw('*,
                        sum(commission) as sum_commission'))
                        ->where('order_number', $payment->sales_order)
                        ->first();
                    /*                    $this->info($payment->sales_person_id);
                                        $this->info($payment->invoice_date);
                                        $this->info($sales_line->sum_amount);
                                        $this->info($sales_line->sum_amount * 0.06);*/

                    Payment::where('id', $payment->payment_id)
                        ->update([
                            'commission' => $sales_line->sum_commission
                        ]);
                    $this->write_to_odoo($payment, -0.01);

                } else {
                    // Ryan Cullerton
                    $sales_line = Salesline::select(DB::raw('*,
                        sum(amount) as sum_amount'))
                        ->where('order_number', $payment->sales_order)
                        ->first();
                    /*                    $this->info($payment->sales_order);
                                        $this->info($payment->invoice_date);
                                        $this->info($sales_line->sum_amount);
                                        $this->info($sales_line->sum_amount * 0.06);*/
                    Payment::where('id', $payment->payment_id)
                        ->update([
                            'commission' => $sales_line->sum_amount * 0.06
                        ]);
                    $this->write_to_odoo($payment, 0.06);
                }
            }
        }
    }

    public function write_to_odoo($payment, $comm_percent)
    {
        $odoo = new \Edujugon\Laradoo\Odoo();
        $odoo->username('alfred.laggner@gmail.com')
            ->password('jahai999')
            ->db('ozinc-production-elf-test-1367461')
            ->host('https://ozinc-production-elf-test-1367461.dev.odoo.com')
            ->connect();

        // $odoo->connect();

        if ($payment->comm_paid_at = Carbon::now()->format('Y-m-d')) {
            $this->info('percent= ' . $comm_percent);
            $odoo->where('id', $payment->invoice_id)
                ->update('account.invoice', [
                    'x_studio_commission' => $payment->commission,
                    'x_studio_commission_percent' => $comm_percent * 100,
                    'x_studio_commission_paid' => $payment->comm_paid_at,
                ]);
        }
    }
}
