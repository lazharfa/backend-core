<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignSummary;
use App\Models\ImageFile;
use App\Traits\Editor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    use Editor;

    public function index(Request $request)
    {
        try {

            $offset = 20;

            if ($request->get('offset')) {
                $offset = $request->get('offset');
            }

            $general_campaigns_offset = env('GENERAL_CAMPAIGN_OFFSET', 3);

            $generalCampaignCount = $offset / $general_campaigns_offset;


            $generalCampaigns = Campaign::ofMember(env('APP_MEMBER'))->with('category')
                ->select('id', 'category_id', 'campaign_title', 'campaign_slug', 'campaign_status', 'campaign_image', 'target_donation', 'total_fund', 'expired_at', 'invitation_message', 'created_at', 'donation_percentage')
                ->where('category_id', 23)->where('campaign_status', 'Publish')
                ->whereNotIn('id', explode(',', env('EMERGENCY_CAMPAIGN', '0')))
                ->filterCategory($request->get('category_slug'))
                ->where('expired_at', '>=', now())
                ->rangePercentage($request->range_percentage)
                ->sortParam($request->sort)
                ->paginate($generalCampaignCount)->getCollection();

            // $offset -= $generalCampaigns->count();

            $campaigns = Campaign::ofMember(env('APP_MEMBER'))->with('category')
                ->where('category_id', '!=', 23)
                ->whereNotIn('id', explode(',', env('EMERGENCY_CAMPAIGN', '0')))
                ->filterCategory($request->get('category_slug'))
                ->expired($request->is_expired)
                ->rangePercentage($request->range_percentage);

            $campaigns = $campaigns->select('id','priority', 'category_id', 'campaign_title', 'campaign_slug', 'campaign_status', 'campaign_image', 'target_donation', 'total_fund', 'expired_at', 'invitation_message', 'created_at', 'donation_percentage');

            if ($request->get('str')) {

                $str = explode(' ', $request->get('str'));

                $campaigns = $campaigns->where(function ($query) use ($str) {
                    foreach ($str as $item) {
                        $query = $query->orWhere('campaign_title', 'ilike', '%' . $item . '%')
                            ->orWhere('campaign_title_en', 'ilike', '%' . $item . '%')
                            ->orWhere('invitation_message', 'ilike', '%' . $item . '%')
                            ->orWhere('invitation_message_en', 'ilike', '%' . $item . '%');

                    }
                });
            }


            if ($request->get('status')) {

                $campaigns = $campaigns->where('campaign_status', $request->get('status'));

            }

            if ($request->get('creator')) {

                $campaigns = $campaigns->where('creator_id', $request->get('creator'));

            }

            $campaigns = $campaigns->sortParam($request->sort)->paginate($offset);

            $message = 'Successfully get campaigns';

            $campaignCollections = $campaigns->getCollection();

            if ($campaignCollections->count() == 0) {
                $message = 'Campaign not found';
            }

            if ($generalCampaigns->count() > 0) {

                $generalCampaigns->reduce(function ($carry, $generalCampaign) use ($campaignCollections, $general_campaigns_offset) {

                    $generalCampaign = collect($generalCampaign);

                    $campaignCollections->splice($carry, 0, $generalCampaign);

                    return $carry + $general_campaigns_offset;

                }, $general_campaigns_offset - 1);

                $campaigns = $campaigns->setCollection($campaignCollections);

            }

            $res['status'] = 'success';
            $res['message'] = $message;
            $res['data'] = $campaigns;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function list(Request $request)
    {
        try {

            $offset = 20;

            if ($request->get('offset')) {
                $offset = $request->get('offset');
            }

            $campaigns = Campaign::with('category')->expired($request->is_expired);

            $campaigns = $campaigns->select('id','priority', 'category_id', 'campaign_title', 'campaign_slug', 'campaign_status', 'campaign_image', 'target_donation', 'total_fund', 'expired_at', 'invitation_message', 'created_at')
                ->filterCategory($request->get('category_slug'))
                ->rangePercentage($request->range_percentage)
                ->sortParam($request->sort);

            if ($request->get('str')) {

                $str = explode(' ', $request->get('str'));

                $campaigns = $campaigns->where(function ($query) use ($str) {

                    foreach ($str as $item) {

                        $query = $query->orWhere('campaign_title', 'ilike', '%' . $item . '%')
                            ->orWhere('campaign_title_en', 'ilike', '%' . $item . '%')
                            ->orWhere('invitation_message', 'ilike', '%' . $item . '%')
                            ->orWhere('invitation_message_en', 'ilike', '%' . $item . '%');

                    }

                });

            }

            if ($request->get('order_column') && $request->get('order_direction')) {

                $campaigns = $campaigns->orderBy($request->get('order_column'), $request->get('order_direction'));

            } else {
                $campaigns = $campaigns->latest();
            }

            if ($request->get('status')) {

                $campaigns = $campaigns->where('campaign_status', $request->get('status'));

            }

            if ($request->get('creator')) {

                $campaigns = $campaigns->where('creator_id', $request->get('creator'));

            }

            $campaigns = $campaigns->paginate($offset);

            $message = 'Successfully get campaigns';

            if ($campaigns->getCollection()->count() == 0) {
                $message = 'Campaign not found';
            }

            $res['status'] = 'success';
            $res['message'] = $message;
            $res['data'] = $campaigns;
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

            $validator = Validator::make($request->all(), [
                'member_id' => 'required',
                'category_id' => 'required',
                'campaign_title' => 'required',
                'campaign_title_en' => 'required',
                'campaign_slug' => 'required|unique:campaigns',
                'campaign_slug_en' => 'required|unique:campaigns',
                'image' => 'required',
                'target_donation' => 'required',
                'expired_at' => 'required',
                'invitation_message' => 'required',
                'invitation_message_en' => 'required',
                'descriptions' => 'required',
                'campaign_status' => 'required',
                'descriptions_en' => 'required'
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $user = Auth::user();

            if ($user->user_status != 'Verified' && env('VERIFIED_USER')) {

                throw new Exception('User unverified');

            }

            if ($request->input('image')) {

                $fileName = time() . '-' . Str::random(50) . '.' . 'jpg';
                $resultImage = ImageFile::storeImageWithCrop($fileName, $request->get('image'), $request->get('angle'), $request->get('scale'), $request->get('h'), $request->get('w'), $request->get('x'), $request->get('y'));

                $request->request->add([
                    'campaign_image' => $fileName
                ]);

                if ($resultImage) {
                    throw new Exception('Failed to save image. ' . $resultImage);
                }

            }

            $request->request->add([
                'creator_id' => $user->id
            ]);

            list($descriptions, $error) = $this->base64ToFile($request->input('descriptions'), $request->input('campaign_slug'));

            if ($error) {
                throw new Exception($error);
            }

            $request->request->add([
                'descriptions' => $descriptions,
                'descriptions_en' => $descriptions,
            ]);

            Campaign::create($request->all());

            $res['status'] = 'success';
            $res['message'] = 'Successfully create campaign';
            $res['data'] = $request->all();
            DB::commit();
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = $request->all();
            return response($res, 200);

        }
    }

    public function show($slug)
    {
        try {

            $campaign = Campaign::with('category')->ofMember(env('APP_MEMBER'))->where('campaign_slug', $slug)->firstOrFail();

            $res['status'] = 'success';
            $res['message'] = 'Successfully get campaign';
            $res['data'] = $campaign;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function showByCategory(Request $request, $slug)
    {

        try {

            DB::beginTransaction();

            $offset = 15;

            if ($request->get('offset')) {
                $offset = $request->get('offset');
            }

            $campaigns = Campaign::with('category')->ofMember(env('APP_MEMBER'))->where('category_id', function ($query) use ($slug) {

                $query
                    ->select('id')
                    ->from('categories')
                    ->where('member_id', env('APP_MEMBER'))
                    ->where('category_slug', $slug)
                    ->first();

            })->latest();

            if ($request->get('status')) {

                $campaigns = $campaigns->where('campaign_status', $request->get('status'));

            }

            $res['status'] = 'success';
            $res['message'] = 'Successfully update progress campaign';
            $res['data'] = $campaigns->paginate($offset);
            DB::commit();
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function update(Request $request, $slug)
    {
        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'member_id' => 'required',
                'category_id' => 'required',
                'campaign_title' => 'required',
                'image' => 'required',
                'target_donation' => 'required',
                'expired_at' => 'required',
                'invitation_message' => 'required',
                'descriptions' => 'required',
                'campaign_status' => 'required'
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ", $validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            $campaign = Campaign::ofMember(env('APP_MEMBER'))->where('campaign_slug', $slug)->firstOrFail();

            if ($request->input('image')) {

                $fileName = time() . '-' . str_random(50) . '.' . 'jpg';

                $resultImage = ImageFile::storeImageWithCrop($fileName, $request->get('image'), $request->get('angle'), $request->get('scale'), $request->get('h'), $request->get('w'), $request->get('x'), $request->get('y'));

                if ($resultImage) {
                    throw new Exception('Failed to save image. ' . $resultImage);
                }

                $domain = explode('.', env('APP_MEMBER'))[0];

                if (Storage::disk('do_spaces')->exists("$domain/public/$campaign->campaign_image")) {
                    $deleteImage = Storage::disk('do_spaces')->delete("$domain/public/$campaign->campaign_image");

                    if (!$deleteImage) {
                        throw new Exception('Failed to delete image. ' . $resultImage);
                    }
                }

                $request->request->add([
                    'campaign_image' => $fileName
                ]);
            }

            list($descriptions, $error) = $this->base64ToFile($request->input('descriptions'), $campaign->campaign_slug);

            if ($error) {
                throw new Exception($error);
            }

            $request->request->add([
                'descriptions' => $descriptions,
                'descriptions_en' => $descriptions,
            ]);

            $campaign->update($request->all());
            $res['status'] = 'success';
            $res['message'] = 'Successfully update campaign';
            $res['data'] = $request->all();
            DB::commit();
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = $request->all();
            return response($res, 200);

        }
    }

    public function destroy($slug)
    {
        try {

            $campaign = Campaign::ofMember(env('APP_MEMBER'))->where('campaign_slug', $slug)->firstOrFail();

            $campaign->update([
                'campaign_slug' => "{$campaign->campaign_slug}-deleted",
                'campaign_slug_en' => "{$campaign->campaign_slug_en}-deleted"
            ]);

            $res['status'] = 'success';
            $res['message'] = 'Successfully delete campaign';
            $res['data'] = '';
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

    public function summary()
    {

        return CampaignSummary::all();

    }

    public function suggest(Request $request)
    {

        try {

            if ($request->get('slug')) {

                $slug = $request->get('slug');

                $currentCampaign = Campaign::ofMember(env('APP_MEMBER'))->with('category')
                    ->select('id', 'category_id', 'campaign_title', 'campaign_slug', 'campaign_status', 'campaign_image', 'target_donation', 'expired_at', 'invitation_message', 'created_at')
                    ->where('campaign_slug', $slug)->firstOrFail();

                $latestCampaign = Campaign::ofMember(env('APP_MEMBER'))->with('category')
                    ->select('id', 'category_id', 'campaign_title', 'campaign_slug', 'campaign_status', 'campaign_image', 'target_donation', 'expired_at', 'invitation_message', 'created_at')
                    ->where('category_id', '!=', $currentCampaign->category_id)->where('id', '!=', $currentCampaign->id)
                    ->where('campaign_status', 'Publish')->latest()->firstOrFail();

                $similarCampaign = Campaign::ofMember(env('APP_MEMBER'))->with('category')
                    ->select('id', 'category_id', 'campaign_title', 'campaign_slug', 'campaign_status', 'campaign_image', 'target_donation', 'expired_at', 'invitation_message', 'created_at')
                    ->where('category_id', $currentCampaign->category_id)->where('id', '!=', $currentCampaign->id)
                    ->where('campaign_status', 'Publish')->latest()->firstOrFail();

                $generalCampaign = Campaign::ofMember(env('APP_MEMBER'))->with('category')
                    ->select('id', 'category_id', 'campaign_title', 'campaign_slug', 'campaign_status', 'campaign_image', 'target_donation', 'expired_at', 'invitation_message', 'created_at')
                    ->where('category_id', 23)->where('id', '!=', $currentCampaign->id)
                    ->where('campaign_status', 'Publish')->latest()->firstOrFail();

                $resultCampaigns = [
                    $latestCampaign,
                    $similarCampaign,
                    $generalCampaign
                ];

            } else {

                $resultCampaigns = Campaign::ofMember(env('APP_MEMBER'))->with('category')
                    ->select('id', 'category_id', 'campaign_title', 'campaign_slug', 'campaign_status', 'campaign_image', 'target_donation', 'expired_at', 'invitation_message', 'created_at')
                    ->where('campaign_status', 'Publish')->latest()->limit(2)->get();

                $generalCampaign = Campaign::ofMember(env('APP_MEMBER'))->with('category')
                    ->select('id', 'category_id', 'campaign_title', 'campaign_slug', 'campaign_status', 'campaign_image', 'target_donation', 'expired_at', 'invitation_message', 'created_at')
                    ->where('category_id', 23)->where('campaign_status', 'Publish')->latest()->firstOrFail();

                $resultCampaigns->push($generalCampaign);

            }

            $res['status'] = 'success';
            $res['message'] = 'Successfully get campaign suggest';
            $res['data'] = $resultCampaigns;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function donation($slug)
    {
        try {

            $campaign = Campaign::select('id', 'campaign_title', 'campaign_image')->where('campaign_slug', $slug)->firstOrFail();

            $res['status'] = 'success';
            $res['message'] = 'Successfully get campaign';
            $res['data'] = $campaign;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

}
