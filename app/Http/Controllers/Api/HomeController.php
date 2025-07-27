<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Common;
use App\Models\User;
use App\Models\Category;
use App\Models\Artist;
use App\Models\Album;
use App\Models\Page;
use App\Models\Package;
use App\Models\Video;
use App\Models\Audio;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Comment;
use App\Models\General_Setting;
use App\Models\User_Notification_Tracking;
use App\Models\Transaction;
use App\Models\Audio_Transaction;
use App\Models\Payment_Option;
use App\Models\Timestemp;
use App\Models\EBook;
use App\Models\EbookTimestamp;
use App\Models\SmartCollection;
use App\Models\SmartCollectionItem;
use App\Models\CustomPackage;
use App\Models\CustomPackageSmartCollection;
use App\Models\CustomTransaction;
use App\Models\EBook_Transaction;
use App\Models\View;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;
use Storage;
use Config;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    private $folder = "user";
    private $folder1 = "app_setting";
    private $folder2 = "category";
    private $folder4 = "package";
    private $folder5 = "video";
    private $folder6 = "notification";

    private $folder7 = "audio";
    private $folder_artist = "artist";
    private $folder_album = "album";
    private $folder_ebook = "e-books";
    public $user;
    public $video;



    public $common;

    public function __construct()
    {
        $this->common = new Common;
        $this->user = new User();
        $this->video = new Video();
    }

    public function GeneralSetting(Request $request)
    {
        try {
            $list = General_Setting::select('id', 'key', 'value')->get();
            foreach ($list as $key => $value) {
                if ($value['key'] == 'app_logo') {
                    $appName = Config::get('app.image_url');
                    if (Storage::disk('public')->exists($this->folder1 . '/' . $value['value'])) {
                        $value['value'] = $appName . $this->folder1 . '/' . $value['value'];
                    } else {
                        $value['value'] = asset('assets/imgs/no_img.png');
                    }
                }
            }
            return $this->common->API_Response(200, __('api_msg.general_setting_record_get'), $list);
           
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Get_pages(Request $request)
    {
        try{
            $data = Page::get();

            $return['status'] = 200;
            $return['message'] = __('api_msg.record_get_succesfully');
            $return['result'] = [];

            for ($i=0; $i < count($data); $i++) { 

                $return['result'][$i]['page_name'] = $data[$i]['page_name'];
                $return['result'][$i]['url'] = env('APP_URL') . $data[$i]['page_name'];
            }
            return $return;

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Get_payment_option(Request $request)
    {
        try {
            $Option_data = Payment_Option::get();

            $return['status'] = 200;
            $return['message'] = __('api_msg.record_get_succesfully');
            $return['result'] =[];

            foreach ($Option_data as $key => $value) {

                $return['result'][$value['name']] = $value;
            }

            return $return;
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function subscription_list(Request $request)
    {
        try {

            $all_data = Transaction::get();
            for ($i = 0; $i < count($all_data); $i++) {
                if ($all_data[$i]['expiry_date'] <= date("Y-m-d")) {
                    $all_data[$i]->status = '0';
                    $all_data[$i]->save();
                }
            }

            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                ],
                [
                    'user_id.required' => __('api_msg.please_enter_required_fields'),
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
            $result = Transaction::where('user_id', $user_id)->with('package')->latest()->get();

            foreach ($result as $key => $value) {
                if ($value['package'] != null) {
                    $value['package_name'] = $value['package']['name'];
                    $value['package_price'] = $value['package']['price'];
                } else {
                    $value['package_name'] = "";
                    $value['package_price'] = 0;
                }

                $value['data'] = $value['created_at']->format('Y-m-d');

                unset($value['package']);
            }

            return $this->common->API_Response(200, __('api_msg.record_get_succesfully'), $result);

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function User_List(Request $request)
    {
        try{
            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = User::orderBy('id', 'DESC');

            $total_rows = $data->count();

            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);
            
            // Image Name to URL
            $this->common->imageNameToUrl($data, 'image', $this->folder);

            return $this->common->API_Response(200, __('api_msg.user_record_get'), $data, $pagination);
           

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Get_Category(Request $request)
    {
        try{
            $page_size =0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Category::with(['subcategories'])->orderBy('id','DESC');  

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

                // Image Name to URL
                $this->common->imageNameToUrl($data, 'image', $this->folder2);

                return $this->common->API_Response(200, __('api_msg.category_record_get'), $data, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }


    public function Get_Package(Request $request)
    {
        try {
            $user_id = isset($request->user_id) ? $request->user_id : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Package::orderBy('id', 'DESC');

            $total_rows = $data->count();

            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            // Image Name to URL
                $this->common->imageNameToUrl($data, 'image', $this->folder4);

                for ($i = 0; $i < count($data); $i++) {
                    $data[$i]['is_buy'] = $this->common->is_buy($user_id,$data[$i]['id']);
                }
                
                return $this->common->API_Response(200, __('api_msg.package_record_get'), $data, $pagination);

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function latest_video(Request $request)
    {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Video::where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })->with('category','artist','user')->latest();

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();


            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);
                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder5, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder5, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('api_msg.video_record_get'), $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function latest_audio(Request $request)
    {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Audio::where('is_aiaudiobook', 0)->
            where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })->with('category','artist','user')->latest();

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();


            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $ra['audio'] = url('audio').'/'.$ra['audio'];

                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);
                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder5, $ra->url);

                }
                //$ra->image = $this->common->getImagePath($this->folder5, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, 'Audio list get succcesfully', $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function video_list(Request $request)
    {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');
            $data = Video::where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })
            ->with('category','artist','user')->latest();
            
            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();


            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);
                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder5, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder5, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('api_msg.video_record_get'), $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function audio_list(Request $request)
    {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Audio::where('is_aiaudiobook', 0)
            ->where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })->with('category','artist','user')->latest();

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();


            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $ra['audio'] = url('audio').'/'.$ra['audio'];
                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);
                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder7, $ra->url);

                }

                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, 'Audio list get succcesfully', $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function video_by_id(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'video_id' => 'required|numeric',
                ],
                [
                    'video_id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('video_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $video_id =$request['video_id'];
            $user_id = isset($request->user_id) ? $request->user_id : 0;

            $data = Video::where('id',$video_id)->with('user','category','artist')->get()->toArray();
            $rk = array();

            foreach ($data as $ra) {


                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->subscriptions = $this->common->getUserAllPlansWithBuyStatus($request['user_id']);

                // $ra->is_follw = "0";
                // if ($user_id != 0) {
                //     $ra->is_follw = $this->common->is_follw($request['user_id']);
                // }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder5, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder5, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                    $ra->artist_image = $this->common->getImagePath($this->folder_artist, $ra->artist['image']);
                }


                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

               
                

                $rk[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            if (sizeof($rk) > 0) {
                return $this->common->API_Response(200, __('api_msg.video_record_get'), $rk);
            } else {
                return $this->common->API_Response(400, __('api_msg.data_not_found'));
            }

        
        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Most_viewed_Video(Request $request)
    {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Video::where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })->orderBy('v_view','DESC')->with('category','artist','user')->latest();

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();


            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder5, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder5, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('api_msg.video_record_get'), $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Premium_Video(Request $request)
    {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Video::where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })->where('is_paid',"1")->with('category','artist','user')->latest();

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();


            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder5, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder5, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('api_msg.video_record_get'), $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Video_by_category(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'category_id' => 'required|numeric',
                ],
                [
                    'category_id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('category_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $category_id =$request->category_id;
            $user_id = isset($request->user_id) ? $request->user_id : 0;
            

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Video::where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })->where('category_id',$category_id)->with('user','category','artist')->latest();

            $total_rows = $data->count();

            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];

            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder5, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder5, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('api_msg.video_record_get'), $dataarray, $pagination);
        
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Video_by_artist(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'artist_id' => 'required|numeric',
                ],
                [
                    'artist_id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('category_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $artist_id = $request->artist_id;
            $user_id = isset($request->user_id) ? $request->user_id : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Video::where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })->where('artist_id',$artist_id)->with('user','artist','category')->latest();

            $total_rows = $data->count();

            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $follow =Follow::where('artist_id',$artist_id)->get()->count();

                $ra['follower_count'] = $follow;

                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder5, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder5, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('api_msg.video_record_get'), $dataarray, $pagination);


        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function artist_profile(Request $request)
    {
        try{

            $validation = Validator::make(
                $request->all(),
                [
                    'artist_id' => 'required|numeric',
                ],
                [
                    'artist_id.required' => "Artist Feild Is Required",
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

            $artist_id = $request->artist_id;
            $user_id = isset($request->user_id) ? $request->user_id : 0;


            $data = Artist::where('id',$artist_id)->first();

            if(!empty($data)){
                $artist_id =$data->id;

                $path = $this->common->getImagePath($this->folder_artist, $data['image']);
                $data['image'] = $path;

                $video =Video::where('artist_id',$artist_id)->count();
                

                $data['artist_video_count'] =$video;

                $data->is_follow = "0";
                if ($user_id != 0) {
                    $data->is_follow = $this->common->is_follow($request['user_id']);
                }

               


                return $this->common->API_Response(200, "Artist Profile Record Get", array($data));
            
            }else{
                return $this->common->API_Response(200, "Record Not Found");
                
            }

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }

    }
    
    public function Search_Video(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ],
                [
                    'name.required' => __('Name Feild Requried'),
                ]
            );
            if ($validation->fails()) {
                $errors = $validation->errors()->first('name');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $video_name = $request['name'];
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $resultr = $this->common->video_search($video_name);
            $data = array();

            foreach ($resultr as $ra) {

                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->image = $this->common->getImagePath($this->folder5, $ra->image);

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder5, $ra->url);
                    
                }

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

            

                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $data[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);

                

            }

            if (sizeof($data) > 0) {
                return $this->common->API_Response(200, __('Video record fetched successfully'), $data);
            } else {
                return $this->common->API_Response(400, __('Data not found'));
            }

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Get_Artist(Request $request)
    {
        try{
            $page_size =0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Artist::orderBy('id','DESC');  

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);
            
            // Image Name to URL
                $this->common->imageNameToUrl($data, 'image', $this->folder_artist);

                return $this->common->API_Response(200, __('api_msg.artist_record_get'), $data, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Get_Album(Request $request)
    {
        try{
            $page_size =0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Album::orderBy('id','DESC');  

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

                for ($i=0; $i < count($data); $i++) {
                    $C_Ids = explode(',', $data[$i]['video_id']);

                    $data[$i]['video_name'] = $this->common->GetVidoeNameByIds($data[$i]['video_id']);


                    if(!empty($data[$i]['image'])){
                        
                        $path = $this->common->getImagePath($this->folder_album, $data[$i]['image']);
                        $data[$i]['image'] = $path;

                    }else{
                        $data[$i]['image'] = asset('/assets/imgs/no_img.png');
                    }
                }

                return $this->common->API_Response(200, __('api_msg.album_record_get'), $data, $pagination);

          

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Get_Album_By_Video(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'album_id' => 'required|numeric',
                ],
                [
                    'album_id.required' => __('api_msg.please_enter_required_fields'),
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

            $album_id = $request->album_id;
            $user_id = isset($request->user_id) ? $request->user_id : 0;
            $data = array();

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $result = album::where('id',$album_id)->get();

            if(!empty(sizeof($result))){
                foreach ($result as $key => $value) {
                    $C_Ids = explode(',', $value['video_id']);
                }
                $data = Video::where(function ($query) {
                    // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                    $query->where('is_created_by_admin', 1)
                          ->orWhere(function ($subquery) {
                              $subquery->where('is_created_by_admin', 0)
                                       ->where('is_approved', 1);
                          });
                })->whereIn('id', $C_Ids)->with('category','artist','user')->orderBy('created_at', 'desc');
    
                $total_rows = $data->count();
    
                $total_page = $page_limit;
                $page_size = ceil($total_rows / $total_page);
                $current_page = $request->page_no ?? 1;
                $offset = $current_page * $total_page - $total_page;
                $data->take($total_page)->offset($offset);
    
                $more_page = $this->common->more_page($current_page, $page_size);
    
                $data = $data->get()->toArray();
    
                $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);
    
                $dataarray = [];
    
                foreach ($data as $ra) {
    
                    $datas = $this->common->get_all_count_for_video($ra['id'], $user_id);
                    $ra = (object) array_merge((array) $ra, $datas);
    
                    $ra->image = $this->common->getImagePath($this->folder5, $ra->image);
    
                    if($ra->video_type =="server_video"){
                        $ra->url = $this->common->getImagePath($this->folder5, $ra->url);
    
                    }
    
                    $ra->is_like = "0";
                    if ($user_id != 0) {
                        $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                    }
    
                    $ra->category_name = "";
                    if (isset($ra->category['name'])) {
                        $ra->category_name = $ra->category['name'];
                    }
    
                    $ra->artist_name = "";
                    if (isset($ra->artist['name'])) {
                        $ra->artist_name = $ra->artist['name'];
                        $ra->artist_image = $this->common->getImagePath($this->folder_artist, $ra->artist['image']);
    
                    }
    
                    $ra->full_name = "";
                    $ra->user_name = "";
                    $ra->profile_img = asset('/assets/imgs/users.png');
                    if (isset($ra->user)) {
                        $ra->full_name = $ra->user['full_name'];
                        $ra->user_name = $ra->user['user_name'];
                        $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                    }
    
                    $dataarray[] = $ra;
                    unset($ra->category, $ra->user, $ra->artist);
                   
                }
    
                return $this->common->API_Response(200, __('Success'), $dataarray, $pagination); 
            }else{
                return $this->common->API_Response(200, __('Success')); 

            }

              

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Get_Notification(Request $request)
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
            $arr = [];

            $all_noti = Notification::all();
            $user_noti = User_Notification_Tracking::where('user_id', $user_id)->get();

            for ($i = 0; $i < count($user_noti); $i++) {
                $arr[] = $user_noti[$i]->notification_id;
            }

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Notification::select('*')->where('user_id',$request->user_id)->whereNotIn('id', $arr)->orderBy('id', 'desc');
            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $return = [];
            foreach ($data as $key => $value) {

                if ($value['type'] == 3) {

                    $post_user = User::where('id', $user_id)->first();

                    $value['post_user_image'] = asset('/assets/imgs/users.png');
                    if (isset($post_user['id'])) {
                        $value['profile_image'] = $this->common->getImagePath($this->folder, $post_user['image']);
                    }

                } else {

                    $post_video = Video::where('id', $value['video_id'])->first();

                    $value['post_user_image'] = asset('/assets/imgs/no_img.png');;
                    if (isset($post_video['id'])) {
                        $value['profile_image'] = $this->common->getImagePath($this->folder5, $post_video['image']);
                    }
                }

                $value['full_name'] = "";
                $value['profile_img'] = asset('/assets/imgs/users.png');
                
                $return[] = $value;
            }

            return $this->common->API_Response(200, __('api_msg.notification_record_get'), $return, $pagination);
           


        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Read_Notification(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                    'notification_id' => 'required|numeric',
                ],
                [
                    'user_id.required' => __('api_msg.user_id_required'),
                    'notification_id.required' => __('api_msg.notification_id_required'),
                ]
            );
            if ($validation->fails()) {
                $errors = $validation->errors()->first('user_id');
                $errors1 = $validation->errors()->first('notification_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                } elseif ($errors1) {
                    $data['message'] = $errors1;
                }
                return $data;
            }

            $user_id =$request->user_id;
            $notification_id =$request->notification_id;

            $insert =new User_Notification_Tracking();
            $insert->user_id =$user_id;
            $insert->notification_id =$notification_id;

            if ($insert->save()) {

                return $this->common->API_Response(200, __('api_msg.notification_read'),array($insert));
            } else {
                return $this->common->API_Response(200, __('api_msg.notification_not_read'));
            }

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Add_transaction(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                    'package_id' => 'required|numeric',
                    'amount' => 'required|numeric',
                ],
                [
                    'user_id.required' => __('api_msg.user_id_required'),
                    'package_id.required' => __('api_msg.package_id_required'),
                    'amount.required' => __('api_msg.amount_required'),
                ]
            );
            if ($validation->fails()) {
                $errors = $validation->errors()->first('user_id');
                $errors1 = $validation->errors()->first('package_id');
                $errors2 = $validation->errors()->first('amount');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                } elseif ($errors1) {
                    $data['message'] = $errors1;
                } elseif ($errors2) {
                    $data['message'] = $errors2;
                }
                return $data;
            }

            $user_id =$request->user_id;
            $package_id =$request->package_id;
            $amount =$request->amount;
            
            $description = isset($request->description) ? $request->description : "";
            $payment_id = isset($request->payment_id) ? $request->payment_id : "";
            $currency_code = isset($request->currency_code) ? $request->currency_code : currency_code();

            $Pdata = Package::where('id',$package_id)->where('status','1')->first();
            if(!empty($Pdata)){
                $Edate =date("Y-m-d" ,strtotime("$Pdata->time $Pdata->type"));
            } else {
                return $this->common->API_Response(400, __('api_msg.please_enter_right_package_id'));
            }

            $insert = new Transaction();
            $insert->user_id =$user_id;
            $insert->package_id =$package_id;
            $insert->description = $description;
            $insert->amount = $amount;
            $insert->payment_id = $payment_id;
            $insert->currency_code = $currency_code;
            $insert->expiry_date = $Edate;
            $insert->status = '1';

            if($insert->save()){
                return $this->common->API_Response(200, __('api_msg.transaction_succesfully'));

            } else {
                return $this->common->API_Response(400, __('api_msg.data_not_save'));
            }

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Add_Aiaudio_transaction(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                    'amount' => 'required|numeric',
                    'aiaudio_id' => 'required|numeric',
                ],
                [
                    'user_id.required' => __('api_msg.user_id_required'),
                    'aiaudio_id.required' => __('api_msg.aiaudio_id_required'),
                    'amount.required' => __('api_msg.amount_required'),
                ]
            );
            if ($validation->fails()) {
                $errors = $validation->errors()->first('user_id');
                $errors1 = $validation->errors()->first('aiaudio_id');
                $errors2 = $validation->errors()->first('amount');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                } elseif ($errors1) {
                    $data['message'] = $errors1;
                } elseif ($errors2) {
                    $data['message'] = $errors2;
                }
                return $data;
            }

            $user_id =$request->user_id;
            $aiAudio_id =$request->aiaudio_id;
            $amount =$request->amount;
            
            $payment_id = isset($request->payment_id) ? $request->payment_id : "";
            $currency_code = isset($request->currency_code) ? $request->currency_code : currency_code();

            $Pdata = Audio::where('id',$aiAudio_id)->where('is_approved','1')->where('is_paid',"1")->where('is_aiaudiobook',1)->first();
            if(!empty($Pdata)){
                $insert = new Audio_Transaction();
                $insert->user_id =$user_id;
                $insert->aiaudio_id =$aiAudio_id;
                $insert->amount = $amount;
                $insert->amount = $amount;
                $insert->payment_id = $payment_id;
                $insert->currency_code = $currency_code;
                $insert->is_purchased = '1';
                $insert->status = '1';
            } else {
                return $this->common->API_Response(400, __('api_msg.please_enter_right_aiaudio_data'));
            }

            if(!empty($Pdata) && isset($insert) && $insert->save()){
                return $this->common->API_Response(200, __('api_msg.transaction_succesfully'), array($insert));
            } else {
                return $this->common->API_Response(400, __('api_msg.data_not_save'));
            }

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function get_like_video(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                ],
                [
                    'user_id.required' => __('api_msg.user_id_is_required'),
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

            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');
            
            $result = Like::select('video_id')->where('user_id', $user_id)->get();

            $videoarray = array();
            foreach ($result as $row) {
                $videoarray[] = $row->video_id;
            }
            $video_ids = implode(',', array_map('intval', $videoarray));

            $Ids = explode(',', $video_ids);

            $data = Video::where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })->whereIn('id', $Ids)->with('user','artist','category')->orderBy('created_at', 'desc');

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder5, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder5, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('api_msg.get_record_successfully'), $dataarray, $pagination);

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function get_comment_video(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                ],
                [
                    'user_id.required' => __('api_msg.user_id_is_required'),
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

            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $result = Comment::select('video_id')->where('user_id', $user_id)->get();

            $videoarray = array();
            foreach ($result as $row) {
                $videoarray[] = $row->video_id;
            }
            $video_ids = implode(',', array_map('intval', $videoarray));

            $Ids = explode(',', $video_ids);

            $data = Video::where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })->whereIn('id', $Ids)->with('user','category','artist')->orderBy('created_at', 'desc');

            $total_rows = $data->count();

            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->image = $this->common->getImagePath($this->folder5, $ra->image);

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder5, $ra->url);

                }

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }


                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('api_msg.get_record_successfully'), $dataarray, $pagination);

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function uploadAudio(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                    'name' => 'required',
                    'artist_id' => 'required|numeric',
                    'category_id' => 'required|numeric',
                    'is_paid' => 'required|numeric',
                    'audio' => 'required',
                    'image' => 'required',
                    'description' => 'required',
                ]
            );
            if ($validation->fails()) {
                $errors = $validation->errors()->first();
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $params = $request->all();
            if (isset($params['image'])) {
                $files = $params['image'];
                $params['image'] = $this->common->saveImage($files, $this->folder7);
            }


            $audioFile = $request->file('audio');
            $fileName = time() . '.' . $audioFile->getClientOriginalExtension();
            $audioFile->move(public_path('audio'), $fileName);

            $params['audio'] = $fileName;
            //$requestData['user_id'] =isset($requestData['user_id']) ? $requestData['user_id'] : 0;

            //$requestData['v_view'] =0;
            //echo "<pre>";print_r($params);exit;
            $video_data = Audio::updateOrCreate(['id' => ''], $params);

            return $this->common->API_Response(200, 'record Get succcesfully', '', '');
           


        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    
    }

    public function ai_audio_book_list(Request $request)
    {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');
            
            $data = Audio::with('category','artist','user')->where('is_aiaudiobook', 1)->latest();
            
            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();
            //echo "<pre>";print_r($data);exit;

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $ra['audio'] = url('audio').'/'.$ra['audio'];
                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);
                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }


                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }
                
                $record_purchase = DB::select(
                    'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                    [
                        'aiaudio_id' => $ra->id,
                        'user_id' => $user_id,
                        'status' => 1,
                        'is_purchased' => 1
                    ]
                );
                if (!empty($record_purchase) || $ra->is_paid == 0) {
                    $ra->is_purchased = 1;
                }else{
                    $ra->is_purchased = 0;
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, 'AI Audio book list get succcesfully', $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function get_category_list(Request $request)
    {
        try{
            $data = Category::orderBy('id','DESC'); 
            $data = $data->get()->toArray();            
            if(!empty($data)){
                // Image Name to URL
                foreach ($data as $o=>$record) {
                    $data[$o]['image'] = $this->common->getImagePath($this->folder2, $record['image']);
                }  
            } 
            return $this->common->API_Response(200, __('api_msg.category_record_get'), $data);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }
    
    public function get_artist_list(Request $request)
    {
        try{
            $data = Artist::orderBy('id','DESC'); 
            $data = $data->get()->toArray();
            if(!empty($data)){
                // Image Name to URL
                foreach ($data as $o=>$record) {
                    $data[$o]['image'] = $this->common->getImagePath($this->folder_artist, $record['image']);
                }  
            }    
            return $this->common->API_Response(200, __('api_msg.artist_record_get'), $data);
        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    
    // Upload Video API :: 02-10-2023
    public function uploadVideo(Request $request)
    {
        try{
            if($request->video_type == "server_video"){
                $validation = Validator::make(
                    $request->all(),
                    [
                        'user_id' => 'required|numeric',
                        'name' => 'required',
                        'artist_id' => 'required|numeric',
                        'category_id' => 'required|numeric',
                        'is_paid' => 'required|numeric',
                        'video' => 'required', // Max size in kilobytes (10 MB)  'video' => 'required|mimes:mp4,mov,avi|max:10240'
                        'image' => 'required',
                        'description' => 'required',
                    ]
                );
            }else{
                $validation = Validator::make($request->all(), [
                    'user_id' => 'required|numeric',
                    'name' => 'required',
                    'artist_id' => 'required',
                    'category_id' => 'required',
                    'is_paid' => 'required|numeric',
                    'url' => 'required',
                    'description' => 'required',
                    'image' => 'required',
                ]);
            }

            if ($validation->fails()) {
                $errors = $validation->errors()->first();
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }
            
            $requestData = $request->all();
            //echo "<pre>";print_r($requestData);exit;
            if (isset($requestData['image'])) {
                $files = $requestData['image'];
                $requestData['image'] = $this->common->saveImage($files, $this->folder5);
            }

            if($requestData['video_type'] == "server_video") {
                $videoFile = $request->file('video');
                $VideofileName = time() . '_' . $videoFile->getClientOriginalName();
                $targetDir = base_path('storage/app/public/video');
                $videoFile->move($targetDir, $VideofileName);
                $requestData['url'] = $VideofileName;
            }

            if($requestData['video_type'] == "url" || $requestData['video_type'] == "youtube" || $requestData['video_type'] == "vimeo"){
                $requestData['url'] = $requestData['url'];
            }
            $requestData['id'] = isset($requestData['id'])?$requestData['id']:0;
            $requestData['user_id'] =isset($requestData['user_id']) ? $requestData['user_id'] : 0;
            
            //echo "<pre>";print_r($params);exit;
            $video_data = Video::updateOrCreate(['id' => $requestData['id']], $requestData);
            if(isset($video_data->id)){
                return $this->common->API_Response(200, 'Video uploaded successfully', '', '');
            }else{
                return $this->common->API_Response(401, 'Something went wrong to upload video', '', '');
            }
        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    //New Integration
    public function Premium_Audio(Request $request)
    {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');
            
            $data = Audio::where('is_paid',"1")->where('is_aiaudiobook',"0")
            ->where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })
            ->leftjoin('tbl_aiaudio_transaction', 'tbl_audio.id', '=', 'tbl_aiaudio_transaction.aiaudio_id')
            ->with('category','artist','user')
            ->select('tbl_audio.*', \DB::raw('CASE WHEN tbl_aiaudio_transaction.is_purchased = 1 THEN 1 ELSE 0 END AS is_purchased'))
            ->latest();

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();


            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('success'), $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Premium_AI_Audio(Request $request)
    {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            // $data = Audio::where('is_paid',"1")->where('is_aiaudiobook',"1")
            // ->leftjoin('tbl_aiaudio_transaction', 'tbl_audio.id', '=', 'tbl_aiaudio_transaction.aiaudio_id')
            // ->with('category','artist','user')
            // ->select('tbl_audio.*', \DB::raw('CASE WHEN tbl_aiaudio_transaction.is_purchased = 1 THEN 1 ELSE 0 END AS is_purchased'))
            // ->latest();
            
            $data = Audio::where('is_paid',"1")->where('is_aiaudiobook',"1")
            ->with('category','artist','user')
            ->select('tbl_audio.*')
            ->latest();

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();


            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_ai_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }
                
                $record_purchase = DB::select(
                    'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                    [
                        'aiaudio_id' => $ra->id,
                        'user_id' => $user_id,
                        'status' => 1,
                        'is_purchased' => 1
                    ]
                );
                if (!empty($record_purchase) || $ra->is_paid == 0) {
                    $ra->is_purchased = 1;
                }else{
                    $ra->is_purchased = 0;
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('success'), $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Most_Viewed_Audio(Request $request)
    {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Audio::orderBy('v_view','DESC')->where('is_aiaudiobook',"0")
            ->where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })
            ->leftjoin('tbl_aiaudio_transaction', 'tbl_audio.id', '=', 'tbl_aiaudio_transaction.aiaudio_id')
            ->with('category','artist','user')
            ->select('tbl_audio.*', \DB::raw('CASE WHEN tbl_aiaudio_transaction.is_purchased = 1 THEN 1 ELSE 0 END AS is_purchased'))
            ->latest();

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();


            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_audio($ra['id'], $user_id, $ra['user_id']);
                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }


                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('Most view Audio list'), $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Most_Viewed_AI_Audio(Request $request)
    {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Audio::orderBy('v_view','DESC')->where('is_aiaudiobook',"1")
            ->with('category','artist','user')
            ->select('tbl_audio.*')
            ->latest();

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();


            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_ai_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }


                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }
                
                $record_purchase = DB::select(
                    'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                    [
                        'aiaudio_id' => $ra->id,
                        'user_id' => $user_id,
                        'status' => 1,
                        'is_purchased' => 1
                    ]
                );
                if (!empty($record_purchase) || $ra->is_paid == 0) {
                    $ra->is_purchased = 1;
                }else{
                    $ra->is_purchased = 0;
                }
                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('Most view AI Audio list'), $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Audio_by_artist(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'artist_id' => 'required|numeric',
                ],
                [
                    'artist_id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('category_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $artist_id = $request->artist_id;
            $user_id = isset($request->user_id) ? $request->user_id : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Audio::where('artist_id',$artist_id)->where('is_aiaudiobook',"0")
            ->where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })
            ->with('user','artist','category')->latest();

            $total_rows = $data->count();

            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $follow =Follow::where('artist_id',$artist_id)->get()->count();

                $ra['follower_count'] = $follow;

                $data1 = $this->common->get_all_count_for_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder7, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('Audio By Artist list'), $dataarray, $pagination);


        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }
    
    public function AI_Audio_by_artist(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'artist_id' => 'required|numeric',
                ],
                [
                    'artist_id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('category_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $artist_id = $request->artist_id;
            $user_id = isset($request->user_id) ? $request->user_id : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Audio::where('artist_id',$artist_id)->where('is_aiaudiobook',"1")->with('user','artist','category')->latest();

            $total_rows = $data->count();

            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $follow =Follow::where('artist_id',$artist_id)->get()->count();

                $ra['follower_count'] = $follow;

                $data1 = $this->common->get_all_count_for_ai_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder7, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }
                
                $record_purchase = DB::select(
                    'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                    [
                        'aiaudio_id' => $ra->id,
                        'user_id' => $user_id,
                        'status' => 1,
                        'is_purchased' => 1
                    ]
                );
                if (!empty($record_purchase) || $ra->is_paid == 0) {
                    $ra->is_purchased = 1;
                }else{
                    $ra->is_purchased = 0;
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('Success'), $dataarray, $pagination);


        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Search_Audio(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ],
                [
                    'name.required' => __('api_msg.Name Feild Requried'),
                ]
            );
            if ($validation->fails()) {
                $errors = $validation->errors()->first('name');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $audio_name = $request['name'];
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $resultr = $this->common->audio_search($audio_name);
            $data = array();

            foreach ($resultr as $ra) {

                $data1 = $this->common->get_all_count_for_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder7, $ra->url);
                    
                }

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

            

                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $data[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);

                

            }

            if (sizeof($data) > 0) {
                return $this->common->API_Response(200, __('Success'), $data);
            } else {
                return $this->common->API_Response(400, __('api_msg.data_not_found'));
            }

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Search_AI_Audio(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ],
                [
                    'name.required' => __('api_msg.Name Feild Requried'),
                ]
            );
            if ($validation->fails()) {
                $errors = $validation->errors()->first('name');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $audio_name = $request['name'];
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $resultr = $this->common->ai_audio_search($audio_name);
            $data = array();

            foreach ($resultr as $ra) {

                $data1 = $this->common->get_all_count_for_ai_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder7, $ra->url);
                    
                }

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

            

                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }
                
                $record_purchase = DB::select(
                    'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                    [
                        'aiaudio_id' => $ra->id,
                        'user_id' => $user_id,
                        'status' => 1,
                        'is_purchased' => 1
                    ]
                );
                if (!empty($record_purchase) || $ra->is_paid == 0) {
                    $ra->is_purchased = 1;
                }else{
                    $ra->is_purchased = 0;
                }

                $data[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);

                

            }

            if (sizeof($data) > 0) {
                return $this->common->API_Response(200, __('Success'), $data);
            } else {
                return $this->common->API_Response(400, __('api_msg.data_not_found'));
            }

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function audio_by_id(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'audio_id' => 'required|numeric',
                ],
                [
                    'aduio_id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('audio_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $video_id =$request['audio_id'];
            $user_id = isset($request->user_id) ? $request->user_id : 0;
            
        
            $data = Audio::where('id',$video_id)->where('is_aiaudiobook',"0")
            ->where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })
            ->with('user','category','artist')->first();

            if ($data) {
                $result = $data->toArray();
                $ra = $result;
              
                $data1 = $this->common->get_all_count_for_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);
                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->subscriptions = $this->common->getUserAllPlansWithBuyStatus($request['user_id']);

                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                    $ra->artist_image = $this->common->getImagePath($this->folder_artist, $ra->artist['image']);
                }

                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }
                unset($ra->category, $ra->user, $ra->artist);
                return $this->common->API_Response(200, __('Success'), $ra);
            }else{
                return $this->common->API_Response(400, __('api_msg.data_not_found'));
            }
        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function ai_audio_by_id(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'video_id' => 'required|numeric',
                ],
                [
                    'video_id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('video_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }
            $video_id =$request['video_id'];
            $user_id = isset($request->user_id) ? $request->user_id : 0;

            $data = Audio::where('id',$video_id)->where('is_aiaudiobook',"1")->with('user','category','artist')->first();
            
            if ($data) {
                $ra = $data->toArray();
                $data1 = $this->common->get_all_count_for_ai_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->subscriptions = $this->common->getUserAllPlansWithBuyStatus($request['user_id']);

                // $ra->is_follw = "0";
                // if ($user_id != 0) {
                //     $ra->is_follw = $this->common->is_follw($request['user_id']);
                // }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder7, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                    $ra->artist_image = $this->common->getImagePath($this->folder_artist, $ra->artist['image']);
                }


                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }
                if($user_id == 0){
                    if($ra->is_paid == 0){
                        $ra->is_purchased = 1;
                    }else{
                        $ra->is_purchased = 0;
                    }
                }else{
                    $record_purchase = DB::select(
                        'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                        [
                            'aiaudio_id' => $ra->id,
                            'user_id' => $user_id,
                            'status' => 1,
                            'is_purchased' => 1
                        ]
                    );
                    if (!empty($record_purchase) || $ra->is_paid == 0) {
                        $ra->is_purchased = 1;
                    }else{
                        $ra->is_purchased = 0;
                    }
                }
                $playlist = DB::select('select upload_file from tbl_multiple_audio where audio_id = :audio_id', ['audio_id' => $video_id]);
                if (!empty($playlist)) {
                    $ra->audio = $playlist[0]->upload_file;
                }             
                unset($ra->category, $ra->user, $ra->artist);
                return $this->common->API_Response(200, __('Success'), $ra);

            } else {
                return $this->common->API_Response(400, __('api_msg.data_not_found'));
            }
        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Audio_by_category(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'category_id' => 'required|numeric',
                ],
                [
                    'category_id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('category_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $category_id =$request->category_id;
            $user_id = isset($request->user_id) ? $request->user_id : 0;
            

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Audio::where('category_id',$category_id)->where('is_aiaudiobook',"0")
            ->where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })
            ->with('user','category','artist')->latest();

            $total_rows = $data->count();

            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];

            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('Success'), $dataarray, $pagination);
        
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function AI_Audio_by_category(Request $request)
    {
        try {
            
            $validation = Validator::make(
                $request->all(),
                [
                    'category_id' => 'required|numeric',
                ],
                [
                    'category_id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('category_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $category_id =$request->category_id;
            $user_id = isset($request->user_id) ? $request->user_id : 0;
            

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = Audio::select('*')->where('category_id',$category_id)->where('is_aiaudiobook',"1")->with('user','category','artist')->latest();
            $total_rows = $data->count();

            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];

            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_ai_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }
                if($user_id == 0){
                    if($ra->is_paid == 0){
                        $ra->is_purchased = 1;
                    }else{
                        $ra->is_purchased = 0;
                    }
                }else{
                    $record_purchase = DB::select(
                        'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                        [
                            'aiaudio_id' => $ra->id,
                            'user_id' => $user_id,
                            'status' => 1,
                            'is_purchased' => 1
                        ]
                    );
                    if (!empty($record_purchase) || $ra->is_paid == 0) {
                        $ra->is_purchased = 1;
                    }else{
                        $ra->is_purchased = 0;
                    }
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('Success'), $dataarray, $pagination);
        
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Get_Album_By_Audio(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'album_id' => 'required|numeric',
                ],
                [
                    'album_id.required' => __('api_msg.please_enter_required_fields'),
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

            $album_id = $request->album_id;
            $user_id = isset($request->user_id) ? $request->user_id : 0;
            $data = array();

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $result = album::where('id',$album_id)->get();

            if(!empty(sizeof($result))){
                foreach ($result as $key => $value) {
                    $C_Ids = explode(',', $value['audio_id']);
                }
                $data = Audio::whereIn('id', $C_Ids)->where('is_aiaudiobook',"0")
                ->where(function ($query) {
                    // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                    $query->where('is_created_by_admin', 1)
                          ->orWhere(function ($subquery) {
                              $subquery->where('is_created_by_admin', 0)
                                       ->where('is_approved', 1);
                          });
                })
                ->with('category','artist','user')->orderBy('created_at', 'desc');
    
                $total_rows = $data->count();
    
                $total_page = $page_limit;
                $page_size = ceil($total_rows / $total_page);
                $current_page = $request->page_no ?? 1;
                $offset = $current_page * $total_page - $total_page;
                $data->take($total_page)->offset($offset);
    
                $more_page = $this->common->more_page($current_page, $page_size);
    
                $data = $data->get()->toArray();
    
                $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);
    
                $dataarray = [];
    
                foreach ($data as $ra) {
    
                    $datas = $this->common->get_all_count_for_audio($ra['id'], $user_id);
                    $ra = (object) array_merge((array) $ra, $datas);
    
                    $ra->image = $this->common->getImagePath($this->folder7, $ra->image);
    
    
                    $ra->is_like = "0";
                    if ($user_id != 0) {
                        $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                    }
    
                    $ra->category_name = "";
                    if (isset($ra->category['name'])) {
                        $ra->category_name = $ra->category['name'];
                    }
    
                    $ra->artist_name = "";
                    if (isset($ra->artist['name'])) {
                        $ra->artist_name = $ra->artist['name'];
                        $ra->artist_image = $this->common->getImagePath($this->folder_artist, $ra->artist['image']);
    
                    }
    
                    $ra->full_name = "";
                    $ra->user_name = "";
                    $ra->profile_img = asset('/assets/imgs/users.png');
                    if (isset($ra->user)) {
                        $ra->full_name = $ra->user['full_name'];
                        $ra->user_name = $ra->user['user_name'];
                        $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                    }
    
                    $dataarray[] = $ra;
                    unset($ra->category, $ra->user, $ra->artist);
                   
                }
    
                return $this->common->API_Response(200, __('Success'), $dataarray, $pagination); 
            }else{
                return $this->common->API_Response(200, __('No record found.')); 

            }

              

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function Get_Album_By_AI_Audio(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'album_id' => 'required|numeric',
                ],
                [
                    'album_id.required' => __('api_msg.please_enter_required_fields'),
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

            $album_id = $request->album_id;
            $user_id = isset($request->user_id) ? $request->user_id : 0;
            $data = array();

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $result = album::where('id',$album_id)->get();

            if(!empty(sizeof($result))){
                foreach ($result as $key => $value) {
                    $C_Ids = explode(',', $value['audiobook_id']);
                }
                $data = Audio::whereIn('id', $C_Ids)->with('category','artist','user')->orderBy('created_at', 'desc');
    
                $total_rows = $data->count();
    
                $total_page = $page_limit;
                $page_size = ceil($total_rows / $total_page);
                $current_page = $request->page_no ?? 1;
                $offset = $current_page * $total_page - $total_page;
                $data->take($total_page)->offset($offset);
    
                $more_page = $this->common->more_page($current_page, $page_size);
    
                $data = $data->get()->toArray();
    
                $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);
    
                $dataarray = [];
    
                foreach ($data as $ra) {
    
                    $datas = $this->common->get_all_count_for_ai_audio($ra['id'], $user_id);
                    $ra = (object) array_merge((array) $ra, $datas);
    
                    $ra->image = $this->common->getImagePath($this->folder7, $ra->image);
    
    
                    $ra->is_like = "0";
                    if ($user_id != 0) {
                        $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                    }
    
                    $ra->category_name = "";
                    if (isset($ra->category['name'])) {
                        $ra->category_name = $ra->category['name'];
                    }
    
                    $ra->artist_name = "";
                    if (isset($ra->artist['name'])) {
                        $ra->artist_name = $ra->artist['name'];
                        $ra->artist_image = $this->common->getImagePath($this->folder_artist, $ra->artist['image']);
    
                    }
    
                    $ra->full_name = "";
                    $ra->user_name = "";
                    $ra->profile_img = asset('/assets/imgs/users.png');
                    if (isset($ra->user)) {
                        $ra->full_name = $ra->user['full_name'];
                        $ra->user_name = $ra->user['user_name'];
                        $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                    }
                    
                    $record_purchase = DB::select(
                        'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                        [
                            'aiaudio_id' => $ra->id,
                            'user_id' => $user_id,
                            'status' => 1,
                            'is_purchased' => 1
                        ]
                    );
                    if (!empty($record_purchase) || $ra->is_paid == 0) {
                        $ra->is_purchased = 1;
                    }else{
                        $ra->is_purchased = 0;
                    }
    
                    $dataarray[] = $ra;
                    unset($ra->category, $ra->user, $ra->artist);
                   
                }
    
                return $this->common->API_Response(200, __('Success'), $dataarray, $pagination); 
            }else{
                return $this->common->API_Response(200, __('No record found')); 

            }

              

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function get_like_audio(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                ],
                [
                    'user_id.required' => __('api_msg.user_id_is_required'),
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

            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');
            
            $result = Like::select('video_id')->where('user_id', $user_id)->where('type', 'audio')->get();

            $videoarray = array();
            foreach ($result as $row) {
                $videoarray[] = $row->video_id;
            }
            $video_ids = implode(',', array_map('intval', $videoarray));

            $Ids = explode(',', $video_ids);

            $data = Audio::whereIn('id', $Ids)->where('is_aiaudiobook',"0")
            ->where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })
            ->with('user','artist','category')->orderBy('created_at', 'desc');

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder7, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('api_msg.get_record_successfully'), $dataarray, $pagination);

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function get_like_ai_audio(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                ],
                [
                    'user_id.required' => __('api_msg.user_id_is_required'),
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

            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');
            
            $result = Like::select('video_id')->where('user_id', $user_id)->where('type', 'aiaudio')->get();

            $videoarray = array();
            foreach ($result as $row) {
                $videoarray[] = $row->video_id;
            }
            $video_ids = implode(',', array_map('intval', $videoarray));

            $Ids = explode(',', $video_ids);
            
            $data = Audio::whereIn('tbl_audio.id', $Ids)
            ->where('tbl_audio.is_aiaudiobook', '1')
            ->select('tbl_audio.*')
            ->with(['user', 'artist', 'category'])
            ->orderBy('tbl_audio.created_at', 'desc');
            
            
            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_ai_audio($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                if($ra->video_type =="server_video"){
                    $ra->url = $this->common->getImagePath($this->folder7, $ra->url);

                }
                $ra->image = $this->common->getImagePath($this->folder7, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }
                
                $record_purchase = DB::select(
                    'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                    [
                        'aiaudio_id' => $ra->id,
                        'user_id' => $user_id,
                        'status' => 1,
                        'is_purchased' => 1
                    ]
                );
                if (!empty($record_purchase) || $ra->is_paid == 0) {
                    $ra->is_purchased = 1;
                }else{
                    $ra->is_purchased = 0;
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('api_msg.get_record_successfully'), $dataarray, $pagination);

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }
    
    
    public function get_timestemp(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                    'audio_id' => 'required|numeric',
                    'is_audiobook' => 'required|numeric',
                    'timestamp' => 'required',
                ],
                [
                    'user_id.required' => __('api_msg.user_id_is_required'),
                    'audio_id.required' => __('api_msg.audio_id_is_required'),
                    'is_audiobook.required' => __('api_msg.is_audiobook_is_required'),
                    'timestamp.required' => __('api_msg.timestamp_is_required'),
                ]
            );
            if ($validation->fails()) {
                $errors = $validation->errors()->first('user_id');
                $errors1 = $validation->errors()->first('audio_id');
                $errors2 = $validation->errors()->first('is_audiobook');
                $errors3 = $validation->errors()->first('timestamp');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                } elseif ($errors1) {
                    $data['message'] = $errors1;
                } elseif ($errors2) {
                    $data['message'] = $errors2;
                } elseif ($errors3) {
                    $data['message'] = $errors3;
                }
                return $data;
            }
            
            $user_id =$request->user_id;
            $audio_id =$request->audio_id;
            $is_audiobook =$request->is_audiobook;
            $timestemp =$request->timestamp;
            $data = Timestemp::where('audio_id',$audio_id)->where('user_id',$user_id)->where('is_audiobook',$is_audiobook)->first();
            
            if($data){
                $update = Timestemp::where('timestemp_id', $data['timestemp_id'])->update(['timestemp' => $timestemp]);
                if($update){
                    return $this->common->API_Response(200, __('api_msg.timestemp_update_successfully'));
                }else{
                    return $this->common->API_Response(400, __('api_msg.data_not_save'));
                }
            }else{
                $insert = new Timestemp();
                $insert->user_id =$user_id;
                $insert->audio_id =$audio_id;
                $insert->is_audiobook = $is_audiobook;
                $insert->timestemp = $timestemp;
                
                if($insert->save()){
                    return $this->common->API_Response(200, __('api_msg.timestemp_store_successfully'));
    
                } else {
                    return $this->common->API_Response(400, __('api_msg.data_not_save'));
                }
            }
            

            
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }
    
    public function audio_view_timestemp(Request $request){
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                    'is_audiobook' => 'required|numeric',
                ],
                [
                    'user_id.required' => __('api_msg.user_id_is_required'),
                    'is_audiobook.required' => __('api_msg.is_audiobook_is_required'),
                ]
            );
            if ($validation->fails()) {
                $errors = $validation->errors()->first('user_id');
                $errors1 = $validation->errors()->first('is_audiobook');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                } elseif ($errors1) {
                    $data['message'] = $errors1;
                }
                return $data;
            }
            
            $user_id =$request->user_id;
            $is_audiobook =$request->is_audiobook;
            
            $data = Audio::where('is_aiaudiobook',$is_audiobook)
                        ->with('user','artist','category')->orderBy('created_at', 'desc')
                        ->leftJoin('tbl_timestemp', function ($join) use ($user_id,$is_audiobook) {
                            $join->on('tbl_audio.id', '=', 'tbl_timestemp.audio_id')
                            ->where('tbl_timestemp.user_id',$user_id)
                            ->where('is_audiobook', $is_audiobook);
                        })
                        ->leftJoin('tbl_aiaudio_transaction', function ($join) use ($user_id) {
                            $join->on('tbl_audio.id', '=', 'tbl_aiaudio_transaction.aiaudio_id')
                                ->where('tbl_aiaudio_transaction.user_id', $user_id);
                        })
                        ->select('tbl_audio.*', 'tbl_timestemp.timestemp as timestemp',DB::raw('CASE WHEN tbl_audio.is_paid = "0" THEN 1 ELSE COALESCE(tbl_aiaudio_transaction.is_purchased, 0) END as is_purchased'))->get()->toArray();
            if($data){
                return $this->common->API_Response(200, __('api_msg.get_record_successfully'), $data);

            } else {
                return $this->common->API_Response(400, __('api_msg.data_not_save'));
            }

            
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }
    
    public function bundel_audio_list(Request $request){
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'audio_id' => 'required|numeric',
                ],
                [
                    'audio_id.required' => __('Audio id is required'),
                ]
            );
            
            if ($validation->fails()) {
                $errors1 = $validation->errors()->first('audio_id');
                $data['status'] = 400;
                if ($errors1) {
                    $data['message'] = $errors1;
                }
                return $data;
            }
            $audio_id = $request->audio_id;
            
            $data = Audio::where('id',$audio_id)->where('is_approved',1)->get();
            if(!empty($data)){
                $playlist = DB::select('select * from tbl_multiple_audio where audio_id = :audio_id', ['audio_id' => $audio_id]);;
                
                if(!empty($playlist)){
                    return $this->common->API_Response(200, __('api_msg.get_record_successfully'), $playlist);
                } else {
                    return $this->common->API_Response(200, __('No album record found'));
                }
                
            } else {
                return $this->common->API_Response(200, __('No audio record found')); 
            }
            
            
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    // Get E-Book List
    public function ebookList(Request $request) {
        try {
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;
            $current_page = $request->page_no ?? 1;
    
            // Generate unique cache key for this user and page
            $cacheKey = "user_liked_ebooks_{$user_id}_page_{$current_page}";
    
            // Check if cached data exists
            // return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($request, $user_id, $current_page) {
                $page_size = 0;
                $more_page = false;
                $page_limit = env('PAGE_LIMIT');
    
                $data = EBook::with('category', 'artist', 'user', 'multipleEbooks')->latest();
                $total_rows = $data->count();
                $total_page = $page_limit;
                $page_size = ceil($total_rows / $total_page);
                $offset = $current_page * $total_page - $total_page;
                $data->take($total_page)->offset($offset);
                $more_page = $this->common->more_page($current_page, $page_size);
    
                $data = $data->get()->toArray();
                $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);
    
                $dataarray = [];                
                foreach ($data as $ra) {
                    $ra['file_url'] = url('storage/documents/' . $ra['upload_file']);
                    $data1 = $this->common->get_all_count_for_ebook($ra['id'], $user_id, $ra['user_id']);
                    $ra = (object) array_merge((array) $ra, $data1);

                    $ra->image = $this->common->getImagePath($this->folder_ebook, $ra->image);
                    $ra->category_name = $ra->category['name'] ?? "";
                    $ra->author_name = $ra->author['name'] ?? "";
                    $ra->full_name = $ra->user['full_name'] ?? "";
                    $ra->user_name = $ra->user['user_name'] ?? "";
                    $ra->profile_img = isset($ra->user) ? $this->common->getImagePath($this->folder, $ra->user['image']) : asset('/assets/imgs/users.png');

                    $record_purchase = DB::select(
                        'select is_purchased from tbl_ebook_transaction where ebook_id = :ebook_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                        [
                            'ebook_id' => $ra->id,
                            'user_id' => $user_id,
                            'status' => 1,
                            'is_purchased' => 1
                        ]
                    );

                    if (!empty($record_purchase) || $ra->is_paid == 0) {
                        $ra->is_purchased = 1;
                    } else{
                        $ra->is_purchased = 0;
                    }
    
                    $dataarray[] = $ra;
                    unset($ra->category, $ra->user, $ra->author);
                }
    
                return $this->common->API_Response(200, 'E-Book list retrieved successfully', $dataarray, $pagination);
            // });
    
        } catch (Exception $e) {
            return response()->json(['status' => 400, 'errors' => $e->getMessage()]);
        }
    }    

    // E-Book
    public function get_like_ebook(Request $request)
    {
        try {
            $rules = [
                'user_id' => 'required|numeric',
            ];
            
            $messages = [
                'user_id.required' => 'User ID is required.',
                'user_id.numeric' => 'User ID must be a number.',
            ];
            
            $validator = Validator::make($request->all(), $rules, $messages);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'message' => $validator->errors()->first(),
                ]);
            }            

            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');
            
            $result = Like::select('video_id')->where('user_id', $user_id)->where('type', 'ebook')->get();

            $videoarray = array();
            foreach ($result as $row) {
                $videoarray[] = $row->video_id;
            }
            $video_ids = implode(',', array_map('intval', $videoarray));

            $Ids = explode(',', $video_ids);

            $data = EBook::with('category', 'artist', 'user', 'multipleEbooks')->where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })->whereIn('id', $Ids)->with('user','artist','category')->orderBy('created_at', 'desc');

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_ebook($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->image = $this->common->getImagePath($this->folder_ebook, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('api_msg.get_record_successfully'), $dataarray, $pagination);

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function get_comment_ebook(Request $request)
    {
        try {
            $rules = [
                'user_id' => 'required|numeric',
            ];
            
            $messages = [
                'user_id.required' => 'User ID is required.',
                'user_id.numeric' => 'User ID must be a number.',
            ];
            
            $validator = Validator::make($request->all(), $rules, $messages);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'message' => $validator->errors()->first(),
                ]);
            }            

            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $result = Comment::select('video_id')->where('user_id', $user_id)->where('type', 'ebook')->get();

            $videoarray = array();
            foreach ($result as $row) {
                $videoarray[] = $row->video_id;
            }
            $video_ids = implode(',', array_map('intval', $videoarray));

            $Ids = explode(',', $video_ids);

            $data = EBook::where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })->whereIn('id', $Ids)->with('user','category','artist')->orderBy('created_at', 'desc');

            $total_rows = $data->count();

            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_ebook($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->image = $this->common->getImagePath($this->folder_ebook, $ra->image);

                // if($ra->video_type =="server_video"){
                //     $ra->url = $this->common->getImagePath($this->folder5, $ra->url);

                // }

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }


                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('api_msg.get_record_successfully'), $dataarray, $pagination);

        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function ebook_by_category(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'category_id' => 'required|numeric',
                ],
                [
                    'category_id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('category_id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $category_id =$request->category_id;
            $user_id = isset($request->user_id) ? $request->user_id : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = EBook::where(function ($query) {
                // Consider either (is_created_by_admin = 1) or (is_created_by_admin = 0 and is_approved = 1)
                $query->where('is_created_by_admin', 1)
                      ->orWhere(function ($subquery) {
                          $subquery->where('is_created_by_admin', 0)
                                   ->where('is_approved', 1);
                      });
            })->with('category', 'artist', 'user', 'multipleEbooks')->orderBy('created_at', 'desc');

            $total_rows = $data->count();

            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];

            foreach ($data as $ra) {

                $data1 = $this->common->get_all_count_for_ebook($ra['id'], $user_id, $ra['user_id']);

                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }

                $ra->image = $this->common->getImagePath($this->folder_ebook, $ra->image);

                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, __('Success'), $dataarray, $pagination);
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function get_ebook_timestamp(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                    'ebook_id' => 'required|numeric',
                    'timestamp' => 'required',
                ],
                [
                    'user_id.required' => 'User ID is required.',
                    'ebook_id.required' => 'Ebook ID is required.',
                    'timestamp.required' => 'Timestamp is required.',
                ]
            );

            if ($validation->fails()) {
                $errors = $validation->errors()->first('user_id') 
                        ?? $validation->errors()->first('ebook_id') 
                        ?? $validation->errors()->first('timestamp');

                return [
                    'status' => 400,
                    'message' => $errors,
                ];
            }

            $user_id = $request->user_id;
            $ebook_id = $request->ebook_id;
            $timestamp = $request->timestamp;

            $data = EbookTimestamp::where('ebook_id', $ebook_id)
                        ->where('user_id', $user_id)
                        ->first();

            if ($data) {
                $updated = $data->update(['timestamp' => $timestamp]);
                if ($updated) {
                    return $this->common->API_Response(200, 'Timestamp updated successfully.');
                }
                return $this->common->API_Response(400, 'Failed to save data.');
            } else {
                $insert = new EbookTimestamp();
                $insert->user_id = $user_id;
                $insert->ebook_id = $ebook_id;
                $insert->timestamp = $timestamp;

                if ($insert->save()) {
                    return $this->common->API_Response(200, 'Timestamp stored successfully.');
                }
                return $this->common->API_Response(400, 'Failed to save data.');
            }

        } catch (Exception $e) {
            return response()->json(['status' => 400, 'errors' => $e->getMessage()]);
        }
    }

    public function ebook_view_timestamp(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                ],
                [
                    'user_id.required' => 'User ID is required.',
                ]
            );

            if ($validation->fails()) {
                return [
                    'status' => 400,
                    'message' => $validation->errors()->first('user_id'),
                ];
            }

            $user_id = $request->user_id;

            $data = Ebook::with('category', 'artist', 'user', 'multipleEbooks') // Add relationships as needed
                ->orderBy('created_at', 'desc')
                ->leftJoin('ebook_timestamps', function ($join) use ($user_id) {
                    $join->on('tbl_ebooks.id', '=', 'ebook_timestamps.ebook_id')
                        ->where('ebook_timestamps.user_id', $user_id);
                })
                ->select('tbl_ebooks.*', 'ebook_timestamps.timestamp as timestamp')
                ->get();

            if ($data) {

                $dataarray = [];
                foreach ($data as $ra) {

                    

                    // $ra = (object) array_merge((array) $ra, $data1);

                    // return response()->json($ra);

                    $ra->is_like = "0";
                    if ($user_id != 0) {
                        $ra->is_like = $this->common->is_like($user_id, $ra->id);
                    }

                    $ra->image = $this->common->getImagePath($this->folder_ebook, $ra->image);

                    $ra->category_name = "";
                    if (isset($ra->category['name'])) {
                        $ra->category_name = $ra->category['name'];
                    }

                    $ra->artist_name = "";
                    if (isset($ra->artist['name'])) {
                        $ra->artist_name = $ra->artist['name'];
                    }

                
                    $ra->full_name = "";
                    $ra->user_name = "";
                    $ra->profile_img = asset('/assets/imgs/users.png');
                    if (isset($ra->user)) {
                        $ra->full_name = $ra->user['full_name'];
                        $ra->user_name = $ra->user['user_name'];
                        $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                    }

                    $data1 = $this->common->get_all_count_for_ebook($ra['id'], $user_id, $ra['user_id']);
                    $dataarray[] = $ra;
                    unset($ra->category, $ra->user, $ra->artist);
                }
                
                return $this->common->API_Response(200, 'Record fetched successfully.', $dataarray);
            }

            return $this->common->API_Response(400, 'Failed to fetch data.');
        } catch (Exception $e) {
            return response()->json(['status' => 400, 'errors' => $e->getMessage()]);
        }
    }

    public function ebook_by_id(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'ebook_id' => 'required|numeric',
                ],
                [
                    'ebook_id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );

            if ($validation->fails()) {
                $errors = $validation->errors()->first('ebook_id');
                return [
                    'status' => 400,
                    'message' => $errors,
                ];
            }

            $ebook_id = $request->ebook_id;
            $user_id = $request->user_id ?? 0;

            $ebook = EBook::with('category', 'artist', 'user', 'multipleEbooks')
                ->where('id', $ebook_id)
                ->first();

            if (!$ebook) {
                return $this->common->API_Response(400, __('api_msg.data_not_found'));
            }

            $ra = $ebook->toArray();
            $ra['file_url'] = url('public/storage/e-book/' . $ra['upload_file']);

            $data1 = $this->common->get_all_count_for_ebook($ra['id'], $user_id, $ra['user_id']);
            $ra = (object) array_merge($ra, $data1);

            $ra->image = $this->common->getImagePath($this->folder_ebook, $ra->image);
            $ra->category_name = $ra->category['name'] ?? "";
            $ra->author_name = $ra->artist['name'] ?? "";
            $ra->full_name = $ra->user['full_name'] ?? "";
            $ra->user_name = $ra->user['user_name'] ?? "";
            $ra->profile_img = isset($ra->user)
                ? $this->common->getImagePath($this->folder, $ra->user['image'])
                : asset('/assets/imgs/users.png');

            $record_purchase = DB::select(
                'select is_purchased from tbl_ebook_transaction where ebook_id = :ebook_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                [
                    'ebook_id' => $ra->id,
                    'user_id' => $user_id,
                    'status' => 1,
                    'is_purchased' => 1
                ]
            );

            if (!empty($record_purchase) || $ra->is_paid == 0) {
                $ra->is_purchased = 1;
            } else{
                $ra->is_purchased = 0;
            }

            // unset($ra->category, $ra->user, $ra->artist);

            return $this->common->API_Response(200, __('Success'), $ra);
        } catch (Exception $e) {
            return response()->json(['status' => 400, 'errors' => $e->getMessage()]);
        }
    }

    public function smartCollectionsList(Request $request)
    {
        try {
            $currentPage = $request->input('page_no', 1);
            $pageLimit = env('PAGE_LIMIT', 10); // fallback to 10 if not set
            $offset = ($currentPage - 1) * $pageLimit;

            $query = SmartCollection::with(['items'])->where('status', 1)->orderByDesc('id');
            $totalRows = $query->count();
            $totalPages = ceil($totalRows / $pageLimit);
            $morePage = $this->common->more_page($currentPage, $totalPages);

            $collections = $query->offset($offset)->limit($pageLimit)->get();

            $pagination = $this->common->pagination_array($totalRows, $totalPages, $currentPage, $morePage);

            return $this->common->API_Response(200, 'Smart collections retrieved successfully', $collections, $pagination);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 400,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function smartCollectionById(Request $request)
    {
        try {
            $id = $request->input('smart_collection_id');

            if (!$id) {
                return $this->common->API_Response(422, 'smart_collection_id is required');
            }

            $collection = SmartCollection::with('items')->where('status', 1)->find($id);

            if (!$collection) {
                return $this->common->API_Response(404, 'Smart collection not found');
            }

            return $this->common->API_Response(200, 'Smart collection retrieved successfully', $collection);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createCustomPackagePriorPayment(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'price' => 'required|numeric',
                'time' => ['required', 'regex:/^\d+$/'],
                'type' => 'required|in:Day,Week,Month,Year',
                'smart_collection_ids' => 'required|array',
                'smart_collection_ids.*' => 'exists:tbl_smart_collections,id',
                'user_id' => 'required|exists:tbl_user,id',
            ],
            [
                'price.required' => 'Price is required.',
                'price.numeric' => 'Price must be a number.',
                'time.required' => 'Time duration is required.',
                'time.regex' => 'Time must be a whole number (e.g. 1, 2, 30).',
                'type.required' => 'Package type is required.',
                'type.in' => 'Type must be one of: Day, Week, Month, or Year.',
                'smart_collection_ids.required' => 'At least one smart collection must be selected.',
                'smart_collection_ids.array' => 'Smart collections must be an array.',
                'smart_collection_ids.*.exists' => 'One or more selected smart collections are invalid.',
                'user_id.required' => 'User is required.',
                'user_id.exists' => 'Selected user does not exist.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ]);
        }

        $postData = $validator->validated();

        DB::beginTransaction();

        try {
            $user = User::find($postData['user_id']);
            $packageName = $user->full_name . ' - ' . $postData['type'] . ' Package';

            $customPackage = CustomPackage::create([
                'name' => $packageName,
                'price' => $postData['price'],
                'currency_type' => currency_code(),
                'time' => $postData['time'],
                'type' => $postData['type'],
                'status' => 0,  // Set status to 0 before payment
            ]);

            foreach ($postData['smart_collection_ids'] as $smartCollectionId) {
                CustomPackageSmartCollection::create([
                    'custom_package_id' => $customPackage->id,
                    'smart_collection_id' => $smartCollectionId,
                ]);
            }

            DB::commit();

            // Return the created custom package data in the response
            return $this->common->API_Response(200, __('Custom package created successfully, awaiting payment.'), [
                'custom_package' => $customPackage
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 400,
                'errors' => $e->getMessage()
            ]);
        }
    }

    public function confirmPackagePayment(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'user_id' => 'required|exists:tbl_user,id',
                'custom_package_id' => 'required|exists:tbl_custom_packages,id',
                'payment_id' => 'required|string',
                'amount' => 'required|numeric',
            ],
            [
                'user_id.required' => 'User is required.',
                'user_id.exists' => 'User not found.',
                'custom_package_id.required' => 'Custom package ID is required.',
                'custom_package_id.exists' => 'Custom package not found.',
                'payment_id.required' => 'Payment ID is required.',
                'amount.required' => 'Amount is required.',
                'amount.numeric' => 'Amount must be numeric.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ]);
        }

        $postData = $validator->validated();

        DB::beginTransaction();

        try {
            $customPackage = CustomPackage::find($postData['custom_package_id']);

            if ($customPackage->status == 1) {
                return response()->json(['message' => 'Package already activated.'], 400);
            }

            // Update custom package status to 1 (activated)
            $customPackage->update([
                'status' => 1,
            ]);

            // Calculate expiry date
            $expiry_date = date('Y-m-d', strtotime('+' . $customPackage->time . ' ' . strtolower($customPackage->type)));

            // Create the transaction
            CustomTransaction::create([
                'user_id' => $postData['user_id'],
                'custom_package_id' => $customPackage->id,
                'amount' => $postData['amount'],
                'payment_id' => $postData['payment_id'],
                'currency_code' => currency_code(),
                'expiry_date' => $expiry_date,
                'status' => 1, // Assuming successful transaction
            ]);

            DB::commit();

            // Success response
            return $this->common->API_Response(200, __('Payment confirmed and package activated successfully.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 400,
                'errors' => $e->getMessage()
            ]);
        }
    }  

    public function userCustomPackagesAllData(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|exists:tbl_user,id',
                ],
                [
                    'user_id.required' => 'User is required.',
                    'user_id.exists' => 'Selected user does not exist.',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'message' => $validator->errors()->first()
                ]);
            }

            // Fetch the user
            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'User not found.'
                ]);
            }

            // Fetch user's custom packages with smart collections and items
            $customPackages = $user->customPackages()
                ->with(['smartCollections.items'])
                ->get();

            // Success response
            return $this->common->API_Response(200, __('Custom packages fetched successfully'), [
                'custom_packages' => $customPackages
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 400,
                'errors' => $e->getMessage()
            ]);
        }
    }

    public function userSmartCollections(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|exists:tbl_user,id',
                ],
                [
                    'user_id.required' => 'User is required.',
                    'user_id.exists' => 'Selected user does not exist.',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => 400,
                    'message' => $validator->errors()->first()
                ]);
            }

            // Get user
            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'User not found.'
                ]);
            }

            // Get unique smart collections with items
            $smartCollections = $user->customPackages()
                ->with('smartCollections.items')
                ->get()
                ->pluck('smartCollections')
                ->flatten()
                ->unique('id')
                ->values();

            // Success response
            return $this->common->API_Response(200, __('Smart collections fetched successfully'), $smartCollections);

        } catch (Exception $e) {
            return response()->json([
                'status' => 400,
                'errors' => $e->getMessage()
            ]);
        }
    }

    public function Add_EBook_transaction(Request $request)
    {
        try{
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                    'amount' => 'required|numeric',
                    'ebook_id' => 'required|numeric',
                ]
                // [
                //     'user_id.required' => __('api_msg.user_id_required'),
                //     'ebook_id.required' => __('api_msg.aiaudio_id_required'),
                //     'amount.required' => __('api_msg.amount_required'),
                // ]
            );
            if ($validation->fails()) {
                $errors = $validation->errors()->first('user_id');
                $errors1 = $validation->errors()->first('ebook_id');
                $errors2 = $validation->errors()->first('amount');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                } elseif ($errors1) {
                    $data['message'] = $errors1;
                } elseif ($errors2) {
                    $data['message'] = $errors2;
                }
                return $data;
            }

            $user_id =$request->user_id;
            $ebook_id =$request->ebook_id;
            $amount =$request->amount;

            $payment_id = isset($request->payment_id) ? $request->payment_id : "";
            $currency_code = isset($request->currency_code) ? $request->currency_code : currency_code();

            $Pdata = EBook::where('id',$ebook_id)->where('is_approved','1')->where('is_paid',"1")->first();
            if(!empty($Pdata)){
                $insert = new EBook_Transaction();
                $insert->user_id =$user_id;
                $insert->ebook_id = $ebook_id;
                $insert->amount = $amount;
                $insert->payment_id = $payment_id;
                $insert->currency_code = $currency_code;
                $insert->is_purchased = '1';
                $insert->status = '1';
            } else {
                return $this->common->API_Response(400, __('Please enter valid ebook details'));
            }

            if (!empty($Pdata) && isset($insert) && $insert->save()){
                return $this->common->API_Response(200, __('E-book purchase recorded successfully.'), array($insert));
            } else {
                return $this->common->API_Response(400, __('Failed to record e-book transaction. Please try again later.'));
            }
        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function audio_books_list(Request $request)
    {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');
            
            $data = Audio::with('category','artist','user')->latest();
            
            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();
            //echo "<pre>";print_r($data);exit;

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $ra['audio'] = url('audio').'/'.$ra['audio'];
                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);
                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }


                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

               
                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }
                
                $record_purchase = DB::select(
                    'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                    [
                        'aiaudio_id' => $ra->id,
                        'user_id' => $user_id,
                        'status' => 1,
                        'is_purchased' => 1
                    ]
                );
                if (!empty($record_purchase) || $ra->is_paid == 0) {
                    $ra->is_purchased = 1;
                }else{
                    $ra->is_purchased = 0;
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, 'AI Audio book list get succcesfully', $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function trending_audio_books(Request $request) {
        try{
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;

            $page_size = 0;
            $current_page = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            $data = Audio::with(['category', 'artist', 'user'])
                ->whereIn('id', function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->select('video_id')
                        ->from('tbl_view')
                        ->where('status', '1')
                        ->whereIn('type', ['audio', 'aiaudio'])
                        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                        ->groupBy('video_id')
                        ->orderByRaw('COUNT(*) DESC');
                });

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $current_page = $request->page_no ?? 1;
            $offset = $current_page * $total_page - $total_page;
            $data->take($total_page)->offset($offset);

            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();
            //echo "<pre>";print_r($data);exit;

            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {

                $ra['audio'] = url('audio').'/'.$ra['audio'];
                $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);
                $ra = (object) array_merge((array) $ra, $data1);

                $ra->is_like = "0";
                if ($user_id != 0) {
                    $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
                }


                $ra->category_name = "";
                if (isset($ra->category['name'])) {
                    $ra->category_name = $ra->category['name'];
                }

                $ra->artist_name = "";
                if (isset($ra->artist['name'])) {
                    $ra->artist_name = $ra->artist['name'];
                }

                $ra->full_name = "";
                $ra->user_name = "";
                $ra->profile_img = asset('/assets/imgs/users.png');
                if (isset($ra->user)) {
                    $ra->full_name = $ra->user['full_name'];
                    $ra->user_name = $ra->user['user_name'];
                    $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
                }

                $record_purchase = DB::select(
                    'select is_purchased from tbl_aiaudio_transaction where aiaudio_id = :aiaudio_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                    [
                        'aiaudio_id' => $ra->id,
                        'user_id' => $user_id,
                        'status' => 1,
                        'is_purchased' => 1
                    ]
                );
                if (!empty($record_purchase) || $ra->is_paid == 0) {
                    $ra->is_purchased = 1;
                }else{
                    $ra->is_purchased = 0;
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->artist);
            }

            return $this->common->API_Response(200, 'Audio book trending list get succcesfully', $dataarray, $pagination);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function trending_e_books(Request $request) {
        try {
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;
            $current_page = $request->page_no ?? 1;

            // Generate unique cache key for this user and page
            $cacheKey = "user_liked_ebooks_{$user_id}_page_{$current_page}";

            // Check if cached data exists
            // return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($request, $user_id, $current_page) {
                $page_size = 0;
                $more_page = false;
                $page_limit = env('PAGE_LIMIT');

                $data = EBook::with('category', 'artist', 'user', 'multipleEbooks')->latest();

                $startOfMonth = Carbon::now()->startOfMonth();
                $endOfMonth = Carbon::now()->endOfMonth();

                $data = EBook::with('category', 'artist', 'user', 'multipleEbooks')
                    ->whereIn('id', function ($query) use ($startOfMonth, $endOfMonth) {
                        $query->select('video_id')
                            ->from('tbl_view')
                            ->where('status', '1')
                            ->whereIn('type', ['ebook'])
                            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                            ->groupBy('video_id')
                            ->orderByRaw('COUNT(*) DESC');
                    });

                $total_rows = $data->count();
                $total_page = $page_limit;
                $page_size = ceil($total_rows / $total_page);
                $offset = $current_page * $total_page - $total_page;
                $data->take($total_page)->offset($offset);
                $more_page = $this->common->more_page($current_page, $page_size);

                $data = $data->get()->toArray();
                $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

                $dataarray = [];
                foreach ($data as $ra) {
                    $ra['file_url'] = url('storage/documents/' . $ra['upload_file']);
                    $data1 = $this->common->get_all_count_for_ebook($ra['id'], $user_id, $ra['user_id']);
                    $ra = (object) array_merge((array) $ra, $data1);

                    $ra->image = $this->common->getImagePath($this->folder_ebook, $ra->image);
                    $ra->category_name = $ra->category['name'] ?? "";
                    $ra->author_name = $ra->author['name'] ?? "";
                    $ra->full_name = $ra->user['full_name'] ?? "";
                    $ra->user_name = $ra->user['user_name'] ?? "";
                    $ra->profile_img = isset($ra->user) ? $this->common->getImagePath($this->folder, $ra->user['image']) : asset('/assets/imgs/users.png');

                    $record_purchase = DB::select(
                        'select is_purchased from tbl_ebook_transaction where ebook_id = :ebook_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                        [
                            'ebook_id' => $ra->id,
                            'user_id' => $user_id,
                            'status' => 1,
                            'is_purchased' => 1
                        ]
                    );

                    if (!empty($record_purchase) || $ra->is_paid == 0) {
                        $ra->is_purchased = 1;
                    } else{
                        $ra->is_purchased = 0;
                    }

                    $dataarray[] = $ra;
                    unset($ra->category, $ra->user, $ra->author);
                }
    
                return $this->common->API_Response(200, 'E-Book trending list retrieved successfully', $dataarray, $pagination);
            // });
    
        } catch (Exception $e) {
            return response()->json(['status' => 400, 'errors' => $e->getMessage()]);
        }
    }

    public function Search_filter_audio(Request $request)
    {
        $user_id = $request->user_id ?? 0;
        $keyword = $request->keyword;
        $page_limit = $request->page_limit ?? 10;

        $data = Audio::with(['category', 'artist', 'user'])
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%$keyword%")
                    ->orWhereHas('artist', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%$keyword%");
                    })
                    ->orWhereHas('category', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%$keyword%");
                    });
            })
            ->orderBy('id', 'desc');

        // Pagination logic
        $total_rows = $data->count();
        $total_page = $page_limit;
        $page_size = ceil($total_rows / $total_page);
        $current_page = $request->page_no ?? 1;
        $offset = $current_page * $total_page - $total_page;
        $data->take($total_page)->offset($offset);

        $more_page = $this->common->more_page($current_page, $page_size);

        $data = $data->get()->toArray();

        $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

        $dataarray = [];
        foreach ($data as $ra) {
            $ra['audio'] = url('audio') . '/' . $ra['audio'];
            $data1 = $this->common->get_all_count_for_video($ra['id'], $user_id, $ra['user_id']);
            $ra = (object) array_merge((array) $ra, $data1);

            $ra->is_like = "0";
            if ($user_id != 0) {
                $ra->is_like = $this->common->is_like($request['user_id'], $ra->id);
            }

            $ra->category_name = $ra->category['name'] ?? "";
            $ra->artist_name = $ra->artist['name'] ?? "";

            $ra->full_name = "";
            $ra->user_name = "";
            $ra->profile_img = asset('/assets/imgs/users.png');
            if (isset($ra->user)) {
                $ra->full_name = $ra->user['full_name'];
                $ra->user_name = $ra->user['user_name'];
                $ra->profile_img = $this->common->getImagePath($this->folder, $ra->user['image']);
            }

            // Purchase check for AI audio
            $record_purchase = DB::select(
                'SELECT is_purchased FROM tbl_aiaudio_transaction WHERE aiaudio_id = :aiaudio_id AND user_id = :user_id AND status = 1 AND is_purchased = 1',
                [
                    'aiaudio_id' => $ra->id,
                    'user_id' => $user_id
                ]
            );

            $ra->is_purchased = (!empty($record_purchase) || $ra->is_paid == 0) ? 1 : 0;

            $dataarray[] = $ra;
            unset($ra->category, $ra->user, $ra->artist);
        }

        return response()->json([
            'status' => true,
            'message' => "Audio search result",
            'data' => $dataarray,
            'pagination' => $pagination
        ]);
    }

    public function searchEbookList(Request $request) {
        try {
            $user_id = isset($request['user_id']) ? $request['user_id'] : 0;
            $current_page = $request->page_no ?? 1;
            $keyword = $request->keyword ?? '';

            $page_size = 0;
            $more_page = false;
            $page_limit = env('PAGE_LIMIT');

            $data = EBook::with('category', 'artist', 'user', 'multipleEbooks')
                        ->when($keyword, function ($q) use ($keyword) {
                            $q->where('name', 'like', "%$keyword%")
                                ->orWhereHas('artist', function ($q) use ($keyword) {
                                    $q->where('name', 'like', "%$keyword%");
                                })
                                ->orWhereHas('category', function ($q) use ($keyword) {
                                    $q->where('name', 'like', "%$keyword%");
                                });
                        })
                        ->latest();

            $total_rows = $data->count();
            $total_page = $page_limit;
            $page_size = ceil($total_rows / $total_page);
            $offset = $current_page * $total_page - $total_page;

            $data->take($total_page)->offset($offset);
            $more_page = $this->common->more_page($current_page, $page_size);

            $data = $data->get()->toArray();
            $pagination = $this->common->pagination_array($total_rows, $page_size, $current_page, $more_page);

            $dataarray = [];
            foreach ($data as $ra) {
                $ra['file_url'] = url('storage/documents/' . $ra['upload_file']);
                $data1 = $this->common->get_all_count_for_ebook($ra['id'], $user_id, $ra['user_id']);
                $ra = (object) array_merge((array) $ra, $data1);

                $ra->image = $this->common->getImagePath($this->folder_ebook, $ra->image);
                $ra->category_name = $ra->category['name'] ?? "";
                $ra->author_name = $ra->author['name'] ?? "";
                $ra->full_name = $ra->user['full_name'] ?? "";
                $ra->user_name = $ra->user['user_name'] ?? "";
                $ra->profile_img = isset($ra->user) ? $this->common->getImagePath($this->folder, $ra->user['image']) : asset('/assets/imgs/users.png');

                $record_purchase = DB::select(
                    'select is_purchased from tbl_ebook_transaction where ebook_id = :ebook_id and user_id = :user_id and status = :status and is_purchased = :is_purchased',
                    [
                        'ebook_id' => $ra->id,
                        'user_id' => $user_id,
                        'status' => 1,
                        'is_purchased' => 1
                    ]
                );

                if (!empty($record_purchase) || $ra->is_paid == 0) {
                    $ra->is_purchased = 1;
                } else {
                    $ra->is_purchased = 0;
                }

                $dataarray[] = $ra;
                unset($ra->category, $ra->user, $ra->author);
            }

            return $this->common->API_Response(200, 'Searched E-Book list retrieved successfully', $dataarray, $pagination);

        } catch (Exception $e) {
            return response()->json(['status' => 400, 'errors' => $e->getMessage()]);
        }
    }

    public function getSubscriptionAllModules()
    {
        $packages = Package::with(['audios', 'videos', 'ebooks'])->get();

        return response()->json([
            'status' => true,
            'message' => 'All subscription modules fetched successfully.',
            'data' => $packages
        ]);
    }
}