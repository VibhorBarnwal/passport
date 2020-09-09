<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\User;
use App\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Mail;
use Carbon\Carbon;
use Lang;


class LoginController extends BaseController
{

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */

    protected $user;
    protected $fromEmail;
    protected $user_object;
    protected $passwordReset_object;
    protected $imagepath;

    public function __construct()
    {
        $this->fromEmail = 'defineYourEmailHere@gmail.com';
        $this->user_object = new User();
        $this->passwordReset_object = new PasswordReset();
        $this->imagepath = '/images/profile/';
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|regex:/^[\pL\s\-]+$/u|min:2',
            'last_name' => 'required|regex:/^[\pL\s\-]+$/u|min:2',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required|min:8',
            'confirm_password' => 'required_with:password|same:password',
            'profile_photo'        =>  'nullable|image|mimes:jpeg,png,jpg'
        ]);


        if($validator->fails()){
            $validaor_error = Lang::get('authentication.error.validation_error');
            return $this->sendError($validaor_error, $validator->errors(), 200);       
        }

        $data = $request->all();
        unset($data['confirm_password']); 
        $data['password'] = bcrypt($data['password']);

        if(!empty($data['profile_photo'])) {
            // image upload details
            $folder_path = $this->imagepath;
            $file_name = basename($_FILES['profile_photo']['name']);
            // getting the extension of profile photo 
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            // image update name
            $image_final_name = modifiedImageName('user');

            $data['profile_photo'] = uploadImageInFolder($request, $image_final_name, $ext, $folder_path, 'profile_photo');
        }
        
        $user = User::create($data);
        // create an output array
        $success = $this->userDetails($user);
        $message = Lang::get('authentication.success.registration_success');
        return $this->sendResponse($success, $message);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

        if($validator->fails()){
            $validaor_error = Lang::get('authentication.error.validation_error');
            return $this->sendError($validaor_error, $validator->errors(), 200);       
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];
    
        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            // create an output array
            $success = $this->userDetails($user);            
            $message = Lang::get('authentication.success.login_success');
            return $this->sendResponse($success, $message);
        } else {
            $message = Lang::get('authentication.error.credential_error');
            $login_error = Lang::get('authentication.error.login_error');
            return $this->sendError($login_error, $message, 200);
        }  
    }

    public function logout()
    { 
        if (Auth::check()) {
            Auth::user()->token()->delete();
            return $this->sendResponse(Lang::get('authentication.success.logout_success'), Lang::get('authentication.success.logout_success_message'));
        } else {
            return $this->sendError(Lang::get('authentication.error.logout_error'), Lang::get('authentication.error.logout_error_message'));
        }
    }

    public function details() 
    { 
        $user = Auth::user(); 
        return response()->json(['success' => $user], 200); 
    } 


    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
              'email' => 'required|email',
              'path' => 'required'
            ]);

            if ($validator->fails()) {
                $validaor_error = Lang::get('authentication.error.validation_error');
                return $this->sendError($validaor_error, $validator->errors());
            }
            $urlPath = $request->path;

            if (!$user = Auth::user()) {
                $user = $this->user_object->getUserDetailsByUserField('email', $request->email);
                if (!$user) {
                    return $this->sendError(Lang::get('authentication.error.account_error'), Lang::get('authentication.error.account_not_found_error'));
                } else { 
                    $toEmail = $request->email;

                    $passwordReset = $this->passwordReset_object->updateOrCreatePasswordResetDetails($user);
                    if($passwordReset) {
                        // Data to be used on the email view
                        $data = array(
                            'user' => $user,
                            'forgotPasswordUrl' => $urlPath . '/' . $user->id . '/' . $passwordReset->token,
                            'emailTemplate' => 'emails.forgot-password',
                            'message' => 'Reset your password',
                            'subject' =>'Account Password Recovery',
                            'toEmail' => $toEmail,
                            'fromEmail' => $this->fromEmail,
                        );
    
                        //use sendMail function from helper for sending email.
                        sendMail($data);
    
                        return $this->sendResponse(Lang::get('authentication.success.password_reset'), Lang::get('authentication.success.password_reset_success'));
                    } else {
                        return $this->sendError(Lang::get('authentication.error.login_error'), Lang::get('authentication.error.email_send_error'));
                    }
                }
            } else {
                return $this->sendError(Lang::get('authentication.error.login_error'), Lang::get('authentication.error.logged_in_error'));
            }
        } catch (Exception $e) {
            return $this->sendError(Lang::get('authentication.error.login_error'), Lang::get('authentication.error.email_send_link_error'));
        }

    }

    public function getForgotPasswordConfirm($userId, $passwordResetCode = null)
    {
        $user = $this->user_object->getUserDetailsByUserField('id', $userId);
        if (!$user) {
            return $this->sendError(Lang::get('authentication.error.account_error'), Lang::get('authentication.error.account_not_found_error'));

        } else { 
            $passwordReset = $this->passwordReset_object->getPasswordResetDetails($userId, $passwordResetCode);
            if ($passwordReset) {
                if (Carbon::parse($passwordReset->updated_at)->addMinutes(1440)->isPast()) {
                    $passwordReset->delete();
                    return $this->sendError(Lang::get('authentication.error.expired_token_error'), Lang::get('authentication.error.expired_token_error_message'));
                } else {
                    return $this->sendResponse(Lang::get('authentication.success.success'), Lang::get('authentication.success.valid_token_success')); 
                }
            } else {
                return $this->sendError(Lang::get('authentication.error.invalid_token_error'), Lang::get('authentication.error.invalid_token_error_message'));
            }
        }
    }

    public function postForgotPasswordConfirm(Request $request)
    {
        if (!$user = Auth::user()) {
            $data = $request->all();
            $validator = Validator::make($data, [
                'password' => 'required|min:8',
                'password_confirm' => 'required_with:password|same:password',
                'user_id' => 'required',
                'token' => 'required',
            ]);
  
            if ($validator->fails()) {
                $validaor_error = Lang::get('authentication.error.validation_error');
                return $this->sendError($validaor_error, $validator->errors());
            }

            $passwordReset = $this->passwordReset_object->getPasswordResetDetails($data['user_id'], $data['token']);

            if (!$passwordReset) {
                return $this->sendError(Lang::get('authentication.error.invalid_token_error'), Lang::get('authentication.error.invalid_token_error_message'));
            } else {
                if (Carbon::parse($passwordReset->updated_at)->addMinutes(1440)->isPast()) {
                    $passwordReset->delete();
                    return $this->sendError(Lang::get('authentication.error.expired_token_error'), Lang::get('authentication.error.expired_token_error_message'));
                } else {
                    $user = $this->user_object->getUserDetailsByUserField('id', $passwordReset->user_id);
                    if (!$user) {
                        return $this->sendError(Lang::get('authentication.error.account_error'), Lang::get('authentication.error.account_not_found_error'));
                    } else {
                        $user->password = bcrypt($request->password);
                        $user->save();
                        $passwordReset->delete();
                        return $this->sendResponse(Lang::get('authentication.success.success'), Lang::get('authentication.success.password_update_success')); 
                    }
                }
            }

        } else {
            return $this->sendError(Lang::get('authentication.error.login_error'), Lang::get('authentication.error.logged_in_error'));
        }
    }
}