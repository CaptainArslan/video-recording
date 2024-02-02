<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Recording;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

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
        $user = User::findOrFail($userId);
        // dd($user->plans->toArray());
        $recordings = Recording::where('user_id', $userId)->latest()->paginate(getPaginate());
        return view('recording.index', get_defined_vars());
    }

    public function store(Request $request)
    {
        $recording = new Recording();
        $recording->user_id = login_id();

        $recording->title = Carbon::now()->format('Y-m-d H:i:s');

        $recording->description = generateRandomQuote();
        $recording->file = $request->video;
        $recording->file_url = $request->videoUrl;

        $recording->short_url = $request->shortUrl;

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
        $recording = Recording::findOrFail(decrypt($id));
        return view('recording.show', get_defined_vars());
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
            $recording = Recording::findOrFail($id);
            $recording->title = $request->title;
            $recording->description = $request->description;
            $recording->save();
            return response()->json(['success' => true, 'message' => 'Recording updated successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => 'Error Occured while updating recording.']);
        }
    }
}
