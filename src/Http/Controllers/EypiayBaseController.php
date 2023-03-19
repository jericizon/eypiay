<?php

namespace JericIzon\Eypiay\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use JericIzon\Eypiay\Traits\EypiayControllerTrait;
use Symfony\Component\HttpFoundation\Response;
use JericIzon\Eypiay\Traits\ResponseApi;

class EypiayBaseController extends Controller
{
    use EypiayControllerTrait, ResponseApi;

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $tableName)
    {
        if(!$this->tableAvailable($tableName)) {
            return $this->responseError(new \Exception('Page not found.'), Response::HTTP_NOT_FOUND);
        }

        try {
            $query = $this->getModel($tableName)->query();
            if($request->has('orderBy') && $request->has('sortedBy')) {
                $query->orderBy($request->query('orderBy'), $request->query('sortedBy', 'asc'));
            }
            return $query->paginate();
        } catch (\Exception $error) {
            return $this->responseError($error, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // TODO: auto generate form fields?
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $tableName)
    {
        if(!$this->tableAvailable($tableName)) {
            return $this->responseError(new \Exception('Page not found.'), Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();
        try {
            $model = $this->getModel($tableName);
            $dataInput = $this->castInputs($tableName, $request->all());
            $modelId = $model->create($dataInput)->id;
            $data = $model->find($modelId);

            DB::commit();
            return $this->responseSuccess('Data created', $data, Response::HTTP_CREATED);

        } catch (\Exception $error) {
            DB::rollback();
            return $this->responseError($error);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $tableName, $id)
    {
        if(!$this->tableAvailable($tableName)) {
            return $this->responseError(new \Exception('Page not found.'), Response::HTTP_NOT_FOUND);
        }

        try {
            $query = $this->getModel($tableName)->query();
            $data = $query->find($id);
            if(!$data) {
                return $this->responseError(new \Exception('Data not found.'), Response::HTTP_NOT_FOUND);
            }
            return $this->responseSuccess('Data found.', $data);
        } catch (\Exception $error) {
            return $this->responseError($error, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

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
