@extends('layouts.app')

@section('title', 'Névnapok: '.$entity['name'].' | Magyar utónevek')

@section('content')
    <p><a href="{{ route('names.show', ['name' => $entity['name']]) }}">Vissza a név adatlapjára</a></p>
    <h1>Névnapok: {{ $entity['name'] }}</h1>

    @if (count($entity['dates'] ?? []))
        <ul>
            @foreach ($entity['dates'] as $date)
                <li>
                    {{ $date['hu'] ?? $date['iso'] ?? '' }}
                    @if (! empty($date['en']))
                        ({{ $date['en'] }})
                    @endif
                    @if (! empty($date['main']))
                        — fő névnap
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <p>Ehhez a névhez nem található névnapadat.</p>
    @endif
@endsection
