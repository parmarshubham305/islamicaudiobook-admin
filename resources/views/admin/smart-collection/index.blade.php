
@extends('layout.page-app')

@section('page_title',  __('label.smart_collection'))

@section('content')
  @include('layout.sidebar')
  
  <div class="right-content">
    @include('layout.header')

    <div class="body-content">
    <!-- mobile title -->
    <h1 class="page-title-sm">@yield('title')</h1>

    <div class="border-bottom row mb-3">
      <div class="col-sm-12">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{__('label.dashboard')}}</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            {{ __('label.smart_collection') }}
          </li>
        </ol>
      </div>
    </div>

    <div class="row">

      <div class="col-12 col-sm-6 col-md-4 col-xl-3">
        <a href="{{ route('smart-collection.create') }}" class="add-video-btn">
          <img src="{{ asset('assets/imgs/add.png') }}" alt="" class="icon" />
          Add New {{ __('label.smart_collection') }}
        </a>
      </div>

      

      @foreach ($data as $key => $value)
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
          <div class="card video-card">
            <div class="position-relative">
              <img class="card-img-top" src="{{$value->image}}" alt="">
            </div>
            
            <div class="card-body">
              <h5 class="card-title mr-5">{{$value->title}} {!! $value->status == 0 ? '<span class="text-danger">(In-Active)</span>' : '' !!}</h5>
              <div class="card-details">
                </div>
                <div class="dropdown dropright">
                    <a href="#" class="btn head-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="{{ asset('assets/imgs/dot.png') }}" class="dot-icon" />
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        <!-- <a class="dropdown-item" href="{{ route('eBookDetail', [$value->id])}}">
                            <img src="{{ asset('assets/imgs/view.png') }}" class="dot-icon" />
                              Details
                        </a> -->
                        <a class="dropdown-item" href="{{ route('smart-collection.edit', [$value->id])}}">
                            <img src="{{ asset('assets/imgs/edit.png') }}" class="dot-icon" />
                              Edit
                        </a>
                        <a class="dropdown-item" href="{{ route('smart-collection.deleteSmartCollection', $value->id)}}" onclick="return confirm('Are You Sure Delete This {{ __('label.smart_collection') }}?')">
                            <img src="{{ asset('assets/imgs/trash.png') }}" class="dot-icon" />
                          Delete
                        </a>
                    </div>
                </div>
            </div>
          </div>
        </div>
      @endforeach

    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center">
      <div> Showing {{ $data->firstItem() }} to {{ $data->lastItem() }} of total {{$data->total()}} entries </div>
      <div class="pb-5"> {{ $data->links('pagination::bootstrap-4') }} </div>
    </div>

  </div>
</div>
@endsection

@section('pagescript')
<script>
    
</script>
@endsection