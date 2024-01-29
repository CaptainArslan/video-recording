<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PlanController extends Controller
{
    const TITLE = 'Plans';

    const VIEW = 'plan';

    const URL = 'plans';

    const PATH = 'plans';

    const TABLE = 'plans';

    const ROUTE = 'plans';

    protected $skip = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected $actions = [
        'edit' => true,
        'destroy' => true,
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tableFields = getTableColumns(self::TABLE, $this->skip);
        $tableFields = array_merge($tableFields, ['action' => 'Action']);
        if ($request->ajax()) {
            $model = Plan::latest()->orderBy('id', 'DESC')->get();

            return DataTables::of($model)
                ->addIndexColumn()
                ->editColumn('action', function ($row) {
                    $dropdown = false;
                    $id = $row->id;
                    $actions = getActions($this->actions, self::ROUTE);
                    $actionhtml = view('components.form.action', get_defined_vars())->render();

                    return $actionhtml;
                })
                ->editColumn('status', function ($row) {
                    return $row->getStatus();
                })
                ->rawColumns(['action', 'status'])
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
        $this->skip = array_merge($this->skip, []);
        $formFields = getFormFields(self::TABLE, $this->skip);

        return view(self::VIEW.'.store', get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|gt:0',
            'recording_limit' => 'required|gt:0',
            'description' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);
        // User::create($request->all());
        Plan::create($request->only('name', 'price', 'recording_limit', 'description', 'status'));

        return redirect()->route(self::ROUTE.'.index')->with('success', self::VIEW.' created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Plan $plan)
    {
        // $user = User::findOrFail($id);
        $this->skip = array_merge($this->skip, []);
        $formFields = getFormFields(self::TABLE, $this->skip, $plan);

        return view(self::VIEW.'.edit', get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Plan $plan)
    {
        // $user = User::findOrFail($id);
        $plan->delete();

        return redirect()->route(self::ROUTE.'.index')->with('success', self::VIEW.' deleted successfully');
    }
}
