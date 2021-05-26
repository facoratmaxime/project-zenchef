<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Request as RequestResource;
use App\Models\Request as RequestModel;
use App\Models\RequestStatus;
use App\Models\User;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index(Request $request, $workerId)
    {
        try {
            $r = RequestModel::where('author', $workerId);

            if (!empty($request->status)) {
                if (!is_int($request->status)) {
                    $status = RequestStatus::where('name', $request->status)->first()->id;
                } else {
                    $status = $request->status;
                }
                $r->where('status', $status);
            }

            $data = $r->get();
            $data['code'] = 200;
            $data['message'] = "OK";

            return json_encode($data);
        } catch (Exception $e) {
            $response = [
                'code' => 500,
                'message' => "Something went wrong. Please try again or contact administrator of this API."
            ];

            return json_encode($response);
        }
    }

    public function show(Request $request, $workerId)
    {
        try {
            $r = RequestModel::where('author', $workerId);

            $data = $r->firstOrFail();
            $data['code'] = 200;
            $data['message'] = "OK";

            return json_encode($data);
        } catch (Exception $e) {
            $response = [
                'code' => 500,
                'message' => "Something went wrong. Please try again or contact administrator of this API."
            ];

            return json_encode($response);
        }
    }

    public function showByStatusApproved(Request $request)
    {
        $request->status = RequestStatus::APPROVED;
        return $this->index($request, null);
    }
    public function showByStatusRejected(Request $request)
    {
        $request->status = RequestStatus::REJECTED;
        return $this->index($request, null);
    }
    public function showByStatusPending(Request $request)
    {
        $request->status = RequestStatus::PENDING;
        return $this->index($request, null);
    }

    public function store(Request $request, $workerId)
    {
        try {
            $user = User::find($workerId);
            if ($user->vacation_remaining_days == 0) {
                throw new Exception("No remaining days");
            }

            if (empty($request->vacation_start_date) || empty($request->vacation_end_date)) {
                throw new Exception("Invalid dates for 'vacation_start_date' AND/OR 'vacation_end_date");
            }

            $start = null;
            $end = null;

            if (strpos($request->vacation_start_date, '-') !== false && strpos($request->vacation_end_date, "-") !== false) {
                $start = DateTime::createFromFormat("Y-m-d", $request->vacation_start_date);
                $end = DateTime::createFromFormat("Y-m-d", $request->vacation_end_date);
            } elseif (strpos($request->vacation_start_date, "/") !== false && strpos($request->vacation_end_date, "/") !== false) {
                $start = DateTime::createFromFormat("Y/m/d", $request->vacation_start_date);
                $end = DateTime::createFromFormat("Y/m/d", $request->vacation_end_date);
            }

            if (empty($start) || empty($end)) {
                throw new Exception("Invalid format dates, accepted formats: Y-m-d OR Y/m/d");
            }


            $diff = $start->diff($end, true)->days;
            if ($diff > $user->vacation_remaining_days) {
                throw new Exception("Not enough remaining days for this interval");
            }

            $data = $request->all();
            $data['author'] = $user->id;

            $r = new RequestModel($data);
            $r->save();

            $user->vacation_remaining_days -= $diff;
            $user->save();
        } catch (Exception $e) {
            return json_encode([
                'code' => 500,
                'message' => "Something happened. More: " . $e->getMessage()
            ]);
        }

        $data = $r->toArray();
        $data['code'] = 200;
        $data['message'] = "Request proceed with success";

        return json_encode($data);
    }

    public function showRemainingDays(Request $request, $workerId)
    {
        try {
            $user = User::select('vacation_remaining_days')->where('id', $workerId)->firstOrFail();
            $user['code'] = 200;
            $user['message'] = "OK";

            return json_encode($user);
        } catch (Exception $e) {
            return json_encode([
                'code' => 500,
                'message' => "Something went wrong. Please try again or contact administrator of this API."
            ]);
        }
    }

    public function update(Request $request, $workerId)
    {
        try {
            $r = RequestModel::find($request->id);

            $start = DateTime::createFromFormat("Y-m-d", $r->vacation_start_date);
            $end = DateTime::createFromFormat("Y-m-d", $r->vacation_end_date);

            // REVERT BACK TO ORIGINAL QUOTA BECAUSE REQUEST HAS BEEN DELETED. 
            // SO THAT YOU HOLD AGAIN YOUR PREVIOUS DAY, WE DO AGAIN THE STORING OF A REQUEST
            $user = User::find($r->author);
            $diff = $start->diff($end, true)->days;
            $user->vacation_remaining_days += $diff;
            $user->save();
            $r->delete();

            return $this->store($request, $workerId);
        } catch (Exception $e) {
            $data = [
                'code' => 500,
                'message' => "Something went wrong. Please try again or contact administrator of this API."
            ];

            return $data;
        }
    }

    public function destroy(Request $request)
    {
        try {
            $r = RequestModel::find($request->request);

            $start = DateTime::createFromFormat("Y-m-d", $r->vacation_start_date);
            $end = DateTime::createFromFormat("Y-m-d", $r->vacation_end_date);

            // REVERT BACK TO ORIGINAL QUOTA BECAUSE REQUEST HAS BEEN DECLINED. SO THAT YOU HOLD AGAIN YOUR PREVIOUS DAYS 
            $user = User::find($r->author);
            $diff = $start->diff($end, true)->days;
            $user->vacation_remaining_days += $diff;
            $user->save();

            $r->delete();

            $data = [
                'code' => 200,
                'message' => "Request deleted"
            ];

            return json_encode($data);
        } catch (Exception $e) {
            return json_encode([
                'code' => 500,
                'message' => "Something went wrong. Please try again or contact administrator of this API."
            ]);
        }
    }
}
