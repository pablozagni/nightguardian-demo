@extends('layouts.site', ['title' => __('site.try.unlock_title').' — NightGuardian', 'noindex' => true])

@section('body')
<div class="center-box">
    <p class="brand" style="margin-bottom:1rem">@include('partials.logo') NightGuardian</p>
    <h1 style="font-size:1.5rem;margin-bottom:.4rem">{{ __('site.try.unlock_title') }}</h1>
    <p class="muted">{{ __('site.try.unlock_text') }}</p>
    <form method="post" action="{{ route('unlock') }}" class="tcard">
        @csrf
        <label for="key">{{ __('site.try.unlock_key') }}</label>
        <input id="key" name="key" type="password" autofocus autocomplete="off" required style="width:100%;margin:.3rem 0 1rem">
        @isset($wrong)<p style="color:var(--danger)">{{ __('site.try.unlock_wrong') }}</p>@endisset
        <button class="btn btn-primary">{{ __('site.try.unlock_button') }}</button>
    </form>
</div>
@endsection
