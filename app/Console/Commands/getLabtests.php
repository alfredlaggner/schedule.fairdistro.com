<?php

namespace App\Console\Commands;

use App\MetrcItem;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class getLabtests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrc:labtests';

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
        $client = new Client([
            'base_uri' => "https://api-ca.metrc.com",
            'timeout' => 2.0,
        ]);
        $headers = [
            'headers' => ['content-type' => 'application/json', 'Accept' => 'application/json'],
            'auth' => ['4w9TaWS-WuFYilK5r91lmKC2LwuGHr0q0nkvYM3axVo1z1Fo', 'IgLHZR3M-5DjNPsXinfVZJ7PWQfm31CxGD8aFC8dZMbzJP5i'],
        ];

        $license = 'C11-0000224-LIC';

        $response = $client->request('GET', '/labtests/v1/results?packageId=4691510&licenseNumber=' . $license, $headers);
        $items = json_decode($response->getBody()->getContents());


        dd(($items));
    }
}
