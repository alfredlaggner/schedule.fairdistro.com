<?php

namespace App\Console\Commands;

use App\MetrcNoProduct;
use App\Package;
use App\Product;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class metrc_update_package extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrc:update_package';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates table metrcPackages with changed amount';

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
     * @return int
     */
    public function handle()
    {

        $client = new Client([
            'base_uri' => "https://api-ca.metrc.com",
            'timeout' => 10.0,
        ]);
        $headers = [
            'headers' => ['content-type' => 'application/json', 'Accept' => 'application/json'],
            'auth' => ['4w9TaWS-WuFYilK5r91lmKC2LwuGHr0q0nkvYM3axVo1z1Fo', 'IgLHZR3M-5DjNPsXinfVZJ7PWQfm31CxGD8aFC8dZMbzJP5i'],
        ];
        $label = '1A4060300004F58000003156';

        $license = 'C11-0000224-LIC';
        $response = $client->request('GET', '/packages/v1/' . $label . '?licenseNumber=' . $license , $headers);
        $packages = json_decode($response->getBody()->getContents());
//dd($packages);
                Package:: updateOrCreate(
                    ['ext_id' => $packages->Id],
                    [
                        'quantity' => $packages->Quantity,
                        'date' => $packages->PackagedDate,
                    ]);
        return 0;
    }
}
