<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Recording;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use AshAllenDesign\ShortURL\Classes\Builder;
use Illuminate\Support\Facades\Auth;

class RecordingController extends Controller
{

    const TITLE = 'My Library';

    const VIEW = 'recording';

    const URL = 'recordings';

    const PATH = 'recordings';

    const TABLE = 'recordings';

    const ROUTE = 'recordings';

    protected $skip = ['id', 'created_at', 'updated_at'];

    protected $actions = [
        'edit' => true,
        'destroy' => true,
        'status' => true,
        'loginwith' => true,
    ];

    protected $validation = [
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required',
    ];

    public function __construct()
    {

        view()->share([
            'url' => url(self::URL),
            'title' => self::TITLE,
            'path' => self::PATH,
            'view' => self::VIEW,
        ]);
    }

    public function index()
    {
        $userId = login_id();
        $user = auth()->user();
        //$recordings = Recording::where('user_id', $userId)->latest()->paginate(getPaginate());
        $fields = getVariables();
        $tableFields = getTableColumns('share_logs', ['id', 'created_at', 'updated_at', 'deleted_at', 'user_id']);
        return view('recording.index', get_defined_vars());
    }

    public function store(Request $request)
    {

        $user = auth()->user();
        if (($user->plan->limit == $user->recordings->count()) || ($user->plan->limit < $user->recordings->count())) {
            return response()->json(['success' => false, 'message' => 'You have reached your limit.']);
        }

        $validator = Validator::make($request->all(), [
            'video' => 'required',
            'videoUrl' => 'nullable',
            'poster' => 'required',
            'posterUrl' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $recording = new Recording();
        $recording->user_id = $user->id;
        $recording->title = Carbon::now()->format('Y-m-d H:i:s');
        $recording->description = generateRandomQuote();
        $recording->file = $request->video;
        $recording->file_url = $request->videoUrl;

        try {
            $builder = new Builder();
            $shortURLObject = $builder->destinationUrl($request->videoUrl)->make();
            $shortURL = $shortURLObject->default_short_url;

            $recording->short_url = $shortURL;
        } catch (\Throwable $th) {
            report($th);
            $recording->short_url = null;
        }

        $recording->poster = $request->poster;
        $recording->poster_url = $request->posterUrl;
        $recording->status = $request->status;
        $recording->duration = $request->duration ?? null;
        $recording->size = $request->size ?? null;
        $recording->type = $request->type ?? null;
        $recording->make_it_private = $request->privacy ?? null;
        $recording->share = $request->share ?? null;
        $recording->embed = $request->embed ?? null;

        if ($recording->save()) {
            return response()->json(['success' => true, 'message' => 'Recording created successfully']);
        }
        return response()->json(['success' => false, 'message' => 'Error Occured while creating recording']);
    }


    public function show($id)
    {
        try {
            $id = decrypt($id);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error Occured while fetching recording.']);
        }
        $recording = Recording::findOrFail($id);
        return view('recording.show', get_defined_vars());
    }

    public function getData()
    {
        $user = Auth::user();
        $recordings = Recording::where('user_id', $user->id)->latest()->paginate(getPaginate());

        $data = [
            'recordings' => $recordings,
        ];

        return response()->json([
            'success' => true,
            'data' => $recordings
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            try {
                $id = decrypt($id);
            } catch (\Throwable $th) {
                // $id = $id;
                info($th->getMessage());
                return response()->json(['success' => false, 'message' => 'Error Occured while fetching recording.']);
            }

            $recording = Recording::findOrFail($id);

            $user = Auth::user();
            if ($recording->user_id != $user->id) {
                return response()->json(['success' => false, 'message' => 'You are not authorized to update this recording.']);
            }

            $recording->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);
            return response()->json(['success' => true, 'message' => 'Recording updated successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error Occured while updating recording.']);
        }
    }


    public function destroy($id)
    {
        try {
            $id = decrypt($id);
        } catch (\Throwable $th) {
            // $id = $id;
            info($th->getMessage());
            return response()->json(['success' => false, 'message' => 'Error Occured while fetching recording.']);
        }
        $recording = Recording::findOrFail($id);
        $recording->delete();
        return response()->json(['success' => true, 'message' => 'Recording deleted successfully.']);
    }
}
