<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\BccRetailer;
use App\CustomerWithBcc;

class newBbcRetail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:newretail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Determine which retail businesses  are not yet in Odoo ';

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

        $bccRetailers = BccRetailer::all();
        $customers = CustomerWithBcc::all();
        foreach ($customers as $customer) {
            $bccRetailers = BccRetailer::
            where('business_name', 'like', '%' . $customer->name . '%')->
            orWhere('dba', 'like', '%' . $customer->name . '%')->limit(1)->get();
            foreach ($bccRetailers as $bccRetailer) {
                if ($bccRetailer->count()) {
                    echo $bccRetailer->business_name . "found<br>";
                    $customer->update([
                        'name' => ucwords(strtolower($bccRetailer->business_name)),
                        'license' => $bccRetailer->license,
                        'is_bcc' => true,
                        'dba' => $bccRetailer->dba,
                    ]);
                    $bccRetailer->update([
                        'is_odoo' => 1,
                        'zip' => $customer->zip,
                    ]);
                } else {
                    echo $bccRetailer->business_name . "not found<br>";
                }
            }
        }
    }
}
