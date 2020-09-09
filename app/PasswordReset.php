<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PasswordReset extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'password_resets';

    /**
     * The attributes to be fillable from the model.
     *
     * A dirty hack to allow fields to be fillable by calling empty fillable array
     *
     * @var array
     */
    protected $fillable = [];

    protected $guarded = ['id'];
    /**
    * To allow soft deletes
    */

    public function updateOrCreatePasswordResetDetails($userData) {
        $passwordReset = PasswordReset::updateOrCreate(
            ['user_id' => $userData->id],
            [
                'email' => $userData->email,
                'user_id' => $userData->id,
                'token' => Str::random(60)
            ]
        );
        return $passwordReset;
    }

    public function getPasswordResetDetails($userId, $passwordResetCode) {
        $passwordReset = PasswordReset::where(['user_id' => $userId])->where(['token' => $passwordResetCode])->first();
        return $passwordReset;
    }

}
