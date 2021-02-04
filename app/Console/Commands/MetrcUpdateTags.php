<?php

namespace App\Console\Commands;

use App\MetrcTag;
use App\Package;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MetrcUpdateTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrc:update_tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marks used tags as used';

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
        $packages = Package::get();
        foreach ($packages as $package) {
            MetrcTag::where('tag', '=', $package->tag)
                ->update(['is_used' => 1, 'used_at' => Carbon::now()]);
        }
    }
}
