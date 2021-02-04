<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\BccRetailer;
use App\CaliforniaZipcode;
class assignZip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:assignzip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign salesperson to zip code and city.';

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

        CaliforniaZipcode::where('county', '=', 'Del Norte')
            ->orWhere('county', '=', 'Siskiyou')
            ->orWhere('county', '=', 'Modoc')
            ->orWhere('county', '=', 'Humboldt')
            ->orWhere('county', '=', 'Trinity')
            ->orWhere('county', '=', 'Shasta')
            ->orWhere('county', '=', 'Lassen')
            ->orWhere('county', '=', 'Mendocino')
            ->orWhere('county', '=', 'Tehama')
            ->orWhere('county', '=', 'Plumas')
            ->orWhere('county', '=', 'Lake')
            ->orWhere('county', '=', 'Glenn')
            ->orWhere('county', '=', 'Butte')
            ->orWhere('county', '=', 'Sonoma')
            ->orWhere('county', '=', 'Napa')
            ->orWhere('county', '=', 'Yolo')
            ->orWhere('county', '=', 'Sutter')
            ->orWhere('county', '=', 'Yuba')
            ->orWhere('county', '=', 'Nevada')
            ->orWhere('county', '=', 'Sierra')
            ->orWhere('county', '=', 'Solano')
            ->orWhere('county', '=', 'Sacramento')
            ->orWhere('county', '=', 'El Dorado')
            ->orWhere('county', '=', 'Amador')
            ->orWhere('county', '=', 'Alpine')
            ->update(['sales_person_id' => 38]); //Bill Sutterfield

        CaliforniaZipcode::where('county', '=', 'Contra Costa')
            ->orWhere('county', '=', 'San Joaquin')
            ->orWhere('county', '=', 'Calaveras')
            ->orWhere('county', '=', 'Alameda')
            ->orWhere('county', '=', 'Stanislaus')
            ->orWhere('county', '=', 'Tuolumne')
            ->orWhere('county', '=', 'Lassen')
            ->orWhere('county', '=', 'Mono')
            ->orWhere('county', '=', 'Merced')
            ->orWhere('county', '=', 'Mariposa')
            ->orWhere('county', '=', 'Lake')
            ->orWhere('county', '=', 'Colusa')
            ->orWhere('county', '=', 'Madera')
            ->orWhere('county', '=', 'Fresno')
            ->orWhere('county', '=', 'Kings')
            ->orWhere('county', '=', 'Napa')
            ->orWhere('county', '=', 'Tulare')
            ->orWhere('county', '=', 'Sutter')
            ->orWhere('county', '=', 'Inyo')
            ->orWhere('county', '=', 'Kern')
            ->orWhere('county', '=', 'Placer')
            ->update(['sales_person_id' => 14]); // Uriel

        CaliforniaZipcode::where('county', '=', 'Marin')
            ->orWhere('county', '=', 'City and County of San Francisco')
            ->orWhere('county', '=', 'San Mateo')
            ->update(['sales_person_id' => 26]); // Eddie Sarano

        CaliforniaZipcode::where('county', '=', 'Santa Cruz')
            ->orWhere('county', '=', 'Santa Clara')
            ->orWhere('county', '=', 'Monterey')
            ->orWhere('county', '=', 'San Benito')
            ->update(['sales_person_id' => 11]); //Karl

        CaliforniaZipcode::where('county', '=', 'San Luis Obispo')
            ->orWhere('county', '=', 'Santa Barbara')
            ->orWhere('county', '=', 'Ventura')
            ->update(['sales_person_id' => 9]); // Wade

        CaliforniaZipcode::where('county', '=', 'Los Angeles')
            ->update(['sales_person_id' => 9]); // John Hodges

        CaliforniaZipcode::where('county', '=', 'San Bernardino')
            ->orWhere('county', '=', 'Riverside')
            ->orWhere('county', '=', 'Orange')
            ->update(['sales_person_id' => 35]); // Matt Gutierrez

        CaliforniaZipcode::where('county', '=', 'San Diego')
            ->orWhere('county', '=', 'Imperial')
            ->update(['sales_person_id' => 9]); // Tanner


        $bbc_retailers = BccRetailer::whereHas('ca_zip_code')->get();
        foreach ($bbc_retailers as $bbc_retailer) {
            $bbc_retailer->sales_person_id = $bbc_retailer->ca_zip_code->sales_person_id;
            $bbc_retailer->save();
        }
        $bbc_retailers = BccRetailer::whereHas('ca_city')->get();
        foreach ($bbc_retailers as $bbc_retailer) {
            $bbc_retailer->sales_person_id = $bbc_retailer->ca_city->sales_person_id;
            $bbc_retailer->sales_person_name = $bbc_retailer->sales_person->name;
            $bbc_retailer->save();
        }
    }
}
