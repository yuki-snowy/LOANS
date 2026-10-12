<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_displayed(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee("LOAN'S");
        $response->assertSee('「理想の家」も「現実の返済」も');
        $response->assertSee('まるごと見える化します');
        $response->assertSee('候補物件へ');
        $response->assertSee('ローンを試算');
        $response->assertSee(route('home'), false);
        $response->assertSee(route('properties.index'), false);
        $response->assertSee(route('simulations.create'), false);
    }

    public function test_app_header_logo_links_to_home(): void
    {
        $response = $this->get(route('properties.index'));

        $response->assertOk();
        $response->assertSee('aria-label="LOAN\'S トップへ"', false);
        $response->assertSee('href="'.route('home').'"', false);
    }
}
