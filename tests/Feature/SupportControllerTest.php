<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Models\User;
use Bestdecoders\ShopifyLaravelEnhanced\Models\SupportExpectation;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\SupportExpectationMail;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\SupportReplyMail;
use Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider;

class SupportControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Load the service provider
        $this->app->register(ShopifyEnhancedServiceProvider::class);

        // Run the migrations
        $this->artisan('migrate', ['--database' => 'testing']);
    }

    protected function createUser(): User
    {
        return User::factory()->create([
            'name' => 'test-shop.myshopify.com',
            'email' => 'shop@test.com',
        ]);
    }

    /** @test */
    public function support_page_renders_successfully()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/support');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_submit_support_expectation()
    {
        Mail::fake();
        $user = $this->createUser();

        $expectationText = 'I need help with configuring the table of contents position.';

        $response = $this->actingAs($user)
            ->postJson('/support/submit/expectation', [
                'expectation' => $expectationText
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Your support request has been submitted successfully!',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'expectation_id'
            ]);

        // Assert database record was created
        $this->assertDatabaseHas('support_expectations', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $expectation = SupportExpectation::where('user_id', $user->id)->first();
        $this->assertEquals($expectationText, $expectation->expectation['message']);
        $this->assertEquals($user->email, $expectation->expectation['user_email']);

        // Assert admin email was sent
        Mail::assertSent(SupportExpectationMail::class, function ($mail) use ($expectation) {
            return $mail->expectation->id === $expectation->id;
        });
    }

    /** @test */
    public function user_cannot_submit_empty_expectation()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->postJson('/support/submit/expectation', [
                'expectation' => ''
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('expectation');

        $this->assertDatabaseCount('support_expectations', 0);
    }

    /** @test */
    public function user_can_view_their_support_request_replies()
    {
        $user = $this->createUser();

        // Create a support expectation with replies
        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'I need help with TOC styling.',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'replies' => [
                [
                    'message' => 'Thanks for your request! We can help you style the TOC.',
                    'admin_email' => 'admin@bestdecoders.com',
                    'created_at' => now()->toISOString(),
                ]
            ],
            'status' => 'replied',
            'last_reply_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->getJson("/support/replies/{$expectation->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'replied',
            ])
            ->assertJsonStructure([
                'success',
                'expectation',
                'replies',
                'status',
                'latest_reply',
                'created_at',
                'last_reply_at'
            ]);
    }

    /** @test */
    public function user_cannot_view_other_users_support_requests()
    {
        $user1 = $this->createUser();
        $user2 = User::factory()->create([
            'name' => 'other-shop.myshopify.com',
            'email' => 'other@test.com',
        ]);

        $expectation = SupportExpectation::create([
            'user_id' => $user2->id,
            'expectation' => [
                'message' => 'Private request',
                'created_at' => now()->toISOString(),
                'user_email' => $user2->email,
            ],
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user1)
            ->getJson("/support/replies/{$expectation->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function admin_can_view_all_expectations()
    {
        $user = $this->createUser();

        SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'Test expectation',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'status' => 'pending',
        ]);

        // Mock admin authentication (you may need to adjust this based on your auth setup)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer test-admin-token',
        ])->getJson('/admin/api/support/expectations');

        // Note: This test might need adjustment based on your actual admin authentication
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'expectations' => [
                    'data' => [
                        '*' => [
                            'id',
                            'expectation',
                            'status',
                            'created_at',
                            'user'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function admin_can_reply_to_expectation()
    {
        Mail::fake();
        $user = $this->createUser();

        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'I need help with TOC configuration.',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'status' => 'pending',
        ]);

        $replyMessage = 'Thanks for your request! We can help you configure the TOC position.';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer test-admin-token',
        ])->postJson("/admin/api/support/expectations/{$expectation->id}/reply", [
            'message' => $replyMessage,
            'admin_email' => 'support@bestdecoders.com'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Reply added successfully and user has been notified via email!',
            ]);

        // Assert the reply was added to the database
        $expectation->refresh();
        $this->assertEquals('replied', $expectation->status);
        $this->assertNotNull($expectation->last_reply_at);
        $this->assertCount(1, $expectation->replies);
        $this->assertEquals($replyMessage, $expectation->replies[0]['message']);
        $this->assertEquals('support@bestdecoders.com', $expectation->replies[0]['admin_email']);

        // Assert user notification email was sent
        Mail::assertSent(SupportReplyMail::class, function ($mail) use ($expectation) {
            return $mail->expectation->id === $expectation->id;
        });
    }

    /** @test */
    public function support_expectation_model_latest_reply_accessor_works()
    {
        $user = $this->createUser();

        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'Test request',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'replies' => [
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
            ],
            'status' => 'replied',
        ]);

        $this->assertEquals('Latest reply', $expectation->latest_reply['message']);
    }

    /** @test */
    public function support_expectation_add_reply_method_works()
    {
        $user = $this->createUser();

        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'Test request',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'status' => 'pending',
        ]);

        $expectation->addReply('This is a test reply', 'admin@test.com');

        $expectation->refresh();
        $this->assertEquals('replied', $expectation->status);
        $this->assertNotNull($expectation->last_reply_at);
        $this->assertCount(1, $expectation->replies);
        $this->assertEquals('This is a test reply', $expectation->replies[0]['message']);
        $this->assertEquals('admin@test.com', $expectation->replies[0]['admin_email']);
    }

    /** @test */
    public function support_expectation_scopes_work_correctly()
    {
        $user = $this->createUser();

        // Create expectations with different statuses and dates
        SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => ['message' => 'Pending request'],
            'status' => 'pending',
            'created_at' => now()->subDays(5),
        ]);

        SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => ['message' => 'Replied request'],
            'status' => 'replied',
            'created_at' => now()->subDays(10),
        ]);

        SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => ['message' => 'Old request'],
            'status' => 'pending',
            'created_at' => now()->subDays(40),
        ]);

        // Test status scope
        $pendingRequests = SupportExpectation::withStatus('pending')->get();
        $this->assertCount(2, $pendingRequests);

        // Test recent scope (default 30 days)
        $recentRequests = SupportExpectation::recent()->get();
        $this->assertCount(2, $recentRequests);

        // Test recent scope with custom days
        $veryRecentRequests = SupportExpectation::recent(7)->get();
        $this->assertCount(1, $veryRecentRequests);
    }
}