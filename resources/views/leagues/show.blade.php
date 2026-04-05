{{-- resources/views/leagues/show.blade.php --}}

@extends('layouts.app')

@section('title', $league->name)

@section('content')
    <div style="max-width: 1200px; margin: 0 auto;">
        {{-- League Header --}}
        <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem;
                    padding: 1.5rem; background: white; border-radius: 12px; border: 1px solid #e5e7eb;">
            @if($league->logo_url)
                <img src="{{ $league->logo_url }}"
                     alt="{{ $league->name }}"
                     style="width: 80px; height: 80px; object-fit: contain;">
            @endif
            <div>
                <h1 style="font-size: 2rem; font-weight: 700; margin: 0;">{{ $league->name }}</h1>
                <p style="font-size: 1rem; color: #6b7280; margin: 0.5rem 0 0;">{{ $league->country }}</p>
                @if($league->short_code)
                    <span style="display: inline-block; margin-top: 0.5rem; padding: 0.25rem 0.75rem;
                                 background: #f3f4f6; border-radius: 6px; font-size: 0.875rem;
                                 font-weight: 500; color: #374151;">
                        {{ $league->short_code }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Fixtures and Standings Grid --}}
        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 1.5rem; align-items: start;">

            {{-- Left column: Fixtures --}}
            <div style="background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #e5e7eb;">
                <h2 style="font-size: 1.5rem; font-weight: 600; margin: 0 0 1.5rem;">Fixtures</h2>

                <div style="text-align: center; padding: 3rem; color: #9ca3af;">
                    <p>League-specific fixture list coming soon...</p>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem;">
                        (Will filter fixtures to show only this league's matches)
                    </p>
                </div>
            </div>

            {{-- Right column: League Table --}}
            <div style="background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #e5e7eb;">
                <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0 0 1.5rem;">Standings</h2>

                <div style="text-align: center; padding: 2rem; color: #9ca3af;">
                    <p>League table coming soon...</p>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem;">
                        (Will show club standings for this league)
                    </p>
                </div>
            </div>
        </div>

        {{-- Back button --}}
        <div style="margin-top: 2rem;">
            <a href="/leagues"
               style="display: inline-block; padding: 0.5rem 1rem; background: #f3f4f6;
                      border-radius: 8px; text-decoration: none; color: #374151; font-weight: 500;">
                ← Back to All Leagues
            </a>
        </div>
    </div>
@endsection
