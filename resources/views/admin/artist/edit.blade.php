@extends('layout.page-app')

@section('page_title',  __('label.edit_artist'))

@section('content')

	@include('layout.sidebar')

	<div class="right-content">
		@include('layout.header')

		<div class="body-content">
			<!-- mobile title -->
			<h1 class="page-title-sm">{{__('label.edit_artist')}}</h1>

			<div class="border-bottom row mb-3">
				<div class="col-sm-10">
					<ol class="breadcrumb">
						<li class="breadcrumb-item">
							<a href="{{ route('admin.dashboard') }}">{{__('label.dashboard')}}</a>
						</li>
						<li class="breadcrumb-item">
							<a href="{{ route('artist.index') }}">{{__('label.artist')}}</a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">
							{{__('label.edit_artist')}}
						</li>
					</ol>
				</div>
				<div class="col-sm-2 d-flex align-items-center justify-content-end">
					<a href="{{ route('artist.index') }}" class="btn btn-default mw-120" style="margin-top:-14px">{{__('label.artist_list')}}</a>
				</div>
			</div>

			<div class="card custom-border-card mt-3">
				<div class="card-body">
					<form name="category" id="artist_update" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="id" value="@if($data){{$data->id}}@endif">
						<div class="form-row">
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label> {{__('label.name')}} </label>
									<input type="text" value="@if($data){{$data->name}}@endif" name="name" class="form-control" placeholder="{{__('label.enter_artist_name')}}">
								</div>
							</div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label>{{__('label.address')}}</label>
                                    <textarea name="address" class="form-control" rows="3" placeholder="Address Hear ...">{{$data->address}}</textarea>
                                   
                                </div>
                            </div>
						</div>
                        <div class="form-row">
                            <div class="form-group col-lg-12">
                                <label>{{__('label.bio')}}</label>
                                <textarea name="bio" class="form-control" rows="5" placeholder="I am artist ...">{{$data->bio}}</textarea>
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
							<button type="button" class="btn btn-default mw-120" onclick="artist_update()">{{__('label.update')}}</button>
							<a href="{{route('artist.index')}}" class="btn btn-cancel mw-120 ml-2">{{__('label.cancel')}}</a>
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
		function artist_update(){
			$("#dvloader").show();
			var formData = new FormData($("#artist_update")[0]);

			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				enctype: 'multipart/form-data',
				type: 'POST',
				url: '{{route("artist.update", [$data->id])}}',
				data: formData,
				cache:false,
				contentType: false,
				processData: false,
				success:function(resp){
					$("#dvloader").hide();
					get_responce_message(resp, 'artist_update', '{{ route("artist.index") }}');
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					$("#dvloader").hide();
					toastr.error(errorThrown.msg,'failed');         
				}
			});
		}
	</script>
@endsection