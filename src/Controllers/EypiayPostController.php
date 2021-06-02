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
        if (count($this->requestValidation)) {
            $validator = Validator::make($request->all(), $this->requestValidation);
            if ($validator->fails()) {
                $this->code = 422;
                $this->response->errors = $validator->errors();
                return $this->eypiayReturn();
            }
        }

        $fillableColumns = $this->getFillableColumns($this->dbTable);

        $post = $request->only($fillableColumns);

        if (count($this->requestCasts)) {
            foreach ($this->requestCasts as $castName => $cast) {
                if (isset($post[$castName])) {
                    $post[$castName] = $this->castData($cast, $post[$castName]);
                }
            }
        }

        if (in_array('created_at', $fillableColumns) && !isset($post['created_at'])) {
            $post['created_at'] = Carbon::now();
        }

        if (in_array('updated_at', $fillableColumns) && !isset($post['updated_at'])) {
            $post['updated_at'] = Carbon::now();
        }

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
