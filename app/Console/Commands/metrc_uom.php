<?php

namespace App\Console\Commands;

use App\MetrcItem;
use App\MetrcUom;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class metrc_uom extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrc:uom';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all Units of Measure';

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
        $client = new Client([
            'base_uri' => "https://api-ca.metrc.com",
            'timeout' => 2.0,
        ]);
        $headers = [
            'headers' => ['content-type' => 'application/json', 'Accept' => 'application/json'],
            'auth' => ['4w9TaWS-WuFYilK5r91lmKC2LwuGHr0q0nkvYM3axVo1z1Fo', 'IgLHZR3M-5DjNPsXinfVZJ7PWQfm31CxGD8aFC8dZMbzJP5i'],
        ];

        $license = 'C11-0000224-LIC';
        $response = $client->request('GET', '/unitsofmeasure/v1/active?licenseNumber=' . $license, $headers);
        $items = json_decode($response->getBody()->getContents());

        for ($i = 0; $i < count($items); $i++) {
            //     $this->info($packages[$i]->ProductName);

            MetrcUom:: updateOrCreate(
                [  'name' => $items[$i]->Name],
                [
                    'QuantityType' => $items[$i]->QuantityType,
                    'Name' => $items[$i]->Name,
                    'Abbreviation' => $items[$i]->Abbreviation,
                ]);
        }
    }
}
