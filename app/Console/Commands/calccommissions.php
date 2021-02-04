<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\SaleInvoice;
use App\Commission;
class calccommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:commissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates all commissions';

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
        {
            $commission_version = 1;
            SaleInvoice::where('sales_person_id', '>', 0)->where('invoice_status','=','invoiced')->chunk(100, function($items) {
                $commission_version = 1;
                foreach ($items as $item) {
                    $commission_percent = $this->getCommission(round($item->margin, 0, PHP_ROUND_HALF_DOWN), $item->salesperson->region, $commission_version);
                    //		$commission = $item->quantity * $item->unit_price * $commission_percent;

                    $commission = ($item->amt_invoiced)* $commission_percent;

                    $si = SaleInvoice::find($item->id);
                    $si->commission = $commission;
                    $si->comm_version = $commission_version;
                    $si->comm_region = $item->salesperson->region;
                    $si->comm_percent = $commission_percent;
                    $si->save();
                }
            });

        }

    }
    function getCommission($margin, $region, $version)
    {
        $query = Commission::select(\DB::raw('max(margin) as max_margin'))->where('region', '=', $region)
            ->get();
        foreach ($query as $q) {
            $max_margin = $q->max_margin;
        }
        if ($margin > $max_margin) {
            $margin = $max_margin;
        };

        $comms = Commission::
        where('margin', '=', $margin)->
        where('region', '=', $region)->
        where('version', '=', $version)->
        limit(1)->get();
        foreach ($comms as $comm) {
            return ($comm->commission);
        }
    }

}
