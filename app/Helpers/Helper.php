<?php

use Carbon\Carbon;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\GhlAuth;
use App\Models\Setting;
use Faker\Factory as Faker;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

function generateRandomQuote()
{
    $faker = Faker::create();
    return $faker->text;
}

function getPaginate($limit = null)
{

    if (!$limit) {
        $limit = env('RECORD_LIMIT') ?? 15;
    }
    return $limit;
}

function uploadFile($file, string $path, string $name): string
{
    return $file->move($path, $name . '.' . $file->getClientOriginalExtension())->getPathname();
}


// function uploadFileToHL($file)
function handleFile($file)
{
    $url = '';
    $dir = 'files';
    $imageName = $file->getClientOriginalName();

    $file->move(public_path('' . $dir), $imageName);
    $files = public_path($dir . '/' . $imageName);
    // $url = uploadFileToHL($file);
    // $url = checkAndCreateContact($files);
    $url = uploadMedia($files);
    // @unlink($files);

    return $url;
}

// upload file to high level
function uploadMedia($path)
{
    $filedata = [
        'form_multi' => [
            [
                'name' => 'file',
                'contents' => fopen($path, 'r'),
                'filename' => basename($path),
            ],
            [
                'name' => 'hosted',
                'contents' => '',
            ],
            [
                'name' => 'fileUrl',
                'contents' => '',
            ],
        ],
    ];
    $res = ghl_api_call('medias/upload-file', 'POST', $filedata);
    if ($res->fileId) {
        $fileurl = ghl_api_call('medias/files?query=' . basename($path));
        return $fileurl->files[0]->url;
    }
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
function is_company()
{
    return is_role() == 'company';
}
function is_role()
{
    $user = auth()->user() ?? session('uid');
    // if ($user) {
    //     $user = User::where('id', $user)->first();
    // }
    switch ($user->role ?? null) {
        case 0:
            return 'admin';
        case 1:
            return 'company';
        default:
            return 'user';
    }
}

function supersetting($key, $default = '')
{
    $setting = Setting::where(['user_id' => 1, 'key' => $key])->first();

    return $setting ? $setting->value : $default;
}

function login_id($id = "")
{
    if (!empty($id)) {
        return $id;
    }

    if (auth()->user()) {
        $id = auth()->user()->id;
    } elseif (session('uid')) {
        $id = session('uid');
    } elseif (Cache::has('user_ids321')) {
        $id = Cache::get('user_ids321');
    }

    return $id;
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
        if (!empty($user) && is_array($user)) {
            $user = (object) $user;
        }
    }
    $form = [];
    foreach ($fields as $key => $field) {
        $key1 = ucwords(str_replace('_', ' ', $key));
        $forcf = $table == 'plans' ? 'plans' : $forcf;
        $form[$key] = createField($key, getFieldType($key), $field, $field, true, $user->$key ?? '', $col = 6, getoptions(getFieldType($key), $key, $user->id ?? '', $forcf), $checkcf = null);
    }

    return $form;
}

function getTableColumns1($table, $skip = [], $showcoltype = false)
{

    $columns = DB::getSchemaBuilder()->getColumnListing($table);
    if (!empty($skip)) {
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
    if (!empty($skip)) {
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
        'label' => $label . $extra,
        'placeholder' => $placeholder,
        'required' => $type == 'file' ? false : $required,
        'value' => $value,
        'col' => $col,
    ];

    if ($type == 'select' && !empty($options)) {
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
    } elseif (strpos($type, 'status') !== false) {
        return 'select';
    }
}

// function getoptions($type, $key, $id, $forcf)
// {
//     if ($forcf) {
//         return get_ghl_customFields();
//     } else {
//         return [];
//     }
// }
function getoptions($type, $key, $id, $class)
{
    $type = strtolower($type);
    // if ($class == 'plans' && $key = 'list') {
    //     return \App\Models\Plan::pluck('title', 'id')->toArray();
    // }
    if (strpos($type, 'select') !== false && $key == 'status') {
        // return User::pluck('first_name', 'id')->toArray();
        $status = ['1' => 'Active', '0' => 'Inactive'];
        if ($class == 'plans') {
            $status['2'] = 'Active - Default';
        }
        return $status;
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
        return 'No token';
    }

    $baseurl = 'services.leadconnectorhq.com/';
    $url = str_replace([$baseurl, 'https://', 'http://'], '', $url);
    $baseurl = 'https://' . $baseurl;
    $version = get_default_settings('oauth_ghl_version', '2021-07-28');
    $location = get_setting($userId, 'location_id');
    $headers['Version'] = $version;

    if ((strpos($url, 'custom') !== false || strpos($url, 'tags') !== false) && strpos($url, 'locations/') === false) {
        $url = 'locations/' . $location . '/' . $url;
    }
    if (strtolower($method) == 'get') {
        $urlap = (strpos($url, '?') !== false) ? '&' : '?';
        if (strpos($url, 'location_id=') === false && strpos($url, 'locationId=') === false && strpos($url, 'locations/') === false) {
            $url .= $urlap;
            $url .= 'locationId=' . $location;
        }
    }

    if ($token) {
        $headers['Authorization'] = $bearer . $token;
    }
    $headers['Content-Type'] = 'application/json';

    $client = new \GuzzleHttp\Client(['http_errors' => false, 'headers' => $headers]);
    // dd($client);
    $options = [];
    if (!empty($data)) {
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

    $url1 = $baseurl . $url;

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

    // if ($bd && isset($bd->error) && $bd->error == 'Unauthorized') {
    if ($bd && isset($bd->error) &&  strtolower($bd->error) == 'unauthorized'  && strpos(strtolower($bd->message), 'authclass') === false) {
        request()->code = get_setting($userId, 'ghl_refresh_token');
        if (strpos($bd->message, 'access') !== false && (strpos($bd->message, 'expired') !== false || stripos($bd->message, 'invalid') !== false)) {
            if (empty(request()->code)) {
                response()->json(['Refresh token no longer exists'])->send();
                exit();
            }
            $tok = ghl_token(request(), '1');
            if (!$tok) {
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

    if (!$code || !property_exists($code, 'access_token')) {
        return null;
    }

    $loc = $code->locationId ?? $request->location ?? '';
    $user = User::where('location_id', $loc)->orWhere('id', login_id())->first();

    if (!$user) {
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

if (!function_exists('ghl_oauth_call')) {
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
            $postv .= $key . '=' . $value;
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

            if (!is_null($userid)) {
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
            'route' => $route . '.' . $key,
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
    $locurl = 'https://services.msgsndr.com/oauth/authorize?location_id=' . $loc . '&response_type=code&userType=Location&redirect_uri=' . $callbackurl . '&client_id=' . superSetting('crm_client_id') . '&scope=calendars.readonly calendars/events.write calendars/groups.readonly calendars/groups.write campaigns.readonly conversations.readonly conversations.write conversations/message.readonly conversations/message.write contacts.readonly contacts.write forms.readonly forms.write links.write links.readonly locations.write locations.readonly locations/customValues.readonly locations/customValues.write locations/customFields.readonly locations/customFields.write locations/tasks.readonly locations/tasks.write locations/tags.readonly locations/tags.write locations/templates.readonly medias.readonly medias.write opportunities.readonly opportunities.write surveys.readonly users.readonly users.write workflows.readonly snapshots.readonly oauth.write oauth.readonly calendars/events.readonly calendars.write businesses.write businesses.readonly';

    $client = new Client(['http_errors' => false]);
    $headers = [
        'Authorization' => 'Bearer ' . $token,
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

function formatTimestamp($timestamp, $format)
{
    // Check if the input is a valid timestamp
    try {
        if (strtotime($timestamp) !== false) {
            // Convert timestamp to the desired format
            return date($format, strtotime($timestamp));
        } else {
            // If input is not a valid timestamp, return it unchanged
            return $timestamp;
        }
    } catch (\Exception $e) {
        return $timestamp;
    }
}
//Findoppurtunity



function getVariables()
{
    $type = 'location_id';
    // $user_id = session()->get('uid');
    $user_id = auth()->user()->id;

    $res = Setting::where(['user_id' => $user_id, 'key' => $type])->first();
    $value = $res->value ?? null;
    $loc_customValues = null;
    $loc_customFields = null;
    if ($value) {
        $loc_customValues = ghl_api_call('locations/' . $value .
            '/customValues', 'get', '', [], false, true);
        $loc_customFields = ghl_api_call('locations/' . $value .
            '/customFields', 'get', '', [], false, true);
    }


    // $location = ghl_api_call('locations/' . $value, 'get', '', [], false, true);
    // $location = $location->location;

    $customValuseKeys = [];
    foreach ($loc_customValues->customValues ?? [] as $lv) {

        $customValuseKeys[] = replace_all_cv($lv->fieldKey, false);
    }
    // dd($loc_customValues->customValues,$customValuseKeys);


    $locfields = $loc_customFields->customFields ?? [];
    $fieldkeys = [];
    foreach ($locfields as $lvv) {
        // $fieldkeys[] = '{{ '.$lvv->fieldKey.' }}';
        $fieldkeys[] = replace_all_cv($lvv->fieldKey, false);
    }


    $defkeys = [
        "{{contact.name}}",
        "{{contact.first_name}}",
        "{{contact.last_name}}",
        "{{contact.email}}",
        "{{contact.phone}}",
        "{{contact.phone_raw}}",
        "{{contact.company_name}}",
        "{{contact.full_address}}",
        "{{contact.address1}}",
        "{{contact.city}}",
        "{{contact.state}}",
        "{{contact.country}}",
        "{{contact.postal_code}}",
        "{{contact.timezone}}",
        "{{contact.date_of_birth}}",
        "{{contact.source}}",
        "{{contact.website}}",
        "{{contact.id}}",
        "{{user.name}}",
        "{{user.first_name}}",
        "{{user.last_name}}",
        "{{user.email}}",
        "{{user.phone}}",
        "{{user.phone_raw}}",
        "{{user.email_signature}}",
        "{{user.calendar_link}}",
        "{{user.twilio_phone_number}}",
        "{{appointment.title}}",
        "{{appointment.start_time}}",
        "{{appointment.only_start_date}}",
        "{{appointment.only_start_time}}",
        "{{appointment.end_time}}",
        "{{appointment.only_end_date}}",
        "{{appointment.only_end_time}}",
        "{{appointment.day_of_week}}",
        "{{appointment.month}}",
        "{{appointment.month_name}}",
        "{{appointment.timezone}}",
        "{{appointment.cancellation_link}}",
        "{{appointment.reschedule_link}}",
        "{{appointment.meeting_location}}",
        "{{appointment.notes}}",
        "{{appointment.add_to_google_calendar}}",
        "{{appointment.add_to_ical_outlook}}",
        "{{appointment.recurring.repeats}}",
        "{{appointment.recurring.times_to_repeat}}",
        "{{appointment.user.name}}",
        "{{appointment.user.first_name}}",
        "{{appointment.user.last_name}}",
        "{{appointment.user.email}}",
        "{{appointment.user.phone}}",
        "{{appointment.user.phone_raw}}",
        "{{appointment.user.email_signature}}",
        "{{appointment.user.twilio_phone_number}}",
        "{{calendar.name}}",
        "{{message.body}}",
        "{{message.subject}}",
        "{{location.name}}",
        "{{location.full_address}}",
        "{{location.address}}",
        "{{location.city}}",
        "{{location.state}}",
        "{{location.country}}",
        "{{location.postal_code}}",
        "{{location.email}}",
        "{{location.phone}}",
        "{{location.phone_raw}}",
        "{{location.website}}",
        "{{location.logo_url}}",
        "{{location_owner.first_name}}",
        "{{location_owner.last_name}}",
        "{{location_owner.email}}",
        "{{location.id}}",
        "{{right_now.second}}",
        "{{right_now.minute}}",
        "{{right_now.hour}}",
        "{{right_now.hour_ampm}}",
        "{{right_now.time}}",
        "{{right_now.time_ampm}}",
        "{{right_now.ampm}}",
        "{{right_now.day}}",
        "{{right_now.day_of_week}}",
        "{{right_now.month}}",
        "{{right_now.month_name}}",
        "{{right_now.month_english}}",
        "{{right_now.year}}",
        "{{right_now.middle_endian_date}}",
        "{{right_now.little_endian_date}}",
        "{{right_now.date}}",
        "{{contact.attributionSource.sessionSource}}",
        "{{ contact.attributionSource.url }}",
        "{{contact.attributionSource.campaign}}",
        "{{contact.attributionSource.utmSource}}",
        "{{contact.attributionSource.utmMedium}}",
        "{{contact.attributionSource.utmContent}}",
        "{{contact.attributionSource.referrer}}",
        "{{contact.attributionSource.campaignId}}",
        "{{contact.attributionSource.fbclid}}",
        "{{contact.attributionSource.gclid}}",
        "{{contact.attributionSource.utmCampaign}}",
        "{{contact.attributionSource.utmKeyword}}",
        "{{contact.attributionSource.utmMatchType}}",
        "{{contact.attributionSource.adGroupId}}",
        "{{contact.attributionSource.adId}}",
        "{{contact.lastAttributionSource.sessionSource}}",
        "{{ contact.lastAttributionSource.url }}",
        "{{contact.lastAttributionSource.campaign}}",
        "{{contact.lastAttributionSource.utmSource}}",
        "{{contact.lastAttributionSource.utmMedium}}",
        "{{contact.lastAttributionSource.utmContent}}",
        "{{contact.lastAttributionSource.referrer}}",
        "{{contact.lastAttributionSource.campaignId}}",
        "{{contact.lastAttributionSource.fbclid}}",
        "{{contact.lastAttributionSource.gclid}}",
        "{{contact.lastAttributionSource.utmCampaign}}",
        "{{contact.lastAttributionSource.utmKeyword}}",
        "{{contact.lastAttributionSource.utmMatchType}}",
        "{{contact.lastAttributionSource.adGroupId}}",
        "{{contact.lastAttributionSource.adId}}"
    ];
    // Combining the keys of Custom Values & CustomFields
    $c = array_merge($customValuseKeys, $fieldkeys, $defkeys);


    // $lockey = variablesforcontact(true);
    // foreach (variablesforcontact() as $key => $field) {

    //     $c[] = replace_all_cv($field, false, $lockey);
    // }
    // $lockey = datefields(true);
    // foreach (datefields() as $key => $field) {

    //     $c[] = replace_all_cv($field, false, $lockey);
    // }
    // $lockey = locationfields(true);
    // foreach (locationfields() as $key => $field) {

    //     $c[] = replace_all_cv($field, false, $lockey);
    // }
    // $lockey = userfields(true);
    // foreach (userfields() as $key => $field) {

    //     $c[] = replace_all_cv($field, false, $lockey);
    // }
    // $data = ['uId' => $user_id];

    return $c;

    // return [$c, $loc_customValues, $loc_customFields];
    // $header_templates = HeaderTemplate::where($data)->get() ?? [];
    // $footer_templates = FooterTemplate::where($data)->get() ?? [];

    // return [$c, $loc_customValues, $loc_customFields, $header_templates, $footer_templates];
}


function replace_all_cv($cft, $append = true, $prefix = '')
{

    $key = str_replace(['{{ ', ' }}', ' '], '', $cft);
    if ($append) {


        return '{{ ' . $prefix . $key . ' }}';
    }

    return $prefix . $key;
}

function variablesforcontact($keyonly = false)
{
    if ($keyonly) {
        return 'contact.';
    }
    //display on the selector side as foreach with like this replace_all_cv($val,true,'contact.');
    return [
        'id', 'firstName', 'lastName', 'country', 'city', 'state', 'address1', 'companyName', 'source',
        'website', 'postalCode', 'dateOfBirth', 'email', 'phone', 'tags', 'fullName'
    ];
}

function datefields($keyonly = false)
{
    if ($keyonly) {
        return 'date.';
    }
    // 'date.';
    return ['onlydate', 'onlytime', 'now'];
}

function locationfields($keyonly = false)
{
    if ($keyonly) {
        return 'location.';
    }
    // 'date.';
    return ['name', 'email', 'phone', 'country', 'city', 'state', 'address', 'postalCode'];
}

function userfields($keyonly = false)
{
    if ($keyonly) {
        return 'user.';
    }
    // 'date.';
    return ['name', 'email', 'phone'];
}
