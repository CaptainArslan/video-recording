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
            $model = ShareLog::where('user_id', $user->id)->latest()->orderBy('id', 'DESC')->get();

            return DataTables::of($model)
                ->addIndexColumn()
                ->editColumn('recording_id', function ($row) {
                    return $row->recording?->title;
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 0 ? 'Failed' : 'Sent';
                })
                ->rawColumns(['status'])
                ->toJson();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ShareLog  $shareLog
     * @return \Illuminate\Http\Response
     */
    public function show(ShareLog $shareLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ShareLog  $shareLog
     * @return \Illuminate\Http\Response
     */
    public function edit(ShareLog $shareLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ShareLog  $shareLog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShareLog $shareLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShareLog  $shareLog
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShareLog $shareLog)
    {
        //
    }
}
