<?php

    namespace App\Console\Commands;

    use App\LicenseNumber;
    use Illuminate\Console\Command;

    class putLicenseNumbers extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'put:license';

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
            $odoo = new \Edujugon\Laradoo\Odoo();
            $odoo = $odoo->connect();

            $licenses = LicenseNumber::all();
            $i = 0;
            foreach ($licenses as $license) {
                $updated = $odoo->where('x_studio_field_api_id', $license->api_id)
                    ->limit(10)
                    ->fields(
                        'id',
                        'name',
                        'vat',
                        'x_studio_field_mu5dT',
                        'x_studio_field_0tSf6'
                    )
                    ->update('res.partner', [
                        /*                       'x_studio_field_mu5dT' => $license->bcc_license,
                                               'x_studio_field_0tSf6' => $license->license_exp,*/
                        'vat' => ''
                    ]);

                $this->info($i);
                $i++;
            }

        }
    }
