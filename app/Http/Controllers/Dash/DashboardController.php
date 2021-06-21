<?php

namespace App\Http\Controllers\Dash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class DashboardController extends Controller
{
    public function donationsGroupYear()
    {
        $data = DB::select(DB::raw("select date_part('year',date_donation) as year, sum(total_donation) as total from donations d 
        where total_donation is not null
        group by year
        order by year"));

        return response()->json([
            'status'    => 'success',
            'message'   => null,
            'data'      => $data
        ]);
    }

    public function donationsGroupMonth()
    {
        $now = date('Y-m-d H:i:s');
        $year_ago = date('Y-m-d H:i:s', strtotime($now . ' - 1 year'));
        $data = DB::select(DB::raw("select TO_CHAR(date_donation, 'YYYY-MM') as month, sum(total_donation) from donations d 
        where total_donation is not null and date_donation >= '${year_ago}' 
        group by month order by month desc"));

        return response()->json([
            'status'    => 'success',
            'message'   => null,
            'data'      => $data
        ]);
    }

    public function mostCampaignPerYear()
    {
        
    }
}
