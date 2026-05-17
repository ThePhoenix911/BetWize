{{-- resources/views/home.blade.php --}}

@extends('layouts.app')
{{-- WHAT: This page uses the layouts/app.blade.php master layout.
     HOW: @extends tells Blade: "I am a child of this layout.
          Fill in my @section content where the layout has @yield." --}}

@section('title', 'Today\'s Matches')
{{-- WHAT: Sets the page title shown in the browser tab.
     HOW: This fills in the @yield('title') in the layout. --}}

@section('content')
    {{-- WHAT: Everything between @section('content') and @endsection
         fills in the @yield('content') slot in the layout. --}}

    <div style="display:grid; grid-template-columns:1fr 350px; gap:1.5rem; align-items:start;">

        {{-- Left column: Today's fixtures --}}
        <div>
            <livewire:fixture-list />
            {{-- WHAT: Renders the FixtureList Livewire component here.
                 HOW: <livewire:component-name /> is Blade's syntax for embedding components.
                      Laravel converts kebab-case (fixture-list) to PascalCase (FixtureList)
                      and finds the class at app/Livewire/FixtureList.php automatically.
                 WHY: One line. The whole interactive fixture list with polling appears here. --}}
        </div>

        {{-- Right column: League table --}}
        <div>
            <livewire:league-table />
        </div>

    </div>

@endsection
