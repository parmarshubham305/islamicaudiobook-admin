<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audio_Transaction;
use App\Models\Package;
use App\Models\User;
use App\Models\Audio;
use Validator;
use DataTables;
use Illuminate\Http\Request;

class AiaudioTransactionController extends Controller
{
    
    public function index(Request $request)
    {
        try {
            $params['data'] = [];
            if ($request->ajax()) {
                $data = Audio_Transaction::with(['audio', 'user'])->latest()->get();

                return DataTables()::of($data)
                    ->addIndexColumn()
                    ->addColumn('date', function($row) {
                        $date = date("Y-m-d", strtotime($row->created_at));
                        return $date;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
            return view('admin.aiaudio_transaction.index', $params);
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

   
    public function create(Request $request)
    {
        try{
            $params['data'] =[];
            $params['user'] = User::where('id', $request->user_id)->first();
            $params['audio'] = Audio::where('is_aiaudiobook',1)->get();

            return view('admin.aiaudio_transaction.add',$params);

        }catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

  
    public function store(Request $request)
    {
        try{
            
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'aiaudio_id' => 'required'
            ]);
            if ($validator->fails()) {
                $errs = $validator->errors()->all();
                return response()->json(array('status' => 400, 'errors' => $errs));
            } else {

                $aiaudio = Audio::where('id', $request->aiaudio_id)->first();

                $Audio_Transaction = new Audio_Transaction();
                $Audio_Transaction->user_id =$request->user_id;
                $Audio_Transaction->aiaudio_id =$request->aiaudio_id;
                $Audio_Transaction->amount =$aiaudio->price;
                $Audio_Transaction->payment_id = 'admin';
                $Audio_Transaction->currency_code = currency_code();
                $Audio_Transaction->status = 1;
                if ($Audio_Transaction->save()) {
                    if ($Audio_Transaction->id) {
                        return response()->json(array('status' => 200, 'success' => 'Transction Add Successfully'));
                    } else {
                        return response()->json(array('status' => 400, 'errors' => 'Transction Not Add'));
                    }
                } else {
                    return response()->json(array('status' => 400, 'errors' => 'Transction Not Add'));
                }

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
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

   
    public function destroy($id)
    {
        //
    }
}
