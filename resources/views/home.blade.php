@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">World Cups</div>

                    <div class="card-body">
                        <ol>
                            <?php $page = isset($_GET['page']) ? $_GET['page'] : 1;
                            $i=($page * $worldCups->perPage()) - ($worldCups->perPage() - 1); ?>
                            @foreach ($worldCups as $worldCup)
                                <li>
                                    <a href="/competitions/{{ $worldCup->id }}">{{ $worldCup->name }} #{{ $i }}</a>

                                    <ul>
                                        @foreach($worldCup->qualifications as $qualification)
                                            <li>
                                                <a href="/competitions/{{ $qualification->id }}">{{ $qualification->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                                <?php $i++; ?>
                            @endforeach
                        </ol>

                        {{ $worldCups->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
