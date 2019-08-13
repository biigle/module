@extends('app')
@section('title', 'Inspiring quotes')
@section('content')
<div class="container">
   <div class="col-sm-8 col-sm-offset-2 col-lg-6 col-lg-offset-3">
      <blockquote>
         {{ Illuminate\Foundation\Inspiring::quote() }}
      </blockquote>
   </div>
</div>
@endsection
