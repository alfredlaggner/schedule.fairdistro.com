<?php

namespace App\Console\Commands;

use App\Package;
use App\SaleInvoice;
use Illuminate\Console\Command;

class metrc_make_trans_package extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrc:trans_package {arg1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes metrc transfer packages';

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
        $sales_order = $this->argument('arg1');
        $saleslines = SaleInvoice::where('invoice_number', '=', $sales_order)
            ->where('uid', '!=', '')
            ->get();

        foreach ($saleslines as $sales_line) {
            $uid = $sales_line->uid;
            $quantity = $sales_line->quantity;
            $unit_price = $sales_line->unit_price;
            $wholesalepreis = $quantity * $unit_price;

            $package = Package::where('tag', '=', $uid)->first();

            $metrc_sub_text = [
                "Packages" => [
                    "PackageLabel" => $uid,
                    "WholesalePrice" => $wholesalepreis,
                ]];

            /*            $metrc_sub_text =
                            '"Packages": [{' .
                            '"PackageLabel":' . '"' . $uid . ',' . '"'.
                            '"WholesalePrice":' . $wholesalepreis . ',' .
                            '},' .
                            ']';*/
            $this->info(json_encode($metrc_sub_text, JSON_PRETTY_PRINT));

        }

    }
}
