<?php

	namespace App\Console\Commands;

	use App\Invoice;
	use App\SavedCommission;
	use Illuminate\Console\Command;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Schema;

	class housekeeping extends Command
	{
		/**
		 * The name and signature of the console command.
		 *
		 * @var string
		 */
		protected $signature = 'odoo:housekeeping';

		/**
		 * The console command description.
		 *
		 * @var string
		 */
		protected $description = 'Clean up various things';

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
			$commissions = SavedCommission::where('is_commissions_paid', false)->get();
			foreach ($commissions as $commission) {

				$saved_commission = $commission->name;
				Schema::dropIfExists($saved_commission);
				$commission->delete();

			}
			$this->info(date_format(date_create(), 'Y-m-d H:i:s'));
		}
	}
