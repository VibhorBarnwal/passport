<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;


class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    // send a user details along with token
    public function userDetails($userDetails) {
        $success['token'] = $userDetails->createToken('Passport')->accessToken;
        $success['name'] =  $userDetails['first_name'] . ' ' . $userDetails['last_name'];
        if(empty($userDetails['profile_photo'])) {
            $success['profile_photo'] =  NULL;
        } else {
            $success['profile_photo'] =  $this->imagepath . $userDetails['profile_photo'];
        }

        return $success;
    }

}