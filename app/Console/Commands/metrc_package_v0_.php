<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Margin;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Package;
use App\Product;

class metrc_packageV0 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrc:package';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve all metrc packages';

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
        $odoo = new \Edujugon\Laradoo\Odoo();
        $odoo = $odoo->connect();
        //    for ($j = 1; $j <= 29; $j++) {
        $client = new Client([
            'base_uri' => "https://api-ca.metrc.com",
            'timeout' => 2.0,
        ]);
        $headers = [
            'headers' => ['content-type' => 'application/json', 'Accept' => 'application/json'],
            'auth' => ['4w9TaWS-WuFYilK5r91lmKC2LwuGHr0q0nkvYM3axVo1z1Fo', 'IgLHZR3M-5DjNPsXinfVZJ7PWQfm31CxGD8aFC8dZMbzJP5i'],
        ];
        $license = 'C11-0000224-LIC';
        $j = Carbon::now()->day;
        $yesterday = date("Y-m-d",strtotime("- 1 day"));
        $today = date('Y-m-d');
        if ($j < 10) {
            $day = '0' . $j;
            $k = $j + 1;
            $day2 = '0' . $k;
        } else {
            $day = $j;
            $day2 = $j + 1;
        }
        $response_string = '&lastModifiedStart='. $yesterday.  'T06:30:00Z&lastModifiedEnd=' . $today . 'T06:30:00Z';
        $this->info($response_string);
        $response = $client->request('GET', '/packages/v1/active?licenseNumber=' . $license . $response_string, $headers);
        $packages = json_decode($response->getBody()->getContents());
        $this->info(count($packages));

   //     DB::table('metrc_packages');

        for ($i = 0; $i < count($packages); $i++) {
            $this->info( $packages[$i]->Label);

            $product_id = 0;
            $ref = '';
            $products = Product::where('name', 'like', $packages[$i]->ProductName)->first();
            if ($products) {
                $product_id = $products->ext_id;
                $ref = $products->code;
            }
            Package:: updateOrCreate(
                ['ext_id' => $packages[$i]->Id],
                [
                    'ext_id' => $packages[$i]->Id,
                    'tag' => $packages[$i]->Label,
                    'item' => $packages[$i]->ProductName,
                    'product_id' => $product_id,
                    'ref' => $ref,
                    'category' => $packages[$i]->ProductCategoryName,
                    'item_strain' => $packages[$i]->ItemStrainName,
                    'quantity' => $packages[$i]->Quantity,
                    'lab_testing' => $packages[$i]->InitialLabTestingState,
                    'date' => $packages[$i]->PackagedDate,
                ]);
        }
        $today = Carbon::today()->toDateString();
        $this->info($today);
        $packages = Package::where('product_id', '>', 0)->where('created_at', '=', $today)->get();
$this->info('to update: ' . $packages->count());
        foreach ($packages as $package) {
            $this->info($package->tag);
            $this->info($package->product_id);
            $this->info($package->quantity);

            $updated = $odoo->create('stock.production.lot', [
                'name' => $package->tag,
                'product_id' => $package->product_id,
                'ref' => $package->ref
            ]);
        }
    }
}
