<?php

namespace App\Console\Commands;

use App\AgedReceivablesTotal;
use App\InvoiceAmtCollect;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ar_set_values_to_collect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:ar_to_collect';

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
        $now = Carbon::now();
        $year = $now->year;
        $week = $now->weekOfYear;

        $year_week = $year . $week;
        $year_week = (int)($year_week);
        echo $year_week;
        $agedReceivablesTotals = AgedReceivablesTotal::all();

        foreach ($agedReceivablesTotals as $ar) {

            InvoiceAmtCollect::updateOrCreate(
                [
                    'week' => $year_week,
                    'customer_id' => $ar->customer_id,],
                [
                    'user_id' => $ar->rep_id,
                    'amount_to_collect' => $ar->customer_total,
                    'customer_name' => $ar->customer,
                    'saved_residual' => $ar->customer_total,
                ]);
        }
    }
}
