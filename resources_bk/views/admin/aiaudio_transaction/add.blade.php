@extends('layout.page-app')

@section('page_title','Add Audio Book Transaction')

@section('content')
    @include('layout.sidebar')

    <div class="right-content">
        @include('layout.header')

        <!-- Start: Body-Content -->
        <div class="body-content">
            <!-- mobile title -->
            <h1 class="page-title-sm">@yield('title')</h1>

            <div class="border-bottom row mb-3">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('aiaudio_transaction.index') }}">Audio Book Transaction</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Add Audio Book Transaction
                        </li>
                    </ol>
                </div>

            </div>

            <div class="card custom-border-card mt-3">
                <form enctype="multipart/form-data" id="search_user">
                    @csrf
                    <div class="form-row">
                        <div class="col-8">
                            <div class="form-group">
                                <input name="name" type="text" class="form-control" id="name" placeholder="Search User Name or Mobile" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-default mw-120 mr-3" onclick="search_user()">Search</button>
                            <a href="{{route('aiaudio_transaction.create')}}" class="btn btn-cancel mw-120">Clear</a>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (isset($user->id)) { ?>
                <div class="card custom-border-card mt-3">
                    <form enctype="multipart/form-data" id="add_transaction">
                        @csrf
                        <div class="form-row">
                            <div class="col-4">
                                <div class="form-group">
                                    <input name="user_id" type="hidden" class="form-control" readonly id="user_id" value="{{$user->id}}">
                                    <label> Full Name</label>
                                    <input name="full_name" type="text" class="form-control" readonly value="{{$user->full_name}}">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label> Mobile Number</label>
                                    <input name="mobile_number" type="text" class="form-control" readonly value="{{$user->mobile_number}}">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label> Email</label>
                                    <input name="email" type="text" class="form-control" readonly value="{{$user->email}}">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label> Select Pakage</label>
                                    <select name="aiaudio_id" class="form-control">
                                        <option value=""> Select Audio</option>
                                        @foreach($audio as $row)
                                        <option value="{{$row->id}}">{{$row->name}} &nbsp; - &nbsp; {{ $row->price}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-default mw-120" onclick="add_transaction()">Save</button>
                        </div>
                    </form>
                </div>
            <?php } else { ?>
                <div class="card custom-border-card mt-3">
                    <div class="col-12">
                        <h3>User List</h3>

                        <div id="user_list"></div>
                    </div>
                </div>
            <?php } ?>
        </div>

    </div>
@endsection

@section('pagescript')
    <script type="text/javascript">
        function add_transaction() {
            var formData = new FormData($("#add_transaction")[0]);
            $("#dvloader").show();
            $.ajax({
                type: 'POST',
                url: '{{ route("aiaudio_transaction.store") }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(resp) {
                    $("#dvloader").hide();
                    get_responce_message(resp, 'add_transaction', '{{ route("aiaudio_transaction.index") }}');
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $("#dvloader").hide();
                    toastr.error(errorThrown.msg, 'failed');
                }
            });
        }

        function search_user() {
            var formData = new FormData($("#search_user")[0]);
            
            $("#dvloader").show();
            $.ajax({
                type: 'POST',
                url: '{{ route("searchUser")}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(resp) {
                    $("#dvloader").hide();
                    $('#user_list').html(resp.result);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $("#dvloader").hide();
                    toastr.error(errorThrown.msg, 'failed');
                }
            });
        }
    </script>
@endsection