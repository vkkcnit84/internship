@extends('layouts.main')

@section('content')
<section style="padding: 40px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center" style="color: white;">
                    <h2 style="margin-bottom: 20px; font-weight: bold;">
                        <i class="fas fa-briefcase" style="margin-right: 12px;"></i>
                        Looking for Paid Internships?
                    </h2>
                    <p style="font-size: 18px; margin-bottom: 25px; opacity: 0.95;">
                        Register for internship programs and gain real-world experience with leading companies. Earn competitive stipends while building your career.
                    </p>
                    <a href="{{ route('internship.register') }}" class="btn btn-light" style="font-weight: bold; padding: 12px 35px; font-size: 16px;">
                        <i class="fas fa-arrow-right" style="margin-right: 8px;"></i>
                        Register Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="special_cource padding_top">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5">
                <div class="section_tittle text-center">
                    <p>Courses</p>
                    <h2>Newest Courses</h2>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($newestCourses as $course)
                <div class="col-sm-6 col-lg-4">
                    <div class="single_special_cource">
                        <img src="{{ optional($course->photo)->getUrl() ?? asset('img/no_image.png') }}" class="special_img" alt="">
                        <div class="special_cource_text">
                            @foreach($course->disciplines as $discipline)
                                <a href="{{ route('courses.index') }}?discipline={{ $discipline->id }}" class="btn_4 mr-1 mb-1">{{ $discipline->name }}</a>
                            @endforeach
                            <h4>{{ $course->getPrice() }}</h4>
                            <a href="{{ route('courses.show', $course->id) }}"><h3>{{ $course->name }}</h3></a>
                            <p>{{ Str::limit($course->description, 100) }}</p>
                            @if($course->institution)
                                <div class="author_info">
                                    <div class="author_img">
                                        <img src="{{ optional($course->institution->logo)->thumbnail ?? asset('img/no_image.png') }}" alt="" class="rounded-circle">
                                        <div class="author_info_text">
                                            <p>Institution</p>
                                            <h5><a href="{{ route('courses.index') }}?institution={{ $course->institution->id }}">{{ $course->institution->name }}</a></h5>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="blog_part section_padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5">
                <div class="section_tittle text-center">
                    <p>Institutions</p>
                    <h2>Random Institutions</h2>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($randomInstitutions as $institution)
                <div class="col-sm-6 col-lg-4 col-xl-4">
                    <div class="single-home-blog">
                        <div class="card">
                            <img src="{{ optional($institution->logo)->url ?? asset('img/no_image.png') }}" class="card-img-top" alt="{{ $institution->name }}">
                            <div class="card-body">
                                <a href="{{ route('courses.index') }}?institution={{ $institution->id }}">
                                    <h5 class="card-title">{{ $institution->name }}</h5>
                                </a>
                                <p>{{ Str::limit($institution->description, 100) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection