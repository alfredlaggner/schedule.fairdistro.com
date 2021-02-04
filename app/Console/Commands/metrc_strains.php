<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MetrcStrain;
use Carbon\Carbon;
use GuzzleHttp\Client;

class metrc_strains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrc:strains';

    /**
     * The console command description.
     *
     * @var string
     *
     */
    protected $description = 'Get all strains from Metrc';

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
            'timeout' => 2.0,
        ]);
        $headers = [
            'headers' => ['content-type' => 'application/json', 'Accept' => 'application/json'],
            'auth' => ['4w9TaWS-WuFYilK5r91lmKC2LwuGHr0q0nkvYM3axVo1z1Fo', 'IgLHZR3M-5DjNPsXinfVZJ7PWQfm31CxGD8aFC8dZMbzJP5i'],
        ];

        $license = 'C11-0000224-LIC';
        $new_package = '1A4060300003C35000019794';

        $response = $client->request('GET', '/strains/v1/active?licenseNumber=' . $license, $headers);
        $strains = json_decode($response->getBody()->getContents());


        //  dd(($strains));

        MetrcStrain::truncate();
        for ($i = 0; $i < count($strains); $i++) {
            //   $this->info($strains[$i]->Name);

            MetrcStrain:: updateOrCreate(
                [
                    'metrc_id' => $strains[$i]->Id,
                    'name' => $strains[$i]->Name,
                    'testing_status' => $strains[$i]->TestingStatus,
                    'thc_level' => $strains[$i]->ThcLevel,
                    'cbd_level' => $strains[$i]->CbdLevel,
                    'indica_percentage' => $strains[$i]->IndicaPercentage,
                    'sativa_percentage'  => $strains[$i]->SativaPercentage,
                    'is_used' => $strains[$i]->IsUsed,
                ]);
        }
        $this->info(date_format(date_create(), 'Y-m-d H:i:s'));

        return 0;
    }
}
