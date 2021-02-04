<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class upload_customers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:upcust';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Customer upload';

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
        $up_loads = UpCustomer::where('is_customer', true)->get();

        foreach ($up_loads as $q) {
            $updated = $odoo->where('id', '=', $q->ext_id)
                ->update('res.partner', [
                    'display_name' => $q->name,
                    'city' => $q->city,
                    'street' => $q->street,
                    'city' => $q->city,
                    'x_studio_field_Bo5Dv' => $q->business_name,
                    'zip' => $q->zip,
                    'email' => $q->name,
                    'x_studio_field_mu5dT' => $q->license,
                    'x_studio_field_api_id' => $q->api_id,
                ]);
        }
    }
}
