@extends('layouts.app')

@section('title', 'Névlista | Magyar utónevek')

@section('content')
    <h1>Névlista</h1>
    <p>Böngéssz a nevek között, és válassz egy nevet a részletek megtekintéséhez.</p>
    <p><a href="{{ route('search.index') }}">Keresés szűrőkkel</a></p>

    @if (session('error'))
        <p role="alert">{{ session('error') }}</p>
    @endif

    @if (count($entities))
        <ul>
            @foreach ($entities as $entity)
                <li>
                    <h2>
                        <a href="{{ route('names.show', ['name' => $entity['name']]) }}">{{ $entity['name'] }}</a>
                    </h2>

                    <dl>
                        @isset($entity['sex'])
                            <dt>Nem</dt>
                            <dd>{{ match ((int) $entity['sex']) { 1 => 'Férfi', 2 => 'Női', 3 => 'Mindkettő', default => 'Ismeretlen' } }}</dd>
                        @endisset
                        @isset($entity['phonetic'])
                            <dt>Fonetikus alak</dt>
                            <dd>{{ $entity['phonetic'] }}</dd>
                        @endisset
                        @isset($entity['certificate'])
                            <dt>Anyakönyvezhető</dt>
                            <dd>{{ filter_var($entity['certificate'], FILTER_VALIDATE_BOOLEAN) ? 'Igen' : 'Nem' }}</dd>
                        @endisset
                        @isset($entity['group'])
                            <dt>Eredet</dt>
                            <dd>{{ config('names.groups.'.$entity['group'], $entity['group']) }}</dd>
                        @endisset
                    </dl>

                    @isset($entity['description'])
                        <p>{{ $entity['description'] }}</p>
                    @endisset
                </li>
            @endforeach
        </ul>
    @else
        <p>Nincs megjeleníthető név.</p>
    @endif

    <nav aria-label="Lapozás">
        @if ($previousUrl)
            <a href="{{ $previousUrl }}">Előző oldal</a>
        @endif
        @if ($nextUrl)
            <a href="{{ $nextUrl }}">Következő oldal</a>
        @endif
    </nav>
@endsection
