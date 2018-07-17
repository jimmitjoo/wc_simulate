@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">World Ranking</div>

                    <div class="card-body">
                        <table class="table">
                            <?php $i = 1; ?>
                            @foreach ($teams as $team)
                                <tr>
                                    <td>{{ $i }}.</td>
                                    <td>
                                        {{ $team->team->name }}
                                    </td>
                                    <td>
                                        {{ $team->ranking_points }}
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
