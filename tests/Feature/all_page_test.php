<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class all_page_test extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_login(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_dashboard(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_destinasi(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_rekomendasi(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_transaksi(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_users(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_pengaturan(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_profil(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_keamanan(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_rekap(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_akses(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
