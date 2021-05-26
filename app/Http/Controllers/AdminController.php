<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\RequestStatus;
use App\Models\User;
use DateTime;
use Exception;

class AdminController extends Controller
{
    public function index(Request $request, $workerId = null)
    {
        try {
            // Set Eloquent builder to add conditional Where's
            $r = RequestModel::select('*');

            if (!empty($request->status)) {
                if (!is_int($request->status)) {
                    $status = RequestStatus::where('name', $request->status)->first()->id;
                } else {
                    $status = $request->status;
                }
                $r->where('status', $status);
            }

            if (!empty($request->worker_id) || !empty($workerId)) {
                if (!empty($request->worker_id)) {
                    $r->where('author', $workerId);
                } else {
                    $r->where('author', $workerId);
                }
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

    public function showRequest(Request $request, $requestId)
    {
        try {
            $r = RequestModel::find($requestId)->toArray();
            $r['code'] = 200;
            $r['message'] = "OK";
            return json_encode($r);
        } catch (Exception $e) {
            $response = [
                'code' => 500,
                'message' => "Something went wrong. Please try again or contact administrator of this API."
            ];
            return json_encode($response);
        }
    }

    public function approveRequest(Request $request, $requestId)
    {
        try {
            $r = RequestModel::find($requestId);
            $r->status = RequestStatus::APPROVED;
            $r->save();
            return json_encode($r);
        } catch (Exception $e) {
            $response = [
                'code' => 500,
                'message' => "Something went wrong. Please try again or contact administrator of this API."
            ];
            return json_encode($response);
        }
    }

    public function rejectRequest(Request $request, $requestId)
    {
        try {
            $r = RequestModel::find($requestId);
            $r->status = RequestStatus::REJECTED;
            $r->save();

            $start = DateTime::createFromFormat("Y-m-d", $r->vacation_start_date);
            $end = DateTime::createFromFormat("Y-m-d", $r->vacation_end_date);

            // REVERT BACK TO ORIGINAL QUOTA BECAUSE REQUEST HAS BEEN DECLINED. SO THAT YOU HOLD AGAIN YOUR PREVIOUS DAYS 
            $user = User::find($r->author);
            $diff = $start->diff($end, true)->days;
            $user->vacation_remaining_days += $diff;
            $user->save();
            return json_encode($r);
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

    public function overlappingRequests(Request $request)
    {
        try {
            $allRequests = RequestModel::all();

            $overlapp = $this->checkOverlapInDateRanges($allRequests->toArray());

            return json_encode($overlapp);
        } catch (Exception $e) {
            $response = [
                'code' => 500,
                'message' => "Something went wrong. Please try again or contact administrator of this API."
            ];
            return json_encode($response);
        }
    }

    private function checkOverlapInDateRanges($ranges)
    {
        try {
            $overlapp = [];
            for ($i = 0; $i < count($ranges); $i++) {
                for ($j = ($i + 1); $j < count($ranges); $j++) {

                    $start = \Carbon\Carbon::parse($ranges[$j]['vacation_start_date']);
                    $end = \Carbon\Carbon::parse($ranges[$j]['vacation_end_date']);

                    $start_first = \Carbon\Carbon::parse($ranges[$i]['vacation_start_date']);
                    $end_first = \Carbon\Carbon::parse($ranges[$i]['vacation_end_date']);

                    if (\Carbon\Carbon::parse($ranges[$i]['vacation_start_date'])->between($start, $end) || \Carbon\Carbon::parse($ranges[$i]['vacation_end_date'])->between($start, $end)) {
                        $overlapp[] = $ranges[$i];
                        break;
                    }
                    if (\Carbon\Carbon::parse($ranges[$j]['vacation_start_date'])->between($start_first, $end_first) || \Carbon\Carbon::parse($ranges[$j]['vacation_end_date'])->between($start_first, $end_first)) {
                        $overlapp[] = $ranges[$j];
                        break;
                    }
                }
            }

            return $overlapp;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function all(Request $request)
    {
        try {
            $r = RequestModel::all()->toArray();
            $r['code'] = 200;
            $r['message'] = "OK";

            return json_encode($r);
        } catch (Exception $e) {
            $response = [
                'code' => 500,
                'message' => "Something went wrong. Please try again or contact administrator of this API."
            ];
            return json_encode($response);
        }
    }
}
