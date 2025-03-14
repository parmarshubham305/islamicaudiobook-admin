@extends('layout.page-app')

@section('page_title',  __('label.edit_user'))

@section('content')

	@include('layout.sidebar')

	<div class="right-content">
		@include('layout.header')

		<div class="body-content">
			<!-- mobile title -->
			<h1 class="page-title-sm">Edit User Details</h1>

			<div class="card custom-border-card mt-3">
				<div class="card-body">
					<form name="admins" id="admin_update" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="id" value="@if($data){{$data->id}}@endif">
						<div class="form-row">
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label> {{__('label.user_name')}} </label>
									<input type="text" value="@if($data){{$data->user_name}}@endif" name="user_name"  class="form-control" placeholder="{{__('label.enter_your_name')}}">
								</div>
							</div>
						</div>
						<div class="form-row">
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label>{{__('label.email')}}</label>
									<input type="email" value="@if($data){{$data->email}}@endif" name="email"  class="form-control" placeholder="{{__('label.enter_your_email')}}">
								</div>
							</div>
						</div>
						 @if(auth()->guard('admin')->user()->permissions_role != "publisher")
						<div class="form-row">
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label>{{__('Enter Phone Number')}}</label>
									<input type="number" value="@if($data){{$data->phone_number}}@endif" name="phone_number"  class="form-control" placeholder="{{__('+91 1234567890')}}">
								</div>
							</div>
						</div>
						<div class="form-row">
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label>{{__('Enter Account Number')}}</label>
									<input type="number" value="@if($data){{$data->account_number}}@endif" name="account_number"  class="form-control" placeholder="{{__('1234567890')}}">
								</div>
							</div>
						</div>
						<div class="form-row">
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label>{{__('Enter IFSC Code')}}</label>
									<input type="text" value="@if($data){{$data->ifsc_code}}@endif" name="ifsc_code"  class="form-control" placeholder="{{__('Enter IFSC code')}}">
								</div>
							</div>
						</div>
						<div class="form-row">
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label>{{__('Enter Branch Code')}}</label>
									<input type="text" value="@if($data){{$data->branch_code}}@endif" name="branch_code"  class="form-control" placeholder="{{__('Enter Branch code')}}">
								</div>
							</div>
						</div>
						<div class="form-row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label> Us Citizen? </label>
                                    <select class="form-control" name="us_citizen" id="us_citizen">
                                        <option value="1" {{$data->us_citizen == '1'  ? 'selected' : ''}}>Yes</option>
                                        <option value="0" {{$data->us_citizen == '0'  ? 'selected' : ''}}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
						<div class="form-row" id="itin_number">
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label>{{__('Enter ITIN Number')}}</label>
									<input type="number" value="@if($data){{$data->itin_number}}@endif" name="itin_number"  class="form-control" placeholder="{{__('Enter ITIN number')}}">
								</div>
							</div>
						</div>
						<div class="form-row" id="ein_number">
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label>{{__('Enter Ein Number')}}</label>
									<input type="number" value="@if($data){{$data->ein_number}}@endif" name="ein_number"  class="form-control" placeholder="{{__('Enter Ein Number')}}">
								</div>
							</div>
						</div>
						@endif
						<div class="form-row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label> Role </label>
                                    @if($data->permissions_role == 'super_admin')
                                    <select class="form-control" name="permissions_role">
                                    <option value="super_admin" {{$data->permissions_role == 'super_admin'  ? 'selected' : ''}}>Super Admin</option>
                                    <option value="author" {{$data->permissions_role == 'author'  ? 'selected' : ''}}>Author</option>
                                    <option value="publisher" {{$data->permissions_role == 'publisher'  ? 'selected' : ''}}>Publisher</option>
                                    </select>
                                    @else
                                    <input type="text" class="form-control" readonly value="{{$data->permissions_role}}">
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label>Change Password</label>
									<input type="password" value="" name="password" class="form-control" placeholder="Enter your new password">
								</div>
							</div>
						</div>
						<div class="border-top pt-3 text-right">
							<button type="button" class="btn btn-default mw-120" onclick="update_admin()">{{__('label.update')}}</button>
							<a href="{{route('admins.index')}}" class="btn btn-cancel mw-120 ml-2">{{__('label.cancel')}}</a>
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
        function isUscitizen(){
	        var us_citizen = $('#us_citizen').find(":selected").val();
            if(us_citizen == "1"){
                $('#ein_number').show();
                $('#itin_number').hide();
            }else{
                $('#ein_number').hide();   
                $('#itin_number').show();
            }
        }
		function update_admin(){
			$("#dvloader").show();
			var formData = new FormData($("#admin_update")[0]);

			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				enctype: 'multipart/form-data',
				type: 'POST',
				url: '{{route("admins.update", [$data->id])}}',
				data: formData,
				cache:false,
				contentType: false,
				processData: false,
				success:function(resp){
					$("#dvloader").hide();
					<?php if (auth()->guard('admin')->user()->permissions_role == 'super_admin'): ?>
                        get_responce_message(resp, 'admin_update', '{{ route("admins.index") }}');
                    <?php else: ?>
                        get_responce_message(resp, 'admin_update', '{{ route("admin.dashboard") }}');
                    <?php endif; ?>
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					$("#dvloader").hide();
					toastr.error(errorThrown.msg,'failed');         
				}
			});
		}
		
		$( document ).ready(function() {
            isUscitizen();
        });
		
		$('#us_citizen').change(function(){
		    isUscitizen();
		})
		
	</script>
@endsection