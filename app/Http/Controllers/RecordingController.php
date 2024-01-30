<?php

namespace App\Http\Controllers;

use App\Models\Recording;
use Illuminate\Http\Request;

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
        $recordings = Recording::paginate(getPaginate());
        return view('recording.index', get_defined_vars());
    }

    public function store(Request $request)
    {
        dd($request->all());
    }
}
