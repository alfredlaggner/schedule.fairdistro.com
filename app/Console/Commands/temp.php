<?php

	namespace App\Console\Commands;

	use App\Salesline;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Console\Command;

	class temp extends Command
	{
		/**
		 * The name and signature of the console command.
		 *
		 * @var string
		 */
		protected $signature = 'temp:temp';

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

            $query = DB::table('customer_ext_ids')
                ->where('ext_id', '>',0)
          //      ->where('ext_id',3490)
                ->get();

            foreach ($query as $q) {

                $updated = $odoo->where('id', $q->ext_id)
                    ->update('res.partner', ['x_studio_field_api_id' => $q->api_id]);

                $this->info($q->ext_id);
                $this->info($q->api_id);

            }

        }

		public function handle1()
		{
			$query = DB::table('customer_ext_ids')
                ->where('ext_id', '>',0)
                ->get();

			foreach ($query as $q) {

				$a = \App\Customer::where('ext_id', $q->ext_id)->update(['api_id' => $q->api_id]);
			}

		}

    }
