{{-- resources/views/livewire/league-table.blade.php --}}

{{-- WHAT: The HTML template for the LeagueTable Livewire component.
     HOW: This is a Blade template — PHP's templating engine.
          {{ }} = echo (output) a value, auto-escaped for security
          {!! !!} = echo raw HTML (unescaped — use carefully)
          @foreach = PHP foreach loop
          @if / @else = PHP if statement
          $this->standings accesses the computed property from the component class.
     WHY: Blade separates HTML structure from PHP logic.
          The component class handles data. This file handles display only. --}}

<div>
    {{-- WHAT: The outer <div> is REQUIRED by Livewire.
         WHY: Every Livewire component must have ONE root element.
              Livewire tracks this element to know what to update in the DOM. --}}

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="font-size:1.25rem; font-weight:600;">
            {{ $league->name }}
            {{-- WHAT: Outputs the league name from the public $league property.
                 HOW: {{ }} auto-escapes HTML. If name contained <script> tags,
                      they'd be displayed as text, not executed. Safe by default. --}}
        </h2>
        <span style="font-size:0.875rem; color:#6b7280;">{{ $season }} season</span>
    </div>

    <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
        <thead>
        <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
            <th style="padding:8px 12px; text-align:left; font-weight:500; color:#6b7280;">#</th>
            <th style="padding:8px 12px; text-align:left; font-weight:500; color:#6b7280;">Club</th>
            <th style="padding:8px 12px; text-align:center; font-weight:500; color:#6b7280;">P</th>
            <th style="padding:8px 12px; text-align:center; font-weight:500; color:#6b7280;">W</th>
            <th style="padding:8px 12px; text-align:center; font-weight:500; color:#6b7280;">D</th>
            <th style="padding:8px 12px; text-align:center; font-weight:500; color:#6b7280;">L</th>
            <th style="padding:8px 12px; text-align:center; font-weight:500; color:#6b7280;">GF</th>
            <th style="padding:8px 12px; text-align:center; font-weight:500; color:#6b7280;">GA</th>
            <th style="padding:8px 12px; text-align:center; font-weight:500; color:#6b7280;">GD</th>
            <th style="padding:8px 12px; text-align:center; font-weight:500; color:#6b7280; font-weight:700;">Pts</th>
        </tr>
        </thead>
        <tbody>
        @foreach($this->standings as $row)
            {{-- WHAT: Loops through each row in the standings Collection.
                 HOW: @foreach is Blade's version of PHP's foreach.
                      $this->standings calls the computed property from the class.
                      $row is one club's stats array from StandingsCalculator. --}}

            <tr style="border-bottom:1px solid #f3f4f6;">
                <td style="padding:10px 12px; color:#9ca3af;">
                    {{ $loop->iteration }}
                    {{-- WHAT: $loop is a special Blade variable available inside @foreach.
                         $loop->iteration = current position (1, 2, 3...)
                         $loop->first = true on first iteration
                         $loop->last  = true on last iteration
                         WHY: Perfect for showing league position numbers. --}}
                </td>
                <td style="padding:10px 12px;">
                    @if($row['club']->logo_url)
                        <img src="{{ $row['club']->logo_url }}"
                             alt="{{ $row['club']->name }}"
                             style="width:20px; height:20px; object-fit:contain; display:inline-block; margin-right:8px; vertical-align:middle;">
                    @endif
                    <span style="font-weight:500;">{{ $row['club']->name }}</span>
                </td>
                <td style="padding:10px 12px; text-align:center;">{{ $row['played'] }}</td>
                <td style="padding:10px 12px; text-align:center;">{{ $row['won'] }}</td>
                <td style="padding:10px 12px; text-align:center;">{{ $row['drawn'] }}</td>
                <td style="padding:10px 12px; text-align:center;">{{ $row['lost'] }}</td>
                <td style="padding:10px 12px; text-align:center;">{{ $row['goals_for'] }}</td>
                <td style="padding:10px 12px; text-align:center;">{{ $row['goals_against'] }}</td>
                <td style="padding:10px 12px; text-align:center;">
                    {{ $row['goal_difference'] > 0 ? '+' : '' }}{{ $row['goal_difference'] }}
                    {{-- WHAT: Ternary operator in Blade.
                         WHY: Positive GD shows "+5". Negative shows "-3". Zero shows "0". --}}
                </td>
                <td style="padding:10px 12px; text-align:center; font-weight:700;">
                    {{ $row['points'] }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if($this->standings->isEmpty())
        <p style="text-align:center; color:#9ca3af; padding:2rem;">
            No completed fixtures yet for this season.
        </p>
    @endif
    {{-- WHAT: Shows a message if no standings data exists yet.
         WHY: Prevents a blank table that confuses users.
              isEmpty() is a Collection method that returns true if the collection has no items. --}}
</div>
