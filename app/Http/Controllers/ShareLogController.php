<?php

namespace App\Http\Controllers;

use App\Models\ShareLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ShareLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $model = ShareLog::where('user_id', $user->id)->orderBy('id', 'DESC')->get();

            return DataTables::of($model)
                ->addIndexColumn()
                ->editColumn('recording_id', function ($row) {
                    return $row->recording?->title;
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 0 ? 'Failed' : 'Sent';
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if ($row->status == 0) {
                        $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm resend" data-id="' . $row->id . '">Resend</a>';
                    }
                    return $btn;
                })
                ->rawColumns(['status', 'action', 'conversation_id'])
                ->toJson();
        }
    }
}
