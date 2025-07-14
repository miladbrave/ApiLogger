<?php

namespace Tests\Feature;

use App\Models\ApiLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiLoggerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Enable API logging for tests
        config(['api-logger.enabled' => true]);
    }

    /** @test */
    public function it_logs_api_requests_and_responses()
    {
        $response = $this->postJson('/api/test', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('api_logs', [
            'method' => 'POST',
            'url' => url('/api/test'),
            'response_status' => 200,
        ]);

        $log = ApiLog::first();
        $this->assertNotNull($log);
        $this->assertEquals('POST', $log->method);
        $this->assertEquals(url('/api/test'), $log->url);
        $this->assertEquals(200, $log->response_status);
        $this->assertGreaterThan(0, $log->response_time_ms);
        $this->assertNotNull($log->correlation_id);
    }

    /** @test */
    public function it_redacts_sensitive_headers()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer secret-token',
            'X-API-Key' => 'api-key-123',
            'Content-Type' => 'application/json',
        ])->postJson('/api/test', []);

        $response->assertStatus(200);

        $log = ApiLog::first();
        $headers = $log->request_headers;

        $this->assertEquals('[REDACTED]', $headers['Authorization']);
        $this->assertEquals('[REDACTED]', $headers['X-API-Key']);
        $this->assertEquals('application/json', $headers['Content-Type']);
    }

    /** @test */
    public function it_redacts_sensitive_body_content()
    {
        $response = $this->postJson('/api/test', [
            'username' => 'john_doe',
            'password' => 'secret_password',
            'token' => 'access_token_123',
        ]);

        $response->assertStatus(200);

        $log = ApiLog::first();
        $this->assertEquals('[Sensitive content redacted]', $log->request_body);
    }

    /** @test */
    public function it_tracks_authenticated_users()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/test', []);

        $response->assertStatus(200);

        $log = ApiLog::first();
        $this->assertEquals($user->id, $log->user_id);
    }

    /** @test */
    public function it_generates_correlation_ids()
    {
        $response = $this->postJson('/api/test', []);
        $response->assertStatus(200);

        $log = ApiLog::first();
        $this->assertNotNull($log->correlation_id);
        $this->assertIsString($log->correlation_id);
    }

    /** @test */
    public function it_respects_custom_correlation_ids()
    {
        $customId = 'custom-correlation-123';

        $response = $this->withHeaders([
            'X-Correlation-ID' => $customId,
        ])->postJson('/api/test', []);

        $response->assertStatus(200);

        $log = ApiLog::first();
        $this->assertEquals($customId, $log->correlation_id);
    }

    /** @test */
    public function it_captures_request_metadata()
    {
        $response = $this->postJson('/api/test?param=value', [
            'data' => 'test',
        ]);

        $response->assertStatus(200);

        $log = ApiLog::first();
        $metadata = $log->metadata;

        $this->assertArrayHasKey('query_parameters', $metadata);
        $this->assertEquals(['param' => 'value'], $metadata['query_parameters']);
        $this->assertArrayHasKey('content_type', $metadata);
        $this->assertEquals('application/json', $metadata['content_type']);
    }

    /** @test */
    public function it_limits_body_size()
    {
        config(['api-logger.max_body_size' => 100]);

        $largeBody = str_repeat('a', 200);
        $response = $this->postJson('/api/test', [
            'data' => $largeBody,
        ]);

        $response->assertStatus(200);

        $log = ApiLog::first();
        $this->assertEquals('[Content too large to log]', $log->request_body);
    }

    /** @test */
    public function it_excludes_specified_paths()
    {
        config(['api-logger.excluded_paths' => ['/health']]);

        $response = $this->get('/health');
        $response->assertStatus(404); // Assuming no health route exists

        $this->assertDatabaseCount('api_logs', 0);
    }

    /** @test */
    public function it_excludes_specified_methods()
    {
        config(['api-logger.excluded_methods' => ['OPTIONS']]);

        $response = $this->call('OPTIONS', '/api/test');
        $response->assertStatus(405); // Method not allowed

        $this->assertDatabaseCount('api_logs', 0);
    }

    /** @test */
    public function it_excludes_specified_status_codes()
    {
        config(['api-logger.excluded_status_codes' => [404]]);

        $response = $this->get('/non-existent-route');
        $response->assertStatus(404);

        $this->assertDatabaseCount('api_logs', 0);
    }

    /** @test */
    public function it_can_be_disabled_via_config()
    {
        config(['api-logger.enabled' => false]);

        $response = $this->postJson('/api/test', []);
        $response->assertStatus(200);

        $this->assertDatabaseCount('api_logs', 0);
    }

    /** @test */
    public function it_provides_web_interface()
    {
        // Create some test logs
        ApiLog::factory()->count(5)->create();

        $response = $this->get('/api-logs');
        $response->assertStatus(200);
        $response->assertSee('API Logs');
    }

    /** @test */
    public function it_provides_api_endpoints()
    {
        // Create some test logs
        ApiLog::factory()->count(3)->create();

        $response = $this->getJson('/api-logs');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'logs' => [
                'data' => [
                    '*' => [
                        'id',
                        'method',
                        'url',
                        'response_status',
                        'response_time_ms',
                        'created_at',
                    ]
                ]
            ],
            'statistics'
        ]);
    }

    /** @test */
    public function it_supports_filtering()
    {
        // Create logs with different methods
        ApiLog::factory()->post()->create();
        ApiLog::factory()->get()->create();
        ApiLog::factory()->post()->create();

        $response = $this->getJson('/api-logs?method=POST');
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(2, $data['logs']['data']);
        
        foreach ($data['logs']['data'] as $log) {
            $this->assertEquals('POST', $log['method']);
        }
    }

    /** @test */
    public function it_provides_statistics()
    {
        // Create logs with different status codes
        ApiLog::factory()->successful()->count(3)->create();
        ApiLog::factory()->error()->count(2)->create();

        $response = $this->getJson('/api-logs/statistics?period=24h');
        $response->assertStatus(200);

        $stats = $response->json();
        $this->assertEquals(5, $stats['total_requests']);
        $this->assertEquals(3, $stats['successful_requests']);
        $this->assertEquals(2, $stats['error_requests']);
    }

    /** @test */
    public function it_supports_export()
    {
        ApiLog::factory()->count(3)->create();

        $response = $this->get('/api-logs/export?format=json');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');

        $data = $response->json();
        $this->assertCount(3, $data);
    }

    /** @test */
    public function it_supports_csv_export()
    {
        ApiLog::factory()->count(2)->create();

        $response = $this->get('/api-logs/export?format=csv');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition');
    }
} 