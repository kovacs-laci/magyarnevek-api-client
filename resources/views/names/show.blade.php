@extends('layouts.app')

@section('title', ($entity['name'] ?? 'Név részletei').' | Magyar utónevek')

@section('content')
    <p><a href="{{ route('names.index') }}">Vissza a névlistához</a></p>
    <h1>{{ $entity['name'] ?? 'Név részletei' }}</h1>

    <section aria-labelledby="details-heading">
        <h2 id="details-heading">A névről</h2>
        <dl>
            @foreach ($entity as $field => $value)
                @if ($field !== 'name')
                    <dt>{{ match ($field) {
                        'sex' => 'Nem',
                        'description' => 'Leírás',
                        'phonetic' => 'Fonetikus alak',
                        'certificate' => 'Anyakönyvezhető',
                        'group' => 'Eredet',
                        default => ucfirst($field),
                    } }}</dt>
                    <dd>
                        @if ($field === 'sex')
                            {{ match ((int) $value) { 1 => 'Férfi', 2 => 'Női', 3 => 'Mindkettő', default => 'Ismeretlen' } }}
                        @elseif ($field === 'certificate')
                            {{ filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Igen, anyakönyvezhető' : 'Nem anyakönyvezhető' }}
                        @elseif ($field === 'group')
                            {{ config('names.groups.'.$value, $value) }}
                        @elseif (is_array($value))
                            <pre>{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                @endif
            @endforeach
        </dl>
    </section>

    <section aria-labelledby="namedays-heading">
        <h2 id="namedays-heading">Névnapok</h2>
        @if ($namedaysError)
            <p role="alert">{{ $namedaysError }}</p>
        @elseif ($namedays && count($namedays['dates'] ?? []))
            <ul>
                @foreach ($namedays['dates'] as $date)
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
            <p>Nincs elérhető névnapadat.</p>
        @endif
    </section>

    <section aria-labelledby="forms-heading">
        <h2 id="forms-heading">Történelmi névformák</h2>
        @if ($nameFormsError)
            <p role="alert">{{ $nameFormsError }}</p>
        @elseif ($nameForms && count($nameForms['forms'] ?? []))
            <dl>
                @foreach ($nameForms['forms'] as $type => $forms)
                    <dt>{{ match ($type) { 'latin' => 'Latin', 'cyrillic' => 'Cirill', 'greek' => 'Görög', default => ucfirst($type) } }}</dt>
                    <dd>{{ implode(', ', $forms) }}</dd>
                @endforeach
            </dl>
        @else
            <p>Nincsenek elérhető történelmi névformák.</p>
        @endif
    </section>

    <section aria-labelledby="stats-heading">
        <h2 id="stats-heading">Népszerűségi statisztikák</h2>
        @if ($statsError)
            <p role="alert">{{ $statsError }}</p>
        @elseif (count($stats))
            <table>
                <thead>
                    <tr>
                        <th scope="col">Év</th>
                        <th scope="col">Első névként</th>
                        <th scope="col">Második névként</th>
                        <th scope="col">Nem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stats as $stat)
                        <tr>
                            <td>{{ $stat['year'] ?? '' }}</td>
                            <td>{{ $stat['first'] ?? '' }}</td>
                            <td>{{ $stat['middle'] ?? '' }}</td>
                            <td>{{ match ((int) ($stat['sex'] ?? 0)) { 1 => 'Férfi', 2 => 'Női', 3 => 'Mindkettő', default => 'Ismeretlen' } }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nincs elérhető népszerűségi statisztika.</p>
        @endif
    </section>
@endsection
