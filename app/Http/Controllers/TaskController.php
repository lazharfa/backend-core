<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class TaskController extends Controller
{
    public function index()
    {

        $offset = 15;

        if (Input::get('offset')) {
            $offset = Input::get('offset');
        }

        $res['status'] = 'success';
        $res['message'] = 'Successfully get campaign realizations';
        $res['data'] = Task::ofMember(env('APP_MEMBER'))->latest()->paginate($offset);
        return response($res, 200);

    }

    public function store(Request $request)
    {

        try {

            DB::beginTransaction();

            $task = Task::create($request->all());

            DB::commit();
            $res['status'] = 'success';
            $res['message'] = 'Successfully create task';
            $res['data'] = $task;
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

            $task = Task::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail();

            $res['status'] = 'success';
            $res['message'] = 'Successfully get task';
            $res['data'] = $task;
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

            $task = Task::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail()->update($request->all());

            $res['status'] = 'success';
            $res['message'] = 'Successfully update task';
            $res['data'] = $task;
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

            $task = Task::ofMember(env('APP_MEMBER'))->where('id', $id)->firstOrFail();
            $task->delete();

            $res['status'] = 'success';
            $res['message'] = 'Successfully delete task';
            $res['data'] = $task;
            return response($res, 200);

        } catch (Exception $exception) {

            $res['status'] = 'error';
            $res['message'] = $exception->getMessage();
            $res['data'] = '';
            return response($res, 200);

        }
    }

}
