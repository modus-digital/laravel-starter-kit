## Pest 4

- Pest v4 is a huge upgrade to Pest and offers: browser testing, smoke testing, visual regression testing, test sharding, and faster type coverage.
- Browser testing is incredibly powerful and useful for this project.
- Browser tests should live in ___SINGLE_BACKTICK___tests/Browser/___SINGLE_BACKTICK___.
- Use the ___SINGLE_BACKTICK___search-docs___SINGLE_BACKTICK___ tool for detailed guidance on utilizing these features.

### Browser Testing
- You can use Laravel features like ___SINGLE_BACKTICK___Event::fake()___SINGLE_BACKTICK___, ___SINGLE_BACKTICK___assertAuthenticated()___SINGLE_BACKTICK___, and model factories within Pest v4 browser tests, as well as ___SINGLE_BACKTICK___RefreshDatabase___SINGLE_BACKTICK___ (when needed) to ensure a clean state for each test.
- Interact with the page (click, type, scroll, select, submit, drag-and-drop, touch gestures, etc.) when appropriate to complete the test.
- If requested, test on multiple browsers (Chrome, Firefox, Safari).
- If requested, test on different devices and viewports (like iPhone 14 Pro, tablets, or custom breakpoints).
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging when appropriate.

### Example Tests
@verbatim
<code-snippet name="Pest Browser Test Example" lang="php">
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in'); // Visit on a real browser...

    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors() // or ->assertNoConsoleLogs()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')

    Notification::assertSent(ResetPassword::class);
});
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Pest Smoke Testing Example" lang="php">
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavascriptErrors()->assertNoConsoleLogs();
</code-snippet>
@endverbatim
