<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class PageController extends Controller
{
    public function index()
    {
        try {

            $offset = 15;

            if (Input::get('offset')) {
                $offset = Input::get('offset');
            }

            $res['status'] = 'success';
            $res['message'] = 'Successfully get pages';
            $res['data'] = Page::ofMember(env('APP_MEMBER'))->paginate($offset);

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

            $request->request->add([
                'creator_id' => Auth::id(),
            ]);

            $page = Page::create($request->all());

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create page';
            $res['data'] = $page;
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

            $page = Page::ofMember(env('APP_MEMBER'))->where('page_slug', $slug)->firstOrFail();

            $res['status'] = 'success';
            $res['message'] = 'Successfully get page';
            $res['data'] = $page;
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

            $page = Page::ofMember(env('APP_MEMBER'))->where('page_slug', $slug)->firstOrFail();

            $request->request->add([
                'updater_id' => Auth::id()
            ]);

            $page->update($request->all());

            $res['status'] = 'success';
            $res['message'] = 'Successfully update page';
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
            Page::ofMember(env('APP_MEMBER'))->where('page_slug', $slug)->firstOrFail()->delete();

            $res['status'] = 'success';
            $res['message'] = 'Successfully delete page';
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
}
