<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Config;

use App\Models\Common;
use App\Models\User;
use App\Models\EBook;
use App\Models\Video;
use App\Models\Audio;
use Illuminate\Http\Request;
use App\Models\Smtp;
use Validator;
use Storage;
use App\Exceptions\Handler;
use App\Mail\Subscribe;
use Illuminate\Support\Facades\DB;
use App\Helpers\AppHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Custom_Class\newInstance; 


class UserController extends Controller
{
    private $folder = "user";
    public $common;

    public function __construct()
    {
        $this->common = new Common;
    }

    public function Login(Request $request)
    {
        try{
            if($request->type ==1) {
                $validation = Validator::make(
                    $request->all(),
                    [
                        'mobile_number' => 'required',
                        'country_code' => 'required',
                        'device_token' => 'required',
                        'device_type' => 'required',

                    ],
                    [
                        'mobile_number.required' => __('api_msg.please_enter_mobile_number'),
                        'country_code.required' => __('api_msg.please_enter_country_code'),
                        'device_token.required' => "Plese Required Device token",
                        'device_type.required' => "Device Type Is Required.",

                    ]
                );
                if ($validation->fails()) {

                    $errors = $validation->errors()->all();

                    $data['status'] = 400;
                    if ($errors) {
                        $data['message'] = $errors;
                    }else if ($errors1) {
                        $data['message'] = $errors1;
                    }
                    return $data;
                }
            } else if ($request->type ==2){
                $validation = Validator::make(
                    $request->all(),
                    [
                        'email' => 'required|email',
                        'device_token' => 'required',
                        'device_type' => 'required',
                    ],
                    [
                        'email.required' => __('api_msg.please_enter_email_address'),
                        'device_token.required' => "Please Enter Device token.",
                        'device_type.required' => "Please Enter Device Type.",
                    ]
                );
                if ($validation->fails()) {

                    $errors = $validation->errors()->all();
                    $data['status'] = 400;
                    if ($errors) {
                        $data['message'] = $errors;
                    }
                    return $data;
                }
            } else if ($request->type ==3){
                $validation = Validator::make(
                    $request->all(),
                    [
                        'email' => 'required|email',
                        'password' => 'required|min:4',
                    ],
                    [
                        'email.required' => __('api_msg.please_enter_email_address'),
                        'password.required' => __('api_msg.please_enter_password'),

                    ]
                );
                if ($validation->fails()) {

                    $errors = $validation->errors()->first('email');
                    $errors1 = $validation->errors()->first('password');
                    $data['status'] = 400;
                    if ($errors) {
                        $data['message'] = $errors;
                    } else if ($errors1) {
                        $data['message'] = $errors1;
                    }
                    return $data;
                }

            } else {
                $data['status'] = 400;
                $data['message'] = __('api_msg.change_type');
                return $data;
            }

            $type = $request->type;

            if ($type ==1 ) {

                $mobile_number =$request->mobile_number;
                $country_code =$request->country_code;
                $device_token = $request['device_token'];
                $device_type = $request['device_type'];


                $data = User::where('mobile_number',$mobile_number)->first();
                if(isset($data['id'])) {
                    
                    // Update a new token
                    User::where('id', $data['id'])->update(['device_token' => $device_token]);
                    // Update Device Type
                    User::where('id', $data['id'])->update(['device_type' => $device_type]);

                    $data['device_type'] = $device_type;
                    $data['device_token'] = $device_token;
                    
                    $data['image'] = $this->common->getImagePath($this->folder, $data['image']);

                    return $this->common->API_Response(200, __('api_msg.login_successfully'), array($data));
                } else {
    
                    $username = rand(0, 1000);
                    $UserName = '@' . $mobile_number . '_' . $username;
                    $array['user_name'] = $UserName;
                    $array['full_name'] = isset($_REQUEST['full_name']) ? $_REQUEST['full_name'] : '';
                    $array['email'] = '';
                    $array['password'] = '';
                    $array['country_code'] =$country_code;
                    $array['mobile_number'] = $mobile_number;
                    $array['date_of_birth'] = date('Y-m-d');
                    $array['gender'] = 1;
                    $array['image'] = '';
                    $array['type'] = $type;
                    $array['bio'] = '';
                    $array['device_token'] = isset($_REQUEST['device_token']) ? $_REQUEST['device_token'] : '';
                    $array['device_type'] = $device_type;

                    $insert_id = User::insertGetId($array);
                    // Update a new token
                    User::where('id', $insert_id)->update(['device_token' => $device_token]);
                   
                     
                    if (isset($insert_id)) {
    
                        $get_data = User::where('id', $insert_id)->first();
                        if (isset($get_data)) {
                            
                            $get_data['image'] = $this->common->getImagePath($this->folder, $get_data['image']);
                        
                            return $this->common->API_Response(200, __('api_msg.login_successfully'), array($get_data));
                        }
                        
                    }
                }
            } 

            if ($type ==2) {

                $email =$request->email;
                $device_token = $request['device_token'];
                $device_type = $request['device_type'];

                $data = User::where('email',$email)->first();
                if (isset($data)) {

                       // Update a new token
                       User::where('id', $data['id'])->update(['device_token' => $device_token]);
                       // Update Device Type
                       User::where('id', $data['id'])->update(['device_type' => $device_type]);
   
                       $data['device_type'] = $device_type;
                       $data['device_token'] = $device_token;

                        $data['image'] = $this->common->getImagePath($this->folder, $data['image']);

                    return $this->common->API_Response(200, __('api_msg.login_successfully'), array($data));
                }else{
                    $username = rand(0, 1000);
                    $email_array = explode('@', $request->email);
                    $array['user_name'] = '@' . $email_array[0] . '_' . $username;
                    $array['full_name'] = isset($_REQUEST['full_name']) ? $_REQUEST['full_name'] : '';
                    $array['email'] = $email;
                    $array['password'] = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
                    $array['mobile_number'] = isset($_REQUEST['mobile_number']) ? $_REQUEST['mobile_number'] : '';
                    $array['country_code'] ="";
                    $array['date_of_birth'] = isset($_REQUEST['date_of_birth']) ? $_REQUEST['date_of_birth'] : date('Y-m-d');
                    $array['gender'] = isset($_REQUEST['gender']) ? $_REQUEST['gender'] : 1;
                    $array['type'] = $type;
                    $array['bio'] = '';
                    $array['device_token'] = isset($_REQUEST['device_token']) ? $_REQUEST['device_token'] : '';
                    $array['device_type'] = $device_type;

                    if ($request->file('image')) {
                        $files = $request->file('image');
                        $array['image'] = $this->common->saveImage($files, $this->folder);
                    } else {
                        $array['image'] = '';
                        
                    }

                    $smtp =$this->common->smtp();
                    if($smtp !=null){
                        try{
                            if(setting_app_name() == ""){
                
                                $title = env('APP_NAME') . " - Login";
                                $body = 'Welcome to ' . env('APP_NAME') . ' App & Enjoy this app.';
                            } else {
                                $title = setting_app_name() . " - Login";                
                                $body = "Welcome to " . setting_app_name() . " App & Enjoy this app.";
                            }
        
                            $details = [
                                'title' => $title,
                                'body' => $body
                            ];
                            Mail::to($email)->send(new Subscribe($details));
    
                        } catch(\Swift_TransportException $e){}
                    }

                    $insert_id = User::insertGetId($array);
                    if (isset($insert_id)) {

                        // Update a new token
                            User::where('id', $insert_id)->update(['device_token' => $device_token]);
                       

                        $get_data = User::where('id', $insert_id)->first();
                        if (isset($get_data)) {
                            $get_data['image'] = $this->common->getImagePath($this->folder, $get_data['image']);
                            
                            return $this->common->API_Response(200, __('api_msg.login_successfully'), array($get_data));
                        }
                    }
                }
            }

            if($type ==3){

                $email =$request->email;
                $password =$request->password;
                $device_token = $request['device_token'];
                $device_type = $request['device_type'];

                $data = User::where('email',$email)->where('password',$password)->first();
                if(isset($data)){

                    // Update a new token
                        User::where('id', $data)->update(['device_token' => $request['device_token']]);
                    // Update Device Type
                        User::where('id', $data['id'])->update(['device_type' => $request['device_type']]);

                    $data['image'] = $this->common->getImagePath($this->folder, $data['image']);
                    
                    return $this->common->API_Response(200, __('api_msg.login_successfully'), array($data));
                }else{
                    return $this->common->API_Response(400, __('api_msg.email_pass_worng'));

                }
            }
        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Registration(Request $request)
    {
        try{
              
          
            if($request->type ==3){
                $validation = Validator::make(
                    $request->all(),
                    [
                        'type' => 'required|numeric',
                        'full_name' =>'required',
                        'email'=>'required|email',
                        'password' => 'required|min:5',
                        'mobile_number' => 'required',
                        'country_code' => 'required',

                    ],
                    [
                        'type.required' => __('api_msg.please_enter_type'),
                        'full_name.required' => __('api_msg.please_enter_fullName'),
                        'email.required' => __('api_msg.please_enter_email_address'),
                        'password.required' => __('api_msg.please_enter_password'),
                        'mobile_number.required' => __('api_msg.please_enter_mobile_number'),
                        'country_code.required' => __('api_msg.please_enter_country_code'),

                    ]
                );
                if ($validation->fails()) {

                    $errors = $validation->errors()->all();
                    $data['status'] = 400;
                    if ($errors) {
                        $data['message'] = $errors;
                    }
                    return $data;
                }
            } else{
                $data['status'] = 400;
                $data['message'] = __('api_msg.change_type');
                return $data;
            }
                $data = User::where('email',$request->email)->first();
                if(isset($data)){
                    return $this->common->API_Response(400, __('api_msg.email_already_exits'));

                }else{
                    
                    $data['type'] = $request->type;
                    $username = rand(0, 1000);
                    $email_array = explode('@', $request->email);
                    $data['user_name'] = '@' . $email_array[0] . '_' . $username;
                    $data['full_name'] = isset($request->full_name) ? $request->full_name : '';
                    $data['email'] = isset($request->email) ? $request->email : '';
                    $data['password'] = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
                    $data['image'] = isset($request->image) ? $request->image : '';
                    $data['mobile_number'] = isset($request->mobile_number) ? $request->mobile_number : '';
                    $data['country_code'] =isset($_REQUEST['country_code']) ? $_REQUEST['country_code'] : '';;
                    $data['date_of_birth'] = date('Y-m-d');
                    $data['gender'] =1;
                    $data['bio'] ="";
                    $data['status'] = 1;
                    $data['device_token'] = isset($request->device_token) ? $request->device_token : '';
                    $data['device_type'] = isset($request->device_token) ? $request->device_type : '';

                    $email = [
                        'email' => $request->email,
                    ];

                    $smtp =$this->common->smtp();
                    if($smtp !=null){
                        try{
                            if(setting_app_name() == ""){
                
                                $title = env('APP_NAME') . " - Registration";
                                $body = 'Welcome to ' . env('APP_NAME') . ' App & Enjoy this app.';
                            } else {
                                $title = setting_app_name() . " - Registration";                
                                $body = "Welcome to " . setting_app_name() . " App & Enjoy this app.";
                            }
        
                            $details = [
                                'title' => $title,
                                'body' => $body
                            ];
                            Mail::to($email)->send(new Subscribe($details));
    
                        } catch(\Swift_TransportException $e){}
                    }
                    

                $user_id = User::insertGetId($data);
    
                    if (isset($user_id)) {
                        $user_data = User::where('id', $user_id)->first();
                        if (isset($user_data)) {
                                $user_data['image'] = $this->common->getImagePath($this->folder, $user_data['image']);
                                return $this->common->API_Response(200, __('api_msg.login_successfully'), array($user_data));
                        }
                    }
                }
                
        }catch (Exception $e) {
           
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Get_Profile(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                ],
                [
                    'user_id.required' => __('api_msg.user_id_required'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('user_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $user_id =$request->user_id;
            $user_data = User::with(['customPackages'])->where('id',$user_id)->first();
            if(!empty($user_data)){

                $path = $this->common->getImagePath($this->folder, $user_data['image']);
                $user_data['image'] = $path;

                $userSubscriptions = $this->common->getUserAllPlansWithBuyStatus($user_id);

                $userSubscriptionsNew = [];

                foreach ($userSubscriptions as $sub) {
                    if (empty($sub['is_buy'])) {
                        continue;
                    }
                    $sub_audios_old = $sub['audios'];
                    $sub_ebooks_old = $sub['ebooks'];

                    $sub['audios'] = [];
                    $sub['ebooks'] = [];

                    foreach ($sub_audios_old as $ra) {
                        $record_purchase = DB::select(
                            'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                            [
                                'aiaudio_id' => $ra['id'],
                                'user_id' => $user_id,
                                'status' => 1,
                                'is_purchased' => 1
                            ]
                        );

                        if (!empty($record_purchase) || $ra['is_paid'] == 0) {
                            $ra['is_purchased'] = 1;
                        } else{
                            $ra['is_purchased'] = 0;
                        }

                        $sub['audios'][] = $ra;
                    }

                    foreach ($sub_ebooks_old as $ra) {
                        $record_purchase = DB::select(
                            'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                            [
                                'aiaudio_id' => $ra['id'],
                                'user_id' => $user_id,
                                'status' => 1,
                                'is_purchased' => 1
                            ]
                        );

                        if (!empty($record_purchase) || $ra['is_paid'] == 0) {
                            $ra['is_purchased'] = 1;
                        } else{
                            $ra['is_purchased'] = 0;
                        }

                        $sub['ebooks'][] = $ra;
                    }

                    $userSubscriptionsNew[] = $sub;
                }

                
                $user_data['subscriptions'] = $userSubscriptionsNew;
                $user_data['subscription_ebooks'] = EBook::where('is_paid', '1')->get();
                $user_data['subscription_audios'] = Audio::where('is_paid', '1')->get();
                $user_data['subscription_videos'] = Video::where('is_paid', '1')->get();

                // Start User purchased Ebooks
                $individualPurchaseebooks = EBook::select('tbl_ebooks.*')
                    ->join('tbl_ebook_transaction', 'tbl_ebooks.id', '=', 'tbl_ebook_transaction.ebook_id')
                    ->where('tbl_ebook_transaction.user_id', $user_id)
                    ->distinct()
                    ->get()
                    ->toArray();

                $allSubscriptionEbooks = [];
                
                foreach ($userSubscriptions as $subscription) {
                    if (empty($subscription['is_buy'])) {
                        continue;
                    }
                    foreach ($subscription['ebooks'] as $ebook) {
                        $allSubscriptionEbooks[] = $ebook;
                    }
                }

                foreach ($individualPurchaseebooks as $ebook) {
                    $allSubscriptionEbooks[] = $ebook;
                }

                $allSubscriptionEbooks = collect($allSubscriptionEbooks)
                ->unique('id')       // keep first occurrence of each unique `id`
                ->values(); 
                // End User purchased Ebooks

                // Start User purchased Audios
                $individualPurchaseAudios = Audio::select('tbl_audio.*')
                    ->join('tbl_aiaudio_transaction', 'tbl_audio.id', '=', 'tbl_aiaudio_transaction.aiaudio_id')
                    ->where('tbl_aiaudio_transaction.user_id', $user_id)
                    ->distinct()
                    ->get()
                    ->toArray();

                $allSubscriptionAudios = [];
                
                foreach ($userSubscriptions as $subscription) {
                    if (empty($subscription['is_buy'])) {
                        continue;
                    }
                    foreach ($subscription['audios'] as $audio) {
                        $allSubscriptionAudios[] = $audio;
                    }
                }

                foreach ($individualPurchaseAudios as $audio) {
                    $allSubscriptionAudios[] = $audio;
                }

                $allSubscriptionAudios = collect($allSubscriptionAudios)
                ->unique('id')       // keep first occurrence of each unique `id`
                ->values(); 
                // End User purchased Audios

                // Start User purchased Videos
                // $individualPurchaseVideo = Video::select('tbl_video.*')
                //     ->join('tbl_aiaudio_transaction', 'tbl_video.id', '=', 'tbl_aiaudio_transaction.aiaudio_id')
                //     ->where('tbl_aiaudio_transaction.user_id', $user_id)
                //     ->distinct()
                //     ->get()
                //     ->toArray();

                // $allSubscriptionVideo = [];
                
                // foreach ($userSubscriptions as $subscription) {
                //     foreach ($subscription['videos'] as $audio) {
                //         $allSubscriptionVideo[] = $audio;
                //     }
                // }

                // foreach ($individualPurchaseVideo as $audio) {
                //     $allSubscriptionVideo[] = $audio;
                // }

                // $allSubscriptionVideo = collect($allSubscriptionVideo)
                // ->unique('id')       // keep first occurrence of each unique `id`
                // ->values(); 
                // End User purchased Videos

                $allSubscriptionEbooksNew = [];
                foreach ($allSubscriptionEbooks as $ra) {
                    $record_purchase = DB::select(
                        'select is_purchased from tbl_ebook_transaction where ebook_id = :ebook_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                        [
                            'ebook_id' => $ra['id'],
                            'user_id' => $user_id,
                            'status' => 1,
                            'is_purchased' => 1
                        ]
                    );

                    if (!empty($record_purchase) || $ra['is_paid'] == 0) {
                        $ra['is_purchased'] = 1;
                    } else{
                        $ra['is_purchased'] = 0;
                    }

                    $allSubscriptionEbooksNew[] = $ra;
                }

                $allSubscriptionAudiosNew = [];
                foreach ($allSubscriptionAudios as $ra) {
                    $record_purchase = DB::select(
                        'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                        [
                            'aiaudio_id' => $ra['id'],
                            'user_id' => $user_id,
                            'status' => 1,
                            'is_purchased' => 1
                        ]
                    );

                    if (!empty($record_purchase) || $ra['is_paid'] == 0) {
                        $ra['is_purchased'] = 1;
                    } else{
                        $ra['is_purchased'] = 0;
                    }

                    $allSubscriptionAudiosNew[] = $ra;
                }

                $user_data['all_purchase_ebooks'] = $allSubscriptionEbooksNew;
                $user_data['all_purchase_audios'] = $allSubscriptionAudiosNew;
                // $user_data['all_purchase_videos'] = [];

                return $this->common->API_Response(200, __('api_msg.user_record_get'), array($user_data));
            } else{
                return $this->common->API_Response(200, __('api_msg.data_not_found'));

            }

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Upadte_Profile(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                ],
                [
                    'user_id.required' => __('api_msg.user_id_required'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('user_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $user_id = $request->user_id;
            $array = array();

            $data = User::where('id', $user_id)->first();
            if(!empty($data)){
                if (isset($_REQUEST['full_name']) && $_REQUEST['full_name'] != '') {
                    $array['full_name'] = $_REQUEST['full_name'];
                }
                if (isset($_REQUEST['email']) && $_REQUEST['email'] != '') {
                    $array['email'] = $_REQUEST['email'];
                }
                if (isset($_REQUEST['password']) && $_REQUEST['password'] != '') {
                    $array['password'] = $_REQUEST['password'];
                }
                if (isset($_REQUEST['country_code']) && $_REQUEST['country_code'] != '') {
                    $array['country_code'] = $_REQUEST['country_code'];
                }
                if (isset($_REQUEST['mobile_number']) && $_REQUEST['mobile_number'] != '') {
                    $array['mobile_number'] = $_REQUEST['mobile_number'];
                }
                if (isset($_REQUEST['date_of_birth']) && $_REQUEST['date_of_birth'] != '') {
                    $array['date_of_birth'] = $_REQUEST['date_of_birth'];
                }
                if (isset($_REQUEST['gender']) && $_REQUEST['gender'] != '') {
                    $array['gender'] = $_REQUEST['gender'];
                }
                if (isset($_REQUEST['bio']) && $_REQUEST['bio'] != '') {
                    $array['bio'] = $_REQUEST['bio'];
                }
                if (isset($_REQUEST['device_token']) && $_REQUEST['device_token'] != '') {
                    $array['device_token'] = $_REQUEST['device_token'];
                }
                if (isset($_REQUEST['device_token']) && $_REQUEST['device_token'] != '') {
                    $array['device_type'] = $_REQUEST['device_type'];
                }
                if (isset($_FILES['image']) && $_FILES['image'] != '') {

                    $image = $request->file('image');
                    $old_image = $data['image'];

                    if ($old_image != "" && $old_image != null) {

                        $files = $request->image;
                        $array['image'] = $this->common->saveImage($files, $this->folder);
                        $this->common->deleteImageToFolder($this->folder, $old_image);

                    } else {
                        $files = $request->image;
                        $array['image'] = $this->common->saveImage($files, $this->folder);
                       
                    }
                }
                
                $user_data = User::where('id', $user_id)->update($array);
                $Data['status'] = 200;
                $Data['message'] = __('api_msg.update_profile_sucessfuly');
                return $Data;

            } else {
                return $this->common->API_Response(200, __('api_msg.data_not_save'));

            }

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

}
