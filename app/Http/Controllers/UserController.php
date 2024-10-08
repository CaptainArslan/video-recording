<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    const TITLE = 'Users';

    const VIEW = 'user';

    const URL = 'users';

    const PATH = 'users';

    const TABLE = 'users';

    const ROUTE = 'users';

    protected $skip = ['id', 'first_name', 'last_name',  'remember_token', 'created_at', 'updated_at', 'email_verified_at', 'added_by', 'ghl_api_key'];

    protected $actions = [
        'edit' => true,
        // 'destroy' => true,
        // 'status' => true,
        // 'loginwith' => true,
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

    public function index(Request $request)
    {
        $this->skip = array_merge($this->skip, ['password', 'image', 'role', 'email']);
        $tableFields = getTableColumns(self::TABLE, $this->skip);
        $tableFields = array_merge($tableFields, ['action' => 'Action']);
        if ($request->ajax()) {
            $model = User::where('role', '!=', 0)->latest()->orderBy('id', 'DESC')->get();

            return DataTables::of($model)
                ->addIndexColumn()
                ->editColumn('action', function ($row) {
                    $dropdown = false;
                    $id = $row->id;
                    $actions = getActions($this->actions, self::ROUTE);
                    $actionhtml = view('components.form.action', get_defined_vars())->render();

                    return $actionhtml;
                })
                ->editColumn('plan_id', function ($row) {
                    return $row->plan?->title;
                })
                ->editColumn('status', function ($row) {
                    return $row->getStatus();
                })
                ->rawColumns(['action', 'status', 'role', 'plan_id'])
                ->toJson();
        }

        return view(self::VIEW . '.index', get_defined_vars());
    }

    public function create()
    {
        $this->skip = array_merge($this->skip, ['status', 'role', 'name', 'image']);
        $formFields = getFormFields(self::TABLE, $this->skip);

        return view(self::VIEW . '.store', get_defined_vars());
    }

    public function store(Request $request)
    {
        return redirect()->route(self::ROUTE . '.index')->with('success', 'User created successfully');
    }

    public function show($id)
    {
    }

    public function edit(User $user)
    {
        $plans = Plan::get();
        return view(self::VIEW . '.edit', get_defined_vars());
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'plan_id' => 'required',
        ]);

        $user->update(['plan_id' => $request->plan_id]);
        return redirect()->back()->with('success', 'User plan updated successfully');
    }

    public function destroy(User $user)
    {

        exit;
        // $user = User::findOrFail($id);

    }

    public function status(User $user)
    {
        // $user = User::find($id);
        $user->status = !$user->status;
        $user->save();

        return redirect()->route(self::ROUTE . '.index')->with('success', ' Status updated successfully');
    }

    public function profile(Request $request)
    {
        $this->skip = array_merge($this->skip, ['status', 'role', 'password', 'plan_id']);
        $formFields = getFormFields(self::TABLE, $this->skip, Auth::user());

        $passwordFields = $this->getPasswordFields();

        return view(self::VIEW . '.profile', get_defined_vars());
    }

    public function updateProfile(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();

        if ($request->hasFile('image')) {
            if ($user->image) {
                deleteFile('uploads/profile' . $user->image);
            }
            $user->image = uploadFile($request->file('image'), 'uploads/profile', $request->first_name . '-' . $request->last_name . '-' . time());
        }

        $user->fill([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->name,
            'email' => $request->email,
            'ghl_api_key' => $request->ghl_api_key,
        ])->save();

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function password()
    {
        $passwordFields = $this->getPasswordFields();

        return view(self::VIEW . '.password', get_defined_vars());
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|password',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        if (Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', 'Password Does Not matched!');
        }
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route(self::ROUTE . '.index')->with('success', 'Password updated Successfully!');
    }

    public function getPasswordFields()
    {
        return [
            'current_password' => [
                'type' => 'password',
                'name' => 'current_password',
                'label' => 'Current Password',
                'value' => '',
                'placeholder' => 'Current Password',
                'required' => true,
                'col' => 6,
                'extra' => '',
            ],
            'password' => [
                'type' => 'password',
                'name' => 'password',
                'label' => 'New Password',
                'value' => '',
                'placeholder' => 'Password',
                'required' => true,
                'col' => 6,
                'extra' => '',
            ],
            'password_confirmation' => [
                'type' => 'password',
                'name' => 'password_confirmation',
                'label' => 'Confirm Password',
                'value' => '',
                'placeholder' => 'Confirm Password',
                'required' => true,
                'col' => 6,
                'extra' => '',
            ],
        ];
    }

    public function loginWith(User $user)
    {
        if ($user && in_array($user->role, [0, 1])) {
            if ($user->role == 1) {
                session()->put('super_admin', Auth::user());
            }
            Auth::loginUsingId($user->id);
        }

        return redirect()->intended('/');
    }

    // public function backToAdmin()
    // {
    //     if (session('super_admin') && !empty(session('super_admin')) && request()->has('admin')) {
    //         Auth::login(session('super_admin'));
    //         session()->forget('super_admin', '');
    //         session()->forget('company_admin', '');
    //     }

    //     if (session('company_admin') && !empty(session('company_admin')) && request()->has('company')) {
    //         Auth::login(session('company_admin'));
    //         session()->forget('company_admin', '');
    //     }

    //     return redirect()->intended('/');
    // }

    public function backToAdmin()
    {
        $superAdmin = session('super_admin');
        $companyAdmin = session('company_admin');

        if ($superAdmin && request()->has('admin')) {
            Auth::login($superAdmin);
            session()->forget(['super_admin', 'company_admin']);
        } elseif ($companyAdmin && request()->has('company')) {
            Auth::login($companyAdmin);
            session()->forget('company_admin');
        }

        return redirect()->intended('/');
    }
}
