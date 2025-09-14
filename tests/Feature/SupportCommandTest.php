<?php

namespace Bestdecoders\ShopifyLaravelEnhanced\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Models\User;
use Bestdecoders\ShopifyLaravelEnhanced\Models\SupportExpectation;
use Bestdecoders\ShopifyLaravelEnhanced\Mail\SupportReplyMail;
use Bestdecoders\ShopifyLaravelEnhanced\ShopifyEnhancedServiceProvider;

class SupportCommandTest extends TestCase
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
    public function support_reply_command_adds_reply_successfully()
    {
        Mail::fake();
        $user = $this->createUser();

        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'I need help with TOC styling.',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'status' => 'pending',
        ]);

        $replyMessage = 'Thanks for your request! We can help you with the TOC styling.';

        $this->artisan('support:reply', [
            'id' => $expectation->id,
            'message' => $replyMessage,
            '--admin-email' => 'support@bestdecoders.com'
        ])
        ->expectsConfirmation('Do you want to reply to this support request?', 'yes')
        ->assertExitCode(0);

        // Assert the reply was added
        $expectation->refresh();
        $this->assertEquals('replied', $expectation->status);
        $this->assertNotNull($expectation->last_reply_at);
        $this->assertCount(1, $expectation->replies);
        $this->assertEquals($replyMessage, $expectation->replies[0]['message']);
        $this->assertEquals('support@bestdecoders.com', $expectation->replies[0]['admin_email']);

        // Assert email was sent
        Mail::assertSent(SupportReplyMail::class);
    }

    /** @test */
    public function support_reply_command_can_update_status()
    {
        Mail::fake();
        $user = $this->createUser();

        $expectation = SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'Issue resolved',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'status' => 'pending',
        ]);

        $this->artisan('support:reply', [
            'id' => $expectation->id,
            'message' => 'This issue has been resolved.',
            '--status' => 'resolved'
        ])
        ->expectsConfirmation('Do you want to reply to this support request?', 'yes')
        ->assertExitCode(0);

        $expectation->refresh();
        $this->assertEquals('resolved', $expectation->status);
    }

    /** @test */
    public function support_reply_command_fails_for_nonexistent_expectation()
    {
        $this->artisan('support:reply', [
            'id' => 999,
            'message' => 'This should fail',
        ])
        ->assertExitCode(1);
    }

    /** @test */
    public function support_reply_command_can_be_cancelled()
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

        $this->artisan('support:reply', [
            'id' => $expectation->id,
            'message' => 'This reply should be cancelled',
        ])
        ->expectsConfirmation('Do you want to reply to this support request?', 'no')
        ->assertExitCode(1);

        // Assert no reply was added
        $expectation->refresh();
        $this->assertEquals('pending', $expectation->status);
        $this->assertEmpty($expectation->replies);
    }

    /** @test */
    public function support_list_command_shows_expectations()
    {
        $user1 = $this->createUser();
        $user2 = User::factory()->create([
            'name' => 'shop2.myshopify.com',
            'email' => 'shop2@test.com',
        ]);

        // Create expectations
        SupportExpectation::create([
            'user_id' => $user1->id,
            'expectation' => [
                'message' => 'First request from shop 1',
                'created_at' => now()->toISOString(),
                'user_email' => $user1->email,
            ],
            'status' => 'pending',
            'created_at' => now()->subDays(2),
        ]);

        SupportExpectation::create([
            'user_id' => $user2->id,
            'expectation' => [
                'message' => 'Request from shop 2',
                'created_at' => now()->toISOString(),
                'user_email' => $user2->email,
            ],
            'status' => 'replied',
            'last_reply_at' => now()->subDay(),
            'created_at' => now()->subDays(1),
        ]);

        // Create old expectation (should not appear in default 7-day filter)
        SupportExpectation::create([
            'user_id' => $user1->id,
            'expectation' => [
                'message' => 'Old request',
                'created_at' => now()->subDays(10)->toISOString(),
                'user_email' => $user1->email,
            ],
            'status' => 'resolved',
            'created_at' => now()->subDays(10),
        ]);

        $this->artisan('support:list')
            ->expectsOutputToContain('Support Requests')
            ->expectsOutputToContain('First request from shop 1')
            ->expectsOutputToContain('Request from shop 2')
            ->assertExitCode(0);
    }

    /** @test */
    public function support_list_command_can_filter_by_status()
    {
        $user = $this->createUser();

        SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'Pending request',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'status' => 'pending',
        ]);

        SupportExpectation::create([
            'user_id' => $user->id,
            'expectation' => [
                'message' => 'Replied request',
                'created_at' => now()->toISOString(),
                'user_email' => $user->email,
            ],
            'status' => 'replied',
        ]);

        $this->artisan('support:list', ['--status' => 'pending'])
            ->expectsOutputToContain('Pending request')
            ->assertExitCode(0);
    }

    /** @test */
    public function support_list_command_can_limit_results()
    {
        $user = $this->createUser();

        // Create multiple expectations
        for ($i = 1; $i <= 5; $i++) {
            SupportExpectation::create([
                'user_id' => $user->id,
                'expectation' => [
                    'message' => "Request number {$i}",
                    'created_at' => now()->toISOString(),
                    'user_email' => $user->email,
                ],
                'status' => 'pending',
                'created_at' => now()->subDays($i),
            ]);
        }

        $this->artisan('support:list', ['--limit' => '3'])
            ->assertExitCode(0);
    }

    /** @test */
    public function support_list_command_shows_empty_message_when_no_requests()
    {
        $this->artisan('support:list')
            ->expectsOutputToContain('No support requests found.')
            ->assertExitCode(0);
    }
}