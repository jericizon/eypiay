<?php

namespace Eypiay\Eypiay\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;

use Eypiay\Eypiay\Traits\HelperTrait as EypiayHelper;

class EypiayPostController extends EypiayBaseController
{
    use EypiayHelper;

    public function post(Request $request)
    {
        $validator = $this->validateRequests($request, $this->requestValidation ?? []);

        if ($validator) {
            $this->code = 422;
            $this->response->errors = $validator->errors;
            return $this->eypiayReturn();
        }

        $post = $this->initPostInserts($this->dbTable, $request, $this->requestCasts ?? []);

        DB::beginTransaction();

        try {
            $createdRecord = $this->query->insertGetId($post);

            if (!$createdRecord) {
                $this->code = 422;
                $this->response->message = 'Failed to insert new record.';
            } else {
                $this->success = true;
                $this->response->message = 'New record added.';

                $visibleColumns = $this->getVisibleColumns($this->dbTable, $this->dbHidden);

                $this->response->result = $this->query->select($visibleColumns)
                    ->where('id', $createdRecord)
                    ->first();
                DB::commit();
            }
        } catch (\Exception $error) {
            $this->code = 422;
            $this->response->message = $error->getMessage();
            DB::rollBack();
        }

        $this->response->params['post'] = $post;
        return $this->eypiayReturn();
    }
}
