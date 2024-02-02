<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    const TITLE = 'Setting';

    const VIEW = 'setting';

    const URL = 'settings';

    const PATH = 'settings';

    const TABLE = 'settings';

    const ROUTE = 'settings';

    protected $skip = [];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $form_fields = [
            'client_id' => [
                'type' => 'text',
                'name' => 'crm_client_id',
                'label' => 'CRM Client Id',
                'value' => supersetting('crm_client_id'),
                'placeholder' => 'Client Client Id',
                'required' => true,
                'col' => 6,
                'extra' => '',
            ],
            'client_secret' => [
                'type' => 'text',
                'name' => 'crm_client_secret',
                'label' => 'CRM Client Secret',
                'value' => supersetting('crm_client_secret'),
                'placeholder' => 'CRM Client Secret',
                'required' => true,
                'col' => 6,
                'extra' => '',
            ],
            'company_logo' => [
                'type' => 'file',
                'name' => 'company_logo',
                'label' => 'Company Logo',
                'value' => setting('company_logo'),
                'placeholder' => 'Company Logo',
                'required' => true,
                'col' => 6,
                'extra' => '',
            ],
        ];

        return view(self::VIEW . '.index', get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            if ($request->hasFile($key)) {
                $value = uploadFile($request->file($key), 'uploads/logos', $key . '_' . time());
            }
            if (setting('company_logo')) {
                deleteFile(asset('/' . setting('company_logo')));
            }
            save_settings($key, $value);
        }

        return redirect()->back()->with('success', 'Settings saved successfully');
    }

    public function goHighLevelCallback(Request $request)
    {
        return ghl_token($request);
    }
}
