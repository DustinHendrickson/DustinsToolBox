@extends('includes.default')

@section('content')

    <div class="alert alert-success" role="alert">
        <strong>Banner Tool</strong> - This tool will help you identify the current banners in rotation, make changes to them and even help push to content-cache.
    </div>

    @foreach ($banners as $banner)


            <div class="card spur-card">
                <div class="card-header bg-success text-white">
                    <div class="spur-card-icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <div class="spur-card-title">
                        {{$banner["banner_name"]}}
                    </div>

                    <div class="spur-card-menu">
                        <div class="dropdown show">
                            <a class="spur-card-menu-link"
                               href="#"
                               role="button"
                               data-toggle="dropdown"
                               aria-haspopup="true"
                               aria-expanded="false">
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                                <a class="dropdown-item" href="#"> <i class="fas fa-cloud-upload-alt"></i> - Enable</a>
                                <a class="dropdown-item" href="#"> <i class="fas fa-cloud-download-alt"></i> - Disable</a>
                                <a class="dropdown-item" href="#"> <i class="fas fa-edit"></i> - Edit</a>
                                <a class="dropdown-item" href="#"> <i class="fas fa-exclamation-triangle"></i> - Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body ">
                    @if ($banner["banner_active"] == true)
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-cloud-upload-alt"></i> Banner is Active
                        </div>
                    @else
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-cloud-download-alt"></i> Banner is Disabled
                        </div>
                    @endif
                    <a href="{{$banner["banner_link"]}}">
                        <img src="{{$banner["banner_image"] }}">
                    </a>
                </div>

                <div class="spur-demo-badges">
                    <b>ROTATIONS:</b>
                @foreach ($banner["banner_rotations"] as $rotation)
                    <span class="badge badge-primary">{{$rotation}}</span>
                @endforeach

                </div>
            </div>

    @endforeach

@stop