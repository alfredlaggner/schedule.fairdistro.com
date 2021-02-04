<?php

    namespace App\Console\Commands;

    use App\Attachment;
    use App\Margin;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Console\Command;

    class productImages extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'odoo:images';

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Puts Odoo images into image files';

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

            $products = $odoo
               ->where('sale_ok', '=', true)
            //    ->where('name', 'like', 'Heavenly Sweet Edible Treats Chocolate 100mg THC')
             //   ->where('id', '=', 363)
                ->fields(
                    'id',
                    'name',
                    'active',
                    'display_name',
                    'product_id',
                    'image'
                )
                ->get('product.template');
//dd($products);
            for ($i = 0; $i < count($products); $i++) {

                Attachment:: updateOrCreate(
                    [
                        'ext_id' => $products[$i]['id']
                    ],
                    [
                        'name' => $products[$i]['name'],
                        'active' => $products[$i]['active'],

                        'display_name' => $products[$i]['display_name'],
                        'image' => $products[$i]['image']
                    ]
                );

$this->info($i);
            }

            $attachments = Attachment::get();
            foreach ($attachments as $attachment) {
              //  $this->info($attachment->name);
                $base64_image = $attachment->image;

                $data = base64_decode($base64_image);
                $name = $attachment->ext_id . ".png";
                Storage::disk('public')->put($name, $data,'public');
                $path = Storage::disk('local')->path($name);
                $this->info($path);

            }
            $this->info(date_format(date_create(), 'Y-m-d H:i:s'));
        }
    }
