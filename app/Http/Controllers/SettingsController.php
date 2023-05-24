<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * @group Settings
     * @response status=200 scenario="success" {
     * "code": int,
     * "success": boolean,
     * "message": string,
     * "result": [
     *    {
     *        "id": int,
     *        "key": string,
     *        "value": string,
     *        "created_at": string,
     *        "updated_at": string
     *    }
     *  ]
     * }
     */
    public function index()
    {
        return $this->response(200, result: Settings::all());
    }

    /**
     * @group Settings
     * @bodyParam id int required The id of the settings. Example: 1
     * @bodyParam value string required The value of the settings. Example: 2023-05-17 07:55:00
      * @response status=200 scenario="success" {
     * "code": int,
     * "success": boolean,
     * "message": string,
     * "result": [
     *    {
     *        "id": int,
     *        "key": string,
     *        "value": string,
     *        "created_at": string,
     *        "updated_at": string
     *    }
     *  ]
     * }
     */

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:settings,id',
            'value' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->response(400, error: $validator->errors());
        }
        $settings = Settings::find($request->id);
        $settings->update($request->all());
        $settings = Settings::find($request->id);
        return $this->response(200, result: $settings);
    }

}
