<?php

namespace App\Console\Commands;

use App\MetrcNoProduct;
use App\MetrcTag;
use App\MetrcUpdate;
use Carbon\Carbon;
use App\Margin;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Package;
use App\Product;
use App\TmpOdooLot;

/**
 * Post
 *
 * @mixin Eloquent
 */
class metrc_package extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrc:package1';

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
        //  $this->copy_missing_lots();
        // $this->copy_correct_product_names();
        // $this->update_odoo();
        //      dd("done");


        $client = new Client([
            'base_uri' => "https://api-ca.metrc.com",
            'timeout' => 10.0,
        ]);
        $headers = [
            'headers' => ['content-type' => 'application/json', 'Accept' => 'application/json'],
            'auth' => ['4w9TaWS-WuFYilK5r91lmKC2LwuGHr0q0nkvYM3axVo1z1Fo', 'IgLHZR3M-5DjNPsXinfVZJ7PWQfm31CxGD8aFC8dZMbzJP5i'],
        ];

        $license = 'C11-0000224-LIC';

        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::now()->subDays(1)->toDateString();
        for ($j = 0; $j <= 500; $j++) {
            $today = Carbon::today()->subDays($j)->toDateString();
            $yesterday = Carbon::now()->subDays($j + 1)->toDateString();
            //$this->info("yesterday= " . $yesterday . "today= " . $today);
         //   $this->info($j);
            //   dd('xxx');
            $response_string = '&lastModifiedStart=' . $yesterday . 'T06:30:00Z&lastModifiedEnd=' . $today . 'T06:30:00Z';
            //   $this->info($response_string);
            $response = $client->request('GET', '/packages/v1/active?licenseNumber=' . $license . $response_string, $headers);
            $packages = json_decode($response->getBody()->getContents());
                    dd($packages);
            // $this->info(count($packages));

            //     DB::table('metrc_packages');

            for ($i = 0; $i < count($packages); $i++) {
                //     $this->info($packages[$i]->ProductName);

                $product_id = 0;
                $ref = '';
                $product_name = $packages[$i]->ProductName;
                //       $products = Product::whereRaw('LOWER(`name`) LIKE ? ', [trim($product_name) . '%'])->first();

                $products = Product::where('name', 'LIKE', $product_name)->first();
                //    $query->whereRaw('LOWER(`newsTitle`) LIKE ? ',[trim(strtolower($newsTitle)).'%']);
                if ($products) {
                    //    $this->info('Product found: ' . $products->name);

                    $product_id = $products->ext_id;
                    $ref = $products->code;
                } else {
                    //    $this->info($product_name);
                    MetrcNoProduct::updateOrCreate([
                        'product_name' => $product_name
                    ]);
                }

                Package:: updateOrCreate(
                    ['ext_id' => $packages[$i]->Id],
                    [
                        'tag' => $packages[$i]->Label,
                        'item' => $packages[$i]->ProductName,
                        'product_id' => $product_id,
                        'ref' => $ref,
                        'category' => $packages[$i]->ProductCategoryName,
                        'item_strain' => $packages[$i]->ItemStrainName,
                        'quantity' => $packages[$i]->Quantity,
                        'uom' => $packages[$i]->UnitOfMeasureName,
                        'lab_testing' => $packages[$i]->InitialLabTestingState,
                        'date' => $packages[$i]->PackagedDate,
                    ]);

                $user = Package::where('quantity', '<=', 0);
                $user->delete();
            }
            //  $this->update_odoo();
            $this->mark_used_tags();
            sleep(4);
        }
        $this->info(date_format(date_create(), 'Y-m-d H:i:s'));
    }

    public function mark_used_tags()
    {
        $packages = Package::get();
        foreach ($packages as $package) {
            MetrcTag::where('tag', '=', $package->tag)
                ->update(['is_used' => 1, 'used_at' => Carbon::now()]);
        }
    }

    public function update_odoo()
    {
        $odoo = new \Edujugon\Laradoo\Odoo();
        $odoo = $odoo->connect();

        $today = Carbon::today()->toDateString();
        //   $this->info($today);
        $packages = Package::where('product_id', '>', 0)->where('created_at', '=', $today)->get();
        //   $this->info('to update: ' . $packages->count());
        foreach ($packages as $package) {
            /*           $this->info($package->tag);
                        $this->info($package->product_id);
                        $this->info($package->quantity);*/

            $updated = $odoo->create('stock.production.lot', [
                'name' => $package->tag,
                'product_id' => $package->product_id,
                'ref' => $package->ref
            ]);
        }
    }

    public
    function soundex_test()
    {
        $packages = Package::all();
        foreach ($packages as $package) {
            $products = Product::where('name', 'like', ($package->item))->first();
            if ($products) {
                //    $this->info(($package->item . ' = ' . $products->name));
                $package->product_id = $products->ext_id;
                $package->update();
            }
        }
    }

    public function copy_correct_product_names()
    {
        $updates = MetrcUpdate::get();
        foreach ($updates as $update) {
            Package::where('tag', '=', $update->tag)
                ->update(['product_name' => $update->item]);
        }
        $products = Product::get();
        foreach ($products as $product) {
            Package::where('product_name', 'like', $product->name)
                ->update([
                    'product_id' => $product->ext_id,
                    'ref' => $product->code,
                ]);
        }

    }


    public function copy_missing_lots()
    {
        $lots = TmpOdooLot::get();
        foreach ($lots as $lot) {
            Package::updateOrCreate(
                ['tag' => $lot->lot_name],
                [
                    'product_name' => $lot->product_name,
                    'product_id' => $lot->product_id,
                    'ref' => $lot->product_sku,
                ]);
        }

    }

    public
    function correct_product_names($client, $headers, $license)
    {
        $client = new Client([
            'base_uri' => "https://api-ca.metrc.com",
            'timeout' => 2.0,
        ]);
        $headers = [
            'headers' => ['content-type' => 'application/json', 'Accept' => 'application/json'],
            'auth' => ['4w9TaWS-WuFYilK5r91lmKC2LwuGHr0q0nkvYM3axVo1z1Fo', 'IgLHZR3M-5DjNPsXinfVZJ7PWQfm31CxGD8aFC8dZMbzJP5i'],
        ];

        $license = 'C11-0000224-LIC';   //oz

        $names = MetrcUpdate::limit(1)->get();
        foreach ($names as $name) {
            $tag = '1A4060300003C35000010636';
            $product_name = 'Dollar Dose - lozenge - Indica Apple - 5mg';

            $item = ['body' => '[{ "Label": ' . $tag . ', "Item": ' . $product_name . '}]'];
            try {
                $response = $client->post('https://api-ca.metrc.com/packages/v1/change/item?licenseNumber=' . $license, $item);
                $rsp_body = $response->getBody()->getContents();
                if ($rsp_body == '')
                    $message = 'Success';
                else
                    $message = "Other error";

                $packages = json_decode($response->getBody()->getContents());
            } catch (GuzzleException $error) {
                $response = $error->getResponse();
                $response_info = json_decode($response->getBody()->getContents(), true);
                $message = $response_info;
                if (is_array($response_info)) {
                    for ($i = 1; $i <= sizeof($response_info); $i++) {
                        $err_text = $response_info['Message'];
                        $message = "Metrc error: " . $err_text;
                        echo $message . '<br>';
                    }
                }
            }
            $rsp_body = $response->getBody()->getContents();
            if ($rsp_body == '')
                $message = 'Success!';
            else
                $message = "Other error";
        }
    }

}


