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

        return view(self::VIEW.'.index', get_defined_vars());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $formFields = getFormFields(self::TABLE, array_merge($this->skip, ['contact_id']));

        return view(self::VIEW.'.store', get_defined_vars());
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
            Log::error('Error Occured while creating contact on CRM '.$th->getMessage());

            return redirect()->route(self::ROUTE.'.index')->with('error', 'Failed to create '.self::TITLE);
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

        return redirect()->route(self::ROUTE.'.index')->with('success', self::TITLE.' created successfully');
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

        return view(self::VIEW.'.edit', get_defined_vars());
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
            ghl_api_call('contacts/'.$contact->contact_id, 'PUT', json_encode(capitalizeKeys($data)), [], true);
        } catch (\Throwable $th) {
            Log::error('Error Occured while updating contact on CRM '.$th->getMessage());
        }

        // Update the local contact
        $contact->update($data);

        return redirect()->route(self::ROUTE.'.index')->with('success', self::TITLE.' updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact)
    {
        try {
            ghl_api_call('contatcs/'.$contact->contact_id, 'DELETE');
        } catch (\Throwable $th) {
            Log::info('Error Occured while deleting Contact from crm : '.$th->getMessage());
        }
        $contact->delete();

        return redirect()->route(self::ROUTE.'.index')->with('success', self::TITLE.' deleted successfully');
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
            if (! empty($response->contacts)) {
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

            return $this->respondWithError('Error occurred while fetching contacts!'.$th->getMessage());
        }
    }
}
