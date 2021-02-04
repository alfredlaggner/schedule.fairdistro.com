<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Margin;
use Eloquent;

class makeSellableProducts extends Command
{
    /**
     * The name and signature of the console command.
     * @mixin Eloquent
     * @var string
     */
    protected $signature = 'odoo:sellable';

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
        $sellable = Margin::where('quantity', '>=', 5)
            ->where('is_active', TRUE)
/*            ->where(function ($query) {
                $prefixes = ['P -'];
                foreach ($prefixes as $prefix) {
                    $query->orWhere('name', 'LIKE', '%' . $prefix . '%');
                }
            })*/
                  ->Where('name', 'LIKE', '%' . 'P -' . '%')

            ->get();
        dd($sellable->toArray());
    }
}
