<?php

namespace App\Http\Controllers;

use App\Models\CampaignSuggest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class CampaignSuggestController extends Controller
{
    public function index()
    {

        try {

            $offset = 15;
            if (Input::get('offset')) {
                $offset = Input::get('offset');
            }

            $res['status'] = 'success';
            $res['message'] = 'Successfully get campaign suggest';
            $res['data'] = CampaignSuggest::ofMember(env('APP_MEMBER'))->paginate($offset);

            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function store(Request $request)
    {

        try {

            DB::beginTransaction();

            $suggest = $request->get('suggest');

            $suggest['campaign_type'] = $request->get('campaign_type');

            $request->request->add([
                'suggest' => json_encode($suggest)
            ]);

            CampaignSuggest::create($request->all());

            DB::commit();

            $res['status'] = 'success';
            $res['message'] = 'Successfully create campaign suggest';
            $res['data'] = '';
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }
}
