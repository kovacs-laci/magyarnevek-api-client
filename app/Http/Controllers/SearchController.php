<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string'],
            'sex' => ['nullable', 'in:1,2,3'],
            'certificate' => ['nullable', 'in:true,false'],
            'group' => ['nullable', 'integer', 'min:0'],
            'nameStart' => ['nullable', 'string'],
            'nameEnd' => ['nullable', 'string'],
            'descContains' => ['nullable', 'string'],
            'vhLow' => ['nullable', 'in:true'],
            'vhMixed' => ['nullable', 'in:true'],
            'vhHigh' => ['nullable', 'in:true'],
            'containVowels' => ['nullable', 'string'],
            'containVowelsOnly' => ['nullable', 'in:true'],
            'containConsonants' => ['nullable', 'string'],
            'containConsonantsOnly' => ['nullable', 'in:true'],
            'syllableMin' => ['nullable', 'integer', 'min:0'],
            'syllableMax' => ['nullable', 'integer', 'min:0'],
            'namedayFrom' => ['nullable', 'string'],
            'namedayTo' => ['nullable', 'string'],
            'limit' => ['nullable', 'integer', 'between:1,20'],
            'pageToken' => ['nullable', 'string'],
            'fields' => ['nullable', 'string'],
        ]);

        $queryParams = array_filter(
            $validated,
            static fn ($value) => $value !== null && $value !== ''
        );
        $queryParams['limit'] ??= 20;
        $queryParams['fields'] ??= '*';

        $filterParams = array_diff_key($queryParams, array_flip(['limit', 'pageToken', 'fields']));

        if ($filterParams === []) {
            return view('names.search', [
                'entities' => [],
                'nextPageToken' => null,
                'error' => null,
                'info' => 'Adj meg legalább egy keresési feltételt.',
            ]);
        }

        $response = Http::api()->get('/search', $queryParams);

        if ($response->failed()) {
            return view('names.search', [
                'entities' => [],
                'nextPageToken' => null,
                'error' => 'Hiba történt a keresés során.',
                'info' => null,
            ]);
        }

        return view('names.search', [
            'entities' => $response->json('data', []),
            'nextPageToken' => $response->json('nextPageToken'),
            'error' => null,
            'info' => null,
        ]);
    }
}
