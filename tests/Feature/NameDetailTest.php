<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NameDetailTest extends TestCase
{
    public function test_it_displays_name_details_namedays_forms_and_statistics(): void
    {
        Http::preventStrayRequests();
        Http::fakeSequence()
            ->push([
                'name' => 'István',
                'sex' => 1,
                'description' => 'A név teljes, részletes leírása.',
                'phonetic' => 'ist-va:n',
                'certificate' => true,
                'group' => 1,
            ])
            ->push([
                'name' => 'István',
                'dates' => [[
                    'hu' => 'aug. 20.',
                    'en' => 'Aug 20',
                    'iso' => '08-20',
                    'main' => true,
                ]],
            ])
            ->push([
                'baseName' => 'István',
                'forms' => [
                    'latin' => ['Stephanus'],
                    'cyrillic' => ['Стефан'],
                    'greek' => ['Στέφανος'],
                ],
            ])
            ->push([
                'data' => [[
                    'name' => 'István',
                    'year' => '2023',
                    'first' => 312,
                    'middle' => 88,
                    'sex' => 1,
                ]],
            ]);

        $this->get(route('names.show', ['name' => 'István']))
            ->assertOk()
            ->assertViewIs('names.show')
            ->assertSee('A név teljes, részletes leírása.')
            ->assertSee('Férfi')
            ->assertSee('ist-va:n')
            ->assertSee('Anyakönyvezhető')
            ->assertSee('Igen')
            ->assertSee('Magyar eredet')
            ->assertSee('aug. 20.')
            ->assertSee('Stephanus')
            ->assertSee('Стефан')
            ->assertSee('Στέφανος')
            ->assertSee('2023')
            ->assertSee('312')
            ->assertSee('88');

        Http::assertSentCount(4);
        Http::assertSent(fn (Request $request) => str_starts_with(
            $request->url(),
            config('services.api.base_uri').'/names/Istv%C3%A1n?'
        ) && $request['fields'] === '*');
        Http::assertSent(fn (Request $request) => $request->url() === config('services.api.base_uri').'/namedays/Istv%C3%A1n');
        Http::assertSent(fn (Request $request) => $request->url() === config('services.api.base_uri').'/name-forms/Istv%C3%A1n');
        Http::assertSent(fn (Request $request) => str_starts_with($request->url(), config('services.api.base_uri').'/stats?')
            && $request['name'] === 'István');
    }

    public function test_it_shows_related_api_errors_without_hiding_name_details(): void
    {
        Http::preventStrayRequests();
        Http::fakeSequence()
            ->push(['name' => 'Anna', 'sex' => 2])
            ->push(['error' => 'not found'], 404)
            ->push(['error' => 'not found'], 404)
            ->push(['error' => 'not found'], 404);

        $this->get(route('names.show', ['name' => 'Anna']))
            ->assertOk()
            ->assertSee('Anna')
            ->assertSee('A névnapadatokat nem sikerült betölteni.')
            ->assertSee('A névformákat nem sikerült betölteni.')
            ->assertSee('A népszerűségi statisztikákat nem sikerült betölteni.');
    }
}
