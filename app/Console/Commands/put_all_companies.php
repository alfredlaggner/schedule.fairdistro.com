<?php

namespace App\Console\Commands;

use App\CustomerImport;
use App\CustomerCreate;
use App\LicenseNumber;
use Illuminate\Console\Command;

class put_all_companies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'put:customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upload all customers to Odoo';

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
        /*        $odoo->username('alfred.laggner@gmail.com')
                    ->password('jahai999')
                    ->db('ozinc-production-elf-test-1329948')
                    ->host('https://ozinc-production-elf-test-1329948.dev.odoo.com')
                    ->connect();*/
        $odoo->connect();
        //  $licenses = LicenseNumber::all();
        $i = 0;
        $customer_creates = CustomerCreate::distinct()->get();
        //      dd($customer_imports);

        foreach ($customer_creates as $c) {
            $this->info($c->name);
            $id = $odoo->where('x_studio_field_mu5dT', $c->license)
                ->update('res.partner', [
                    'is_company' => 1,

/*                    'name' => $c->name,
                    'x_studio_field_api_id' => $c->api_id,
                    'x_studio_field_mu5dT' => $c->license,
                    'x_studio_field_0tSf6' => $c->expiration,
                    'x_studio_reference_id' => $c->reference_id,
                    'x_studio_field_Bo5Dv' => $c->business_name,
                    'street' => $c->street,
                    'street2' => $c->street2,
                    'city' => $c->city,
                    'zip' => $c->zip,
                    'user_id' => $c->user_id,
                    'x_studio_territory' => $c->territory,
                    'email' => $c->email,
                    'phone' => $c->phone,
                    'x_studio_license_type' => $c->license_type,
                    'website' => $c->website*/
                ]);

            $this->info($i . '= '. $c->license);
            $i++;
        }

/*
        foreach ($customer_imports as $c) {
            $this->info($c->name);
            $id = $odoo->create('res.partner', [
                'name' => $c->name,
                'x_studio_field_api_id' => $c->api_id,
                'x_studio_field_mu5dT' => $c->license,
                'x_studio_field_0tSf6' => $c->expiration,
                'x_studio_reference_id' => $c->reference_id,
                'x_studio_field_Bo5Dv' => $c->business_name,
                'street' => $c->street,
                'street2' => $c->street2,
                'city' => $c->city,
                'zip' => $c->zip,
                'user_id' => $c->user_id,
                'x_studio_territory' => $c->territory,
                'email' => $c->email,
                'phone' => $c->phone,
                'x_studio_license_type' => $c->license_type,
                'website' => $c->website
            ]);

            $this->info($id);
            $i++;
        }
        $this->info($i);*/
    }
}
