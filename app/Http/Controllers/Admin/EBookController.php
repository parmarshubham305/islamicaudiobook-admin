<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Common;
use Illuminate\Http\Request;
use App\Models\EBook;
use App\Models\MultipleEbook;
use App\Models\Category;
use App\Models\Artist;
use App\Models\Album;
use App\Models\User;
use App\Models\Package;
use DataTables;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Storage;
use Validator;
use DB;
use Illuminate\Support\Facades\File;

class EBookController extends Controller
{
    private $folder ="e-books";
    private $folder_category ="category";
    private $folder_artist ="artist";

    public $common;

    public function __construct()
    {
        $this->common = new Common;
    }
    public function index(Request $request)
    {      
        
        // return view('admin.e-book.index');

        try{
            if(Auth::guard('admin')->user()->permissions_role == 'super_admin'){ 
                $data = EBook::select('*')->with('artist')->latest()->paginate(7);  
            }else{
                $user_id = Auth::guard('admin')->user()->id;
                $data = EBook::select('*')->with('artist')->where('publisher_id',$user_id)->latest()->paginate(7);
            }
            
            if(!empty($data)){
                foreach($data as $k=>$record){
                    $userdetails =User::where('id',$record['user_id'])->first();
                    if($userdetails){
                        $data[$k]['username'] = $userdetails['full_name'];
                    }
                    $this->common->imageNameToUrl(array($record), 'image', $this->folder);
                }
            }
            $params['data'] = $data;
            //echo "<pre>";print_r($params);exit;
            return view('admin.e-book.index',$params);
        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }

    }

    public function create()
    {
        $category = Category::select('*')->get();
        $artist =Artist::select('*')->get();
        $user =User::select('*')->get();

        return view('admin.e-book.add',['category' =>$category,'artist'=>$artist,'user'=>$user]);
    }

    public function store(Request $request)
    {
        ini_set('memory_limit', '-1');
        try{
            $requestData = $request->all();

            // return response()->json($requestData = $request->all());

            // if($request->isAudioTab == 1){
            //     $validator = Validator::make($request->all(), [
            //         'name' => 'required',
            //         'artist_id' => 'required',
            //         'category_id' => 'required',
            //         'description' => 'required',    
            //         'e-book' => 'required',   
            //     ]);
            // }else{
                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'artist_id' => 'required',
                    'category_id' => 'required',
                    'subcategory_id' => 'nullable|integer',
                    'description' => 'required',    
                    'e-book' => [
                        'required',
                        // 'file',
                        // 'max:10000', // Set a reasonable maximum file size (in kilobytes)
                        // 'mimes:mpga,wav,mp3',
                    ],
                    'package_id' => 'nullable|array',
                    'package_id.*' => 'exists:tbl_package,id'
                ]);
            // }
            
          
            if ($validator->fails()) {
                $errs = $validator->errors()->all();
                return response()->json(array('status' => 400, 'errors' => $errs));
            }
            
            $upload_file_name = '';
            if(isset($requestData['upload_file'])){
                $document = $requestData['upload_file'];
                $upload_file_name = time() . '.' . $document->getClientOriginalExtension();
                $document->move(base_path('storage/app/public/documents'), $upload_file_name);
                Storage::disk('local')->put($requestData['upload_file'], $upload_file_name);
                $requestData['upload_file'] = $upload_file_name;
            }

            if (isset($requestData['image'])) {
                $files = $requestData['image'];
                $requestData['image'] = $this->common->saveImage($files, $this->folder);
            }
            
            // if($request->isAudioTab == 0){
                unset($requestData['e-book']);
                $requestAudioName = $requestData['e-book_name'];
                unset($requestData['e-book_name']);
            // }

            $requestData['user_id'] =isset($requestData['user_id']) ? $requestData['user_id'] : 0;

            $requestData['is_created_by_admin'] = (Auth::guard('admin')->user()->permissions_role == 'super_admin') ? 1 : 0 ;
            $requestData['is_approved'] = (Auth::guard('admin')->user()->permissions_role != 'super_admin') ? 0 : 1 ;
            $requestData['is_aiaudiobook'] = 1;
            $publisherId = Auth::guard('admin')->user()->id;
            $requestData['publisher_id'] = $publisherId;

            $ebook_data = EBook::updateOrCreate(['id' => $requestData['id']], $requestData);
            if(isset($ebook_data->id)){

                if (!empty($requestData['package_id']) && is_array($requestData['package_id'])) {
                    // Sync polymorphic many-to-many relation
                    $ebook_data->subscriptions()->sync($requestData['package_id']);
                } else {
                    // Remove all package relations if none selected
                    $ebook_data->subscriptions()->detach();
                }
                
                // if($request->isAudioTab == 0){
                    $ebookFiles = $request->file('e-book');

                    if(!empty($ebookFiles)){  
                        foreach ($ebookFiles as $key => $ebookFile){
                            $f_name = time() + $key;
                            $fileName = $f_name . '.' . $ebookFile->getClientOriginalExtension();

                            $ebookPath = public_path('e-book');

                            
                            // Create the directory if it doesn't exist
                            if (!File::exists($ebookPath)) {
                                File::makeDirectory($ebookPath, 0755, true);
                                
                            }

                            // Move the file
                            $ebookFile->move($ebookPath, $fileName);

                            $ebookFileData = [
                                'ebook_id' => $ebook_data->id,
                                'upload_file' => $fileName,
                                'ebook_name' => $requestAudioName[$key] 
                            ];

                            // return res
                            MultipleEbook::create($ebookFileData);
                        }
                    }
                // }
                return response()->json(array('status' => 200, 'success' => __('label.e_book_save')));
            }else{
                return response()->json(array('status' => 400, 'errors' => __('label.e_book_not_save')));
            }
        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }


    public function edit($id)
    {
        try{

            // $eBookData = EBook::findOrFail($id);
            // $params['data'] = $eBookData;
            // $params['category'] = Category::select('*')->get();
            // $params['artist'] = Artist::select('*')->get();
            // $params['user'] = User::select('*')->get();
            
            // $audios = DB::select('select * from tbl_multiple_audio where audio_id = :audio_id', ['audio_id' => 3]);


            $eBookData = EBook::with('multipleEbooks')->findOrFail($id);

            $params['data'] = $eBookData;
            $params['category'] = Category::all();
            $params['artist'] = Artist::all();
            $params['user'] = User::all();
            $params['multiple_ebooks'] = $eBookData->multipleEbooks;


            
            // dd($audios);
            // $params['audios'] =$audios; 
    
            $this->common->imageNameToUrl(array($params['data']), 'image', $this->folder);
            //echo "<pre>";print_r($params);exit;
            if ($params['data'] != null) {
                return view('admin.e-book.edit', $params);
            } else {
                return redirect()->back()->with('error', __('label.page_not_found'));
            }

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    // public function show($id)
    // {
    //     try{
    //         $data =EBook::where('id',$id)->first();
    //         dd($data);
    //         if (isset($data)) {
    //             $data->delete();
    //         }
    //         return redirect()->back()->with('success', __('label.e_book_delete'));

    //     }catch (Exception $e) {
    //         return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
    //     }
    // }

    public function deleteEBook($id)
    {
        try {
            $eBook = EBook::findOrFail($id); // Throws 404 if not found
            $eBook->delete();
    
            return redirect()->route('e-book.index')->with('success', __('label.e_book_delete'));
    
        } catch (Exception $e) {
            return redirect()->route('e-book.index')->with('error', $e->getMessage());
        }
    }

    public function deleteEBookFile($id)
    {
        try {
            $eBookFile = MultipleEbook::findOrFail($id); // Throws 404 if not found
            $eBookFile->delete();
    
            return response()->json([
                'success' => __('label.e_book_file_delete')
            ]);
    
        } catch (Exception $e) {
            return redirect()->route('e-book.index')->with('error', $e->getMessage());
        }
    }
    

    public function update(Request $request, $id)
    {
        try{
            ini_set('memory_limit', '-1');
            $requestData = $request->all();
            
            $eBookData = EBook::with(['multipleEbooks'])->findOrFail($id);
            $validation_array = [
                'name' => 'required',
                'artist_id' => 'required',
                'category_id' => 'required',
                'description' => 'required',
                'package_id' => 'nullable|array',
                'package_id.*' => 'exists:tbl_package,id'
            ];


            if ($eBookData->multipleEbooks->count() <= 0 && !$request->hasFile('e-book')) {
                $validation_array['e-book'] = ['required', 'file'];
            }                
            
            $validator = Validator::make($request->all(), $validation_array);
           
            if ($validator->fails()) {
                $errs = $validator->errors()->all();
                return response()->json(array('status' => 400, 'errors' => $errs));
            }

            if (isset($requestData['image'])) {
                $files = $requestData['image'];
                $requestData['image'] = $this->common->saveImage($files, $this->folder);
            }

            $upload_file_name = '';
            if(isset($requestData['upload_file'])){
                $document = $requestData['upload_file'];
                $upload_file_name = time() . '.' . $document->getClientOriginalExtension();
                $document->move(base_path('storage/app/public/documents'), $upload_file_name);
                $requestData['upload_file'] = $upload_file_name;
            }
            $requestData['user_id'] = isset($requestData['user_id']) ? $requestData['user_id'] : 0;
            unset($requestData['e-book']);
            $requestAudioName = $requestData['e-book_name'];
            unset($requestData['e-book_name']);

            $eBookData->update($requestData);
            if (isset($eBookData->id)) {
                if (!empty($requestData['package_id']) && is_array($requestData['package_id'])) {
                    // Sync polymorphic many-to-many relation
                    $eBookData->subscriptions()->sync($requestData['package_id']);
                } else {
                    // Remove all package relations if none selected
                    $eBookData->subscriptions()->detach();
                }

                if($request->isAudioTab == 0){
                    $ebookFiles = $request->file('e-book');
                    if(!empty($ebookFiles)){  
                        foreach ($ebookFiles as $key => $ebookFile){
                            $f_name = time() + $key;
                            $fileName = $f_name . '.' . $ebookFile->getClientOriginalExtension();
                            $ebookPath = public_path('e-book');

                            // Create the directory if it doesn't exist
                            if (!File::exists($ebookPath)) {
                                File::makeDirectory($ebookPath, 0755, true);
                                
                            }

                            $ebookFile->move($ebookPath, $fileName);
                            $ebookFileData = [
                                'ebook_id' => $eBookData->id,
                                'upload_file' => $fileName,
                                'ebook_name' => $requestAudioName[$key] 
                            ];
                            MultipleEbook::create($ebookFileData);
                        }
                    }
                }
                return response()->json(array('status' => 200, 'success' => __('label.e_book_update')));
            } else {
                return response()->json(array('status' => 400, 'errors' => __('label.e_book_not_update')));
            }

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    // public function getconvertedaudio(Request $request)
    // {
    //     // Your custom logic here
    //     try{
    //         $requestData = $request->all();
    //        // $requestData = json_decode( $requestData);
    //        $voice_name = isset($requestData['voice_name'])?$requestData['voice_name']:'';
    //        $text = isset($requestData['text'])?$requestData['text']:'';
    //         // The URL of the HTTP API endpoint
    //         $apiUrl = 'http://85.31.234.223:5002/text';

    //         // JSON data to send
    //         $data = [
    //             'voice_name' => $voice_name,
    //             'text' => $text,
    //             // Add more key-value pairs as needed
    //         ];

    //         // Convert the data array to JSON
    //         $jsonData = json_encode($data);

    //         // Create a new cURL resource
    //         $ch = curl_init();

    //         // Set cURL options
    //         curl_setopt($ch, CURLOPT_URL, $apiUrl);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_POST, true); // Set the request method to POST
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Set the JSON payload
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, [
    //             'Content-Type: application/json', // Specify JSON content type
    //             'Content-Length: ' . strlen($jsonData) // Set the content length
    //         ]);

    //         // Execute cURL session and store the response
    //         $response = curl_exec($ch);

    //         // Check for cURL errors
    //         if (curl_errno($ch)) {
    //             return response()->json(array('status' => 400, 'errors' => curl_error($ch)));
    //         }

    //         // Close cURL session
    //         curl_close($ch);

    //         // Process the API response (e.g., print it)
    //         $filename = 'audio_file_'.time().'.mp3';
    //         $file = fopen(public_path('audio').'/'.$filename, 'w');
    //         fwrite($file, $response);
    //         fclose($file);

    //         return response()->json(array('status' => 200, 'filename' => $filename));
           
    //     }catch (Exception $e) {
    //         return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
    //     }
    // }

    public function detail(Request $request, $id) {
        try {
            // Eager load related data to minimize queries
            $detail = EBook::with(['category', 'artist', 'user', 'multipleEbooks'])->findOrFail($id);

            // Convert image name to URL
            $this->common->imageNameToUrl([$detail], 'image', $this->folder);

            // Pass data to the view
            return view('admin.e-book.detail', [
                'detail' => $detail,
                'category' => $detail->category,
                'artist' => $detail->artist,
                'user' => $detail->user,
                'multiple_ebooks' => $detail->multipleEbooks
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 400, 'errors' => $e->getMessage()]);
        }
    }
    
    // public function delete_audio($id){
    //     try{
    //         $audio = DB::table('tbl_multiple_audio')->where('id', $id)->first();

    //         if ($audio) {
    //             // Delete the audio file from the server
    //             $filePath = public_path('audio/' . $audio->upload_file);
    //             if (file_exists($filePath)) {
    //                 unlink($filePath);
    //             }
    
    //             // Delete the audio record from the database
    //             DB::table('tbl_multiple_audio')->where('id', $id)->delete();
    
    //             return response()->json(['success' => true]);
    //         }
    
    //         return response()->json(['success' => false, 'message' => 'Audio not found.']);
    //     }catch (Exception $e) {
    //         return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
    //     }
    // }

    public function ebookDownload($id)
    {
        $ebook = MultipleEbook::findOrFail($id);
        $filePath = public_path('e-book/' . $ebook->upload_file);

        if (!File::exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        return response()->download($filePath, $ebook->ebook_name ?? basename($filePath));
    }
}
