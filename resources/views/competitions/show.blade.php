@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1>{{ $competition->name }}</h1>
            </div>
            <nav class="col">

            </nav>
        </div>

        @if (count($competition->playoffs))
            <?php $currentStep = null; ?>
            @foreach ($competition->playoffs as $playoff)
            @if ($currentStep != $playoff->step)
            @if ($currentStep != null) </ul> @endif
        <?php $currentStep = $playoff->step; ?>
        <h4>{{ playoffName($playoff->step) }}</h4>
        <ul>
            @endif

            @foreach ($playoff->po_games as $game)
                <li>{{ $game->starting_time }}: {{ $game->hometeam->name }}
                    @if ($game->status == 'ended' && $game->hometeam_score_fulltime == $game->awayteam_score_fulltime)
                        ({{ $game->hometeam_penalties_score }})
                    @endif

                    @if ($game->status == 'ended') {{ $game->hometeam_score_fulltime }} @endif
                    -
                    @if ($game->status == 'ended') {{ $game->awayteam_score_fulltime }} @endif

                    @if ($game->status == 'ended' && $game->hometeam_score_fulltime == $game->awayteam_score_fulltime)
                        ({{ $game->awayteam_penalties_score }})
                    @endif
                    {{ $game->awayteam->name }}</li>
            @endforeach
            @endforeach
        </ul>
        @endif


        @foreach ($competition->leagues as $league)
            <div class="row">
                <div class="col">
                    <h3>{{ $league->name }}</h3>

                    <table class="table">
                        <thead>
                        <td></td>
                        <td>MP</td>
                        <td>W</td>
                        <td>D</td>
                        <td>L</td>
                        <td>GF</td>
                        <td>GA</td>
                        <td>+/-</td>
                        <td>PTS</td>
                        </thead>
                        @foreach ($league->teams as $team)
                            <tr>
                                <td>{{ $team->team->name }}</td>
                                <td>{{ ($team->won + $team->tied + $team->lost) }}</td>
                                <td>{{ $team->won }}</td>
                                <td>{{ $team->tied }}</td>
                                <td>{{ $team->lost }}</td>
                                <td>{{ $team->scored }}</td>
                                <td>{{ $team->conceded }}</td>
                                <td>{{ ($team->scored - $team->conceded) }}</td>
                                <td><strong>{{ $team->points }}</strong></td>
                            </tr>
                        @endforeach
                    </table>

                    <ul style="display: none">
                        <?php $round = 0; ?>
                        @foreach ($league->games as $game)
                            @if ($round != 0 && $round != $game->round)
                                <?php $round = $game->round; ?>
                    </ul>
                    <ul>
                        @endif

                        <li>{{ $game->starting_time }}: {{$game->hometeam->name }}
                            @if ($game->status == 'ended')
                                {{$game->hometeam_score_fulltime }}
                            @endif
                            -
                            @if ($game->status == 'ended')
                                {{ $game->awayteam_score_fulltime }}
                            @endif
                            {{ $game->awayteam->name }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
@endsection