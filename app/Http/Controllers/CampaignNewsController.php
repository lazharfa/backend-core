<?php

namespace App\Http\Controllers;

use App\Models\CampaignNews;
use App\Models\ImageFile;
use App\Traits\Editor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CampaignNewsController extends Controller
{
    use Editor;

    public function index(Request $request)
    {
        try {

            $offset = 15;

            if ($request->get('offset')) {
                $offset = $request->get('offset');
            }

            $campaignNews = CampaignNews::with('category:id,category_name,category_slug,category_slug_en')
                ->select('campaign_id', 'news_title', 'news_content', 'news_date', 'news_slug', 'category_id', 'news_image', 'creator_id', 'created_at')
                ->orderByDesc('news_date')->latest();

            if ($request->get('campaign_slug')) {

                $campaignNews = $campaignNews->where('campaign_id', function ($query) use ($request) {

                    $query
                        ->select('id')
                        ->from('campaigns')
                        ->where('member_id', env('APP_MEMBER'))
                        ->where('campaign_slug', $request->get('campaign_slug'))
                        ->first();

                });

            }

            if ($request->get('campaign')) {
                $campaignNews = $campaignNews->whereNotNull('campaign_id');
            }

            if ($request->get('non_campaign')) {
                $campaignNews = $campaignNews->whereNull('campaign_id');
            }

            if ($request->get('str')) {

                $str = explode(' ', $request->get('str'));

                $campaignNews = $campaignNews->where(function ($query) use ($str) {

                    foreach ($str as $item) {

                        $query = $query->orWhere('news_title', 'ilike', '%' . $item . '%')
                            ->orWhere('news_title_en', 'ilike', '%' . $item . '%')
                            ->orWhere('news_content', 'ilike', '%' . $item . '%')
                            ->orWhere('news_content_en', 'ilike', '%' . $item . '%');

                    }

                });

            }

            $campaignNews = $campaignNews->paginate($offset);

            $dayToHari = [
                'Sunday' => 'Ahad',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];

            $campaignNews = $campaignNews->setCollection(

                $campaignNews->getCollection()
                    ->map(function ($item) use ($dayToHari) {

                        $item->post_at = $item->created_at ? $dayToHari[$item->created_at->format('l')] . ', ' . $item->created_at->format('d/m/y') : '';

                        return $item;
                    })
            );

            $res['status'] = 'success';
            $res['message'] = 'Successfully get all campaign news';
            $res['data'] = $campaignNews;

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
                'news_title' => 'required',
                'news_title_en' => 'required',
                'news_slug' => 'required|unique:campaign_news',
                'news_slug_en' => 'required|unique:campaign_news',
                'news_date' => 'required',
                'news_content' => 'required',
                'news_content_en' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);

            if ($validator->fails()) {
                $rawString = implode(", ",$validator->messages()->all());
                throw new Exception(str_replace('.', '', $rawString));
            }

            if ($request->input('image')) {

                $fileName = time() . '-' . str_random(50) . '.' . 'jpg';
                $resultImage = ImageFile::storeImageWithCrop($fileName, $request->get('image'), $request->get('angle'), $request->get('scale'), $request->get('h'), $request->get('w'), $request->get('x'), $request->get('y'));

                if ($resultImage) {
                    throw new Exception('Failed to save image. ' . $resultImage);
                }

                $request->request->add([
                    'news_image' => $fileName,
                ]);

            }

            $request->request->add([
                'creator_id' => Auth::id(),
            ]);

            list($descriptions, $error) = $this->base64ToFile($request->input('news_content'), $request->input('news_slug'));

            if ($error) {
                throw new Exception($error);
            }

            $request->request->add([
                'news_content' => $descriptions,
                'news_content_en' => $descriptions,
            ]);

            $campaignNews = CampaignNews::create($request->all());

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create progress campaign';
            $res['data'] = $campaignNews;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function show($slug)
    {

        try {

            $campaignNews = CampaignNews::with('category', 'campaign')->ofMember(env('APP_MEMBER'))->where('news_slug', $slug)->firstOrFail();

            $res['status'] = 'success';
            $res['message'] = 'Successfully update progress campaign';
            $res['data'] = $campaignNews;
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

            $campaignNews = CampaignNews::with('category')->ofMember(env('APP_MEMBER'))->latest()->where('category_id', function ($query) use ($slug) {

                $query
                    ->select('id')
                    ->from('categories')
                    ->where('member_id', env('APP_MEMBER'))
                    ->where('category_slug', $slug)
                    ->first();

            });

            if ($request->get('campaign')) {

                $campaignNews = $campaignNews->where('campaign_id', function ($query) use ($request) {
                    $query
                        ->select('id')
                        ->from('campaigns')
                        ->where('member_id', env('APP_MEMBER'))
                        ->where('campaign_slug', $request->get('campaign'))
                        ->first();
                });

            }

            $res['status'] = 'success';
            $res['message'] = 'Successfully update progress campaign';
            $res['data'] = $campaignNews->paginate($offset);
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

            $campaignNews = CampaignNews::ofMember(env('APP_MEMBER'))->where('news_slug', $slug)->firstOrFail();

            if ($request->input('image')) {

                $fileName = time() . '-' . str_random(50) . '.' . 'jpg';
                $resultImage = ImageFile::storeImageWithCrop($fileName, $request->get('image'), $request->get('angle'), $request->get('scale'), $request->get('h'), $request->get('w'), $request->get('x'), $request->get('y'));

                if ($resultImage) {
                    throw new Exception('Failed to save image. ' . $resultImage);
                }

                $domain = explode('.', env('APP_MEMBER'))[0];
                if (Storage::disk('do_spaces')->exists("$domain/public/$campaignNews->news_image")) {
                    $deleteImage = Storage::disk('do_spaces')->delete("$domain/public/$campaignNews->news_image");

                    if (!$deleteImage) {
                        throw new Exception('Failed to delete image. ' . $resultImage);
                    }
                }

                $request->request->add([
                    'news_image' => $fileName,
                ]);

            }

            $request->request->add([
                'updater_id' => Auth::id()
            ]);

            list($descriptions, $error) = $this->base64ToFile($request->input('news_content'), $campaignNews->news_slug);

            if ($error) {
                throw new Exception($error);
            }

            $request->request->add([
                'news_content' => $descriptions,
                'news_content_en' => $descriptions,
            ]);

            $campaignNews->update($request->all());

            $res['status'] = 'success';
            $res['message'] = 'Successfully update progress campaign';
            $res['data'] = '';
            DB::commit();
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }

    }

    public function destroy($slug)
    {
        try {

            DB::beginTransaction();
            CampaignNews::ofMember(env('APP_MEMBER'))->where('news_slug', $slug)->firstOrFail()->delete();

            $res['status'] = 'success';
            $res['message'] = 'Successfully delete progress campaign';
            $res['data'] = '';
            DB::commit();
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);
        }
    }

    public function userRecentNews()
    {
        $news = CampaignNews::whereHas('campaign', function($q){
            $q->whereHas('donations', function($q1){
                $q1->where('donor_id', Auth::id())->whereNotNull('total_donation');
            });
        })->select('news_slug', 'news_title', 'news_image', 'created_at', DB::raw('SUBSTRING(news_content from 0 for 31)'))->orderBy('id', 'desc')->paginate(12);

        $news->transform(function($item){
            $item->substring = strlen($item->substring) < 30 ? $item->substring : ($item->substring . ' ....');
            return $item;
        });

        return response()->json([
            'status'    => 'success',
            'message'   => null,
            'data'      => $news
        ]);
    }

}
