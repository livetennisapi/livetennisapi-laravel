{{-- Server-rendered live tennis scores. All values escaped via {{ }}. --}}
<div class="lta-live-scores" data-status="{{ $status }}">
    @if ($upgradeRequired)
        <p class="lta-scores-upgrade">Completed-match listings need the BASIC tier ($9.99/mo) or any History plan &mdash; <a href="https://livetennisapi.com/subscribe/upgrade">upgrade at livetennisapi.com/subscribe/upgrade</a>.</p>
    @elseif ($error)
        <p class="lta-scores-error">Live scores are unavailable right now.</p>
    @elseif (count($matches) === 0)
        <p class="lta-scores-empty">No {{ $status }} matches right now.</p>
    @else
        <ul class="lta-matches">
            @foreach ($matches as $match)
                <li class="lta-match" @if ($match->id !== null) data-match-id="{{ $match->id }}" @endif>
                    @if ($match->tournament)
                        <span class="lta-tournament">{{ $match->tournament }}</span>
                    @endif

                    <span class="lta-players">
                        <span class="lta-p1">{{ $match->p1()?->name ?? 'TBD' }}</span>
                        <span class="lta-vs">vs</span>
                        <span class="lta-p2">{{ $match->p2()?->name ?? 'TBD' }}</span>
                    </span>

                    @if ($match->score !== null)
                        @php($sets = $match->score->sets ?? [])
                        @php($points = $match->score->points ?? [])
                        <span class="lta-score">
                            @if (count($sets) >= 2)
                                <span class="lta-sets">{{ $sets[0] }}&ndash;{{ $sets[1] }}</span>
                            @endif
                            @if (count($points) >= 2)
                                <span class="lta-points">{{ $points[0] }}&ndash;{{ $points[1] }}</span>
                            @endif
                            @if ($match->score->server !== null)
                                <span class="lta-serving" data-server="{{ $match->score->server }}">&#9679;</span>
                            @endif
                        </span>
                    @else
                        <span class="lta-scheduled">{{ $match->scheduled_time ?? 'upcoming' }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
