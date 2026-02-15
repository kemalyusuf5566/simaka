@extends('layouts.adminlte')

@section('title','Dashboard Guru')
@section('page_title','Dashboard')

@section('content')
<div class="row">
  @foreach($cards as $card)
    <div class="col-lg-3 col-6">
      <div class="small-box {{ $card['color'] }}">
        <div class="inner">
          <h3>{{ $card['count'] }}</h3>
          <p>{{ $card['title'] }}</p>
        </div>
        <div class="icon">
          <i class="{{ $card['icon'] }}"></i>
        </div>
        <a href="{{ $card['route'] }}" class="small-box-footer">
          Lihat detail <i class="fas fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
  @endforeach
</div>
@endsection
