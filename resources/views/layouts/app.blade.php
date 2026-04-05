{{-- resources/views/layouts/app.blade.php --}}
{{-- WHAT: The master layout that wraps every page in the app.
     WHY: Instead of repeating <html>, <head>, nav etc. on every page,
          you define it once here. Every page extends this layout.
     VANILLA EQUIVALENT: Like a header.php + footer.php you include on every page,
          but more powerful — you can define multiple named sections. --}}

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BetWize — @yield('title', 'Football Stats')</title>
    {{-- WHAT: @yield('title', 'Football Stats') outputs the content of the 'title' section.
         HOW: Child pages define @section('title', 'PSL Standings') to set their own title.
              The second argument 'Football Stats' is the default if no section is defined. --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- WHAT: Loads your CSS and JavaScript through Vite (Laravel's asset bundler).
         WHY: Vite handles compilation (Tailwind, minification) in development and production.
         VANILLA EQUIVALENT: Like <link href="style.css"> and <script src="app.js">
              but with cache-busting and hot reload built in. --}}

    @livewireStyles
    {{-- WHAT: Outputs the CSS that Livewire needs to function.
         WHY: Livewire injects special CSS for its DOM diffing system.
              Must be in the <head> for correct rendering. --}}
</head>
<body style="background:#f9fafb; margin:0; font-family:system-ui, sans-serif;">

{{-- Navigation bar --}}
<nav style="background:white; border-bottom:1px solid #e5e7eb; padding:0 1.5rem;">
    <div style="max-width:1200px; margin:0 auto; display:flex; align-items:center;
                    justify-content:space-between; height:56px;">
        <a href="/" style="font-weight:700; font-size:1.125rem; color:#059669;
                               text-decoration:none;">BetWize</a>
        <div style="display:flex; gap:1.5rem;">
            <a href="/" style="font-size:0.875rem; color:#374151; text-decoration:none;">Today</a>
            <a href="/leagues" style="font-size:0.875rem; color:#374151; text-decoration:none;">Leagues</a>
        </div>
    </div>
</nav>

{{-- Main content area --}}
<main style="max-width:1200px; margin:0 auto; padding:1.5rem;">
    @yield('content')
    {{-- WHAT: Outputs whatever content the child page defines in @section('content').
         WHY: This is where the actual page content appears.
              Everything above and below is the shared layout. --}}
</main>

@livewireScripts
{{-- WHAT: Outputs the JavaScript that Livewire needs.
     WHY: This JS handles the AJAX communication between your Livewire components
          and the server. Without it, Livewire components are static — no reactivity.
     PLACEMENT: Must be at the bottom of <body>, AFTER your content. --}}
</body>
</html>
