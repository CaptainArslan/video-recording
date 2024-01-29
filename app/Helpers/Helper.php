<?php

use App\Models\GhlAuth;
use App\Models\Setting;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

function uploadFile($file, string $path, string $name): string
{
    return $file->move($path, $name.'.'.$file->getClientOriginalExtension())->getPathname();
}

function deleteFile($filePath)
{
    $fullPath = public_path($filePath);

    if (file_exists($fullPath)) {
        unlink($fullPath);

        return true;
    }

    return false;
}

function setting(string $key, $default = null)
{
    $setting = Setting::where(['user_id' => login_id(), 'key' => $key])->first();

    return $setting ? $setting->value : $default;
}

function is_role()
{
    $user = auth()->user()->id ?? session('uid');
    if ($user) {
        $user = User::where('id', $user)->first();
        switch ($user->role ?? null) {
            case 0:
                return 'admin';
            case 1:
                return 'company';
            default:
                return 'user';
        }
    }
}

function supersetting($key, $default = '')
{
    $setting = Setting::where(['user_id' => 1, 'key' => $key])->first();

    return $setting ? $setting->value : $default;
}

function login_id(string $id = '')
{
    return $id ?: (string) (Auth::user()->id ?: session('uid') ?: Cache::get('user_ids321', ''));
}

function getFormFields($table, $skip = [], $user = '', $forcf = false, $checkcf = false)
{
    if ($checkcf) {
        $fields = getTableColumns($table, $skip);
        $setuser = [];
        $user = (object) $user;
        foreach ($user as $u) {
            $setuser[$u->key] = $u->value;
        }
        $user = (object) $setuser;
    } else {
        $fields = getTableColumns1($table, $skip);
        if (! empty($user) && is_array($user)) {
            $user = (object) $user;
        }
    }
    $form = [];
    foreach ($fields as $key => $field) {
        $key1 = ucwords(str_replace('_', ' ', $key));
        $form[$key] = createField($key, getFieldType($key), $field, $field, true, $user->$key ?? '', $col = 6, getoptions(getFieldType($key), $key, $user->id ?? '', $forcf), $checkcf = null);
    }

    return $form;
}

function getTableColumns1($table, $skip = [], $showcoltype = false)
{

    $columns = DB::getSchemaBuilder()->getColumnListing($table);
    if (! empty($skip)) {
        $columns = array_diff($columns, $skip);
    }

    $cols = [];
    foreach ($columns as $key => $column) {
        $cols[$column] = ucwords(str_replace('_', ' ', $column));
    }

    return $cols;
}

function getTableColumns($table, $skip = [], $showcoltype = false)
{
    $columns = DB::getSchemaBuilder()->getColumnListing($table);
    if (! empty($skip)) {
        $columns = array_diff($columns, $skip);
    }

    $cols = [];
    foreach ($columns as $key => $column) {
        $cols[$column] = ucwords(str_replace('_', ' ', $column));
    }

    return $cols;
}

function createField($field1, $type = 'text', $label = '', $placeholder = '', $required = false, $value = '', $col = 12, $options = [], $enrollment_id = null)
{
    if ($type == 'select' && empty($options)) {
        $type = 'text';
        $required = false;
    }
    $extra = '';

    $field = [
        'type' => $type,
        'name' => $field1,
        'label' => $label.$extra,
        'placeholder' => $placeholder,
        'required' => $type == 'file' ? false : $required,
        'value' => $value,
        'col' => $col,
    ];

    if ($type == 'select' && ! empty($options)) {
        $field['options'] = $options;
        $field['is_select2'] = true;
        $field['is_multiple'] = false;
    }

    return $field;
}

function getFieldType($type)
{
    $type = strtolower($type);
    if (strpos($type, 'email') !== false) {
        return 'email';
    } elseif (strpos($type, 'password') !== false) {
        return 'password';
    } elseif (strpos($type, 'image') !== false) {
        return 'file';
    } elseif (strpos($type, 'new_puppy_name') !== false || strpos($type, 'puppy_breed') !== false || strpos($type, 'puppy_dob') !== false || strpos($type, 'microchip_number') !== false || strpos($type, 'pickup_date') !== false) {
        return 'select';
    }
}

function getoptions($type, $key, $id, $forcf)
{
    if ($forcf) {
        return get_ghl_customFields();
    } else {
        return [];
    }
}

function get_ghl_customFields()
{
    $allcustomfield = [];
    if (is_connected() == false) {
        return $allcustomfield;
    }

    $customfields = ghl_api_call('customFields');
    if ($customfields && property_exists($customfields, 'customFields')) {
        foreach ($customfields->customFields as $field) {
            if (in_array($field->dataType, ['TEXT', 'LARGE_TEXT', 'DATE'])) {
                if ($field->fieldKey) {
                    $field->fieldKey = str_replace(['{', '}'], '', $field->fieldKey);
                    $parts = explode('.', $field->fieldKey);
                    $allcustomfield[$parts[1]] = ucfirst(strtolower($field->name));
                }
            }
        }
    }

    return $allcustomfield;
}

function is_connected(): bool
{
    $user = GhlAuth::where('user_id', login_id())->first();

    return $user ? true : false;
}

function get_setting($id, $type): ?string
{
    $res = Setting::where(['user_id' => $id,  'key' => $type])->first();

    return $res ? $res->value : null;
}

function get_default_settings($j, $k)
{
    return $k;
}

function ghl_api_call($url = '', $method = 'get', $data = '', $headers = [], $json = false, $is_v2 = true)
{
    //$baseurl = 'https://rest.gohighlevel.com/v1/';
    $bearer = 'Bearer ';
    $userId = login_id();
    $token = get_setting($userId, 'ghl_access_token');

    if (empty($token)) {
        if (session('cronjob')) {
            return false;
        }
        exit('No Token');
    }

    $baseurl = 'services.leadconnectorhq.com/';
    $url = str_replace([$baseurl, 'https://', 'http://'], '', $url);
    $baseurl = 'https://'.$baseurl;
    $version = get_default_settings('oauth_ghl_version', '2021-07-28');
    $location = get_setting($userId, 'location_id');
    $headers['Version'] = $version;

    if (strpos($url, 'custom') !== false && strpos($url, 'locations/') === false) {
        $url = 'locations/'.$location.'/'.$url;
    }
    if (strtolower($method) == 'get') {
        $urlap = (strpos($url, '?') !== false) ? '&' : '?';
        if (strpos($url, 'location_id=') === false && strpos($url, 'locationId=') === false && strpos($url, 'locations/') === false) {
            $url .= $urlap;
            $url .= 'locationId='.$location;
        }
    }
    if ($token) {
        $headers['Authorization'] = $bearer.$token;
    }
    $headers['Content-Type'] = 'application/json';

    $client = new \GuzzleHttp\Client(['http_errors' => false, 'headers' => $headers]);
    // dd($client);
    $options = [];
    if (! empty($data)) {
        $keycheck = 'form_data';
        $keycheck1 = 'form_multi';
        if (isset($data[$keycheck]) && is_array($data[$keycheck])) {
            $options['form_params'] = $data[$keycheck];
        } elseif (isset($data[$keycheck1]) && is_array($data[$keycheck1])) {
            $options[RequestOptions::MULTIPART] = $data[$keycheck1];
        } else {
            $options['body'] = $data;
        }
    }

    $url1 = $baseurl.$url;

    $bd = null;
    try {
        $response = $client->request($method, $url1, $options);
        $bd = $response->getBody()->getContents();
        $bd = json_decode($bd);
    } catch (ConnectException $e) {
        // log the error here
        Log::Warning('guzzle_connect_exception', [
            'url' => $url1,
            'message' => $e->getMessage(),
        ]);
    } catch (RequestException $e) {

        Log::Warning('guzzle_connection_timeout', [
            'url' => $url1,
            'message' => $e->getMessage(),
        ]);
    }

    if ($bd && isset($bd->error) && $bd->error == 'Unauthorized') {
        request()->code = get_setting($userId, 'ghl_refresh_token');
        if (strpos($bd->message, 'expired') !== false) {
            if (empty(request()->code)) {
                response()->json(['Refresh token no longer exists'])->send();
                exit();
            }
            $tok = ghl_token(request(), '1');
            if (! $tok) {
                response()->json(['Invalid Refresh token'])->send();

                return $bd;
            }
            sleep(1);

            return ghl_api_call($url, $method, $data, $headers, $json, $is_v2);
        }
    }

    return $bd;
}

function ghl_token($request, $type = '', $method = 'view')
{
    $code = $request->code ?? $request;
    $code = ghl_oauth_call($code, $type);

    if (! $code || ! property_exists($code, 'access_token')) {
        return null;
    }

    $loc = $code->locationId ?? $request->location ?? '';
    $user = User::where('location_id', $loc)->orWhere('id', login_id())->first();

    if (! $user) {
        if ($type == 1) {
            return false;
        }
        abort(redirect()->route('auth.check'));
    }

    $ui = $user->id;

    session()->put('ghl_api_token', $code->access_token);
    session()->put('ghl_location_id', $loc);
    save_auth($code, $type, $ui);

    if ($method == 'view') {
        abort(redirect()->route('dashboard')->with('success', 'connected'));
    } else {
        return true;
    }
}

if (! function_exists('ghl_oauth_call')) {
    function ghl_oauth_call($code = '', $method = '')
    {
        $url = 'https://api.msgsndr.com/oauth/token';
        $curl = curl_init();
        $data = [];
        $data['client_id'] = supersetting('crm_client_id');
        $data['client_secret'] = supersetting('crm_client_secret');
        $md = empty($method) ? 'code' : 'refresh_token';
        $data[$md] = $code;
        $data['grant_type'] = empty($method) ? 'authorization_code' : 'refresh_token';
        $postv = '';
        $x = 0;
        foreach ($data as $key => $value) {
            if ($x > 0) {
                $postv .= '&';
            }
            $postv .= $key.'='.$value;
            $x++;
        }
        $curlfields = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postv,
        ];
        curl_setopt_array($curl, $curlfields);
        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);

        return $response;
    }
}

// function save_auth($code, $type = 'code', $userid = null)
// {
//     $user_id = is_null($userid) ? login_id() : $userid;

//     $data = [
//         'ghl_access_token' => $code->access_token,
//         'ghl_refresh_token' => $code->refresh_token,
//         'location_id' => empty($type) ? ($code->locationId ?? $user_id) : null,
//         'user_type' => empty($type) ? ($code->userType ?? 'Location') : null,
//     ];

//     $auth = GhlAuth::updateOrCreate(
//         ['user_id' => $user_id],
//         $data
//     );

//     $save = save_in_settings(['user_id' => $user_id] + $data, $userid);
//     var_dump($save);
//     return $auth;
// }

function save_auth($code, $type = 'code', $userid = null)
{
    if (is_null($userid)) {
        $user_id = login_id();
    } else {
        $user_id = $userid;
    }

    $data = [
        'ghl_access_token' => $code->access_token,
        'ghl_refresh_token' => $code->refresh_token,
        'user_id' => $user_id,
    ];

    if (empty($type)) {
        $data['location_id'] = $code->locationId ?? $user_id;
        $data['user_type'] = $code->userType ?? 'Location';
    }

    $auth = GhlAuth::updateOrCreate(
        ['user_id' => $user_id],
        $data
    );
    $data['user_id'] = $user_id;
    save_in_settings($data, $userid);

    return $auth;
}

function save_in_settings($data, $userid = null)
{
    DB::beginTransaction();

    try {
        foreach ($data as $key => $value) {
            $conditions = [
                'key' => $key,
            ];

            if (! is_null($userid)) {
                $conditions['user_id'] = $userid;
            }

            Setting::updateOrCreate(
                $conditions,
                [
                    'value' => $value,
                ]
            );
        }

        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback the transaction if any exception occurs
        throw $e; // Re-throw the exception to propagate it up the call stack
    }

    return true;
}

function save_settings($key, $value = '', $userid = null)
{
    $user_id = $userid ? $userid : (auth()->id() ? auth()->id() : session('uid'));

    return Setting::updateOrCreate(
        [
            'user_id' => $user_id,
            'key' => $key,
        ],
        [
            'value' => $value,
        ]
    );
}

function getActions(array $actions = [], string $route = '')
{
    $acs = [];
    foreach ($actions as $key => $action) {
        $acs[$key] = [
            'title' => ucwords(str_replace('_', ' ', $key)),
            'route' => $route.'.'.$key,
            'extraclass' => $key == 'delete' ? 'confirm-delete deleted' : '',
        ];
    }

    return $acs;
}

function capitalizeKeys($array)
{
    $result = [];

    foreach ($array as $key => $value) {
        $convertedKey = convertLocationId($key);
        $result[$convertedKey] = $value;
    }

    return $result;
}

function convertLocationId($key)
{
    // Assuming 'location_id' is the format you want to convert
    // to 'locationId'. Adjust this conversion logic as needed.
    $parts = explode('_', $key);
    $convertedKey = '';

    foreach ($parts as $part) {
        $convertedKey .= ucfirst($part);
    }

    return lcfirst($convertedKey);
}
function ConnectOauth($loc, $token, $method = '')
{
    $tokenx = false;
    $callbackurl = route('authorization.gohighlevel.callback');
    $locurl = 'https://services.msgsndr.com/oauth/authorize?location_id='.$loc.'&response_type=code&userType=Location&redirect_uri='.$callbackurl.'&client_id='.superSetting('crm_client_id').'&scope=calendars.readonly calendars/events.write calendars/groups.readonly calendars/groups.write campaigns.readonly conversations.readonly conversations.write conversations/message.readonly conversations/message.write contacts.readonly contacts.write forms.readonly forms.write links.write links.readonly locations.write locations.readonly locations/customValues.readonly locations/customValues.write locations/customFields.readonly locations/customFields.write locations/tasks.readonly locations/tasks.write locations/tags.readonly locations/tags.write locations/templates.readonly medias.readonly medias.write opportunities.readonly opportunities.write surveys.readonly users.readonly users.write workflows.readonly snapshots.readonly oauth.write oauth.readonly calendars/events.readonly calendars.write businesses.write businesses.readonly';

    $client = new Client(['http_errors' => false]);
    $headers = [
        'Authorization' => 'Bearer '.$token,
    ];
    $request = new Request('POST', $locurl, $headers);
    $res1 = $client->sendAsync($request)->wait();
    $red = $res1->getBody()->getContents();
    $red = json_decode($red);
    if ($red && property_exists($red, 'redirectUrl')) {
        $url = $red->redirectUrl;
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        // dd($query);
        $tokenx = ghl_token($query['code'] ?? '', '', 'eee3');
    }

    return $tokenx;
}
