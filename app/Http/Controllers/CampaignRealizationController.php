<?php

namespace App\Http\Controllers;

use App\Models\CampaignRealization;
use App\Models\CampaignRealizationDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CampaignRealizationController extends Controller
{
    public function index(Request $request)
    {

        $offset = 15;

        if ($request->query('offset')) {
            $offset = $request->query('offset');
        }

        $campaignRealizations = CampaignRealization::with('campaign', 'details', 'progresses', 'category')->ofMember(env('APP_MEMBER'))->latest()->paginate($offset);

        $res['status'] = 'success';
        $res['message'] = 'Successfully get campaign realizations';
        $res['data'] = $campaignRealizations;
        return response($res, 200);

    }

    public function store(Request $request)
    {

        try {

            DB::beginTransaction();
            $input = $request->all();
            $input['creator_id'] = Auth::id();
            $input['realization_number'] = CampaignRealization::realizationNumber();
            $input['realization_status'] = 'Waiting Approve';

            $campaignRealization = CampaignRealization::create($input);

            foreach ($input['details'] as $detail) {

                $detail['creator_id'] = Auth::id();
                $detail['realization_id'] = $campaignRealization->id;

                CampaignRealizationDetail::create($detail);

            }

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create campaign';
            $res['data'] = $campaignRealization;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function show($id)
    {

        try {

            $campaignRealization = CampaignRealization::with('campaign', 'details', 'progresses', 'category')->ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail();

            $res['status'] = 'success';
            $res['message'] = 'Successfully update request fund';
            $res['data'] = $campaignRealization;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function update(Request $request, $id)
    {

        try {

            DB::beginTransaction();
            $input = $request->all();

            if ($request->input('delete')) {
                CampaignRealizationDetail::where('campaign_realization_id', $id)->whereIN('id', $input['delete'])->delete();
            }

            if (isset($input['details'])) {

                foreach ($input['details'] as $detail) {

                    $detail['creator_id'] = Auth::id();
                    $detail['campaign_realization_id'] = $id;

                    CampaignRealizationDetail::create($detail);

                }

            }

            $campaignRealization = CampaignRealization::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail()->update($input);

            $res['status'] = 'success';
            $res['message'] = 'Successfully update request fund';
            $res['data'] = $campaignRealization;
            DB::commit();
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function destroy($id)
    {
        try {

            CampaignRealization::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail()->delete();
            $res['status'] = 'success';
            $res['message'] = 'Successfully delete request fund';
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
