<?php

    namespace App\Console\Commands;

    use App\Notifications\customerStatements;
    use App\Notifications\InvoiceDue;
    use App\User;
    use Illuminate\Database\Eloquent\Model;
    use App\Invoice;
    use App\Customer;
    use App\Payment;
    use Illuminate\Console\Command;

    class emailCustomerStatements extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'ar:statements';

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
            $customers = Customer::where('total_overdue', '>', 100)->get();
            $this->info($customers->count());
            foreach ($customers as $customer) {
                if (filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
                    // invalid emailaddress
                    $customer->notify(new customerStatements($customer));
                } else {
                    $this->info($customer->name . " -> " . $customer->email);
                }
              sleep(5);
            }
        }


        public function customer_statement($customer_id = 6322)
        {
            $ledger = [];
            $total_amount = 0;
            $query = Invoice::where('customer_id', $customer_id)
                ->get();
            $total_residual = 0;
            foreach ($query as $q) {
                $total_amount += $q->amount_total;
                $total_residual += $q->residual;
                array_push($ledger, [
                        'sales_order' => $q->sales_order,
                        'date' => $q->invoice_date,
                        'name' => $q->name,
                        'due' => $q->due_date,
                        'payment_date' => 0,
                        'amount' => $q->amount_total,
                        'payment_amount' => 0,
                        'residual' => $q->residual,
                        'difference' => 0]
                );
            }
            $query = Payment::where('customer_id', $customer_id)
                ->get();
            $total_payment = 0;
            foreach ($query as $q) {
                $total_payment += $q->amount;
                array_push($ledger, [
                        'sales_order' => '',
                        'date' => $q->payment_date,
                        'name' => $q->move_name,
                        'due' => '',
                        'payment_date' => $q->payment_date,
                        'amount' => 0,
                        'payment_amount' => $q->amount,
                        'residual' => 0,
                        'difference' => $q->payment_difference]
                );

            }
            //     dd("xxx");
            $out_ledger = collect($ledger);
            $ledgers = $out_ledger->sortByDesc('date');
            //  dd($out_ledger);
            return view('ar.customer_statement', compact('ledgers', 'total_amount', 'total_residual', 'total_payment'));
        }

    }
