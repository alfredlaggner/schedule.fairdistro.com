<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Invoice;

class test_aged extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:aged';

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

    $invoices = Invoice::select(DB::raw("	*,
    CASE
        WHEN age < 20 THEN 'Under 20'
        WHEN age BETWEEN 20 and 29 THEN '20 - 29'
        WHEN age BETWEEN 30 and 39 THEN '30 - 39'
        WHEN age BETWEEN 40 and 49 THEN '40 - 49'
        WHEN age BETWEEN 50 and 59 THEN '50 - 59'
        WHEN age BETWEEN 60 and 69 THEN '60 - 69'
        WHEN age BETWEEN 70 and 79 THEN '70 - 79'
        WHEN age >= 80 THEN 'Over 80'
        WHEN age IS NULL THEN 'Not Filled In (NULL)'
    END as age_range"))
   ->groupBy('age_range')
		->where('state','=','open')
		->get();

    dd($invoices->count());
    }
}
