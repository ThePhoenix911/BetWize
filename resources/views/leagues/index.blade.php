{{-- resources/views/leagues/index.blade.php --}}

@extends('layouts.app')

@section('title', 'All Leagues')

@section('content')
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 2rem;">All Leagues</h1>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            @foreach($leagues as $league)
                <a href="{{ route('leagues.show', $league) }}"
                   style="display: block; padding: 1.5rem; border: 1px solid #e5e7eb; border-radius: 12px;
                          text-decoration: none; color: inherit; transition: all 0.2s;"
                   onmouseover="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)'"
                   onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">

                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        @if($league->logo_url)
                            <img src="{{ $league->logo_url }}"
                                 alt="{{ $league->name }}"
                                 style="width: 48px; height: 48px; object-fit: contain;">
                        @endif
                        <div>
                            <h3 style="font-size: 1.125rem; font-weight: 600; margin: 0;">
                                {{ $league->name }}
                            </h3>
                            <p style="font-size: 0.875rem; color: #6b7280; margin: 0.25rem 0 0;">
                                {{ $league->country }}
                            </p>
                        </div>
                    </div>

                    @if($league->short_code)
                        <div style="display: inline-block; padding: 0.25rem 0.75rem;
                                    background: #f3f4f6; border-radius: 6px; font-size: 0.75rem;
                                    font-weight: 500; color: #374151;">
                            {{ $league->short_code }}
                        </div>
                    @endif
                </a>
            @endforeach
        </div>

        @if($leagues->isEmpty())
            <div style="text-align: center; padding: 3rem; color: #9ca3af;">
                <p>No leagues found.</p>
            </div>
        @endif
    </div>
@endsection
