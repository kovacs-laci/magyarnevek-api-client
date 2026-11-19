@extends('layouts.app')

@section('title', 'Történelmi névformák: '.$entity['baseName'].' | Magyar utónevek')

@section('content')
    <p><a href="{{ route('names.show', ['name' => $entity['baseName']]) }}">Vissza a név adatlapjára</a></p>
    <h1>Történelmi névformák: {{ $entity['baseName'] }}</h1>

    @if (count($entity['forms'] ?? []))
        <dl>
            @foreach ($entity['forms'] as $type => $forms)
                <dt>{{ match ($type) { 'latin' => 'Latin', 'cyrillic' => 'Cirill', 'greek' => 'Görög', default => ucfirst($type) } }}</dt>
                <dd>{{ implode(', ', $forms) }}</dd>
            @endforeach
        </dl>
    @else
        <p>Ehhez a névhez nem található történelmi névforma.</p>
    @endif
@endsection
