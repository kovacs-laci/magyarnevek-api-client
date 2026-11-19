<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NameController extends Controller
{
    public function index(Request $request)
    {
        $queryParams = $request->only([
            'firstLetter',
            'sex',
            'certificate',
            'group',
            'search',
            'limit',
            'pageToken',
            'fields',
        ]);

        if (! $request->filled('fields')) {
            $queryParams['fields'] = '*';
        }

        $response = Http::api()->get('/names', $queryParams);

        if ($response->failed()) {
            return redirect()
                ->route('names.index')
                ->with('error', 'Hiba történt az adatok lekérdezése során.');
        }

        $nextPageToken = $response->json('nextPageToken') ?? null;
        $currentPageToken = $request->filled('pageToken')
            ? $request->query('pageToken')
            : null;

        if ($currentPageToken === null) {
            session()->forget('prevPageToken');
        }
        $history = session()->get('prevPageToken', []);

        if ($currentPageToken !== null && ! in_array($currentPageToken, $history, true)) {
            $history[] = $currentPageToken;
            session()->put('prevPageToken', $history);
        }

        $currentPageIndex = array_search($currentPageToken, $history, true);
        $prevPageToken = $currentPageIndex !== false && $currentPageIndex > 0
            ? $history[$currentPageIndex - 1]
            : null;

        $previousUrl = null;

        $previousPageParams = $queryParams;

        if ($prevPageToken === null) {
            unset($previousPageParams['pageToken']);
        } else {
            $previousPageParams['pageToken'] = $prevPageToken;
        }

        if ($currentPageToken !== null) {
            $previousUrl = route('names.index', $previousPageParams);
        }

        $nextPageParams = $queryParams;
        $nextPageParams['pageToken'] = $nextPageToken;
        $nextUrl = $nextPageToken
            ? route('names.index', $nextPageParams)
            : null;

        return view('names.index', [
            'entities' => $response->json('data'),
            'previousUrl' => $previousUrl,
            'nextUrl' => $nextUrl,
        ]);
    }

    public function show(string $name)
    {
        $queryParams = ['fields' => '*'];
        $encodedName = rawurlencode($name);
        $response = Http::api()->get("/names/$encodedName", $queryParams);

        if ($response->failed()) {
            return redirect()
                ->route('names.index')
                ->with('error', 'Hiba történt az adatok lekérdezése során.');
        }

        $namedaysResponse = Http::api()->get("/namedays/$encodedName");
        $nameFormsResponse = Http::api()->get("/name-forms/$encodedName");
        $statsResponse = Http::api()->get('/stats', ['name' => $name]);

        return view('names.show', [
            'entity' => $response->json(),
            'namedays' => $namedaysResponse->successful() ? $namedaysResponse->json() : null,
            'namedaysError' => $namedaysResponse->failed() ? 'A névnapadatokat nem sikerült betölteni.' : null,
            'nameForms' => $nameFormsResponse->successful() ? $nameFormsResponse->json() : null,
            'nameFormsError' => $nameFormsResponse->failed() ? 'A névformákat nem sikerült betölteni.' : null,
            'stats' => $statsResponse->successful() ? $statsResponse->json('data', []) : [],
            'statsError' => $statsResponse->failed() ? 'A népszerűségi statisztikákat nem sikerült betölteni.' : null,
        ]);
    }

    public function namedays(string $name, Request $request)
    {
        $response = Http::api()->get('/namedays/'.rawurlencode($name));

        if ($response->failed()) {
            return redirect()
                ->route('names.index')
                ->with('error', 'Hiba történt az adatok lekérdezése során.');
        }

        return view('names.namedays', [
            'entity' => $response->json(),
        ]);
    }

    public function nameforms(string $name, Request $request)
    {
        $response = Http::api()->get('/name-forms/'.rawurlencode($name));

        if ($response->failed()) {
            return redirect()
                ->route('names.index')
                ->with('error', 'Hiba történt az adatok lekérdezése során.');
        }

        return view('names.nameforms', [
            'entity' => $response->json(),
        ]);
    }

    private function formatEntity(array $entity): array
    {
        $formatted = [];

        foreach ($entity as $field => $value) {
            $formatted[ucfirst($field)] = match ($field) {
                'sex' => match ($value) {
                    1 => 'Férfi',
                    2 => 'Nő',
                    3 => 'Egyéb',
                    default => $value,
                },

                'certificate' => $value ? 'Igen' : 'Nem',

                default => $value,
            };
        }

        return $formatted;
    }
}
