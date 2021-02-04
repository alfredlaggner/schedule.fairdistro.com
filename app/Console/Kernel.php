<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        'App\Console\Commands\getCashin',
        'App\Console\Commands\getProductsFromOdoo',
        'App\Console\Commands\getSalesOrders',
        'App\Console\Commands\getSalesLines',
        'App\Console\Commands\getInvoiceLines',
        'App\Console\Commands\getAccountingSalesOrders',
        'App\Console\Commands\calcSalesPerDay',
        'App\Console\Commands\getSalesPersons',
        'App\Console\Commands\getCostomers',
        'App\Console\Commands\calccommissions',
        'App\Console\Commands\getOdooCategory',
        'App\Console\Commands\getStockMoves',
        'App\Console\Commands\getStockPicking',
        'App\Console\Commands\getStockMoves',
        'App\Console\Commands\saleslines',
        'App\Console\Commands\test_aged',
        'App\Console\Commands\calcAgedReceivables',
		'App\Console\Commands\ar_set_values_to_collect',
		'App\Console\Commands\temp',
        'App\Console\Commands\housekeeping',
        'App\Console\Commands\MetrcUpdateTags',
        'App\Console\Commands\putLicenseNumbers',
        'App\Console\Commands\count_sales_per_product',
        'App\Console\Commands\makeNextSku',
        'App\Console\Commands\calcLastSku',
        'App\Console\Commands\calcLastBarcode',
        'App\Console\Commands\getStockQuant',
        'App\Console\Commands\makeSellableProducts',
     //   'App\Console\Commands\calcTenNinety',
        'App\Console\Commands\calcTenNinetyBiMonthly',
        'App\Console\Commands\emailDueInvoices',
        'App\Console\Commands\getPayments',
        'App\Console\Commands\getPayments_AR',
        'App\Console\Commands\getAllProducts',
        'App\Console\Commands\getProductProduct',
        'App\Console\Commands\emailCustomerStatements',
        'App\Console\Commands\update_odoo_products',
        'App\Console\Commands\productImages',
        'App\Console\Commands\employee_bonus',
        'App\Console\Commands\put_all_companies',
        'App\Console\Commands\check_logs',
        'App\Console\Commands\ResetOdooCommissions',
        'App\Console\Commands\metrc_packageV0',
        'App\Console\Commands\metrc_create_package',
        'App\Console\Commands\metrc_items',
        'App\Console\Commands\metrc_lab_report',
        'App\Console\Commands\metrc_uom',
        'App\Console\Commands\metrc_strains',
        'App\Console\Commands\getLabtests',
        'App\Console\Commands\metrc_get_package',
        'App\Console\Commands\metrc_get_package',
        'App\Console\Commands\metrc_update_package',
        'App\Console\Commands\metrc_make_trans_package',

    ];

	protected function scheduleTimezone()
	{
		return 'America/Los_Angeles';
	}

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */

    protected function schedule(Schedule $schedule)
    {
		date_default_timezone_set('America/Los_Angeles');

        $schedule->command('odoo:brands')->daily()->withoutOverlapping()->appendOutputTo(storage_path('brands.log'));
        $schedule->command('odoo:images')->daily()->withoutOverlapping()->appendOutputTo(storage_path('images.log'));
		$schedule->command('odoo:housekeeping')->hourly()->withoutOverlapping()->appendOutputTo(storage_path('housekeeping.log'));

        $schedule->command('odoo:salesorder')->everyFiveMinutes()->withoutOverlapping()->appendOutputTo(storage_path('salesorder.log'));

        $schedule->command('odoo:saleslines')->hourly()->runInBackground()->withoutOverlapping()->appendOutputTo(storage_path('saleslines_minute.log'));
        $schedule->command('odoo:saleslines /-6/ months')
            ->daily()
			->runInBackground()->appendOutputTo(storage_path('saleslines_daily.log'));


        $schedule->command('odoo:invoice_lines /-1/ day')->everyMinute()->withoutOverlapping()->appendOutputTo(storage_path('invoice_lines_minute.log'));
        $schedule->command('odoo:invoice_lines /-6/ months')
            ->daily()->withoutOverlapping()
			->runInBackground()->appendOutputTo(storage_path('invoice_lines_daily.log'));

        $schedule->command('odoo:invoicelines  /-1/ day')->hourly()->withoutOverlapping()->appendOutputTo(storage_path('invoicelines_hourly.log'));
        $schedule->command('odoo:invoicelines  /-6/ months')
            ->daily()->withoutOverlapping()
			->runInBackground()->appendOutputTo(storage_path('invoicelines_daily.log'));

        $schedule->command('calc:lastsku')->everyMinute()->appendOutputTo(storage_path('lastSku.log'));
        $schedule->command('odoo:margin')->daily()->withoutOverlapping()->runInBackground()->appendOutputTo(storage_path('margin.log'));
        $schedule->command('odoo:products')->everyFiveMinutes()->withoutOverlapping()->runInBackground()->appendOutputTo(storage_path('product.log'));
        $schedule->command('odoo:productproducts')->daily()->withoutOverlapping()->runInBackground()->appendOutputTo(storage_path('productproduct.log'));
        $schedule->command('odoo:salespersons')->daily()->appendOutputTo(storage_path('salesperson.log'));
        $schedule->command('odoo:customers')->daily()->appendOutputTo(storage_path('customers.log'));
        $schedule->command('odoo:getstock')->daily()->appendOutputTo(storage_path('getstock.log'));
        $schedule->command('odoo:stockpicking')->daily()->runInBackground()->appendOutputTo(storage_path('getstockpicking.log'));
        $schedule->command('calc:salescount')->daily()->runInBackground()->appendOutputTo(storage_path('salescount.log'));
        $schedule->command('calc:paid_commissions')->daily()->appendOutputTo(storage_path('paid_commissions.log'));
        $schedule->command('calc:ten_ninety_bi')->daily()->appendOutputTo(storage_path('ten_ninety_bi.log'));
        $schedule->command('odoo:invoices')->hourly()->withoutOverlapping()->appendOutputTo(storage_path('invoices.log'));
        $schedule->command('calc:ar')->hourly()->appendOutputTo(storage_path('ar.log'));
        $schedule->command('odoo:payments')->hourly()->appendOutputTo(storage_path('payments.log'));
        $schedule->command('odoo:payments_AR')->daily()->appendOutputTo(storage_path('payments.log'));
		$schedule->command('calc:ar_to_collect')->weeklyOn(1, '6:00')->appendOutputTo(storage_path('ar'));
        $schedule->command('tntsearch:import App\\AgedReceivablesTotals')->daily()->appendOutputTo(storage_path('tntsearch'));
        $schedule->command('metrc:package1')->daily()->appendOutputTo(storage_path('metrc_package.log'));
        $schedule->command('metrc:items')->hourly()->appendOutputTo(storage_path('metrc_items.log'));
        $schedule->command('metrc:strains')->hourly()->appendOutputTo(storage_path('metrc_strains.log'));
     //   $schedule->command('metrc:update_tags')->hourly()->appendOutputTo(storage_path('package.log'));
      //  $schedule->command('calc:ten_ninety')->daily()->appendOutputTo(storage_path('ten_ninety.log'));
        $schedule->command('backup:run')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
