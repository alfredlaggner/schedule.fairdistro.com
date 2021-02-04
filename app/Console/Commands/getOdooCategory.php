<?php

	namespace App\Console\Commands;

	use Illuminate\Console\Command;
	use \App\Brand;


	class getOdooCategory extends Command
	{
		/**
		 * The name and signature of the console command.
		 *
		 * @var string
		 */
		protected $signature = 'odoo:brands';

		/**
		 * The console command description.
		 *
		 * @var string
		 */
		protected $description = 'Get all product categories';

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
			$categories = $odoo
                ->where ('product_count','>',0)
				->fields(
					'id',
					'code',
					'name',
					'display_name',
					'parent_id',
					'parent_left',
					'parent_right'
				)
				->get('product.category');

			//	\DB::table('brands')->delete();
			/*		for ($i = 0; $i < count($categories); $i++) {
						$categ_temp = explode('/', $categories[$i]['display_name']);
						$brand = '';
						$len = sizeof($categ_temp) - 1 > 0 ?  sizeof($categ_temp) - 1 : 0;
						if ($len)
							$brand = $categ_temp[$len];*/
			for ($i = 0; $i < count($categories); $i++) {


				$categ_temp = explode('/', $categories[$i]['display_name']);
				$brand = 'no brand';
				if (sizeof($categ_temp) >= 3) {
					if (!in_array(" CBD ", $categ_temp, false)) {
						$brand = $categ_temp[2];
						/*						if ($categories[$i]['id'] == 198){
													dd ($categ_temp);
												}*/
					}
				}
				$category = "no category";
				if (sizeof($categ_temp) >= 2) {
					if (!in_array(" CBD ", $categ_temp, false)) {
						$category = $categ_temp[1];

						/*
												if ($categories[$i]['id'] == 278) {
													dd($categ_temp);
												}*/

					}
				}
				$subcategory = "";
				if (sizeof($categ_temp) >= 4) {
					if (!in_array(" CBD ", $categ_temp, false)) {
						$subcategory = $categ_temp[3];

						/*
												if ($categories[$i]['id'] == 278) {
													dd($categ_temp);
												}*/

					}
				}


				Brand:: updateOrCreate(
					[
						'ext_id' => $categories[$i]['id']
					],
					[
						'name' => $brand,
						'category_full' => $categories[$i]['display_name'],
						'category' => $category,
						'subcategory' => $subcategory,
					]
				);
			}
			$this->info(date_format(date_create(), 'Y-m-d H:i:s'));

		}
	}
