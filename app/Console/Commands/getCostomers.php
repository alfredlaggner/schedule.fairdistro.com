<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Customer;
use PHPUnit\Framework\Constraint\IsTrue;

class getCostomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull customers from Odoo';

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
        $odoo = new \Edujugon\Laradoo\Odoo();
        $odoo = $odoo->connect();
        /*        $updated = $odoo->where('id', 1289)
                    ->update('res.partner',['name' => '11 Spot']);

                $id = $odoo->create('res.partner',['name' => 'John Odoo']);*/

        $customers = $odoo->fields(
            'id',
            'is_company',
            'display_name',
            'name',
            'street',
            'street2',
            'city',
            'zip',
            'website',
            'phone',
            'email',
            'user_id',
            'sale_order_count',
            'total_due',
            'total_overdue',
            'partner_latitude',
            'partner_longitude',
            'x_studio_field_mu5dT',
            'x_studio_field_WzUo6',
            'x_studio_field_DN9mZ',
            'x_studio_field_api_id',
            'x_studio_field_0tSf6',
            'x_studio_reference_id',
            'x_studio_license_type',
            'x_studio_bcc_business_name',
            'x_studio_territory',
            'x_studio_field_Bwbev'
        )
            ->get('res.partner');
        //   dd($customers);
        for ($i = 0; $i < count($customers); $i++) {

            //   $this->info($customers[$i]['email']);
            if (!$customers[$i]['street2']) {
                $street2 = NULL;
            } else {
                $street2 = $customers[$i]['street2'];
            }

            Customer::updateOrCreate(
                [
                    'ext_id' => $customers[$i]['id']
                ],
                [
                    'ext_id_contact' => $customers[$i]['id'],
                    'name' => preg_replace("/[^a-zA-Z0-9_\s]/", " ", $customers[$i]['display_name']),
                    'street' => $customers[$i]['street'],
                    'street2' => $street2,
                    'city' => $customers[$i]['city'],
                    'zip' => $customers[$i]['zip'],
                    'website' => $customers[$i]['website'],
                    /*                    'latitude' => $customers[$i]['partner_latitude'],
                                        'longitude' => $customers[$i]['partner_longitude'],*/
                    'phone' => $customers[$i]['phone'],
                    'email' => $customers[$i]['email'],
                    'license' => substr($customers[$i]['x_studio_field_mu5dT'], 0, 20),
                    'license_expiration' => $customers[$i]['x_studio_field_0tSf6'],
                    'license2' => substr($customers[$i]['x_studio_field_DN9mZ'], 0, 20),
                    'license_expiration2' => $customers[$i]['x_studio_field_Bwbev'],
                    'api_id' => $customers[$i]['x_studio_field_api_id'],
                    'user_id' => $customers[$i]['user_id'][0],
                    'sales_person' => $customers[$i]['user_id'][1],
                    'sale_order_count' => $customers[$i]['sale_order_count'],
                    'reference_id' => $customers[$i]['x_studio_reference_id'],
                    'license_type' => $customers[$i]['x_studio_license_type'],
                    'bcc_business_name' => $customers[$i]['x_studio_bcc_business_name'],
                    'territory' => $customers[$i]['x_studio_territory'],

                    'total_due' => $customers[$i]['total_due'],
                    'total_overdue' => $customers[$i]['total_overdue'],

                ]);
        }
        $this->info(date_format(date_create(), 'Y-m-d H:i:s'));

    }
}
