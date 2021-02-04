<?php

	namespace App\Traits;

	use Illuminate\Http\Request;
	use App\Commission;
	use App\Promotion;
	use App\Promotion_to_Saleinvoice;

	trait CommissionTrait
	{

		public function getCommission($margin, $region, $version, $sales_person_id = 0, $invoice_date = '')
		{
         //   $query = Commission::select(\DB::raw('max(margin) as max_margin'))->where('region', '=', $region)
			$query = Commission::select(\DB::raw('max(margin) as max_margin'))
				->get();
			foreach ($query as $q) {
				$max_margin = $q->max_margin;
			}
			if ($margin > $max_margin) {
				$margin = $max_margin;
			};

			$comms = Commission::
			where('margin', '=', $margin)->
		//	where('region', '=', $region)->
			where('version', '=', $version)->
			limit(1)->get();
			foreach ($comms as $comm) {
				if ($comm->commission > 0 AND $sales_person_id == 14 AND $invoice_date >= date('Y-m-d', strtotime('2019-04-01'))) {
					$commission = 0.05;
					return $commission;
				} else {
					return ($comm->commission);
				}
			}

		}

		public function getPromotion($saleinvoice_id, $order_date)
		{
			$promotions = Promotion::whereDate('date_from', '<=', $order_date)->whereDate('date_to', '>=', $order_date)->get();
			if ($promotions->count()) {
				$promotion_amount = 0;
				foreach ($promotions as $promotion) {

					if ($promotion->is_active) {

						$promotion_amount += $promotion->amount;
					}
				}
				return ($promotion_amount);
			} else {
				return false;
			}
		}
	}
