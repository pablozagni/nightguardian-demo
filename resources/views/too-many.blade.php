@extends('layouts.site', ['title' => __('site.try.too_many').' — NightGuardian', 'noindex' => true])

@section('body')
<div class="center-box">
    <p class="brand" style="margin-bottom:1rem">@include('partials.logo') NightGuardian</p>
    <h1 style="font-size:1.5rem;margin-bottom:.6rem">429</h1>
    <p class="muted">{{ __('site.try.too_many') }}</p>
    <a class="btn btn-ghost btn-sm" href="{{ route('try') }}">{{ __('site.try.back') }}</a>
</div>
@endsection
