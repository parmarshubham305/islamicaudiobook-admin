@extends('layout.page-app')
    
    @section('page_title',  __('Edit E-Book'))
    
    @section('content')
        @include('layout.sidebar')
    
        <div class="right-content">
            @include('layout.header')
            <style>
                .loading-button {
                    position: relative;
                }

                .spinner {
                    display: none;
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 20px;
                    height: 20px;
                    border: 3px solid rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    border-top: 3px solid #3498db;
                    animation: spin 1s linear infinite;
                }

                @keyframes spin {
                    0% { transform: translate(-50%, -50%) rotate(0deg); }
                    100% { transform: translate(-50%, -50%) rotate(360deg); }
                }
                
                .audio-container {
                    display: flex;
                    align-items: center;
                    margin-bottom: 10px;
                }
                
                .audio-container audio {
                    margin-right: 10px;
                }

            </style>
            <div class="body-content">
                <!-- mobile title -->
                <h1 class="page-title-sm">{{__('Edit E-Book')}}</h1>
                <div class="border-bottom row mb-3">
                    <div class="col-sm-10">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{__('label.dashboard')}}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('e-book.index') }}">E-Book</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{__('Edit E-Book')}}
                            </li>
                        </ol>
                    </div>
                    <div class="col-sm-2 d-flex align-items-center justify-content-end">
                        <a href="{{ route('e-book.index') }}" class="btn btn-default mw-120" style="margin-top:-14px">{{__('E-Book List')}}</a>
                    </div>
                </div>
                <div class="card custom-border-card mt-3">
                    <div class="card-body">
                        <form enctype="multipart/form-data" id="eBookForm" autocomplete="off">
                            @csrf
                            <input type="hidden" name="id" value="@if($data){{$data->id}}@endif"> 

                            <div class="form-row">
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label for="name">{{__('label.name')}}</label>
                                    <input type="text" name="name" value="@if($data){{$data->name}}@endif" class="form-control" placeholder="{{__('label.enter_e_book_name')}}">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label for="artist_id">{{__('label.artist')}}</label>
                                    <select class="form-control" style="width:100%!important;" name="artist_id" id="artist_id">
                                        <option value="">Select Artist</option>
                                        @foreach ($artist as $key => $value)
                                        <option value="{{ $value->id}}" {{$value->id == $data->artist_id ? 'selected' : ''}}>
                                            {{ $value->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label for="user_id">{{__('Application Users')}}</label>
                                    <select class="form-control" style="width:100%!important;" name="user_id" id="user_id">
                                        <option value="">Select Users</option>
                                        @foreach ($user as $key => $value)
                                        <option value="{{ $value->id}}" {{$value->id == $data->user_id ? 'selected' : ''}}>
                                            {{ $value->full_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                        </div>
               
                        <div class="form-row">
                            <div class="col-md-3 mb-3">
                                <div class="form-group">
                                    <label for="category_id">{{__('label.category')}}</label>
                                    <select class="form-control" style="width:100%!important;" name="category_id" id="category_id">
                                    <option value="">Select Category</option>
                                        @foreach ($category as $key => $value)
                                        <option value="{{ $value->id}}" {{$value->id == $data->category_id ? 'selected' : ''}}>
                                            {{ $value->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="form-group">
                                    <label for="subcategory_id">Subcategory</label>
                                    {{-- Subcategory Dropdown --}}
                                    <select class="form-control" name="subcategory_id" id="subcategory_id" style="width:100%!important;">
                                        <option value="">Select Subcategory</option>

                                        @php
                                            $selectedCategory = $category->firstWhere('id', $data->category_id);
                                        @endphp

                                        @if ($selectedCategory && $selectedCategory->subcategories->count())
                                            @foreach ($selectedCategory->subcategories as $subcat)
                                                <option value="{{ $subcat->id }}" {{ $subcat->id == $data->subcategory_id ? 'selected' : '' }}>
                                                    {{ $subcat->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="form-group Is_Download">
                                    <label for="download">{{__('label.Feature')}}</label>
                                    <select class="form-control" name="is_feature">
                                    <option value="0" {{ $data->is_feature == 0  ? 'selected' : ''}}>{{__('label.no')}}</option>
                                    <option value="1" {{ $data->is_feature == 1  ? 'selected' : ''}}>{{__('label.yes')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-group">
                                    <label for="name">{{__('label.price')}}</label>
                                    <input type="text" name="price" value="@if($data){{$data->price}}@endif" class="form-control" placeholder="{{__('label.enter_price')}}">
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="form-group IS Paid">
                                    <label for="download">IS Paid</label>
                                    <select class="form-control" name="is_paid" id="is_paid">
                                    <option value="0" {{ $data->is_paid == 0  ? 'selected' : ''}}>{{__('label.free')}}</option>
                                    <option value="1" {{ $data->is_paid == 1  ? 'selected' : ''}}>{{__('label.paid')}}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3" id="package_id_div">
                                <div class="form-group">
                                    <label for="download">Subscription Package</label>
                                    @php
                                        use App\Models\Package;
                                        $packages = Package::all();
                                        $subscribedPackageIds = $data->subscriptions->pluck('id')->toArray(); // Safely collect IDs
                                    @endphp

                                    <select class="form-control" name="package_id[]" multiple id="package_id">
                                        @foreach($packages as $package)
                                            <option value="{{ $package->id }}" {{ in_array($package->id, $subscribedPackageIds ?? []) ? 'selected' : '' }}>
                                                {{ $package->name ?? 'Unnamed Package' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label>{{__('label.description')}}</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Address Hear ...">{{$data->description}}</textarea>
                                   
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label>{{__('Upload Copyright Ebook file')}}</label>
                                    <div class="form-group">
                                        <input type="file" name="upload_file" id="upload_file" accept=".pdf">
                                    </div>
                                    @if($data->upload_file != NULL)
                                    <a href="{{ asset('storage/documents/' . $data->upload_file) }}" target="_blank">View uploaded file</a>
                                    @endif
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
                                        <img  src="{{$data->image}}" height="120px" width="120px" id="preview-image-before-upload">
                                        <input type="hidden" name="old_thumbnail" value="@if($data){{$data->image}}@endif"> 
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $aiaudioActive = ''; ?>
                        <?php $audioActive = ''; ?>
                        @if($data->isAudioTab == 0)
                            <?php $audioActive = "active show"; ?>
                        @else
                            <?php $aiaudioActive = "active show"; ?>
                        @endif
                        
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link {{$audioActive}}" id="audio-tab" data-toggle="tab" href="#audio" role="tab" onclick="checkActiveTab(this)" aria-controls="audio"
                                        aria-selected="true">Upload E-Book</a>
                                    </li>
                                </ul>
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade {{$audioActive}}" id="audio" role="tabpanel" aria-labelledby="audio-tab">
                                        <div class="form-row"> 
                                            <div class="col-md-6 mb-3">                
                                                <div class="server_video box form-group col-lg-12 videoLink" style="margin-top:30px;">
                                                    <div id="serverVideo" style="display: block;">
                                                        <div id="filelist"></div>
                                                        <div id="container" style="position: relative;">
                                                            <div class="form-group audio-container">
                                                                <div id="audio-tab-div">
                                                                    <input type="text" name="e-book_name[]" class="audio-text-input" placeholder="Enter e-book name">
                                                                    <input type="file" name="e-book[]" id="audio_converted_url" style="position: relative; z-index: 1;">
                                                                    <button type="button" class="remove-audio" style="display: none;">-</button>
                                                                </div>  
                                                            </div>
                                                            <button type="button" class="add-another-audio">Add More</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3" style="margin-top:25px;">
                                                    @if(!empty($multiple_ebooks))
                                                        @foreach($multiple_ebooks as $ebook)
                                                            <div class="audio-container" data-id="{{$ebook->id}}">
                                                                <input type="text" style="width: 200px; margin-right: 5px; border: 0px solid;" readonly="true" value="{{$ebook->ebook_name}}">
                                                                <a href="{{ route('e-book.ebookDownload', ['id' => $ebook->id]) }}" 
                                                                style="width: 100px;margin-right: 5px; text-decoration: none; align-items: center;" 
                                                                type="button" 
                                                                value="{{ $ebook->id }}">
                                                                    Download
                                                                </a>

                                                                <button style="margin-right: 5px;" type="button" value="{{$ebook->id}}" class="db-remove-audio">-</button>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                    <div id="ajax-message" style="display: none; padding: 10px; background-color: #4CAF50; color: white; margin-top: 10px;"></div>
                                                
                                                <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="confirmDeleteLabel">Confirm Delete</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete this e-book file?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>            
                            </div>
                        </div>

                            <div class="border-top mt-2 pt-3 text-right">
                                <button type="button" class="btn btn-default mw-120" onclick="edit_audio_book()">{{__('label.update')}}</button>
                                <a href="{{route('e-book.index')}}" class="btn btn-cancel mw-120 ml-2">{{__('label.cancel')}}</a>
                                <input type="hidden" name="_method" value="PATCH">
                            </div>
                        </form>
                    </div>
                </div>
    
           
    @endsection
    
    @section('pagescript')
    <script>
        var assetBaseUrl = "{{ asset('') }}";
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
     <script>
        function checkActiveTab(event){
            var isAudioTab = '<?php echo $data->isAudioTab; ?>';
            if(isAudioTab == 1){
                var audio_value = '<?php echo $data->audio; ?>';
            }else{
                var audio_value = '';
            }
            
            if(event.id == 'aiaudio-tab'){
                var html = '<input type="hidden" name="audio"  id="audio_converted_url"  value="'+audio_value+'">';
                $("#aiaudio-tab-div").html(html);
                $("#audio-tab-div").html('');
                $("#isAudioTab").val(1);
            }else{
                var initialAudioHtml = `
                    <input type="text" name="e-book_name[]" class="audio-text-input" placeholder="Enter e-book name">
                    <input type="file" name="audio[]" id="audio_converted_url" style="position: relative; z-index: 1;">
                `;
                $("#audio-tab-div").html(initialAudioHtml);
                $("#aiaudio-tab-div").html('');
                if(isAudioTab == 0){
                    $("#audio_content").val('');
                }
                
                $("#isAudioTab").val(0);
            }
        }
    </script>
    <script>
            
            function setTypes(){
                var voice_language = $("#voice_language").val();
                var html = '';
                if(voice_language == 'english'){
                    html = html+'<option value="en-US-SaraNeural">Sara --- en-US-SaraNeural</option>';
                    html = html+'<option value="en-US-SteffanNeural">Steffan --- en-US-SteffanNeural</option>';
                    html = html+'<option value="en-US-MichelleNeural">Michelle --- en-US-MichelleNeural</option>';
                    html = html+'<option value="en-US-JaneNeural">Jane --- en-US-JaneNeural</option>';
                    html = html+'<option value="en-US-JacobNeural">Jacob --- en-US-JacobNeural</option>';
                    html = html+'<option value="en-US-EricNeural">Eric --- en-US-EricNeural</option>';
                    html = html+'<option value="en-US-AshleyNeural">Ashley --- en-US-AshleyNeural</option>';
                    html = html+'<option value="en-US-GuyNeural">Guy --- en-US-GuyNeural</option>';
                }else if(voice_language == 'hindi'){
                    html = html+'<option value="hi-IN-SwaraNeural">hi-IN-SwaraNeural (Female)-- Hindi</option>';
                    html = html+'<option value="hi-IN-MadhurNeural">hi-IN-MadhurNeural (Male)-- Hindi</option>';
                }else if(voice_language == 'arabic'){
                    html = html+'<option value="ar-AE-FatimaNeural">ar-AE-FatimaNeural (Female)-- arabic</option>';
                    html = html+'<option value="ar-AE-HamdanNeural">ar-AE-HamdanNeural (Male)-- arabic</option>';
                }else if(voice_language == 'french'){
                    html = html+'<option value="fr-FR-DeniseNeural">fr-FR-DeniseNeural (Female)-- french</option>';
                    html = html+'<option value="fr-FR-HenriNeural">fr-FR-HenriNeural (Male)-- french</option>';
                    html = html+'<option value="fr-FR-AlainNeural">fr-FR-AlainNeural (Male)-- french</option>';
                    html = html+'<option value="fr-FR-BrigitteNeural">fr-FR-BrigitteNeural (Female)-- french</option>';
                }else if(voice_language == 'urdu'){
                    html = html+'<option value="ur-IN-GulNeural">ur-IN-GulNeural (Female)-- urdu</option>';
                    html = html+'<option value="ur-IN-SalmanNeural">ur-IN-SalmanNeural (Male)-- urdu</option>';
                }else if(voice_language == 'russian'){
                    html = html+'<option value="ru-RU-SvetlanaNeural">ru-RU-SvetlanaNeural (Female)-- russian</option>';
                    html = html+'<option value="ru-RU-DmitryNeural">ru-RU-DmitryNeural (Male)-- russian</option>';
                    html = html+'<option value="ru-RU-DariyaNeural">ru-RU-DariyaNeural (Female)-- russian</option>';
                }else if(voice_language == 'malay'){
                    html = html+'<option value="ms-MY-YasminNeural">ms-MY-YasminNeural (Female)-- malay</option>';
                    html = html+'<option value="ms-MY-OsmanNeural">ms-MY-OsmanNeural (Male)-- malay</option>';
                }else if(voice_language == 'spanish'){
                    html = html+'<option value="es-ES-ElviraNeural">es-ES-ElviraNeural (Female)-- spanish</option>';
                    html = html+'<option value="es-ES-AlvaroNeural">es-ES-AlvaroNeural (Male)-- spanish</option>';
                    html = html+'<option value="es-ES-AbrilNeural">es-ES-AbrilNeural (Female)-- spanish</option>';
                    html = html+'<option value="es-ES-ArnauNeural">es-ES-ArnauNeural (Male)-- spanish</option>';
                }
                $("#voice_name").html(html);
            }
       
        $(document).ready(function() {
            let is_paid = $("#is_paid").val();
            console.log("is_paid : ", is_paid);

            if (is_paid == 0) {
                $("#package_id").val([]);
                $("#package_id_div").hide();
            } else {
                $("#package_id_div").show();
            }

            let audioIdToDelete;
            
            $("#type").change(function() {
                $(this).find("option:selected").each(function() {
                    var optionValue = $(this).attr("value");
                    if (optionValue) {
                        $(".box").not("." + optionValue).hide();
                        $("." + optionValue).show();
                    } else {
                        $(".box").hide();
                    }
                });
            }).change();

            $('#package_id').select2({
                placeholder: 'Select...',
                allowClear: true,
                width: '100%'
            });

            $(document).on('change', '#is_paid', function() {
                let is_paid = $(this).val();
                console.log("is_paid : ", is_paid);

                if (is_paid == 0) {
                    $("#package_id").val([]);
                    $("#package_id_div").hide();
                } else {
                    $("#package_id_div").show();
                }
            });
            
            $('#container').on('click', '.add-another-audio', function() {
                let audioContainer = $(this).closest('#container').find('.audio-container').first();
                let newAudioContainer = audioContainer.clone();
        
                newAudioContainer.find('input[type="file"]').val('');
                newAudioContainer.find('.audio-text-input').val('');
                newAudioContainer.find('.remove-audio').show();
        
                $(this).before(newAudioContainer);
            });
    
            $('#container').on('click', '.remove-audio', function() {
                $(this).closest('.audio-container').remove();
            });
            
            $('.db-remove-audio').on('click', function() {
                audioIdToDelete = $(this).val();
                $('#confirmDeleteModal').modal('show');
            });
            
            $('#confirmDeleteBtn').on('click', function() {
                $.ajax({
                    url: "{{ route('e-book.deleteEBookFile', ['id' => ':id']) }}".replace(':id', audioIdToDelete), // Adjust the URL as necessary
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.audio-container[data-id="' + audioIdToDelete + '"]').remove(); // Reload the page to reflect changes
                            $('#ajax-message').text('E-Book file successfully removed.').show().delay(3000).fadeOut();
                        } else {
                            alert('Error deleting audio file.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert('Error deleting e-book file.');
                    }
                });
                $('#confirmDeleteModal').modal('hide');
            });
        });

        $(document).on('change', '#category_id', function() {
            let category_id = $(this).val();

            if (category_id) {
                $.ajax({
                    url: "{{ route('admin.get_category_subcategories') }}", // Use this in Blade view
                    type: "POST",
                    data: {
                        id: category_id,
                        _token: '{{ csrf_token() }}' // Add CSRF token for POST
                    },
                    success: function(response) {
                        // Populate subcategory dropdown (example)
                        let $subcat = $('#subcategory_id');
                        $subcat.empty().append('<option value="">Select Subcategory</option>');
                        if (response.status && response.data.length > 0) {
                            $.each(response.data, function(index, subcat) {
                                $subcat.append(`<option value="${subcat.id}">${subcat.name}</option>`);
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching subcategories:", xhr.responseText);
                    }
                });
            }
        });
		
        function edit_audio_book(){
            $("#dvloader").show();
            var formData = new FormData($("#eBookForm")[0]);
            $.ajax({
                type:'POST',
                url:'{{ route("e-book.update" ,[$data->id]) }}',
                data:formData,
                cache:false,
                contentType: false,
                processData: false,
                success:function(resp){
                    $("#dvloader").hide();
                    get_responce_message(resp, 'eBookForm', '{{ route("e-book.index") }}');
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $("#dvloader").hide();
                    toastr.error(errorThrown.msg,'failed');         
                }
            });
        }
	</script>
        
    @endsection</h1>