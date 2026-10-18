<?php

use App\Jobs\DemoFailingJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    // Keep the slow button quick in tests.
    config(['demo.slow_request_seconds' => 0.01]);
});

it('shows every button and says that the SDK is switched off in tests', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Throw an exception')
        ->assertSee('Report a handled error')
        ->assertSee('Write the logs')
        ->assertSee('Call the JSON endpoint')
        ->assertSee('Make a slow request')
        ->assertSee('Run a slow query')
        ->assertSee('Queue the job')
        ->assertSee('Arm the failing task')
        ->assertSee('Send the burst')
        ->assertSee('Mark a deploy')
        ->assertSee('Open a missing page')
        ->assertSee('Submit an invalid form')
        ->assertSee('Submit without a token')
        ->assertSee('SDK status')
        ->assertSee('INACTIVE');
});

it('answers 500 to the unhandled exception button', function () {
    $this->get('/demo/exception', ['Accept' => 'text/html'])->assertStatus(500);
});

it('answers the JSON endpoint with a JSON 500 and, with the SDK off, no reference header', function () {
    $response = $this->getJson('/demo/json-error');

    $response->assertStatus(500)->assertJsonStructure(['message']);
    expect($response->headers->has('X-NightGuardian-Reference'))->toBeFalse();
});

it('reports a handled error without breaking the page', function () {
    $this->post('/demo/handled')
        ->assertRedirect('/')
        ->assertSessionHas('result.title', 'Handled error reported');
});

it('writes the log lines', function () {
    $this->post('/demo/logs')->assertRedirect('/')->assertSessionHas('result.title', 'Two log lines written');
});

it('makes a slow request', function () {
    $this->post('/demo/slow-request')->assertRedirect('/')->assertSessionHas('result.title', 'Slow request finished');
});

it('explains that the slow query needs MySQL when running on another database', function () {
    $this->post('/demo/slow-query')->assertRedirect('/')->assertSessionHas('result.title', 'Slow query skipped');
});

it('queues the failing job and processes the queue', function () {
    Queue::fake();
    $this->post('/demo/job')->assertRedirect('/')->assertSessionHas('result.title', 'Job queued');
    Queue::assertPushed(DemoFailingJob::class);
});

it('processes an empty queue', function () {
    config(['queue.default' => 'database']);

    $this->post('/demo/run-queue')->assertRedirect('/')->assertSessionHas('result.title', 'Queue processed');
});

it('arms the failing scheduled task and runs the scheduler', function () {
    $this->post('/demo/arm-schedule')->assertRedirect('/')->assertSessionHas('result.title', 'Failing task armed');
    expect(Cache::get('demo.fail_schedule'))->toBeTrue();

    $this->post('/demo/run-scheduler')->assertRedirect('/')->assertSessionHas('result.title', 'Scheduler ran');
});

it('sends a burst of the requested size, at most the configured maximum', function () {
    $this->post('/demo/burst', ['count' => 7])->assertSessionHas('result.title', 'Burst of 7 errors');
    $this->post('/demo/burst', ['count' => 100000])->assertSessionHas('result.title', 'Burst of 200 errors');
    $this->post('/demo/burst', ['count' => 0])->assertSessionHas('result.title', 'Burst of 1 errors');
});

it('marks a deploy (and says why it could not while the SDK is off)', function () {
    $this->post('/demo/deploy')->assertRedirect('/')->assertSessionHas('result.title', 'Deploy marked');
});

// --- what must not be reported: ordinary behaviour -----------------------------------------------------------------

it('answers 404 for a missing page', function () {
    $this->get('/demo/not-found')->assertNotFound();
});

it('answers a validation error with a redirect and the errors', function () {
    $this->post('/demo/validation', ['email' => 'not-an-email'])->assertRedirect('/')->assertSessionHasErrors('email');
});

it('answers 419 when the CSRF token is missing', function () {
    // Laravel skips the CSRF check while its own tests run; pretend this is a real environment.
    app()['env'] = 'production';

    $this->post('/demo/csrf-expired')->assertStatus(419);
});

// --- DEMO_KEY ----------------------------------------------------------------------------------------------------

it('is open when no key is set', function () {
    config(['demo.key' => null]);

    $this->get('/')->assertOk()->assertDontSee('Type the key');
});

it('asks for the key when DEMO_KEY is set and locks every route', function () {
    config(['demo.key' => 'letmein']);

    $this->get('/')->assertStatus(401)->assertSee('Type the key')->assertDontSee('Throw an exception');
    $this->get('/demo/exception')->assertStatus(401);
    $this->post('/demo/handled')->assertStatus(401);
});

it('rejects a wrong key and accepts the right one', function () {
    config(['demo.key' => 'letmein']);

    $this->post('/unlock', ['key' => 'nope'])->assertStatus(401)->assertSee('That is not the key.');
    $this->get('/')->assertStatus(401);

    $this->post('/unlock', ['key' => 'letmein'])->assertRedirect('/');
    $this->get('/')->assertOk()->assertSee('Throw an exception');
});

it('keeps the key out of the page', function () {
    config(['demo.key' => 'a-very-secret-key']);
    $this->post('/unlock', ['key' => 'letmein']);

    $this->get('/')->assertDontSee('a-very-secret-key');
});
