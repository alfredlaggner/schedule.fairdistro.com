<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\SalesOrder;


class getSalesOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:salesorders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all sales orders';

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
        $orders = $odoo
            //->where( 'create_date', '>=',  date('Y-m-d', strtotime("-999 days")))
            // ->where('display_name', '=', 'SO3261')
          //  ->limit(10)
            ->fields(
                'id',
                'state',
                'display_name',
                'date_order',
                'create_date',
                'confirmation_date',
                'partner_id',
                'user_id',
                'margin',
                'write_date',
                'invoice_status',
                'order_partner_id',
                'amount_total',
                'amount_tax',
                'amount_untaxed',
                'activity_state'
            )
            ->get('sale.order');
        for ($i = 0; $i < count($orders); $i++) {
            $order_date = ($orders[$i]['date_order'] == true) ? date_format(date_create($orders[$i]['date_order']), "Y-m-d") : NULL;
            $create_date = ($orders[$i]['create_date'] == true) ? date_format(date_create($orders[$i]['create_date']), "Y-m-d") : NULL;
            $write_date = ($orders[$i]['write_date'] == true) ? date_format(date_create($orders[$i]['write_date']), "Y-m-d") : NULL;
            $confirmation_date = ($orders[$i]['confirmation_date'] == true) ? date_format(date_create($orders[$i]['confirmation_date']), "Y-m-d h:i:s") : NULL;
            SalesOrder:: updateOrCreate(
                ['ext_id' => $orders[$i]['id']],
                [
                    'sales_order' => $orders[$i]['display_name'],
                    'state' => $orders[$i]['state'],
                    'create_date' => $create_date,
                    'order_date' => $order_date,
                    'confirmation_date' => $confirmation_date,
                    'write_date' => $order_date,
                    'invoice_status' => $orders[$i]['invoice_status'],
                    'activity_state' => $orders[$i]['activity_state'],
                    'created_at' => $orders[$i]['create_date'],
                    'salesperson_id' => $orders[$i]['user_id'][0],
                    'sales_order_id' => substr($orders[$i]['display_name'], 2),
                    'customer_id' => $orders[$i]['partner_id'][0],
                    'amount_total' => $orders[$i]['amount_total'],
                    'amount_tax' => $orders[$i]['amount_tax'],
                    'amount_untaxed' => $orders[$i]['amount_untaxed'],
                ]
            );
        }
        $this->info(date_format(date_create(), 'Y-m-d H:i:s'));

    }
}
