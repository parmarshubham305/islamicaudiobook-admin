<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Common;
use App\Models\Video;
use App\Models\Audio;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Storage;
use Validator;
;

class AlbumController extends Controller
{
    private $folder = "album";
    public $common;

    public function __construct()
    {
        $this->common = new Common;
    }
  
    public function index(Request $request)
    {
        try {
            $params['data'] = [];
            if ($request->ajax()) {
                $data = Album::select('*')->latest()->get();
                
                $this->common->imageNameToUrl($data, 'image', $this->folder);

                return DataTables()::of($data)
                    ->addIndexColumn()
                    ->addColumn('video_name', function($row) {
                        $videoName = [];
                        if (!is_null($row->audio_id) && $row->audio_id != '') {
                            $videoName[] = 'Audio';
                        }
                        if (!is_null($row->audiobook_id) && $row->audiobook_id != '') {
                            $videoName[] = 'AudioBook';
                        }
                        if (!is_null($row->video_id) && $row->video_id != '') {
                            $videoName[] = 'Video';
                        }
                        return implode(', ', $videoName);
                    })
                    ->addColumn('action', function ($row) {
                        $delete = ' <form onsubmit="return confirm(\'Are you sure want to delete this data ?\');" method="POST"  action="' . route('album.destroy', [$row->id]) . '">
                                <input type="hidden" name="_token" value="' . csrf_token() . '">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn "><img src="' . asset('assets/imgs/trash.png') . '" /></button></form>';

                        $btn = '<div class="d-flex justify-content-around"><a class="btn" href="' . route('album.edit', [$row->id]) . '">';
                        $btn .= '<img src="' . asset('assets/imgs/edit.png') . '" />';
                        $btn .= '</a>';
                        $btn .= $delete;
                        $btn .= '</a></div>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
            return view('admin.album.index', $params);
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

   
    public function create()
    {
        try {
            $params['data'] = [];

            $video =Video::select('*')->get();
            $audio =Audio::select('*')->where('is_aiaudiobook', 0)->where('is_approved', 1)->get();
            $audioBook =Audio::select('*')->where('is_aiaudiobook', 1)->where('is_approved', 1)->get();
            
            return view('admin.album.add', $params ,['video'=>$video,'audio'=>$audio,'audioBook'=>$audioBook]);
            // return view('admin.album.add', $params ,['video'=>$video]);
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

   
    public function store(Request $request)
    {
        try{
            
            if(!isset($request['video_id']) && !isset($request['audio_id']) && !isset($request['audiobook_id'])){
                $validate['audio_id'] = 'required';
            }
            $validate['name'] = 'required|min:2';
            $validate['image'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
            
            $validator = Validator::make($request->all(), $validate);

            if ($validator->fails()) {
                $errs = $validator->errors()->all();
                return response()->json(array('status' => 400, 'errors' => $errs));
            }

            $requestData = $request->all();


            if(isset($requestData['video_id'])){
                $video_id = implode(',', $requestData['video_id']);
            }else{
                $video_id = '';
            }
            if(isset($requestData['audio_id'])){
                $audio_id = implode(',', $requestData['audio_id']);
            }else{
                $audio_id = '';
            }
            if(isset($requestData['audiobook_id'])){
                $audiobook_id = implode(',', $requestData['audiobook_id']);
            }else{
                $audiobook_id = '';
            }

            if (isset($requestData['image'])) {
                $files = $requestData['image'];
                $requestData['image'] = $this->common->saveImage($files, $this->folder);
            }
            $requestData['video_id'] =$video_id;
            $requestData['audio_id'] =$audio_id;
            $requestData['audiobook_id'] =$audiobook_id;

            $album_data = Album::updateOrCreate(['id' => $requestData['id']], $requestData);
            if (isset($album_data->id)) {
                return response()->json(array('status' => 200, 'success' => __('label.album_save')));
            } else {
                return response()->json(array('status' => 400, 'errors' => __('label.album_not_save')));
            }


        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    
    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        try {
            $params['data'] = Album::where('id', $id)->first();

            $video =Video::select('*')->get();
            $audio =Audio::select('*')->where('is_aiaudiobook', 0)->where('is_approved', 1)->get();
            $audioBook =Audio::select('*')->where('is_aiaudiobook', 1)->where('is_approved', 1)->get();

            $this->common->imageNameToUrl(array($params['data']), 'image', $this->folder);

            if ($params['data'] != null) {
                return view('admin.album.edit', $params ,['video'=>$video,'audio'=>$audio,'audioBook'=>$audioBook]);
            } else {
                return redirect()->back()->with('error', __('label.page_not_found'));
            }
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    
    public function update(Request $request, $id)
    {
        try{
            if(!isset($request['video_id']) && !isset($request['audio_id']) && !isset($request['audiobook_id'])){
                $validate['audio_id'] = 'required';
            }
            $validate['name'] = 'required|min:2';
            $validator = Validator::make($request->all(), $validate);

            if ($validator->fails()) {
                $errs = $validator->errors()->all();
                return response()->json(array('status' => 400, 'errors' => $errs));
            }

            $requestData = $request->all();


            if(isset($requestData['video_id'])){
                $video_id = implode(',', $requestData['video_id']);
            }else{
                $video_id = '';
            }
            if(isset($requestData['audio_id'])){
                $audio_id = implode(',', $requestData['audio_id']);
            }else{
                $audio_id = '';
            }
            if(isset($requestData['audiobook_id'])){
                $audiobook_id = implode(',', $requestData['audiobook_id']);
            }else{
                $audiobook_id = '';
            }

            if (isset($requestData['image'])) {
                $files = $requestData['image'];
                $requestData['image'] = $this->common->saveImage($files, $this->folder);
            }

            $requestData['video_id'] =$video_id;
            $requestData['audio_id'] =$audio_id;
            $requestData['audiobook_id'] =$audiobook_id;

            $album_data = Album::updateOrCreate(['id' => $requestData['id']], $requestData);
            if (isset($album_data->id)) {
                return response()->json(array('status' => 200, 'success' => __('label.album_update')));
            } else {
                return response()->json(array('status' => 400, 'errors' => __('label.album_not_update')));
            }


        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function destroy($id)
    {
        try{
            $data =Album::where('id',$id)->first();

                if (isset($data)) {
                    $this->common->deleteImageToFolder($this->folder, $data['image']);
                    $data->delete();
                }

                return redirect()->back()->with('success', __('label.album_delete'));

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }
}
