<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Bestdecoders\ShopifyLaravelEnhanced\Models\SupportExpectation;
use Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider;

class SupportExpectationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Load the service provider
        $this->app->register(ShopifyEnhancedServiceProvider::class);

        // Load specific migrations for our package
        $this->loadLaravelMigrations(['--database' => 'testing']);

        // Load our package migrations
        $this->artisan('migrate', [
            '--database' => 'testing',
            '--path' => 'packages/Bestdecoders/shopify-laravel-enhanced/database/migrations'
        ]);
    }

    protected function createUser(): User
    {
        return User::factory()->create([
            'name' => 'test-shop.myshopify.com',
            'email' => 'shop@test.com',
        ]);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = $this->createUser();

        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'Test expectation',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(User::class, $expectation->user);
        $this->assertEquals($user->id, $expectation->user->id);
    }

    /** @test */
    public function it_casts_expectation_and_replies_to_arrays()
    {
        $user = $this->createUser();

        $expectationData = [
            'message' => 'Test message',
            'created_at' => now()->toISOString(),
            'user_email' => $user->email,
        ];

        $repliesData = [
            [
                'message' => 'Test reply',
                'admin_email' => 'admin@test.com',
                'created_at' => now()->toISOString(),
            ]
        ];

        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => $expectationData,
            'replies' => $repliesData,
            'status' => 'replied',
        ]);

        $this->assertIsArray($expectation->expectation);
        $this->assertIsArray($expectation->replies);
        $this->assertEquals($expectationData, $expectation->expectation);
        $this->assertEquals($repliesData, $expectation->replies);
    }

    /** @test */
    public function it_can_get_latest_reply()
    {
        $user = $this->createUser();

        $replies = [
            [
                'message' => 'First reply',
                'admin_email' => 'admin@test.com',
                'created_at' => now()->subHour()->toISOString(),
            ],
            [
                'message' => 'Latest reply',
                'admin_email' => 'admin@test.com',
                'created_at' => now()->toISOString(),
            ]
        ];

        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'Test expectation',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'replies' => $replies,
            'status' => 'replied',
        ]);

        $latestReply = $expectation->latest_reply;

        $this->assertNotNull($latestReply);
        $this->assertEquals('Latest reply', $latestReply['message']);
        $this->assertEquals($replies[1], $latestReply);
    }

    /** @test */
    public function latest_reply_returns_null_when_no_replies()
    {
        $user = $this->createUser();

        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'Test expectation',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'status' => 'pending',
        ]);

        $this->assertNull($expectation->latest_reply);
    }

    /** @test */
    public function it_can_add_a_reply()
    {
        $user = $this->createUser();

        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'Test expectation',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'status' => 'pending',
        ]);

        $replyMessage = 'This is a test reply';
        $adminEmail = 'admin@bestdecoders.com';

        $expectation->addReply($replyMessage, $adminEmail);

        $expectation->refresh();

        $this->assertEquals('replied', $expectation->status);
        $this->assertNotNull($expectation->last_reply_at);
        $this->assertCount(1, $expectation->replies);

        $reply = $expectation->replies[0];
        $this->assertEquals($replyMessage, $reply['message']);
        $this->assertEquals($adminEmail, $reply['admin_email']);
        $this->assertArrayHasKey('created_at', $reply);
    }

    /** @test */
    public function it_can_add_multiple_replies()
    {
        $user = $this->createUser();

        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'Test expectation',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'status' => 'pending',
        ]);

        $expectation->addReply('First reply', 'admin1@test.com');
        $expectation->addReply('Second reply', 'admin2@test.com');

        $expectation->refresh();

        $this->assertCount(2, $expectation->replies);
        $this->assertEquals('First reply', $expectation->replies[0]['message']);
        $this->assertEquals('Second reply', $expectation->replies[1]['message']);
        $this->assertEquals('admin1@test.com', $expectation->replies[0]['admin_email']);
        $this->assertEquals('admin2@test.com', $expectation->replies[1]['admin_email']);
    }

    /** @test */
    public function with_status_scope_filters_by_status()
    {
        $user = $this->createUser();

        SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => ['message' => 'Pending request'],
            'status' => 'pending',
        ]);

        SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => ['message' => 'Replied request'],
            'status' => 'replied',
        ]);

        SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => ['message' => 'Resolved request'],
            'status' => 'resolved',
        ]);

        $pendingRequests = SupportExpectation::withStatus('pending')->get();
        $repliedRequests = SupportExpectation::withStatus('replied')->get();

        $this->assertCount(1, $pendingRequests);
        $this->assertCount(1, $repliedRequests);
        $this->assertEquals('Pending request', $pendingRequests->first()->expectation['message']);
        $this->assertEquals('Replied request', $repliedRequests->first()->expectation['message']);
    }

    /** @test */
    public function recent_scope_filters_by_date()
    {
        $user = $this->createUser();

        // Create expectations at different dates
        SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => ['message' => 'Recent request'],
            'status' => 'pending',
            'created_at' => now()->subDays(5),
        ]);

        SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => ['message' => 'Old request'],
            'status' => 'pending',
            'created_at' => now()->subDays(40),
        ]);

        $recentRequests = SupportExpectation::recent(30)->get();
        $veryRecentRequests = SupportExpectation::recent(3)->get();

        $this->assertCount(1, $recentRequests);
        $this->assertCount(0, $veryRecentRequests);
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $expectation = new SupportExpectation();

        $expectedFillable = [
            'user_id',
            'expectation',
            'replies',
            'status',
            'last_reply_at',
        ];

        $this->assertEquals($expectedFillable, $expectation->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $expectation = new SupportExpectation();

        $expectedCasts = [
            'expectation' => 'array',
            'replies' => 'array',
            'last_reply_at' => 'datetime',
        ];

        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertEquals($cast, $expectation->getCasts()[$attribute]);
        }
    }
}