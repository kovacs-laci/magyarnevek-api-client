@extends('layouts.app')

@section('title', 'Névkeresés | Magyar utónevek')

@section('content')
    <h1>Névkeresés</h1>
    <p>Keress nevet, vagy szűkítsd a találatokat a részletes keresési feltételekkel.</p>

    @if ($error)
        <p role="alert">{{ $error }}</p>
    @endif

    @if ($info)
        <p role="status">{{ $info }}</p>
    @endif

    @if ($errors->any())
        <ul role="alert">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="GET" action="{{ route('search.index') }}">
        <input type="hidden" name="fields" value="*">

        <p>
            <label for="q">Név</label>
            <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Például: Anna">
        </p>

        <p>
            <label for="sex">Nem</label>
            <select id="sex" name="sex">
                <option value="">Mindegy</option>
                <option value="1" @selected(request('sex') == 1)>Férfi</option>
                <option value="2" @selected(request('sex') == 2)>Női</option>
                <option value="3" @selected(request('sex') == 3)>Mindkettő</option>
            </select>
        </p>

        <p>
            <label for="certificate">Anyakönyvezhető</label>
            <select id="certificate" name="certificate">
                <option value="">Mindegy</option>
                <option value="true" @selected(request('certificate') === 'true')>Igen</option>
                <option value="false" @selected(request('certificate') === 'false')>Nem</option>
            </select>
        </p>

        <details>
            <summary>Részletes keresés</summary>

            <p>
                <label for="group">Eredet</label>
                <select id="group" name="group">
                    <option value="">Mindegy</option>
                    @foreach (config('names.groups') as $groupId => $groupLabel)
                        <option value="{{ $groupId }}" @selected((string) request('group') === (string) $groupId)>
                            {{ $groupLabel }}
                        </option>
                    @endforeach
                </select>
            </p>

            <p>
                <label for="nameStart">A név ezzel kezdődik</label>
                <input id="nameStart" type="text" name="nameStart" value="{{ request('nameStart') }}">
            </p>

            <p>
                <label for="nameEnd">A név ezzel végződik</label>
                <input id="nameEnd" type="text" name="nameEnd" value="{{ request('nameEnd') }}">
            </p>

            <p>
                <label for="descContains">Leírás tartalmazza</label>
                <input id="descContains" type="text" name="descContains" value="{{ request('descContains') }}">
            </p>

            <p>
                <label for="syllableMin">Minimális szótag</label>
                <input id="syllableMin" type="number" min="0" name="syllableMin" value="{{ request('syllableMin') }}">
            </p>

            <p>
                <label for="syllableMax">Maximális szótag</label>
                <input id="syllableMax" type="number" min="0" name="syllableMax" value="{{ request('syllableMax') }}">
            </p>

            <p>
                <label for="namedayFrom">Névnap ettől (hó-nap)</label>
                <input id="namedayFrom" type="text" name="namedayFrom" value="{{ request('namedayFrom') }}" placeholder="3-15">
            </p>

            <p>
                <label for="namedayTo">Névnap eddig (hó-nap)</label>
                <input id="namedayTo" type="text" name="namedayTo" value="{{ request('namedayTo') }}" placeholder="8-20">
            </p>

            <p>
                <label for="containVowels">Tartalmazandó magánhangzók</label>
                <input id="containVowels" type="text" name="containVowels" value="{{ request('containVowels') }}" placeholder="a,e">
            </p>

            <p>
                <label for="containConsonants">Tartalmazandó mássalhangzók</label>
                <input id="containConsonants" type="text" name="containConsonants" value="{{ request('containConsonants') }}" placeholder="r,cs,ty">
            </p>

            <p>
                <label for="limit">Találatok száma (max. 20)</label>
                <input id="limit" type="number" min="1" max="20" name="limit" value="{{ request('limit', 20) }}">
            </p>

            <fieldset>
                <legend>Magánhangzó-harmónia</legend>
                @foreach (['vhLow' => 'Mély', 'vhMixed' => 'Vegyes', 'vhHigh' => 'Magas'] as $field => $label)
                    <label>
                        <input type="checkbox" name="{{ $field }}" value="true" @checked(request($field) === 'true')>
                        {{ $label }}
                    </label>
                @endforeach
            </fieldset>

            <fieldset>
                <legend>Karakterkorlátozások</legend>
                <label>
                    <input type="checkbox" name="containVowelsOnly" value="true" @checked(request('containVowelsOnly') === 'true')>
                    Csak a felsorolt magánhangzók
                </label>
                <label>
                    <input type="checkbox" name="containConsonantsOnly" value="true" @checked(request('containConsonantsOnly') === 'true')>
                    Csak a felsorolt mássalhangzók
                </label>
            </fieldset>
        </details>

        <p>
            <button type="submit">Keresés</button>
            <a href="{{ route('search.index') }}">Szűrők törlése</a>
        </p>
    </form>

    <section aria-labelledby="results-heading">
        <h2 id="results-heading">Keresési eredmények</h2>

        @if (count($entities))
            <p>{{ count($entities) }} találat</p>
            <ul>
                @foreach ($entities as $entity)
                    <li>
                        <h3>
                            <a href="{{ route('names.show', ['name' => $entity['name']]) }}">{{ $entity['name'] }}</a>
                        </h3>

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
        @elseif (! $error && ! $info)
            <p>Nincs a feltételeknek megfelelő találat.</p>
        @endif

        @if ($nextPageToken)
            <a href="{{ route('search.index', array_merge(request()->query(), ['pageToken' => $nextPageToken])) }}">
                Következő oldal
            </a>
        @endif
    </section>
@endsection
