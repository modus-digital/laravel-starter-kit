## Pest

### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use ___SINGLE_BACKTICK___php artisan make:test --pest <name>___SINGLE_BACKTICK___.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the ___SINGLE_BACKTICK___tests/Feature___SINGLE_BACKTICK___ and ___SINGLE_BACKTICK___tests/Unit___SINGLE_BACKTICK___ directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: ___SINGLE_BACKTICK___php artisan test___SINGLE_BACKTICK___.
- To run all tests in a file: ___SINGLE_BACKTICK___php artisan test tests/Feature/ExampleTest.php___SINGLE_BACKTICK___.
- To filter on a particular test name: ___SINGLE_BACKTICK___php artisan test --filter=testName___SINGLE_BACKTICK___ (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like ___SINGLE_BACKTICK___assertForbidden___SINGLE_BACKTICK___ and ___SINGLE_BACKTICK___assertNotFound___SINGLE_BACKTICK___ instead of using ___SINGLE_BACKTICK___assertStatus(403)___SINGLE_BACKTICK___ or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the ___SINGLE_BACKTICK___Pest\Laravel\mock___SINGLE_BACKTICK___ Pest function, but always import it via ___SINGLE_BACKTICK___use function Pest\Laravel\mock;___SINGLE_BACKTICK___ before using it. Alternatively, you can use ___SINGLE_BACKTICK___$this->mock()___SINGLE_BACKTICK___ if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>
