<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Common;
use Illuminate\Http\Request;
use App\Models\EBook;
use App\Models\Audio;
use App\Models\MultipleEbook;
use App\Models\Category;
use App\Models\Artist;
use App\Models\Album;
use App\Models\User;
use App\Models\SmartCollection;
use App\Models\SmartCollectionItem;
use DataTables;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Storage;
use Validator;
use DB;
use Illuminate\Support\Facades\File;

class SmartCollectionController extends Controller
{
    private $folder ="smart-collection";
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

        try {

            $data = SmartCollection::latest()->paginate(7);

            if(!empty($data)){
                foreach($data as $k=>$record){
                    $this->common->imageNameToUrl(array($record), 'image', $this->folder);
                }
            }

            $params['data'] = $data;
            //echo "<pre>";print_r($params);exit;
            return view('admin.smart-collection.index',$params);
        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }

    }

    public function create()
    {
        $category = Category::select('*')->get();
        $artist =Artist::select('*')->get();
        $user =User::select('*')->get();

        return view('admin.smart-collection.add',['category' =>$category,'artist'=>$artist,'user'=>$user]);
    }

    public function getAllEbooks(Request $request)
    {
        try {
            $query = EBook::select('id', 'name', 'artist_id', 'category_id', 'created_at')
                ->with(['artist:id,name', 'category:id,name']) // Adjust relation fields as needed
                ->where(function ($q) {
                    $q->where('is_created_by_admin', 1)
                    ->orWhere(function ($sub) {
                        $sub->where('is_created_by_admin', 0)
                            ->where('is_approved', 1);
                    });
                });

            // Filter by artist_ids (array)
            if ($request->has('artist_ids') && is_array($request->artist_ids)) {
                $query->whereIn('artist_id', $request->artist_ids);
            }

            // Filter by category_ids (array)
            if ($request->has('category_ids') && is_array($request->category_ids)) {
                $query->whereIn('category_id', $request->category_ids);
            }

            $ebooks = $query->get();

            return response()->json([
                'status' => true,
                'message' => $ebooks->isEmpty() ? 'No eBooks found.' : 'eBooks fetched successfully.',
                'data' => $ebooks
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching eBooks: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching eBooks.'
            ], 500);
        }
    }

    public function getAllAudioBooks(Request $request)
    {
        try {
            $query = Audio::select('id', 'name', 'artist_id', 'category_id', 'created_at')
                ->with(['artist:id,name', 'category:id,name'])
                ->where('is_aiaudiobook', '!=', 0)
                ->where(function ($q) {
                    $q->where('is_created_by_admin', 1)
                        ->orWhere(function ($sub) {
                            $sub->where('is_created_by_admin', 0)
                                ->where('is_approved', 1);
                        });
                });

            // Filter by artist_ids (array)
            if ($request->has('artist_ids') && is_array($request->artist_ids)) {
                $query->whereIn('artist_id', $request->artist_ids);
            }

            // Filter by category_ids (array)
            if ($request->has('category_ids') && is_array($request->category_ids)) {
                $query->whereIn('category_id', $request->category_ids);
            }

            $audioBooks = $query->get();

            return response()->json([
                'status' => true,
                'message' => $audioBooks->isEmpty() ? 'No audio books found.' : 'Audio books fetched successfully.',
                'data' => $audioBooks
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching audio books: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching audio books.'
            ], 500);
        }
    }

    public function getArtists()
    {
        try {
            $artists = Artist::select('id', 'name')
                ->where('status', 1)
                ->get();

            return response()->json([
                'status' => true,
                'message' => $artists->isEmpty() ? 'No active artists found.' : 'Active artists fetched successfully.',
                'data' => $artists
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching active artists: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching active artists.'
            ], 500);
        }
    }

    public function getCategories()
    {
        try {
            $categories = Category::select('id', 'name')
                ->where('status', 1)
                ->get();

            return response()->json([
                'status' => true,
                'message' => $categories->isEmpty() ? 'No active categories found.' : 'Active categories fetched successfully.',
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching active categories: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching active categories.'
            ], 500);
        }
    }

    public function createSmartCollection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:1,0',
            'type' => 'required|in:e_book,audio_book',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|numeric|distinct',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $requestData = $request->all();

            if (isset($requestData['image'])) {
                $files = $requestData['image'];
                $requestData['image'] = $this->common->saveImage($files, $this->folder);
            }

            // Create Smart Collection
            $smartCollection = SmartCollection::create([
                'title' => $requestData['title'],
                'type' => $requestData['type'],
                'description' => $requestData['description'],
                'image' => $requestData['image'],
                'currency_type' => currency_code(),
                'price' => $requestData['price'],
                'status' => $requestData['status'],
            ]);

            if (in_array($requestData['type'], ['e_book', 'audio_book'])) {
                // Create SmartCollectionItems
                foreach ($requestData['item_ids'] as $itemId) {
                    SmartCollectionItem::create([
                        'smart_collection_id' => $smartCollection->id,
                        'item_id' => $itemId,
                        'item_type' => $requestData['type'],
                    ]);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Smart collection created successfully.',
                    // 'data' => $smartCollection->load('items'),
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Please pass the supported type.',
                    'error' => $e->getMessage(),
                ], 500);   
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateSmartCollection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tbl_smart_collections,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:1,0',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|numeric|distinct',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $requestData = $request->all();
            $smartCollection = SmartCollection::findOrFail($requestData['id']);

            if (isset($requestData['image'])) {
                $requestData['image'] = $this->common->saveImage($requestData['image'], $this->folder);
            }

            $smartCollection->update([
                'title' => $requestData['title'],
                'description' => $requestData['description'],
                'image' => $requestData['image'] ?? $smartCollection->image,
                'currency_type' => currency_code(),
                'price' => $requestData['price'],
                'status' => $requestData['status'],
            ]);

            // return response()->json($smartCollection->items);

            $existingItemIds = $smartCollection->items->pluck('item_id')->toArray();
            $newItemIds = $requestData['item_ids'];

            // Items to delete (exist in DB but not in request)
            $itemsToDelete = array_diff($existingItemIds, $newItemIds);

            // Items to add (exist in request but not in DB)
            $itemsToAdd = array_diff($newItemIds, $existingItemIds);

            // Delete old items
            if (!empty($itemsToDelete)) {
                SmartCollectionItem::where('smart_collection_id', $smartCollection->id)
                    ->whereIn('item_id', $itemsToDelete)
                    ->delete();
            }

            // Add new items
            foreach ($itemsToAdd as $itemId) {
                SmartCollectionItem::create([
                    'smart_collection_id' => $smartCollection->id,
                    'item_id' => $itemId,
                    'item_type' => $smartCollection->type,
                ]);
            }

            $smartCollection->refresh();

            
            return response()->json([
                'status' => true,
                'message' => 'Smart collection updated successfully.',
                'data' => $smartCollection
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addItemToSmartCollection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'smart_collection_id' => 'required|exists:tbl_smart_collections,id',
            'item_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $smartCollection = SmartCollection::findOrFail($request->smart_collection_id);
            $itemId = $request->item_id;

            // Check if item already assigned
            $exists = SmartCollectionItem::where('smart_collection_id', $smartCollection->id)
                        ->where('item_id', $itemId)
                        ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Item is already assigned to the smart collection.',
                ], 409);
            }

            // Check if item is valid based on type
            $item = null;
            switch ($smartCollection->type) {
                case 'e_book':
                    $item = EBook::find($itemId);
                    break;
                case 'audio_book':
                    $item = Audio::find($itemId);
                    break;
            }

            if (!$item) { // Or use custom logic/method like $item->isValid()
                return response()->json([
                    'status' => false,
                    'message' => 'The item is not valid or not found.',
                ], 404);
            }

            // Assign item to smart collection
            SmartCollectionItem::create([
                'smart_collection_id' => $smartCollection->id,
                'item_id' => $itemId,
                'item_type' => $smartCollection->type,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Item added to smart collection successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSmartCollectionsListByType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:e_book,audio_book', // Add more types if needed
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $collections = SmartCollection::where('type', $request->type)
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Smart collections fetched successfully.',
                'data' => $collections
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function store(Request $request)
    // {
    //     ini_set('memory_limit', '-1');
    //     try{
    //         $requestData = $request->all();

    //         // return response()->json($requestData = $request->all());

    //         // if($request->isAudioTab == 1){
    //         //     $validator = Validator::make($request->all(), [
    //         //         'name' => 'required',
    //         //         'artist_id' => 'required',
    //         //         'category_id' => 'required',
    //         //         'description' => 'required',    
    //         //         'e-book' => 'required',   
    //         //     ]);
    //         // }else{
    //             $validator = Validator::make($request->all(), [
    //                 'name' => 'required',
    //                 'artist_id' => 'required',
    //                 'category_id' => 'required',
    //                 'description' => 'required',    
    //                 'e-book' => [
    //                     'required',
    //                     // 'file',
    //                     // 'max:10000', // Set a reasonable maximum file size (in kilobytes)
    //                     // 'mimes:mpga,wav,mp3',
    //                 ],   
    //             ]);
    //         // }
            
          
    //         if ($validator->fails()) {
    //             $errs = $validator->errors()->all();
    //             return response()->json(array('status' => 400, 'errors' => $errs));
    //         }
            
    //         $upload_file_name = '';
    //         if(isset($requestData['upload_file'])){
    //             $document = $requestData['upload_file'];
    //             $upload_file_name = time() . '.' . $document->getClientOriginalExtension();
    //             $document->move(base_path('storage/app/public/documents'), $upload_file_name);
    //             Storage::disk('local')->put($requestData['upload_file'], $upload_file_name);
    //             $requestData['upload_file'] = $upload_file_name;
    //         }

    //         if (isset($requestData['image'])) {
    //             $files = $requestData['image'];
    //             $requestData['image'] = $this->common->saveImage($files, $this->folder);
    //         }
            
    //         // if($request->isAudioTab == 0){
    //             unset($requestData['e-book']);
    //             $requestAudioName = $requestData['e-book_name'];
    //             unset($requestData['e-book_name']);
    //         // }

    //         $requestData['user_id'] =isset($requestData['user_id']) ? $requestData['user_id'] : 0;

    //         $requestData['is_created_by_admin'] = (Auth::guard('admin')->user()->permissions_role == 'super_admin') ? 1 : 0 ;
    //         $requestData['is_approved'] = (Auth::guard('admin')->user()->permissions_role != 'super_admin') ? 0 : 1 ;
    //         $requestData['is_aiaudiobook'] = 1;
    //         $publisherId = Auth::guard('admin')->user()->id;
    //         $requestData['publisher_id'] = $publisherId;
    //         // dd();
    //         // exit;
    //         $ebook_data = EBook::updateOrCreate(['id' => $requestData['id']], $requestData);
    //         if(isset($ebook_data->id)){
                
    //             // if($request->isAudioTab == 0){
    //                 $ebookFiles = $request->file('e-book');

    //                 if(!empty($ebookFiles)){  
    //                     foreach ($ebookFiles as $key => $ebookFile){
    //                         $f_name = time() + $key;
    //                         $fileName = $f_name . '.' . $ebookFile->getClientOriginalExtension();

    //                         $ebookPath = public_path('e-book');

                            
    //                         // Create the directory if it doesn't exist
    //                         if (!File::exists($ebookPath)) {
    //                             File::makeDirectory($ebookPath, 0755, true);
                                
    //                         }

    //                         // Move the file
    //                         $ebookFile->move($ebookPath, $fileName);

    //                         $ebookFileData = [
    //                             'ebook_id' => $ebook_data->id,
    //                             'upload_file' => $fileName,
    //                             'ebook_name' => $requestAudioName[$key] 
    //                         ];

    //                         // return res
    //                         MultipleEbook::create($ebookFileData);
    //                     }
    //                 }
    //             // }
    //             return response()->json(array('status' => 200, 'success' => __('label.e_book_save')));
    //         }else{
    //             return response()->json(array('status' => 400, 'errors' => __('label.e_book_not_save')));
    //         }
    //     }catch (Exception $e) {
    //         return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
    //     }
    // }


    public function edit($id)
    {
        try {            
            $params = [];
            

            $smartCollection = SmartCollection::find($id);
            $params['smartCollection'] = $smartCollection;

            return view('admin.smart-collection.add', $params);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function getSmartCollectionDataById($id)
    {
        try {
            $smartCollection = SmartCollection::with(['items'])->find($id);

            if (!$smartCollection) {
                return response()->json([
                    'status' => false,
                    'message' => 'Smart collection not found.',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Smart collection fetched successfully.',
                'data' => $smartCollection
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching smart collection: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching the smart collection.'
            ], 500);
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

    // public function deleteSmartCollection($id)
    // {
    //     try {
    //         $eBook = EBook::findOrFail($id); // Throws 404 if not found
    //         $eBook->delete();
    
    //         return redirect()->route('smart-collection.index')->with('success', __('label.e_book_delete'));
    
    //     } catch (Exception $e) {
    //         return redirect()->route('smart-collection.index')->with('error', $e->getMessage());
    //     }
    // }

    // public function deleteEBookFile($id)
    // {
    //     try {
    //         $eBookFile = MultipleEbook::findOrFail($id); // Throws 404 if not found
    //         $eBookFile->delete();
    
    //         return response()->json([
    //             'success' => __('label.e_book_file_delete')
    //         ]);
    
    //     } catch (Exception $e) {
    //         return redirect()->route('smart-collection.index')->with('error', $e->getMessage());
    //     }
    // }
    

    // public function update(Request $request, $id)
    // {
    //     try{
    //         ini_set('memory_limit', '-1');
    //         $requestData = $request->all();
            
    //         $eBookData = EBook::with(['multipleEbooks'])->findOrFail($id);
    //         $validation_array = [
    //             'name' => 'required',
    //             'artist_id' => 'required',
    //             'category_id' => 'required',
    //             'description' => 'required',
    //         ];


    //         if ($eBookData->multipleEbooks->count() <= 0 && !$request->hasFile('e-book')) {
    //             $validation_array['e-book'] = ['required', 'file'];
    //         }                
            
    //         $validator = Validator::make($request->all(), $validation_array);
        
           
    //         if ($validator->fails()) {
    //             $errs = $validator->errors()->all();
    //             return response()->json(array('status' => 400, 'errors' => $errs));
    //         }

    //         if (isset($requestData['image'])) {
    //             $files = $requestData['image'];
    //             $requestData['image'] = $this->common->saveImage($files, $this->folder);
    //         }
    //         $upload_file_name = '';
    //         if(isset($requestData['upload_file'])){
    //             $document = $requestData['upload_file'];
    //             $upload_file_name = time() . '.' . $document->getClientOriginalExtension();
    //             $document->move(base_path('storage/app/public/documents'), $upload_file_name);
    //             $requestData['upload_file'] = $upload_file_name;
    //         }
    //         $requestData['user_id'] = isset($requestData['user_id']) ? $requestData['user_id'] : 0;
    //         unset($requestData['e-book']);
    //         $requestAudioName = $requestData['e-book_name'];
    //         unset($requestData['e-book_name']);

    //         $eBookData->update($requestData);
    //         if (isset($eBookData->id)) {
    //             if($request->isAudioTab == 0){
    //                 $ebookFiles = $request->file('e-book');
    //                 if(!empty($ebookFiles)){  
    //                     foreach ($ebookFiles as $key => $ebookFile){
    //                         $f_name = time() + $key;
    //                         $fileName = $f_name . '.' . $ebookFile->getClientOriginalExtension();
    //                         $ebookPath = public_path('e-book');

    //                         // Create the directory if it doesn't exist
    //                         if (!File::exists($ebookPath)) {
    //                             File::makeDirectory($ebookPath, 0755, true);
                                
    //                         }

    //                         $ebookFile->move($ebookPath, $fileName);
    //                         $ebookFileData = [
    //                             'ebook_id' => $eBookData->id,
    //                             'upload_file' => $fileName,
    //                             'ebook_name' => $requestAudioName[$key] 
    //                         ];
    //                         MultipleEbook::create($ebookFileData);
    //                     }
    //                 }
    //             }
    //             return response()->json(array('status' => 200, 'success' => __('label.e_book_update')));
    //         } else {
    //             return response()->json(array('status' => 400, 'errors' => __('label.e_book_not_update')));
    //         }

    //     }catch (Exception $e) {
    //         return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
    //     }
    // }

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

    // public function detail(Request $request, $id) {
    //     try {
    //         // Eager load related data to minimize queries
    //         $detail = EBook::with(['category', 'artist', 'user', 'multipleEbooks'])->findOrFail($id);

    //         // Convert image name to URL
    //         $this->common->imageNameToUrl([$detail], 'image', $this->folder);

    //         // Pass data to the view
    //         return view('admin.e-book.detail', [
    //             'detail' => $detail,
    //             'category' => $detail->category,
    //             'artist' => $detail->artist,
    //             'user' => $detail->user,
    //             'multiple_ebooks' => $detail->multipleEbooks
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 400, 'errors' => $e->getMessage()]);
    //     }
    // }
    
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

    // public function ebookDownload($id)
    // {
    //     $ebook = MultipleEbook::findOrFail($id);
    //     $filePath = public_path('e-book/' . $ebook->upload_file);

    //     if (!File::exists($filePath)) {
    //         return response()->json(['error' => 'File not found.'], 404);
    //     }

    //     return response()->download($filePath, $ebook->ebook_name ?? basename($filePath));
    // }
}
