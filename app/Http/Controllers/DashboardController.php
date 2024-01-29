<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    const TITLE = 'Dashboard';

    const VIEW = 'dashboard';

    const URL = 'dashboard';

    const PATH = 'dashboard';

    const TABLE = 'dashboard';

    const ROUTE = 'dashboard';

    protected $skip = ['id', 'created_at', 'updated_at'];

    protected $actions = [];

    protected $validation = [];

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
        return view(self::VIEW.'.index');
    }

    public function connect()
    {
        return view(self::VIEW.'.connect');
    }

    public function handleAuth($token, $res, $locurl)
    {
        $client = new Client(['http_errors' => false]);
        $headers = [
            'Authorization' => 'Bearer '.$token,
        ];
        $request = new Psr7Request('POST', $locurl, $headers);
        $res1 = $client->sendAsync($request)->wait();
        $red = $res1->getBody()->getContents();
        $red = json_decode($red);
        if ($red && property_exists($red, 'redirectUrl')) {
            // @file_get_contents($red->redirectUrl);
            $url = $red->redirectUrl;
            $parts = parse_url($url);
            parse_str($parts['query'], $query);
            $code = $query['code'];
            request()->code = $code;
            $res->crm_connected = ghl_token(request(), '', 'eee');
        }

        return $res;
    }

    // if (! $request->ajax() || ! $request->has(['location', 'token'])) {
    //     return false;
    // }
    public function authChecking(Request $req)
    {
        if ($req->ajax()) {
            if ($req->has('location') && $req->has('token')) {
                $location = $req->location;
                $user = User::where('location_id', $req->location)->first();
                if (! $user) {
                    // aapi call
                    $user = new User();
                    $user->first_name = 'Test';
                    $user->last_name = 'User';
                    $user->email = $location.'@gmail.com';
                    $user->password = bcrypt('shada2e3ewdacaeedd233edaf');
                    $user->location_id = $location;
                    $user->ghl_api_key = $req->token;
                    $user->role = 1;
                    // $user->save();

                    $user->save();
                }
                $user->ghl_api_key = $req->token;
                $user->save();
                request()->merge(['user_id' => $user->id]);
                session([
                    'location_id' => $user->location_id,
                    'uid' => $user->id,
                    'user_id' => $user->id,
                    'user_loc' => $user->location_id,
                ]);

                Cache::put('user_ids321', $user->id, 120);
                Auth::login($user);
                $res = new \stdClass;
                $res->user_id = $user->id;
                $res->location_id = $user->location_id ?? null;
                $res->is_crm = false;
                request()->user_id = $user->id;
                $res->token = $user->ghl_api_key;
                $token = get_setting($user->id, 'ghl_refresh_token');
                $res->crm_connected = false;
                if ($token) {
                    request()->code = $token;
                    $res->crm_connected = ghl_token(request(), '1', 'eee');
                    if (! $res->crm_connected) {
                        $res = ConnectOauth($req->location, $res->token);
                    }
                } else {
                    $res->crm_connected = ConnectOauth($req->location, $res->token);
                }
                $res->is_crm = $res->crm_connected;

                return response()->json($res);
            }

            return;
        }
    }

    public function authError()
    {
        return view(self::VIEW.'.error');
    }
}
