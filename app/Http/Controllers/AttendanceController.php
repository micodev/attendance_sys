<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Attachment;
use App\Models\Attendance;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * @group Attendance Management
     *
     * get attendance of a employee according to token.
     * @authenticated
     * @response status=401 scenario="unauthorized" {
     * "code": 401,
     * "success": false,
     * "message": "يجب تسجيل الدخول"
     * }
     * @response status=200 scenario="success" {
     * "code": int,
     * "success": boolean,
     * "message": string,
     * "result": [
    *  {
    *    "id": int,
    *    "check_in": string,
    *    "check_out": string,
    *    "sys_in": string,
    *    "sys_out": string,
    *    "date": string,
    *    "user_id": int,
    *    "delay": int,
    *    "sus": boolean,
    *    "ip": string,
    *    "attachment_id": int,
    *    "created_at": string,
    *    "updated_at": string,
    *    "attachment_in": {
    *    "id": int,
    *    "path": string,
    *    "type": string,
    *     "model_name": string,
    *    "target_id": int,
    *    "created_at": string,
    *    "updated_at": string
    *  },
    *    "attachment_out": {
    *    "id": int,
    *    "path": string,
    *    "type": string,
    *     "model_name": string,
    *    "target_id": int,
    *    "created_at": string,
    *    "updated_at": string
    *  },
    *  "user": {
    *   "id": int,
    *   "first_name": string,
    *   "last_name": string,
    *   "middle_name": string,
    *   "username": string,
    *   "email": string,
    *   "email_verified_at": string,
    *   "created_at": string,
    *   "updated_at": string
    *   }
* }

    */

    public function index(Request $request)
    {
        return $this->get_attendances($request);
    }

    /**
     * @group Attendance Management
     *
     * get attendance of a employee according to token.
     * @authenticated
     * @response status=401 scenario="unauthorized" {
     * "code": 401,
     * "success": false,
     * "message": "يجب تسجيل الدخول"
     * }
     * @response status=200 scenario="success" {
     * "code": int,
     * "success": boolean,
     * "message": string,
     * "result": [
    *  {
    *    "id": int,
    *    "check_in": string,
    *    "check_out": string,
    *    "sys_in": string,
    *    "sys_out": string,
    *    "date": string,
    *    "user_id": int,
    *    "delay": int,
    *    "sus": boolean,
    *    "ip": string,
    *    "attachment_id": int,
    *    "created_at": string,
    *    "updated_at": string,
    *    "attachment_in": {
    *    "id": int,
    *    "path": string,
    *    "type": string,
    *     "model_name": string,
    *    "target_id": int,
    *    "created_at": string,
    *    "updated_at": string
    *  },
    *    "attachment_out": {
    *    "id": int,
    *    "path": string,
    *    "type": string,
    *     "model_name": string,
    *    "target_id": int,
    *    "created_at": string,
    *    "updated_at": string
    *  },
    *  "user": {
    *   "id": int,
    *   "first_name": string,
    *   "last_name": string,
    *   "middle_name": string,
    *   "username": string,
    *   "email": string,
    *   "email_verified_at": string,
    *   "created_at": string,
    *   "updated_at": string
    *   }
* }

    */

    public function user_index(Request $request)
    {
        return $this->get_attendances($request, true);
    }

    private function get_attendances(Request $request, $is_user=false)
    {
        $relations = ['attachment_in','attachment_out','user'];
        if($is_user) {
            $relations[] = 'user';
        }

        $attendances = Attendance::with($relations);
        if($request->date) {
            $attendances = $attendances->where('date', $request->date);
        }
        if($request->suspicious) {
            $attendances = $attendances->where('sus', $request->suspicious);
        }
        if($request->is_delayed) {
            $attendances = $attendances->where('sus', $request->is_delayed);
        }

        if($request->user_id) {
            $attendances = $attendances->where('user_id', $request->user_id)->get();
        } else {
            if($is_user) {
                if(Auth::user() == null) {
                    return $this->response(
                        code: 401,
                        message: 'يجب تسجيل الدخول'
                    );
                }
                $attendances =  $attendances->where('user_id', Auth::user()->id)->get();
            } else {
                $attendances =  $attendances->get();
            }
        }

        return $this->response(
            result: $attendances
        );
    }



    public function create(Request $request)
    {

        $in = Settings::get('sys_in');
        $out = Settings::get('sys_out');
        // convert $in to carbon time then
        $in = Carbon::createFromFormat('Y-m-d H:i:s', $in);
        $out = Carbon::createFromFormat('Y-m-d H:i:s', $out);
        $in = $in->setDate(Carbon::now()->year, Carbon::now()->month, Carbon::now()->day);
        $out = $out->setDate(Carbon::now()->year, Carbon::now()->month, Carbon::now()->day);

        $users = User::all();

        foreach ($users as $user) {
            $attendance = Attendance::where('user_id', $user->id)->where('date', Carbon::now())->first();
            if($attendance) {
                continue;
            }
            $attendance = Attendance::create([
                'sys_in'=>  $in,  //from settings
                'sys_out'=> $out, //from settings
                'date'=> Carbon::now(),
                'user_id'=> $user->id,
                'delay'=> 0,
            ]);
        }
        return  $this->response(
            message:'تم انشاء حضور ليوم '. Carbon::now(),
        );

    }

    /**
     * @group Attendance Management
     *
     * check in / out according time
     *
     * @authenticated
     * @bodyParam ip string required ip address of the user.
     * @bodyParam attachment string required attachment of the user.
    */
    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip'=> 'required|string',
            'attachment'=> 'required|string',
        ]);
        if($validator->fails()) {
            return $this->response(
                code:422,
                message:'هناك خطأ بالمدخلات',
                error:$validator->errors()->all(),
            );
        }
        $user = Auth::user();
        $ip = $request->ip ?? null;
        $attendance = null;
        $time = Carbon::now()->setHour(14);
        if($user) {

            $attendance = Attendance::where('user_id', $user->id)->where('date', $time->toDateString())->first();
            if($attendance) {

                $attendance->ip = $ip;
                $is_off_in = Settings::get('off_after_in');
                if($is_off_in) {
                    if($attendance->sys_in > $time) {
                        if($attendance->check_in == null) {
                            $attendance->check_in = $time;
                            $base64 = $request->attachment;

                            $path =  $this->uploadPicture($base64, '/attachments/attendance/d_'.Carbon::now()->format('Y_m_d').'/'.$user->id.'/');
                            $attachment = Attachment::create([
                                'path'=>$path,
                                'user_id'=>$user->id,
                                'type'=>'attendance',
                                'model_name'=>'attendance',
                                'target_id'=> $attendance->id,
                            ]);
                            $attendance->attachment_id = $attachment->id;
                            $attendance->save();
                        }
                    } elseif ($attendance->sys_out <= $time) {
                        $attendance->check_out = $time;

                        $base64 = $request->attachment;
                        $path =  $this->uploadPicture($base64, '/attachments/attendance/d_'.Carbon::now()->format('Y_m_d').'/'.$user->id.'/');
                        $attachment = Attachment::create([
                            'path'=>$path,
                            'user_id'=>$user->id,
                            'type'=>'attendance',
                            'model_name'=>'attendance',
                            'target_id'=> $attendance->id,
                        ]);
                        $attendance->attachment_out_id = $attachment->id;
                        $attendance->save();
                    }

                } else {

                    $totalMinutes = Carbon::createFromFormat('Y-m-d H:i:s', $attendance->sys_out)->diffInMinutes(Carbon::createFromFormat('Y-m-d H:i:s', $attendance->sys_in));
                    $halfMinutes = $totalMinutes / 2;
                    $is_check_in = Carbon::createFromFormat('Y-m-d H:i:s', $attendance->sys_in)->diffInMinutes($time) <   $halfMinutes;
                    if($is_check_in) {
                        if($attendance->check_in == null) {
                            $attendance->check_in = $time;
                            $base64 = $request->attachment;

                            $path =  $this->uploadPicture($base64, '/attachments/attendance/d_'.Carbon::now()->format('Y_m_d').'/'.$user->id.'/');
                            $attachment = Attachment::create([
                                'path'=>$path,
                                'user_id'=>$user->id,
                                'type'=>'attendance',
                                'model_name'=>'attendance',
                                'target_id'=> $attendance->id,
                            ]);
                            $attendance->attachment_id = $attachment->id;
                            $attendance->save();
                        }
                    } else {
                        $attendance->check_out = $time;

                        $base64 = $request->attachment;
                        $path =  $this->uploadPicture($base64, '/attachments/attendance/d_'.Carbon::now()->format('Y_m_d').'/'.$user->id.'/');
                        $attachment = Attachment::create([
                            'path'=>$path,
                            'user_id'=>$user->id,
                            'type'=>'attendance',
                            'model_name'=>'attendance',
                            'target_id'=> $attendance->id,
                        ]);
                        $attendance->attachment_out_id = $attachment->id;

                        $attendance->save();
                    }
                }
            }
        }
        if($attendance) {
            $attendance = Attendance::where('user_id', $user->id)->where('date', $time->toDateString())->first();
            return $this->response(
                result: $attendance,
                message: 'تم تسجيل الحضور بنجاح'
            );
        } else {
            return $this->response(
                code: 422,
                message: 'لم يتم تسجيل الحضور'
            );
        }

    }

}
