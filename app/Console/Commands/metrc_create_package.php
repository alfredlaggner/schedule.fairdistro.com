<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

class metrc_create_package extends Command
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
        {
            $client = new Client([
                'base_uri' => "https://api-ca.metrc.com",
                'timeout' => 5.0,
                'headers' => ['content-type' => 'application/json', 'Accept' => 'application/json'],
                'auth' => ['4w9TaWS-WuFYilK5r91lmKC2LwuGHr0q0nkvYM3axVo1z1Fo', 'IgLHZR3M-5DjNPsXinfVZJ7PWQfm31CxGD8aFC8dZMbzJP5i'],
            ]);
            $headers = [
                'headers' => ['content-type' => 'application/json', 'Accept' => 'application/json'],
                'auth' => ['4w9TaWS-WuFYilK5r91lmKC2LwuGHr0q0nkvYM3axVo1z1Fo', 'IgLHZR3M-5DjNPsXinfVZJ7PWQfm31CxGD8aFC8dZMbzJP5i'],
            ];

            $license = 'C11-0000224-LIC';   //oz

            $source_package = '1A4060300003C35000019460';
            $new_package = '1A4060300003C35000012185';
            $quantity = 112.0;
            $strain = "New Mexico Badlands";
            $item = "Royal Tree Hybrid Flower New Mexico Badlands 3.5g";
            $packages_create = [
                'Tag' => $new_package,
                "Quantity" => 1.0,
                "UnitOfMeasure" => "Grams",
                "ActualDate" => "2020-05-28",
                "Note"=> "This is a test package, created by Alfred.",
                "Item" => $item,
                "Strain" => $strain,
                "IsProductionBatch" => false,
                "ProductionBatchNumber" => null,
                "IsDonation" => false,
                "ProductRequiresRemediation" => false,
                "UseSameItem" => false,
                "Ingredients" =>
                    [[
                        "Package" => $source_package,
                        "Quantity" => 108.0,
                        "UnitOfMeasure" => "Grams"
                    ]]];
            $data11 = json_encode($packages_create, JSON_PRETTY_PRINT);

            // dd($packages_create);
            $item_create = [
                "ItemCategory" => "Flower",
                "Name" => "Alfred New High Up",
                "UnitOfMeasure" => "Ounces",
                "Strain" => "Granimals"
            ];

            $data1 = json_encode($item_create, JSON_PRETTY_PRINT);
            //    dd($data1);
            $data2 = "[" . $data11 . "]";
            $data = ['body' => $data2];
//dd($data);

            try {
                $response = $client->post('https://api-ca.metrc.com/packages/v1/create?licenseNumber=' . $license, $data);
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
