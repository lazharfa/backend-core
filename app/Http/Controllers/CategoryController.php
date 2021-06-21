<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {

            $categories = Category::ofMember(env('APP_MEMBER'));

            if ($request->get('category_type')) {
                $categories = $categories->where('category_type', $request->get('category_type'));
            }

            if ($request->get('menu')) {
                $categories = $categories->where('is_menu', $request->get('menu'));
            }

            $categories = $categories->get();

            $res['status'] = 'success';
            $res['message'] = 'Successfully get categories';
            $res['data'] = $categories;
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
            $input = $request->all();
            $user = Auth::user();
            $input['user_id'] = $user->id;
            $input['member_id'] = env('APP_MEMBER');
            Category::create($input);
            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create category';
            $res['data'] = '';
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

            $category = Category::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail();
            $res['status'] = 'success';
            $res['message'] = 'Successfully get category';
            $res['data'] = $category;
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

            $category = Category::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail()->update($request->all());
            $res['status'] = 'success';
            $res['message'] = 'Successfully update category';
            $res['data'] = $category;
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

            Category::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail()->delete();
            $res['status'] = 'success';
            $res['message'] = 'Successfully delete category';
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
