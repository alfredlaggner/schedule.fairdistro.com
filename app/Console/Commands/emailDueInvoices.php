<?php

namespace App\Console\Commands;

use App\Agedthreshold;
use App\DriverLog;
use App\Invoice;
use App\User;
use Illuminate\Console\Command;
use App\Notifications\InvoiceDue;

class emailDueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:due';

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

    public function handle()
    {
        $invoices = Invoice::where('type', 'out_invoice')->get();
        foreach ($invoices as $invoice) {
            $threshold = Agedthreshold::where('age', $invoice->age)->first();
            if ($threshold) {
                $this->info("send email to " . $invoice->sales_person_name . ' for ' . $invoice->customer_name . ". AGE: " . $threshold->age . " days!");
                $user = User::where('sales_person_id', $invoice->sales_person_id)->first();
                $user->notify(new InvoiceDue($invoice, $threshold));
                sleep(5);
            }
        }
    }
}
