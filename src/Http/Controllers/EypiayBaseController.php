<?php

namespace JericIzon\Eypiay\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

use JericIzon\Eypiay\Traits\EypiayControllerTrait;
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
            $resource = $this->getResource($tableName);
            return $resource::collection($query->paginate());
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

        $model = $this->getModel($tableName);
        $validations = $this->getValidations($tableName);

        if(count($validations)) {
            $validator = Validator::make($request->all(), $validations);

            if($validator->fails()) {
                // dd($validator->messages());
                return $this->responseValidationError($validator->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        DB::beginTransaction();
        try {
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
            $model = $this->getModel($tableName);
            $primaryKey = $model->getKeyName();
            $data = $model->where($primaryKey, $id)->first();
            if(!$data) {
                return $this->responseError(new \Exception('Data not found.'), Response::HTTP_NOT_FOUND);
            }

            $resource = $this->getResource($tableName);
            return new $resource($data);
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
        // TODO: auto generate form fields?
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $tableName, $id)
    {
        if(!$this->tableAvailable($tableName)) {
            return $this->responseError(new \Exception('Page not found.'), Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();
        try {
            $model = $this->getModel($tableName);
            $primaryKey = $model->getKeyName();

            $dataInput = $this->castInputs($tableName, $request->all());

            $updated = $model
                ->where($primaryKey, $id)
                ->update($dataInput);

            if(!$updated) {
                return $this->responseError(new \Exception('Updating failed.'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data = $model->where($primaryKey, $id)->first();
            $resource = $this->getResource($tableName);
            $result =  new $resource($data);
            DB::commit();

            return $this->responseSuccess('Data updated', $result, Response::HTTP_ACCEPTED);

        } catch (\Exception $error) {
            DB::rollback();
            return $this->responseError($error);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $tableName, $id)
    {
        if(!$this->tableAvailable($tableName)) {
            return $this->responseError(new \Exception('Page not found.'), Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();

        try {
            $model = $this->getModel($tableName);
            $primaryKey = $model->getKeyName();

            $delete = $model
                    ->where($primaryKey, $id)
                    ->delete();

            DB::commit();
            return $this->responseSuccess('Record deleted', [], Response::HTTP_ACCEPTED);
        } catch (\Exception $error) {
            DB::rollback();
            return $this->responseError($error);
        }
    }
}
