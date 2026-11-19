<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    public function test_it_searches_names_with_the_submitted_filters(): void
    {
        Http::preventStrayRequests();
        $description = str_repeat('A teljes névleírás minden részlete. ', 8);
        Http::fake([
            config('services.api.base_uri').'/search*' => Http::response([
                'data' => [[
                    'name' => 'Anna',
                    'description' => $description,
                    'sex' => 2,
                    'phonetic' => 'an-na',
                    'certificate' => true,
                    'group' => 1,
                ]],
                'nextPageToken' => 'cursor-2',
            ]),
        ]);

        $filters = [
            'q' => 'An',
            'sex' => '2',
            'certificate' => 'true',
            'group' => '4',
            'nameStart' => 'A',
            'nameEnd' => 'a',
            'descContains' => 'jelentés',
            'vhLow' => 'true',
            'vhMixed' => 'true',
            'vhHigh' => 'true',
            'containVowels' => 'a,e',
            'containVowelsOnly' => 'true',
            'containConsonants' => 'r,cs,ty',
            'containConsonantsOnly' => 'true',
            'syllableMin' => '1',
            'syllableMax' => '3',
            'namedayFrom' => '3-15',
            'namedayTo' => '8-20',
            'limit' => '10',
            'pageToken' => 'cursor-1',
            'fields' => 'name,description',
        ];

        $this->get(route('search.index', $filters))
            ->assertOk()
            ->assertViewIs('names.search')
            ->assertSee('Anna')
            ->assertSee($description)
            ->assertSee(route('names.show', ['name' => 'Anna']), false)
            ->assertSee('Női')
            ->assertSee('an-na')
            ->assertSee('Anyakönyvezhető')
            ->assertSee('Igen')
            ->assertSee('Eredet')
            ->assertSee('Nem tisztázott eredet')
            ->assertSee('Magyar eredet')
            ->assertSee('Rokonsági alapon átvett')
            ->assertSee('Magyar formában meghonosodott')
            ->assertSee('4-es csoport')
            ->assertSee('Idegen eredet')
            ->assertSee('200-as csoport')
            ->assertSee(e(route('search.index', [...$filters, 'pageToken' => 'cursor-2'])), false);

        Http::assertSent(function (Request $request) {
            return str_starts_with($request->url(), config('services.api.base_uri').'/search?')
                && $request->data() === [
                    'q' => 'An',
                    'sex' => '2',
                    'certificate' => 'true',
                    'group' => '4',
                    'nameStart' => 'A',
                    'nameEnd' => 'a',
                    'descContains' => 'jelentés',
                    'vhLow' => 'true',
                    'vhMixed' => 'true',
                    'vhHigh' => 'true',
                    'containVowels' => 'a,e',
                    'containVowelsOnly' => 'true',
                    'containConsonants' => 'r,cs,ty',
                    'containConsonantsOnly' => 'true',
                    'syllableMin' => '1',
                    'syllableMax' => '3',
                    'namedayFrom' => '3-15',
                    'namedayTo' => '8-20',
                    'limit' => '10',
                    'pageToken' => 'cursor-1',
                    'fields' => 'name,description',
                ];
        });
    }

    public function test_it_returns_an_empty_result_set_on_api_failure(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            config('services.api.base_uri').'/search*' => Http::response(['message' => 'error'], 500),
        ]);

        $this->get(route('search.index', ['sex' => '1']))
            ->assertOk()
            ->assertViewIs('names.search')
            ->assertViewHas('entities', [])
            ->assertViewHas('error', 'Hiba történt a keresés során.');

        Http::assertSentCount(1);
    }

    public function test_it_rejects_search_parameters_outside_the_api_contract(): void
    {
        Http::preventStrayRequests();
        Http::fake();

        $this->get(route('search.index', [
            'limit' => 21,
            'vhLow' => 'false',
        ]))
            ->assertInvalid(['limit', 'vhLow']);

        Http::assertNothingSent();
    }

    public function test_it_uses_the_default_limit_when_none_is_submitted(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            config('services.api.base_uri').'/search*' => Http::response(['data' => []]),
        ]);

        $this->get(route('search.index', ['sex' => '1']))->assertOk();

        Http::assertSent(fn (Request $request) => str_starts_with($request->url(), config('services.api.base_uri').'/search?')
            && $request['sex'] == 1
            && $request['limit'] == 20
            && $request['fields'] === '*'
            && ! array_key_exists('q', $request->data()));
    }

    public function test_it_does_not_call_the_api_without_any_search_filter(): void
    {
        Http::preventStrayRequests();
        Http::fake();

        $this->get(route('search.index'))
            ->assertOk()
            ->assertViewIs('names.search')
            ->assertViewHas('entities', [])
            ->assertSee('Adj meg legalább egy keresési feltételt.');

        Http::assertNothingSent();
    }
}
