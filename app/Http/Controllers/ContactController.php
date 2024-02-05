<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Yajra\DataTables\DataTables;

class ContactController extends Controller
{
    const TITLE = 'Contacts';

    const VIEW = 'contact';

    const URL = 'contacts';

    const TABLE = 'contacts';

    const ROUTE = 'contacts';

    protected $skip = [
        'id',
        'user_id',
        // 'contact_id',
        'location_id',
        'contact_name',
        'company_name',
        'dnd',
        'type',
        'source',
        'assigned_to',
        'city',
        'state',
        'postal_code',
        'address1',
        'date_of_birth',
        'business_id',
        'tags',
        'followers',
        'country',
        'additional_emails',
        'attributions',
        'custom_fields',
        'date_added',
        'date_updated',
        'created_at',
        'updated_at',
    ];

    protected $actions = [
        'edit' => true,
        'destroy' => true,
    ];

    protected $validation = [
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email|unique:users',
        'phone' => 'required',
    ];

    public function __construct()
    {
        view()->share([
            'url' => url(self::URL),
            'title' => self::TITLE,
            'view' => self::VIEW,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->skip = array_merge($this->skip, ['password', 'image']);
        $tableFields = getTableColumns(self::TABLE, $this->skip);
        $tableFields = array_merge($tableFields, ['action' => 'Action']);
        if ($request->ajax()) {
            $model = Contact::where('user_id', login_id())->latest()->orderBy('id', 'DESC')->get();

            return DataTables::of($model)
                ->addIndexColumn()
                ->editColumn('action', function ($row) {
                    $dropdown = false;
                    $id = $row->id;
                    $actions = getActions($this->actions, self::ROUTE);
                    $actionhtml = view('components.form.action', get_defined_vars())->render();

                    return $actionhtml;
                })
                ->rawColumns(['action'])
                ->toJson();
        }

        return view(self::VIEW . '.index', get_defined_vars());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $formFields = getFormFields(self::TABLE, array_merge($this->skip, ['contact_id']));

        return view(self::VIEW . '.store', get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->validation);
        $location_id = setting('location_id');
        $request->merge([
            'location_id' => $location_id,
        ]);
        $data = json_encode(capitalizeKeys($request->only('first_name', 'last_name', 'email', 'phone', 'location_id')));
        $contact = null;
        try {
            $contact = ghl_api_call('contacts/', 'POST', $data, [], true);
        } catch (\Throwable $th) {
            Log::error('Error Occured while creating contact on CRM ' . $th->getMessage());

            return redirect()->route(self::ROUTE . '.index')->with('error', 'Failed to create ' . self::TITLE);
        }

        Contact::create([
            'user_id' => login_id(),
            'contact_id' => isset($contact->contact) ? $contact->contact->id : null,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'location_id' => $location_id,
        ]);

        return redirect()->route(self::ROUTE . '.index')->with('success', self::TITLE . ' created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Contact $contact)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Contact $contact)
    {
        $formFields = getFormFields(self::TABLE, array_merge($this->skip, ['contact_id']), $contact);

        return view(self::VIEW . '.edit', get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contact $contact)
    {
        $request->validate($this->validation);

        $location_id = setting('location_id');
        $request->merge(['location_id' => $location_id]);

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'location_id' => $location_id,
        ];

        // Log the API call
        try {
            ghl_api_call('contacts/' . $contact->contact_id, 'PUT', json_encode(capitalizeKeys($data)), [], true);
        } catch (\Throwable $th) {
            Log::error('Error Occured while updating contact on CRM ' . $th->getMessage());
        }

        // Update the local contact
        $contact->update($data);

        return redirect()->route(self::ROUTE . '.index')->with('success', self::TITLE . ' updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact)
    {
        try {
            ghl_api_call('contatcs/' . $contact->contact_id, 'DELETE');
        } catch (\Throwable $th) {
            Log::info('Error Occured while deleting Contact from crm : ' . $th->getMessage());
        }
        $contact->delete();

        return redirect()->route(self::ROUTE . '.index')->with('success', self::TITLE . ' deleted successfully');
    }

    /**
     * to sync contact of the CRM
     *
     * @return void
     */
    public function sync()
    {
        try {
            DB::beginTransaction();
            $response = ghl_api_call('contacts/', 'GET');
            if (!empty($response->contacts)) {
                // Contact::where('user_id', login_id())->delete();
                foreach ($response->contacts as $contact) {
                    Contact::updateOrCreate(
                        [
                            'user_id' => login_id(),
                            'contact_id' => $contact->id,
                        ],
                        [
                            'contact_id' => $contact->id,
                            'location_id' => $contact->locationId,
                            'contact_name' => $contact->contactName,
                            'first_name' => $contact->firstName,
                            'last_name' => $contact->lastName,
                            'company_name' => $contact->companyName,
                            'email' => $contact->email,
                            'phone' => $contact->phone,
                            'dnd' => $contact->dnd,
                            'type' => $contact->type,
                            'source' => $contact->source,
                            'assigned_to' => $contact->assignedTo,
                            'city' => $contact->city,
                            'state' => $contact->state,
                            'postal_code' => $contact->postalCode,
                            'address1' => $contact->address1,
                            'date_added' => $contact->dateAdded,
                            'date_updated' => $contact->dateUpdated,
                            'date_of_birth' => $contact->dateOfBirth,
                            'business_id' => $contact->businessId,
                            'tags' => json_encode($contact->tags),
                            'followers' => json_encode($contact->followers),
                            'country' => $contact->country,
                            'additional_emais' => json_encode($contact->additionalEmails),
                            'custom_fields' => json_encode($contact->customFields),
                        ]
                    );
                }
                DB::commit();

                return $this->respondWithSuccess(null, 'Contacts fetched successfully');
            }
        } catch (Throwable $th) {
            DB::rollBack();

            return $this->respondWithError('Error occurred while fetching contacts!' . $th->getMessage());
        }
    }


    public function contacts(Request $req, $ret = '')
    {


        $page = $req->page ?? 1;
        $query = $req->q  ?? $req->term ?? '';

        $login_id = login_id();
        $key = 'contacts' . $login_id;
        $contactsCache = cache()->get($key) ?? [];

        try {
            if (is_connected() == false) {
                // return response()->json(['error' => true, 'message' => "Please Connect to CRM"]);
            }
            $apiUrl = "contacts/?limit=100&query=$query&page=$page";
            $nextReq = false;
            $data = [];
            $total = 0;
            $contacts = ghl_api_call($apiUrl); // Make the API call
            if (is_string($contacts) && $contacts == 'No token') {
                $data[] = [
                    'id' => '-',
                    'text' => $contacts
                ];
            } else {

                if ($contacts) {
                    $total = $contacts->meta->total ?? 0;
                    if (property_exists($contacts, 'contacts') && count($contacts->contacts) > 0) {

                        foreach ($contacts->contacts as $contact) {
                            $name = $contact->contactName ?? "";
                            if (empty($name)) {
                                $name = "No Name - " . $contact->id;
                            }

                            $contactsCache[$contact->id] = $name;
                            $data[] = [
                                'id' => $contact->id,
                                'text' => $name,
                            ];
                        }

                        cache()->put($key, $contactsCache, 50);
                        if (property_exists($contacts, 'meta') && property_exists($contacts->meta, 'nextPageUrl') && property_exists($contacts->meta, 'nextPage') && !is_null($contacts->meta->nextPage) && !empty($contacts->meta->nextPageUrl)) {
                            $apiUrl = $contacts->meta->nextPageUrl;
                            $nextReq = true;
                        }
                    }
                }
            }
            if (count($data) == 0) {
                $nextReq = false;
            }


            return response()->json(['results' => $data, 'pagination' => ['more' => $nextReq], 'total_count' => $total]);
        } catch (\Throwable $th) {



            //throw $th;
            return response()->json(['success' => false, 'message' => 'Error Occured while fetching contacts ' . $th->getMessage()]);
        }
    }

    public function tags()
    {
        $tags = [];
        try {
            $response = ghl_api_call('tags', 'get');

            if ($response && property_exists($response, 'tags') && count($response->tags) > 0) {
                $tags = $response->tags;
            }
            return response()->json(['success' => true, 'message' => 'Contacts data', 'data' => $tags]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['success' => false, 'message' => 'Error Occured while fetching contacts']);
        }
    }

    function convFinder($contactid, $location, $data)
    {

        $res = ghl_api_call('conversations/search?contactId=' . $contactid, 'GET', '', [], false, true);
        $actsend = false;
        $conversationid = '';
        $type = $data['type'] ?? 'email';
        $type = strtolower($type);
        //'WhatsApp'=>$smsTemplate,
        $types = ['email' => ['type' => 'html', 'value' => 'Email'], 'sms' => ['type' => 'message', 'value' => 'SMS']];
        $msg = '';
        $contactName = $data['contactName'] ?? '';
        try {
            if ($res && property_exists($res, 'total')) {

                if ($res->total == 0) {
                    $gh_res = ghl_api_call('conversations/', 'POST', [
                        'form_data' => [
                            'locationId' => $location,
                            'contactId' => $contactid,
                        ]
                    ]);



                    if ($gh_res && property_exists($gh_res, 'conversation')) {
                        $conv = $gh_res->conversation->id;
                    } else {

                        return '';
                    }
                } else {
                    $conv = $res->conversations[0]->id;
                }

                $typeAct = $types[$type];
                $mt =  [
                    'conversationId' => $conv,
                    'contactId' => $contactid,
                    'type' => $typeAct['value'],
                    'subject' => $data['subject'] ?? 'Recording', // convert into one time function
                    $typeAct['type'] => $data['body'],


                ];

                if (!empty($senderemail)) {
                    $mt['emailFrom'] = $senderemail;
                }
                $res = ghl_api_call('conversations/messages', 'POST', [

                    'form_data' => $mt
                ]);


                if ($res && property_exists($res, 'conversionId')) {
                    $actsend = true;
                    $conversationid = $res->conversionId;
                }

                $msg = $res->msg ?? $res->message ?? json_encode($res);
            }
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            //throw $th;
        }
        $is_saved = $data['log_id'] ?? '';
        $shareLog = false;
        try {
            if (!empty($is_saved)) {
                $shareLog = \App\Models\ShareLog::find($is_saved);
            } else {
                $shareLog = \App\Models\ShareLog::where(['contact_id' => $contactid, 'recording_id' => $data['recording_id'], 'type' => $type])->first();
            }
            $msg = substr($msg, 0, 255);
            $subject = $data['subject'] ?? "";
            $tags = $data['all_tags'] ?? "";
            if (strlen($tags) > 255) {
                $tags = substr(($tags), 0, 255);
            }
            if ($shareLog) {
                if ($subject != '') {
                    $shareLog->subject = $subject;
                }

                if ($tags != '') {

                    $shareLog->all_tags = $tags;
                }

                $shareLog->status = $actsend;
                $shareLog->conversation_id = $conversationid;
                $shareLog->message = $msg;
            } else {


                $shareLog = new \App\Models\ShareLog();

                $shareLog->user_id = $data['login_id']; //foreign user
                $shareLog->contact_id = $contactid;
                $shareLog->contact_name = $contactName;
                $shareLog->type = $type;
                $shareLog->subject = $data['subject'] ?? "";
                $shareLog->body = $data['body'];
                $shareLog->recording_id = $data['recording_id']; //foreign recording
                $shareLog->all_tags = $tags;
                $shareLog->status = $actsend;
                $shareLog->conversation_id = $conversationid;
                $shareLog->message = $msg;
            }

            $shareLog->save();

            if ($contactName != '') {

                \App\Models\ShareLog::where(['contact_id' => $contactid])->update([
                    'contact_name' => $contactName
                ]);
            }
        } catch (\Throwable $th) {

            //throw $th;
        }
        return 'Added to Process';
    }

    function retryLog(Request $req, $id)
    {
        $log = \App\Models\ShareLog::with('user')->find($id);
        if ($log) {
            $data = [
                'type' => $log->type,
                'body' => $log->body,
                'subject' => $log->subject,
                'log_id' => $log->id
            ];
            $this->convFinder($log->contact_id, $log->user->location_id, $data);
            return redirect()->back()->with('success', 'Retry Processed');
        }
        return redirect()->back()->with('error', 'Log not found');
    }
    function processConv(Request $req)
    {

        $contacts = $req->contacts ?? '';
        $user = auth()->user();
        $contactCache = cache()->get('contacts' . $user->id) ?? [];
        $location = $user->location_id;
        $contacts = explode(',', $contacts) ?? [];
        $tags = $req->tags ?? '';
        $data = [
            'type' => $req->type ?? 'Email',
            'body' => $req->body,
            'subject' => $req->subject ?? 'Recording',
            'recording_id'  => $req->recording_id ?? '',
            'all_tags' => $tags,
            'login_id' => $user->id,

        ];

        $tags = explode(',', $tags) ?? [];
        $sharewith = $req->share ?? 'contact';
        // if ($sharewith == 'tags') {
        //     $contacts = [];
        //     foreach ($tags as $t) {
        //         $req->merge(['q' => $t]);
        //         $data = self::contacts($req, 'tag');
        //     }
        // }

        foreach ($contacts as $contactid) {

            if (empty($contactid)) {
                continue;
            }
            $data['contactName'] = $contactCache[$contactid] ?? '';
            self::convFinder($contactid, $location, $data);
        }
        return response()->json(['success' => true, 'message' => 'Process Completed']);
    }
}
