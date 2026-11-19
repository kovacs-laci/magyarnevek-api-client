<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NamePaginationTest extends TestCase
{
    public function test_it_can_navigate_back_and_forth_through_name_pages(): void
    {
        Http::preventStrayRequests();
        Http::fakeSequence()
            ->push(['data' => [[
                'name' => 'First',
                'description' => 'A név teljes leírása.',
                'sex' => 1,
                'phonetic' => 'first',
                'certificate' => true,
                'group' => 1,
            ]], 'nextPageToken' => 'token-2'])
            ->push(['data' => [['name' => 'Second']], 'nextPageToken' => 'token-3'])
            ->push(['data' => [['name' => 'Third']], 'nextPageToken' => 'token-4'])
            ->push(['data' => [['name' => 'Second']], 'nextPageToken' => 'token-3']);

        $firstPage = $this->get(route('names.index'));
        $firstPage
            ->assertOk()
            ->assertDontSee('Previous')
            ->assertSee(route('names.show', ['name' => 'First']), false)
            ->assertSee('A név teljes leírása.')
            ->assertSee('Férfi')
            ->assertSee('first')
            ->assertSee('Igen')
            ->assertSee('Magyar eredet')
            ->assertSee(e(route('names.index', ['fields' => '*', 'pageToken' => 'token-2'])), false);

        $secondPage = $this->get(route('names.index', ['pageToken' => 'token-2']));
        $secondPage
            ->assertOk()
            ->assertSee('href="'.e(route('names.index', ['fields' => '*'])).'"', false)
            ->assertSee(e(route('names.index', ['pageToken' => 'token-3', 'fields' => '*'])), false);

        $thirdPage = $this->get(route('names.index', ['pageToken' => 'token-3']));
        $thirdPage
            ->assertOk()
            ->assertSee(e(route('names.index', ['pageToken' => 'token-2', 'fields' => '*'])), false);

        $backOnSecondPage = $this->get(route('names.index', ['pageToken' => 'token-2']));
        $backOnSecondPage
            ->assertOk()
            ->assertSee('href="'.e(route('names.index', ['fields' => '*'])).'"', false)
            ->assertSee(e(route('names.index', ['pageToken' => 'token-3', 'fields' => '*'])), false);

        Http::assertSent(fn (Request $request) => str_starts_with($request->url(), config('services.api.base_uri').'/names?')
            && ! array_key_exists('pageToken', $request->data())
            && $request['fields'] === '*');
    }
}
