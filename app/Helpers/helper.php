<?php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

function sendMail($data){
    Mail::send($data['emailTemplate'], $data, function ($m) use ($data) {
        $m->from($data['fromEmail'], $data['message']);
        $m->to($data['toEmail'], $data['user']->first_name . ' ' . $data['user']->last_name);
        $m->subject($data['subject']);
    });

}

function modifiedImageName($first_name) {
    // Take current time stamp
    $current_time = strtotime("now");
    $image_name = $first_name . "_" . $current_time;
    return $image_name;
}

//Upload given file in folder
function uploadImageInFolder($request, $without_extension, $ext, $folder_path, $field_name) {   
    $actualName = $without_extension;
    $file_name = $actualName . "." . $ext;
    $destination_path = public_path() . $folder_path . '/';
    //Check of destination path exist or not
    //If not create one
    if (!is_dir($destination_path)) {
        mkdir($destination_path, 0777, true);
    }
    /* Get the details of images after saving in a folder */
    $photo = $request->file($field_name);
    $destination_path = public_path() . $folder_path;
    $photo->move($destination_path, $file_name);
    $uploadData = $file_name;
   
    return $uploadData;
}
