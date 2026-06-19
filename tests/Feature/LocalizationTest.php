<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_cookie_locale_sets_arabic_language()
    {
        // Request with cookie 'locale' set to 'ar'
        $response = $this->withHeaders(['Cookie' => 'locale=ar'])
            ->get('/');

        $response->assertStatus(200);
        
        // Assert the page contains Arabic translation for 'Login' instead of English
        $response->assertSee('تسجيل الدخول');
        $response->assertDontSee('Sign In');
    }

    public function test_cookie_locale_defaults_to_english()
    {
        // Request without cookie
        $response = $this->get('/');

        $response->assertStatus(200);
        
        // Assert the page contains English for 'Login'
        $response->assertSee('Login');
        $response->assertDontSee('تسجيل الدخول');
    }

    public function test_cookie_lang_fallback_sets_arabic_language()
    {
        // Request with cookie 'lang' set to 'ar'
        $response = $this->withHeaders(['Cookie' => 'lang=ar'])
            ->get('/');

        $response->assertStatus(200);
        
        // Assert the page contains Arabic translation
        $response->assertSee('تسجيل الدخول');
    }

    public function test_query_parameter_lang_sets_arabic_and_queues_cookie()
    {
        $response = $this->get('/?lang=ar');

        $response->assertStatus(200);
        
        // Assert page is rendered in Arabic
        $response->assertSee('تسجيل الدخول');
        
        // Assert the locale cookie is set in response (not encrypted)
        $cookies = $response->headers->getCookies();
        $localeCookie = collect($cookies)->first(fn($c) => $c->getName() === 'locale');
        $langCookie = collect($cookies)->first(fn($c) => $c->getName() === 'lang');

        $this->assertNotNull($localeCookie);
        $this->assertEquals('ar', $localeCookie->getValue());
        $this->assertNotNull($langCookie);
        $this->assertEquals('ar', $langCookie->getValue());
    }

    public function test_query_parameter_lang_sets_english_and_queues_cookie()
    {
        // Initially set cookie to ar to prove that query parameter overrides it
        $response = $this->withHeaders(['Cookie' => 'locale=ar'])
            ->get('/?lang=en');

        $response->assertStatus(200);
        
        // Assert page is rendered in English
        $response->assertSee('Login');
        
        // Assert the locale cookie is set to 'en' in response (not encrypted)
        $cookies = $response->headers->getCookies();
        $localeCookie = collect($cookies)->first(fn($c) => $c->getName() === 'locale');
        $langCookie = collect($cookies)->first(fn($c) => $c->getName() === 'lang');

        $this->assertNotNull($localeCookie);
        $this->assertEquals('en', $localeCookie->getValue());
        $this->assertNotNull($langCookie);
        $this->assertEquals('en', $langCookie->getValue());
    }
}
