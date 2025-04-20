@extends('layout.page-app')
    
@section('page_title',  __('label.smart_collection'))

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

        </style>
        <div class="body-content">
            <!-- mobile title -->
            <h1 class="page-title-sm">Add {{ __('label.smart_collection') }}</h1>
            <div class="border-bottom row mb-3">
                <div class="col-sm-10">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('smart-collection.index') }}">{{ __('label.smart_collection') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Add {{ __('label.smart_collection') }}
                        </li>
                    </ol>
                </div>
                <div class="col-sm-2 d-flex align-items-center justify-content-end">
                    <a href="{{ route('smart-collection.index') }}" class="btn btn-default mw-120" style="margin-top:-14px">{{ __('label.smart_collection') }} List</a>
                </div>
            </div>
            
            <div class="card custom-border-card mt-3">
                <div class="card-body">
                    <form enctype="multipart/form-data" id="smart-collection" autocomplete="off">
                        @csrf
                        <input type="hidden" name="id" value="">

                        <!-- Start Smart Collection -->
                        <div class="form-row">
                            <div class="col-4">
                                <div class="form-row">
                                    <div class="col-12 mb-3">
                                        <div class="form-group">
                                            <label for="name">{{__('label.name')}}</label>
                                            <input type="text" name="title" class="form-control" placeholder="{{__('label.enter_smart_collection_name')}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-6 mb-3">
                                        <div class="form-group">
                                            <label for="priceInput">{{__('label.price')}}</label>
                                            <input type="text" name="price" id="priceInput" class="form-control" placeholder="{{__('label.enter_price')}}">
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="form-group">
                                            <label for="statusSelect">{{__('label.status')}}</label>
                                            <select name="status" id="statusSelect" class="form-control">
                                                <option value="1" selected>{{ __('label.active') }}</option>
                                                <option value="0">{{ __('label.in_active') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-12 mb-3">
                                        <div class="form-group">
                                            <label>{{__('label.description')}}</label>
                                            <textarea name="description" class="form-control" rows="3" placeholder="{{__('label.enter_description')}}"></textarea>
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
                                                <img  src="{{asset('assets/imgs/no_img.png')}}" height="120px" width="120px" id="Uploaded-Image">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="form-row">
                                    <div class="col-3 mb-3">
                                        <div class="form-group">
                                            <label for="smart-collection-by-select">{{__('label.create_smart_collection_by')}}</label>
                                            <select name="type" id="smart-collection-by-select" class="form-control">
                                                <option value="audio_book" selected>{{ __('label.audio_book') }}</option>
                                                <option value="e_book">{{ __('label.e_book') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-3 mb-3">
                                        <div class="form-group">
                                            <label for="artist-select">{{ __('label.artist') }}</label>
                                            <select id="artist-select" class="form-control" multiple></select>
                                        </div>
                                    </div>
                                    <div class="col-3 mb-3">
                                        <div class="form-group">
                                            <label for="category-select">{{ __('label.category') }}</label>
                                            <select id="category-select" class="form-control" multiple></select>
                                        </div>
                                    </div>
                                    <div class="col-3 mb-3 mt-4">
                                        <button class="btn btn-primary btn-reset" type="button">Reset</button>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-12 mb-3">
                                        <table id="table-e-book" class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" id="table-e-book-select-all"></th>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Artist</th>
                                                    <th>Category</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                        </table>

                                        <table id="table-audio-book" class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" id="table-audio-book-select-all"></th>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Artist</th>
                                                    <th>Category</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Smart Collection -->
                                
                        <div class="border-top mt-2 pt-3 text-right">
                            <button type="button" class="btn btn-default mw-120 btn-submit">{{__('label.save')}}</button>
                            <a href="{{route('smart-collection.index')}}" class="btn btn-cancel mw-120 ml-2">{{__('label.cancel')}}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pagescript')
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- DataTables Select extension -->
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>

    <script>
        $(document).ready(function () {
            let ebookTable = null;
            let audioBookTable = null;

            // Initialize the e-book table dynamically
            function initializeEbookTable() {
                if (ebookTable === null) {
                    ebookTable = $('#table-e-book').DataTable({
                        processing: true,
                        serverSide: false,
                        select: {
                            style: 'multi',
                            selector: 'td:first-child input[type="checkbox"]',
                            items: 'row'
                        },
                        ajax: {
                            url: '{{ route("smart-collection.ebooks.get-all") }}',
                            type: 'POST',
                            data: function (d) {
                                d._token = '{{ csrf_token() }}';
                                d.artist_ids = $('#artist-select').val() || [];
                                d.category_ids = $('#category-select').val() || [];
                            },
                            dataSrc: 'data'
                        },
                        columns: [
                            {
                                data: null,
                                orderable: false,
                                searchable: false,
                                defaultContent: '',
                                render: function () {
                                    return '<input type="checkbox">';
                                }
                            },
                            { data: 'id' },
                            { data: 'name' },
                            { data: 'artist.name', defaultContent: '-' },
                            { data: 'category.name', defaultContent: '-' },
                            {
                                data: 'created_at',
                                render: function (data) {
                                    return data ? moment(data).format('MMM DD, YYYY') : '-';
                                }
                            }
                        ]
                    });
                }
            }

            // Initialize the audio book table dynamically
            function initializeAudioBookTable() {
                if (audioBookTable === null) {
                    audioBookTable = $('#table-audio-book').DataTable({
                        processing: true,
                        serverSide: false,
                        select: {
                            style: 'multi',
                            selector: 'td:first-child input[type="checkbox"]',
                            items: 'row'
                        },
                        ajax: {
                            url: '{{ route("smart-collection.audiobooks.get-all") }}',
                            type: 'POST',
                            data: function (d) {
                                d._token = '{{ csrf_token() }}';
                                d.artist_ids = $('#artist-select').val() || [];
                                d.category_ids = $('#category-select').val() || [];
                            },
                            dataSrc: 'data'
                        },
                        columns: [
                            {
                                data: null,
                                orderable: false,
                                searchable: false,
                                defaultContent: '',
                                render: function () {
                                    return '<input type="checkbox">';
                                }
                            },
                            { data: 'id' },
                            { data: 'name' },
                            { data: 'artist.name', defaultContent: '-' },
                            { data: 'category.name', defaultContent: '-' },
                            {
                                data: 'created_at',
                                render: function (data) {
                                    return data ? moment(data).format('MMM DD, YYYY') : '-';
                                }
                            }
                        ]
                    });
                }
            }

            function refreshTableByType() {
                const selectedType = $('#smart-collection-by-select').val();

                if (selectedType === 'audio_book') {
                    $('#table-audio-book').show();
                    $('#table-e-book').hide();

                    if (audioBookTable === null) {
                        initializeAudioBookTable();
                    } else {
                        audioBookTable.ajax.reload();
                    }

                    if (ebookTable !== null) {
                        ebookTable.clear().destroy();
                        ebookTable = null;
                    }
                } else if (selectedType === 'e_book') {
                    $('#table-e-book').show();
                    $('#table-audio-book').hide();

                    if (ebookTable === null) {
                        initializeEbookTable();
                    } else {
                        ebookTable.ajax.reload();
                    }

                    if (audioBookTable !== null) {
                        audioBookTable.clear().destroy();
                        audioBookTable = null;
                    }
                }
            }

            $('#smart-collection-by-select').on('change', function() {
                refreshTableByType();

                const selectedType = $('#smart-collection-by-select').val();
                // Deselect checkboxes based on selected type
                if (selectedType === 'audio_book') {
                    $('#table-e-book-select-all').prop('checked', false).trigger('change');
                } else if (selectedType === 'e_book') {
                    $('#table-audio-book-select-all').prop('checked', false).trigger('change');
                }
            });
            // Handle change event for the select dropdown
            $('#artist-select, #category-select').on('change', refreshTableByType);

            // Trigger the change event to load the initial table
            $('#smart-collection-by-select').trigger('change');

            // Handle select all checkboxes for all pages
            $('#table-e-book-select-all').on('click', function () {
                let rows = ebookTable.rows({ search: 'applied' }).nodes(); // Get rows in the current page
                let selectedRows = ebookTable.rows({ page: 'all' }).nodes(); // Get rows in all pages

                let isAllSelected = $(this).prop('checked'); // Check if the "Select All" checkbox is checked

                // Set the checkbox status for all rows on all pages
                $('input[type="checkbox"]', selectedRows).prop('checked', isAllSelected);

                // Select or deselect rows across pages
                if (isAllSelected) {
                    ebookTable.rows({ page: 'all' }).select();
                } else {
                    ebookTable.rows({ page: 'all' }).deselect();
                }
            });

            // let audioBookTable = $('#table-audio-book').DataTable({
            //     processing: true,
            //     serverSide: false,
            //     select: {
            //         style: 'multi',
            //         selector: 'td:first-child input[type="checkbox"]',  // Ensure checkbox selection
            //         items: 'row'  // Ensures row selection only
            //     },
            //     ajax: {
            //         url: '{{ route("smart-collection.audiobooks.get-all") }}',
            //         type: 'POST',
            //         data: function (d) {
            //             d._token = '{{ csrf_token() }}';
            //             d.artist_ids = $('#artist-select').val() || [];
            //             d.category_ids = $('#category-select').val() || [];
            //         },
            //         dataSrc: 'data'
            //     },
            //     columns: [
            //         {
            //             data: null,
            //             orderable: false,
            //             searchable: false,
            //             defaultContent: '',
            //             render: function () {
            //                 return '<input type="checkbox">';
            //             }
            //         },
            //         { data: 'id' },
            //         { data: 'name' },
            //         { data: 'artist.name', defaultContent: '-' },
            //         { data: 'category.name', defaultContent: '-' },
            //         {
            //             data: 'created_at',
            //             render: function (data) {
            //                 return data ? moment(data).format('MMM DD, YYYY') : '-';
            //             }
            //         }
            //     ]
            // });

            // Handle select all checkboxes for all pages
            $('#table-audio-book-select-all').on('click', function () {
                let rows = audioBookTable.rows({ search: 'applied' }).nodes(); // Get rows in the current page
                let selectedRows = audioBookTable.rows({ page: 'all' }).nodes(); // Get rows in all pages

                let isAllSelected = $(this).prop('checked'); // Check if the "Select All" checkbox is checked

                // Set the checkbox status for all rows on all pages
                $('input[type="checkbox"]', selectedRows).prop('checked', isAllSelected);

                // Select or deselect rows across pages
                if (isAllSelected) {
                    audioBookTable.rows({ page: 'all' }).select();
                } else {
                    audioBookTable.rows({ page: 'all' }).deselect();
                }
            });


            function createSmartCollectionAjax() {
                console.log("createSmartCollectionAjax");

                // Get the form data
                const formData = new FormData($('#smart-collection')[0]);

                // Get the selected type
                const selectedType = $('#smart-collection-by-select').val();
                let selectedRows = [];

                // Check the selected type and get rows accordingly
                if (selectedType === 'e_book' && ebookTable) {
                    selectedRows = ebookTable.rows({ selected: true }).data().toArray();
                    console.log('Selected E-Book Rows:', selectedRows);
                    
                } else if (selectedType === 'audio_book' && audioBookTable) {
                    selectedRows = audioBookTable.rows({ selected: true }).data().toArray();
                    console.log('Selected Audio Book Rows:', selectedRows);
                } else {
                    toastr.error('Table not initialized or no selection made.', 'failed');
                    return false;
                }

                if (!selectedRows.length) {
                    console.log("===================");
                    toastr.error('Please select at least one item before submitting.', 'No Selection');
                    return false; // optionally prevent further execution
                }

                console.log("formData : ", formData);



                // Map selected rows to item ids
                let selected_item_ids = selectedRows.map((item) => item.id);

                // Append selected item IDs as an array to the formData
                selected_item_ids.forEach((id, index) => {
                    formData.append('item_ids[]', id);  // Using [] to indicate it's an array
                });
                
                // Send the data via AJAX
                $.ajax({
                    url: '{{ route('smart-collection.create') }}',
                    type: 'POST',
                    data: formData,
                    processData: false, // Important for FormData
                    contentType: false, // Important for FormData
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        toastr.success(response.message, 'Success');
                        setTimeout(() => {
                            window.location.href = "{{ route('smart-collection.index') }}";    
                        }, 2000);
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON?.message || 'Something went wrong.';
                        toastr.error(message, 'Failed');
                    }
                });
            }

            $('#smart-collection').validate({
                rules: {
                    title: {
                        required: true,
                        maxlength: 255
                    },
                    price: {
                        required: true,
                        number: true,
                        min: 0
                    },
                    status: {
                        required: true
                    },
                    description: {
                        maxlength: 1000
                    }
                },
                messages: {
                    title: {
                        required: "Please enter a collection name",
                        maxlength: "Maximum 255 characters allowed"
                    },
                    price: {
                        required: "Please enter a price",
                        number: "Please enter a valid number",
                        min: "Price must be greater than or equal to 0"
                    },
                    status: {
                        required: "Please select status"
                    },
                    description: {
                        maxlength: "Maximum 1000 characters allowed"
                    }
                },
                submitHandler: function (form) {
                    createSmartCollectionAjax();
                }
            });

            $('.btn-submit').on('click', function () {
                $('#smart-collection').submit();
            });

            $('.btn-reset').on('click', function () {
                // Clear artist and category selects
                $('#artist-select').val(null).trigger('change');
                $('#category-select').val(null).trigger('change');

                // Refresh the table
                refreshTableByType();
            });

            // Initialize Select2 on both selects
            $('#artist-select, #category-select').select2({
                placeholder: 'Select...',
                allowClear: true,
                width: '100%'
            });

            // Fetch Artists
            $.ajax({
                url: '{{ route("smart-collection.artists.get-all") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.status && Array.isArray(response.data)) {
                        const $artistSelect = $('#artist-select');
                        response.data.forEach(artist => {
                            $artistSelect.append(new Option(artist.name, artist.id));
                        });
                    }
                },
                error: function (xhr, status, error) {
                    let errorMessage = 'Something went wrong while fetching artists.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    toastr.error(errorMessage, 'Failed');
                }
            });

            // Fetch Categories
            $.ajax({
                url: '{{ route("smart-collection.categories.get-all") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.status && Array.isArray(response.data)) {
                        const $categorySelect = $('#category-select');
                        response.data.forEach(category => {
                            $categorySelect.append(new Option(category.name, category.id));
                        });
                    }
                },
                error: function (xhr, status, error) {
                    let errorMessage = 'Something went wrong while fetching categories.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    toastr.error(errorMessage, 'Failed');
                }
            });
        });
    </script>
@endsection