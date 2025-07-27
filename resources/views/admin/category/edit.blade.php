@extends('layout.page-app')

@section('page_title',  __('label.edit_category'))

@section('content')

	@include('layout.sidebar')

	<div class="right-content">
		@include('layout.header')

		<div class="body-content">
			<!-- mobile title -->
			<h1 class="page-title-sm">{{__('label.edit_category')}}</h1>

			<div class="border-bottom row mb-3">
				<div class="col-sm-10">
					<ol class="breadcrumb">
						<li class="breadcrumb-item">
							<a href="{{ route('admin.dashboard') }}">{{__('label.dashboard')}}</a>
						</li>
						<li class="breadcrumb-item">
							<a href="{{ route('category.index') }}">{{__('label.category')}}</a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">
							{{__('label.edit_category')}}
						</li>
					</ol>
				</div>
				<div class="col-sm-2 d-flex align-items-center justify-content-end">
					<a href="{{ route('category.index') }}" class="btn btn-default mw-120" style="margin-top:-14px">{{__('label.category_list')}}</a>
				</div>
			</div>

			<div class="card custom-border-card mt-3">
				<div class="card-body">
					<form name="category" id="category_update" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="id" value="@if($data){{$data->id}}@endif">
						<div class="form-row">
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label> {{__('label.name')}} </label>
									<input type="text" value="@if($data){{$data->name}}@endif" name="name" class="form-control" placeholder="{{__('label.enter_category_name')}}">
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
							<a href="javascript:void(0);" class="btn btn-default mw-120 ml-2 btn-create-subcategory" role="button">
								Create Subcategory
							</a>
							<button type="button" class="btn btn-default mw-120" onclick="update_category()">{{__('label.update')}}</button>
							<a href="{{route('category.index')}}" class="btn btn-cancel mw-120 ml-2">{{__('label.cancel')}}</a>
							<input type="hidden" name="_method" value="PATCH">
						</div>
					</form>
				</div>
			</div>

			<!-- subcategories Datatable -->
			 @if($data->subcategories->count())
				<div class="card mt-4">
					<div class="card-header">
						<h5 class="mb-0">Subcategories</h5>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table id="subcategoriesTable" class="table table-bordered table-striped">
								<thead>
									<tr>
										<th>#</th>
										<th>Name</th>
										<th>Image</th>
										<th>Status</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									@foreach($data->subcategories as $index => $sub)
										<tr>
											<td>{{ $index + 1 }}</td>
											<td>{{ $sub->name }}</td>
											<td>
												@if($sub->image)
													<img src="{{ $sub->image_url }}" alt="{{ $sub->name }}" width="50">
												@else
													<span class="text-muted">No image</span>
												@endif
											</td>
											<td>
												<span class="badge badge-{{ $sub->status ? 'success' : 'secondary' }}">
													{{ $sub->status ? 'Active' : 'Inactive' }}
												</span>
											</td>
											<td>
												<button class="btn btn-sm btn-outline-primary edit-subcategory" data-id="{{ $sub->id }}">Edit</button>
												<button class="btn btn-sm btn-outline-danger delete-subcategory" data-id="{{ $sub->id }}">
													Delete
												</button>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			@else
				<div class="mt-4 text-muted">No subcategories found.</div>
			@endif
			<!-- ./ subcategories datatable -->
		</div>
	</div>

	<!-- Create SubCategory Modal -->
	<div class="modal fade" id="createSubcategoryModal" tabindex="-1" role="dialog" aria-labelledby="createSubcategoryLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<form id="subcategory_create" enctype="multipart/form-data" autocomplete="off">
			@csrf
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="createSubcategoryLabel">Create Subcategory</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span>&times;</span>
					</button>
					</div>
					<div class="modal-body">
					<input type="hidden" name="category_id" id="category_id" value="{{ $data->id }}">
					<input type="hidden" name="id" id="subcategory_id" value="">
					
					<div class="form-group">
						<label>Name</label>
						<input type="text" name="name" id="subcategory_name" class="form-control" placeholder="Enter subcategory name">
					</div>

					<div class="form-group">
						<label>Image</label>
						<input type="file" name="image" class="form-control" id="subcategoryImage">
						<img id="subcategoryImagePreview" src="#" alt="Image Preview" style="display:none; margin-top:10px; max-height:120px;">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-cancel" data-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-default btn-save-create-subcaytegory">Save</button>
				</div>
			</div>
			</form>
		</div>
	</div>
@endsection

@section('pagescript')
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		const SUB_CATEGORIES = @json($data->subcategories);

		function update_category(){
			$("#dvloader").show();
			var formData = new FormData($("#category_update")[0]);

			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				enctype: 'multipart/form-data',
				type: 'POST',
				url: '{{route("category.update", [$data->id])}}',
				data: formData,
				cache:false,
				contentType: false,
				processData: false,
				success:function(resp){
					$("#dvloader").hide();
					get_responce_message(resp, 'category_update', '{{ route("category.index") }}');
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					$("#dvloader").hide();
					toastr.error(errorThrown.msg,'failed');         
				}
			});
		}
		
		$(document).ready(function () {
			$('#subcategoriesTable').DataTable({
				pageLength: 10,
				ordering: true,
				columnDefs: [
					{ orderable: false, targets: [2, 4] } // Disable sorting for Image and Action columns
				]
			});
			
			// Show modal
			$('.btn-create-subcategory').click(function () {
				$('#createSubcategoryModal').modal('show');
			});

			// Preview image
			$('#subcategoryImage').change(function () {
				let reader = new FileReader();
				reader.onload = (e) => {
					$('#subcategoryImagePreview')
						.attr('src', e.target.result)
						.show();
				};
				reader.readAsDataURL(this.files[0]);
			});

			$(".btn-save-create-subcaytegory").click(function() {
				let formData = new FormData($("#subcategory_create")[0]);

				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					url: '{{ route("admin.create_subcategory") }}',
					type: 'POST',
					data: formData,
					contentType: false,
					processData: false,
					success: function (response) {
						$("#dvloader").hide();
						$('#createSubcategoryModal').modal('hide');
						toastr.success("Subcategory created successfully.");

						setTimeout(function () {
							location.reload();
						}, 500); // Reloads page after 500ms
					},
					error: function (xhr) {
						$("#dvloader").hide();

						if (xhr.responseJSON && xhr.responseJSON.message) {
							toastr.error(xhr.responseJSON.message);
						} else {
							toastr.error("Something went wrong.");
						}
					}
				});
			});

			$(document).on('click', '.delete-subcategory', function () {
				let subcategoryId = $(this).data('id');

				Swal.fire({
					title: 'Are you sure?',
					text: "You want to delete this subcategory?",
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Yes, delete it!'
				}).then((result) => {
					if (result.isConfirmed) {
						$.ajax({
							url: "{{ route('admin.delete_subcategory') }}",
							type: "POST",
							data: {
								id: subcategoryId,
								_token: "{{ csrf_token() }}"
							},
							success: function (response) {
								if (response.status) {
									Swal.fire('Deleted!', response.message, 'success');
									setTimeout(() => location.reload(), 800);
								} else {
									Swal.fire('Error!', response.message, 'error');
								}
							},
							error: function (xhr) {
								Swal.fire('Error!', 'Something went wrong.', 'error');
							}
						});
					}
				});
			});

			$(document).on('click', '.edit-subcategory', function () {
				let subcategoryId = $(this).data('id');
				let subcategoryData = SUB_CATEGORIES.filter((sub) => {
					return sub.id == subcategoryId;
				});

				if (subcategoryData.length) {
					subcategoryData = subcategoryData[0];
					$('#createSubcategoryModal #subcategory_create #subcategory_id').val(subcategoryData.id ?? null);
					$('#createSubcategoryModal #subcategory_create #category_id').val(subcategoryData.category_id ?? null);
					$('#createSubcategoryModal #subcategory_create #subcategory_name').val(subcategoryData.name ?? null);
					$('#createSubcategoryModal #subcategory_create #subcategoryImagePreview').attr('src', subcategoryData.image_url ?? null).show();
				}
				$('#createSubcategoryModal').modal('show');
			});
		});
	</script>
@endsection