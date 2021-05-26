<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request)
    {
        try {
            $u = new User($request->all());
            $u->save();
        } catch (Exception $e) {
            return json_encode([
                'code' => 500,
                'message' => $e->getMessage()
            ]);
        }

        return json_encode($u->toArray());
    }

    public function destroy(Request $request)
    {
        try {
            $u = User::find($request->id)->delete();
        } catch (Exception $e) {
            return json_encode([
                'code' => 500,
                'message' => $e->getMessage()
            ]);
        }

        return json_encode([
            'code' => 200,
            'message' => "User " . $request->id . " deleted"
        ]);
    }
}
