{{-- resources/views/livewire/fixture-list.blade.php --}}

<div wire:poll.60s>
    {{-- WHAT: wire:poll.60s is a Livewire directive.
         HOW: Every 60 seconds, Livewire automatically:
              1. Calls render() on the component
              2. Runs the fixtures() computed property (re-queries the database)
              3. Diffs the new HTML against the current DOM
              4. Updates ONLY the parts that changed
         WHY: Live scores update in the database via the import job.
              wire:poll makes the browser show those updates without a manual refresh.
              No JavaScript. No WebSockets. Just this one attribute.
         NOTE: Change 60s to 30s for faster updates during live matches. --}}

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="font-size:1.25rem; font-weight:600;">Today's Fixtures</h2>

        <select wire:model.live="leagueFilter"
                style="font-size:0.875rem; padding:4px 8px; border:1px solid #e5e7eb; border-radius:6px;">
            {{-- WHAT: wire:model.live="leagueFilter" is two-way data binding.
                 HOW: When the user selects a different option:
                      1. Livewire updates $this->leagueFilter on the server
                      2. The computed property re-runs with the new filter
                      3. The fixture list re-renders automatically
                      '.live' means it triggers on every change (not just on form submit).
                 WHY: Zero JavaScript. Select a league, list updates. Magic.
                 VANILLA EQUIVALENT: Would require onchange event + AJAX request + DOM update. --}}
            <option value="">All competitions</option>

            @foreach($this->leagues as $league)
                <option value="{{ $league->id }}">{{ $league->name }}</option>
            @endforeach
        </select>
    </div>

    @forelse($this->fixtures as $fixture)
        {{-- WHAT: @forelse is like @foreach but includes a built-in @empty fallback.
             WHY: Cleaner than @foreach + @if(count == 0) --}}

        <div style="display:grid; grid-template-columns:1fr auto 1fr;
                    align-items:center; gap:12px; padding:12px;
                    border:1px solid #f3f4f6; border-radius:8px; margin-bottom:8px;">

            <div style="text-align:right;">
                <p style="font-weight:500; margin:0;">{{ $fixture->homeTeam->name }}</p>
                {{-- HOW: $fixture->homeTeam accesses the eager-loaded BelongsTo relationship.
                     Because we used with(['homeTeam']) in the query, this doesn't
                     trigger another database query. The data is already loaded. --}}
            </div>

            <div style="text-align:center;">
                @if($fixture->status === 'NS')
                    {{-- Match hasn't started — show kick-off time --}}
                    <p style="font-size:0.75rem; color:#9ca3af; margin:0;">
                        {{ $fixture->match_at->format('H:i') }}
                        {{-- HOW: ->format('H:i') converts Carbon datetime to "15:30" format.
                             This works because of the 'datetime' cast in the Fixture model.
                             Without the cast, match_at would be a plain string
                             and ->format() wouldn't exist. --}}
                    </p>
                    <p style="font-size:0.75rem; font-weight:500; margin:0;">vs</p>
                @elseif(in_array($fixture->status, ['1H', '2H', 'HT', 'ET', 'P']))
                    {{-- Match is LIVE — show score with green indicator --}}
                    <p style="font-size:1.25rem; font-weight:700; margin:0; color:#059669;">
                        {{ $fixture->home_score }} - {{ $fixture->away_score }}
                    </p>
                    <p style="font-size:0.625rem; color:#059669; font-weight:600; margin:0;">
                        {{ $fixture->status }}
                    </p>
                @elseif($fixture->status === 'FT')
                    {{-- Match FINISHED — show final score --}}
                    <p style="font-size:1.25rem; font-weight:700; margin:0;">
                        {{ $fixture->home_score }} - {{ $fixture->away_score }}
                    </p>
                    <p style="font-size:0.625rem; color:#9ca3af; margin:0;">FT</p>
                @else
                    {{-- PST (postponed), ABD (abandoned), etc. --}}
                    <p style="font-size:0.75rem; color:#ef4444; margin:0;">{{ $fixture->status }}</p>
                @endif

                <p style="font-size:0.625rem; color:#9ca3af; margin:4px 0 0;">
                    {{ $fixture->competition->name ?? 'Unknown' }}
                    {{-- HOW: $fixture->competition uses the MorphTo relationship.
                         Laravel reads competition_type to know if it's a League or Cup,
                         then fetches the correct record using competition_id.
                         '?? Unknown' is a null fallback in case competition is null. --}}
                </p>
            </div>

            <div>
                <p style="font-weight:500; margin:0;">{{ $fixture->awayTeam->name }}</p>
            </div>
        </div>

    @empty
        <div style="text-align:center; padding:3rem; color:#9ca3af;">
            <p>No fixtures scheduled for today.</p>
        </div>
    @endforelse
</div>
