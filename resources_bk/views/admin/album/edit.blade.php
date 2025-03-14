@extends('layout.page-app')

@section('page_title',  __('label.edit_album'))

@section('content')
    
    @include('layout.sidebar')

    <div class="right-content">
        @include('layout.header')
    
        <div class="body-content">
            <!-- mobile title -->
            <h1 class="page-title-sm">{{__('label.edit_album')}}</h1>
            <div class="border-bottom row mb-3">
                <div class="col-sm-10">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{__('label.dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('album.index') }}">{{__('label.album')}}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{__('label.edit_album')}}
                        </li>
                    </ol>
                </div>
                <div class="col-sm-2 d-flex align-items-center justify-content-end">
                    <a href="{{ route('album.index') }}" class="btn btn-default mw-120" style="margin-top:-14px">{{__('label.album_list')}}</a>
                </div>
            </div>

            <div class="card custom-border-card mt-3">
                <div class="card-body">
                    <form name="album" id="album" autocomplete="off" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="@if($data){{$data->id}}@endif"> 
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label> {{__('label.name')}} </label>
                                    <input type="text" name="name" value="@if($data){{$data->name}}@endif" class="form-control" placeholder="{{__('label.enter_album_name')}}">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <?php 
                                        $x = explode(",",$data->video_id);
                                    ?>
                                    <label for="video_id">{{__('label.video')}}</label>
                                        <select class="form-control selectd2" style="width:100%!important;" name="video_id[]" multiple id="video_id">
                                            @foreach ($video as $key => $value)
                                            <option value="{{ $value->id}}" {{(in_array($value->id, $x)) ? 'selected' : ''}}>
                                                {{ $value->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6"> 
                                <div class="form-group"> 
                                    <label>{{__('label.image')}}</label> 
                                    <input type="file" class="form-control" name="image" value="" id="image"> 
                                    <label class="mt-1 text-gray">{{__('label.note')}}</label>
                                </div>
                            </div>
                            <div class="col-md-6"> 
                                <div class="form-group">
                                    <div class="custom-file ml-5"> 
                                        <img  src="{{$data['image']}}" height="120px" width="120px" id="Uploaded-Image">
										<input type="hidden" name="old_image" value="@if($data){{$data->image}}@endif">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-top pt-3 text-right">
                            <button type="button" class="btn btn-default mw-120" onclick="edit_album()">{{__('label.save')}}</button>
                            <a href="{{route('album.index')}}" class="btn btn-cancel mw-120 ml-2">{{__('label.cancel')}}</a>
                            <input type="hidden" name="_method" value="PATCH">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pagescript')
	<script>
        $(document).ready(function() {
            $("#video_id").select2();
			$(".selectd2").select2({
				placeholder: "{{__('label.select_video')}}"
			});
        });
		function edit_album(){
			$("#dvloader").show();
			var formData = new FormData($("#album")[0]);
			$.ajax({
                headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
                enctype: 'multipart/form-data',
				type:'POST',
				url:'{{ route("album.update",[$data->id]) }}',
				data:formData,
				cache:false,
				contentType: false,
				processData: false,
				success:function(resp){
					$("#dvloader").hide();
					get_responce_message(resp, 'album', '{{ route("album.index") }}');
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					$("#dvloader").hide();
					toastr.error(errorThrown.msg,'failed');         
				}
			});
		}
	</script>
@endsection