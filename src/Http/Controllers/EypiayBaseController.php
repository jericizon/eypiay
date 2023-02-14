<?php

namespace JericIzon\Eypiay\Http\Controllers;

use Illuminate\Http\Request;
use JericIzon\Eypiay\Traits\EypiayControllerTrait;

class EypiayBaseController extends Controller
{
    use EypiayControllerTrait;

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $tableName)
    {
        $jsonResponse = [
            'success' => false,
        ];

        if(!$this->tableAvailable($tableName)) {
            $jsonResponse['message'] = 'Page not found.';
            return response()->json($jsonResponse, 404);
        }

        try {

            $query = $this->getModel($tableName)->query();

            if($request->has('orderBy') && $request->has('sortedBy')) {
                $query->orderBy($request->query('orderBy'), $request->query('sortedBy', 'asc'));
            }

            return $query->paginate();

        } catch (\Exception $error) {
            \Log::error($error);
            $jsonResponse = [
                'success' => false,
                'message' => 'Something went wrong.',
            ];
            if(config('app.debug') && !str_contains(config('app.env'), 'prod')) {
                $jsonResponse['error'] = $error->getMessage();
            }
            return response()->json($jsonResponse, 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
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
    public function destroy($id)
    {
        //
    }
}
