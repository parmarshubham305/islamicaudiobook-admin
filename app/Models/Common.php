<?php

namespace App\Models;

use Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\Subscribe;

use App\Models\Package;
use Storage;
use Validator;
use Carbon\Carbon;

class Common extends Model
{

    public function imageNameToUrl($array, $column, $folder)
    {
        try {
            foreach ($array as $key => $value) {
                if (isset($value[$column]) && $value[$column] != "") {
                  
                    $appName = Config::get('app.image_url');
                    $url= $appName . $folder . '/' . $value[$column];
                    if($folder =="user"){
                        if (Storage::disk('public')->exists($folder . '/' . $value[$column])) {
                            $value[$column] = $appName . $folder . '/' . $value[$column];
                        } else {
                            $value[$column] = asset('assets/imgs/users.png');
                        }
                    } else{
                        if (Storage::disk('public')->exists($folder . '/' . $value[$column])) {
                            $value[$column] = $appName . $folder . '/' . $value[$column];
                        } else {
                            $value[$column] = asset('assets/imgs/no_img.png');
                        }
                    }
                  
                } else {
                    if($folder =="user"){
                        
                        $value[$column] = asset('assets/imgs/users.png');
                    }else{
                        $value[$column] = asset('assets/imgs/no_img.png');

                    }   
                }
            }
            return $array;
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function saveImage($org_name, $folder)
    {
        try {
            $img_ext = $org_name->getClientOriginalExtension();
            $filename = time() . '.' . $img_ext;
            $path = $org_name->move(base_path('storage/app/public/' . $folder), $filename);
            return $filename;
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function savelandscapeImage($org_name1, $folder1)
    {
        try {
            $img_ext1 = $org_name1->getClientOriginalExtension();
            $filename1 = 'land_' . time() . '.' . $img_ext1;
            $path1 = $org_name1->move(base_path('storage/app/public/' . $folder1), $filename1);
            return $filename1;
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function deleteImageToFolder($folder, $name)
    {
        try {

            Storage::disk('public')->delete($folder . '/' . $name);

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function SettimgImagePath($folder = "", $name = "")
    {
        try {
            if ($folder != "" && $name != "") {
                $appName = Config::get('app.image_url');
                if($folder =="app_setting"){
                    if (Storage::disk('public')->exists($folder . '/' . $name)) {
                        $data = $appName . $folder . '/' . $name;
                    } else{
                        $data = asset('assets/imgs/no_img.png');

                    }
                } 
                
            } else {
                $data = asset('/assets/imgs/no_img.png');
            }
            return $data;
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function getImagePath($folder = "", $name = "")
    {
        try {
            if ($folder != "" && $name != "") {
                $appName = Config::get('app.image_url');

                $data = $appName . $folder . '/' . $name;
            } else {
                $data = asset('/assets/imgs/no_img.png');
            }
            return $data;
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function API_Paginate_Response($status_code, $message, $array = [], $total_records = 0, $total_page = 0)
    {
        try {
            $data['status'] = $status_code;
            $data['message'] = $message;
            if ($status_code == 200) {
                $data['result'] = $array;
                $data['total_records'] = $total_records;
                $data['total_page'] = $total_page;
            }
            return $data;
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function send_mail($testMailData){

        Mail::to($testMailData)->send(new Subscribe($testMailData));
        return true;

    }

    public function smtp() {
        $setting =\App\Models\Smtp::first();

        if($setting !=null){
            if($setting->status ==1){
                return $setting;
            }
        }

    }
  
    public function API_Response($status, $message, $array = [],$pagination = '')
    {
        $data['status'] = $status;
        $data['message'] = $message;
        if ($status == 200) {
            $data['result'] = $array;
        } 
        if ($pagination) {
            $data['total_rows'] = $pagination['total_rows'];
            $data['total_page'] = $pagination['total_page'];
            $data['current_page'] = $pagination['current_page'];
            $data['more_page'] = $pagination['more_page'];
        }
        return $data;
    }

    public function video_search($name)
    {
        $q = Video::where(function ($query) {
            // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
            $query->where('is_created_by_admin', 1)
                  ->orWhere(function ($subquery) {
                      $subquery->where('is_created_by_admin', 0)
                               ->where('is_approved', 1);
                  });
        })->where('name', 'like', '%' . $name . '%')->with('user','category','artist')->latest()->get()->toArray();
        return $q;
    }

    public function GetCategoryNameByIds($ids)
    {   
        $Ids = $ids;
        $data = Category::select('id', 'name')->where('id', $Ids)->get();
    
        if (count($data) > 0) {
    
            foreach ($data as $key => $value) {
                $final_data = $value['name'];
            }
            
            $IDs = $final_data;
            return $IDs;
        } else {
            return "";
        }
    }

    public function GetArtistNameByIds($ids)
    {   
        $Ids = $ids;
        $data = Artist::select('id', 'name')->where('id', $Ids)->get();
    
        if (count($data) > 0) {
    
            foreach ($data as $key => $value) {
                $final_data = $value['name'];
            }
            
            $IDs = $final_data;
            return $IDs;
        } else {
            return "";
        }
    }

    public function GetVidoeNameByIds($ids)
    {   
        $Ids = explode(',', $ids);
        $data = Video::select('id', 'name')->whereIn('id', $Ids)->get();
    
        if (count($data) > 0) {
    
            foreach ($data as $key => $value) {
                $final_data[] = $value['name'];
            }
    
            $IDs = implode(", ", $final_data);
            return $IDs;
        } else {
            return "";
        }
    }

    public function is_buy($user_id =0 ,$package_id)
    {
        $expiry = Transaction::where('status', 1)->get();
        for ($i = 0; $i < count($expiry); $i++) {
            if ($expiry[$i]['expiry_date'] < date('Y-m-d')) {
                $expiry[$i]['status'] = 0;
                $expiry[$i]->save();
            }
        }

        $is_buy = Transaction::where('user_id', $user_id)->where('package_id',$package_id)->where('status',1)->first();
        if (!empty($is_buy)) {
            return 1;
            
        } else {
            return 0;
        }
    }

    public function check_is_buy($user_id =0,$package_id)
    {
        $expiry = Transaction::where('status', 1)->get();
        for ($i = 0; $i < count($expiry); $i++) {
            if ($expiry[$i]['expiry_date'] < date('Y-m-d')) {
                $expiry[$i]['status'] = 0;
                $expiry[$i]->save();
            }
        }

        $is_buy = Transaction::where('user_id', $user_id)->where('package_id',$package_id)->where('status', 1)->first();
        if (!empty($is_buy)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getUserAllPlansWithBuyStatus($userId) {
        $packages = Package::with(['audios', 'videos', 'ebooks'])->get();
        $subscriptionsData = [];
    
        foreach ($packages as $package) {
            $packageData = $package->toArray();
            $packageData['is_buy'] = $this->check_is_buy($userId, $package->id);
            

            $packageLimit = config("subscription-packages.{$package->identifier}.package_limits", []);
            $packageData['package_limit'] = $packageLimit;
            $packageData['limit_statistics'] = [];

            // Page Limits Statistics
            if ($package->identifier == 'e-book-subscription') {
                $maxViews = $packageLimit['max_view']['value']; // Max allowed views (e.g., 30)
                $duration = $packageLimit['max_view']['duration']; // Duration in months
            
                $createdAt = Carbon::parse($package->created_at);
                $now = Carbon::now();
            
                // Find the number of months since creation
                $monthsSinceCreation = $createdAt->diffInMonths($now);
            
                // Get the start of the current cycle
                $startDate = $createdAt->copy()->addMonths($monthsSinceCreation);
            
                // End date is one month after the start date
                $endDate = $startDate->copy()->addMonth();
            
                $userPaidEbookViews = View::join('tbl_ebooks', 'tbl_view.video_id', '=', 'tbl_ebooks.id')
                    ->where('tbl_view.user_id', $userId)
                    ->where('tbl_view.type', 'ebook')
                    ->where('tbl_ebooks.is_paid', '1')
                    ->select(
                        'tbl_view.*', // Select all fields from tbl_view
                        'tbl_ebooks.is_paid'
                    )
                    ->whereBetween('tbl_view.updated_at', [$startDate, $endDate]) // Apply filter after selecting
                    ->count();
            
                $packageData['limit_statistics'] = [
                    'ebook_views_count' => $userPaidEbookViews,
                    'max_view_limit' => $maxViews,
                    'max_view_limit_reached' => ($userPaidEbookViews >= $maxViews),
                    'current_cycle_start' => $startDate->toDateTimeString(),
                    'current_cycle_end' => $endDate->toDateTimeString()
                ];
            }            
            

            $subscriptionsData[] = $packageData; 
        }
    
        return $subscriptionsData;
    }

    public function pagination_array($total_rows, $page_size, $current_page, $more_page)
    {
        $array['total_rows'] = $total_rows;
        $array['total_page'] = $page_size;
        $array['current_page'] = (int) $current_page;
        $array['more_page'] = $more_page;

        return $array;
    }

    public function more_page($current_page, $page_size)
    {
        $more_page = false;
        if ($current_page < $page_size) {
            $more_page = true;
        }
        return $more_page;
    }

    public function is_follow($user_id)
    {
        try{
            $is_follow = Follow::where('user_id', $user_id)->first();

            if(isset($is_follow['id'])){
                return $is_follow = "1";
            } else {
                return $is_follow = "0";
            }
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function get_all_count_for_video($id, $user_id = 0, $to_user = 0)
    {
        $data = [];
        $total_comment = Comment::where('video_id', $id)->where('type', 'video')->count();
        $total_like = Like::where('video_id', $id)->where('status', 1)->where('type', 'video')->count();
        $total_dislike = Like::where('video_id', $id)->where('status', 2)->where('type', 'video')->count();

        $avg_rating = Comment::where('video_id', $id)->where('type', 'video')->avg('rating');
        $data['avg_rating'] = number_format($avg_rating, 2);

        // $data['is_follow'] = "0";
        // if ($user_id > 0 && $to_user > 0) {
        //     $is_favorite = Follow::where('user_id', $user_id)->where('to_user_id', $to_user)->first();
        //     if ($is_favorite) {
        //         $data['is_follow'] = "1";
        //     }
        // }

        $data['is_favorite'] = "0";
        if ($user_id) {
            $is_favorite = Favourite::where('video_id', $id)->where('user_id', $user_id)->where('type', 'video')->first();
            if ($is_favorite) {
                $data['is_favorite'] = "1";
            }
        }

        $data['is_bookmarked'] = "0";
        if ($user_id) {
            $is_bookmarked = Bookmark::where('video_id', $id)->where('user_id', $user_id)->where('type', 'video')->first();
            if ($is_bookmarked) {
                $data['is_bookmarked'] = "1";
            }
        }
       
        $data['is_download'] = "0";
        if ($user_id) {
            $is_favorite = Download::where('video_id', $id)->where('user_id', $user_id)->where('type', 'video')->first();
            if ($is_favorite) {
                $data['is_download'] = "1";
            }
        }
        
 
        $data['total_comment'] = strval($total_comment);
        $data['total_like'] = strval($total_like);
        $data['total_dislike'] = strval($total_dislike);



       return $data;
    }

    public function get_all_count_for_ebook($id, $user_id = 0, $to_user = 0) {
        $data = [];

        $data['multiple_ebooks'] = MultipleEbook::where('ebook_id', $id)->get();

        $total_comment = Comment::where('video_id', $id)->where('type', 'ebook')->count();
        $total_like = Like::where('video_id', $id)->where('status', 1)->where('type', 'ebook')->count();
        $total_dislike = Like::where('video_id', $id)->where('status', 2)->where('type', 'ebook')->count();

        $avg_rating = Comment::where('video_id', $id)->where('type', 'ebook')->avg('rating');
        $data['avg_rating'] = number_format($avg_rating, 2);

        // $data['is_follow'] = "0";
        // if ($user_id > 0 && $to_user > 0) {
        //     $is_favorite = Follow::where('user_id', $user_id)->where('to_user_id', $to_user)->first();
        //     if ($is_favorite) {
        //         $data['is_follow'] = "1";
        //     }
        // }

        $data['is_favorite'] = "0";
        if ($user_id) {
            $is_favorite = Favourite::where('video_id', $id)->where('user_id', $user_id)->where('type', 'ebook')->first();
            if ($is_favorite) {
                $data['is_favorite'] = "1";
            }
        }

        $data['is_bookmarked'] = "0";
        if ($user_id) {
            $is_bookmarked = Bookmark::where('video_id', $id)->where('user_id', $user_id)->where('type', 'ebook')->first();
            if ($is_bookmarked) {
                $data['is_bookmarked'] = "1";
            }
        }
   
        $data['is_download'] = "0";
        if ($user_id) {
            $is_favorite = Download::where('video_id', $id)->where('user_id', $user_id)->where('type', 'ebook')->first();
            if ($is_favorite) {
                $data['is_download'] = "1";
            }
        }
     
        $data['total_comment'] = strval($total_comment);
        $data['total_like'] = strval($total_like);
        $data['total_dislike'] = strval($total_dislike);

       return $data;
    }
    
    public function is_like($user_id, $video_id)
    {
        $datas = Like::where('user_id', $user_id)->where('video_id', $video_id)->where('status', 1)->first();
        if (isset($datas['id'])) {
            return "1";
        } else {
            return "0";
        }
    }

    public function sendnotification($array)
    {
        $noty =\App\Models\General_Setting::where('key','onesignal_apid')->orwhere('key','onesignal_rest_key')->get();
        $notification =[];
        foreach ($noty as $row) {
            $notification[$row->key] =$row->value;
        }

        $ONESIGNAL_APP_ID = $notification['onesignal_apid'];
        $ONESIGNAL_REST_KEY = $notification['onesignal_rest_key'];

        $content = array(
            "en" => $array['description'],
        );
    
        $fields = array(
            'app_id' => $ONESIGNAL_APP_ID,
            'included_segments' => array('All'),
            'data' => $array,
            'headings' => array("en" => $array['name']),
            'contents' => $content,
            'big_picture' => $array['image'],
        );
        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $ONESIGNAL_REST_KEY,
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        $response = curl_exec($ch);
        curl_close($ch);
    }
    
    //New Integration
    public function get_all_count_for_audio($id, $user_id = 0, $to_user = 0)
    {
        $data = [];
        $total_comment = Comment::where('video_id', $id)->where('type', 'audio')->count();
        $total_like = Like::where('video_id', $id)->where('status', 1)->where('type', 'audio')->count();
        $total_dislike = Like::where('video_id', $id)->where('status', 2)->where('type', 'audio')->count();

        $avg_rating = Comment::where('video_id', $id)->where('type', 'audio')->avg('rating');
        $data['avg_rating'] = number_format($avg_rating, 2);

        $data['is_favorite'] = "0";
        if ($user_id) {
            $is_favorite = Favourite::where('video_id', $id)->where('user_id', $user_id)->where('type', 'audio')->first();
            if ($is_favorite) {
                $data['is_favorite'] = "1";
            }
        }

        $data['is_bookmarked'] = "0";
        if ($user_id) {
            $is_bookmarked = Bookmark::where('video_id', $id)->where('user_id', $user_id)->where('type', 'audio')->first();
            if ($is_bookmarked) {
                $data['is_bookmarked'] = "1";
            }
        }
       
       
        $data['is_download'] = "0";
        if ($user_id) {
            $is_favorite = Download::where('video_id', $id)->where('user_id', $user_id)->where('type', 'audio')->first();
            if ($is_favorite) {
                $data['is_download'] = "1";
            }
        }
        
        $data['total_comment'] = strval($total_comment);
        $data['total_like'] = strval($total_like);
        $data['total_dislike'] = strval($total_dislike);

       return $data;
    }

public function get_all_count_for_ai_audio($id, $user_id = 0, $to_user = 0)
    {
        $data = [];
        $total_comment = Comment::where('video_id', $id)->whereIn('type', ['aiaudio', 'audio'])->count();
        $total_like = Like::where('video_id', $id)->where('status', 1)->whereIn('type', ['aiaudio', 'audio'])->count();
        $total_dislike = Like::where('video_id', $id)->where('status', 2)->whereIn('type', ['aiaudio', 'audio'])->count();

        $avg_rating = Comment::where('video_id', $id)->whereIn('type', ['aiaudio', 'audio'])->avg('rating');
        $data['avg_rating'] = number_format($avg_rating, 2);

        $data['is_favorite'] = "0";
        if ($user_id) {
            $is_favorite = Favourite::where('video_id', $id)->where('user_id', $user_id)->whereIn('type', ['aiaudio', 'audio'])->first();
            if ($is_favorite) {
                $data['is_favorite'] = "1";
            }
        }

        $data['is_bookmarked'] = "0";
        if ($user_id) {
            $is_bookmarked = Bookmark::where('video_id', $id)->where('user_id', $user_id)->whereIn('type', ['aiaudio', 'audio'])->first();
            if ($is_bookmarked) {
                $data['is_bookmarked'] = "1";
            }
        }
       
       
        $data['is_download'] = "0";
        if ($user_id) {
            $is_favorite = Download::where('video_id', $id)->where('user_id', $user_id)->whereIn('type', ['aiaudio', 'audio'])->first();
            if ($is_favorite) {
                $data['is_download'] = "1";
            }
        }
        
 
        $data['total_comment'] = strval($total_comment);
        $data['total_like'] = strval($total_like);
        $data['total_dislike'] = strval($total_dislike);

       return $data;
    }

    public function audio_search($name)
    {
        $query = Audio::where('name', 'like', '%' . $name . '%')->where('is_aiaudiobook',"0")
        ->where(function ($query) {
            // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
            $query->where('is_created_by_admin', 1)
                  ->orWhere(function ($subquery) {
                      $subquery->where('is_created_by_admin', 0)
                               ->where('is_approved', 1);
                  });
        })
        ->with('user','category','artist')->latest()->get()->toArray();
        return $query;
    }

    public function ai_audio_search($name)
    {
        $q = Audio::where('name', 'like', '%' . $name . '%')->where('is_aiaudiobook',"1")->with('user','category','artist')->latest()->get()->toArray();
        return $q;
    }

}
