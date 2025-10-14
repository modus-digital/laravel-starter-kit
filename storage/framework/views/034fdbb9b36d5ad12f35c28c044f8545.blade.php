## PHP

@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
@if($assist->shouldEnforceStrictTypes())
- Always use strict typing at the head of a ___SINGLE_BACKTICK___.php___SINGLE_BACKTICK___ file: ___SINGLE_BACKTICK___declare(strict_types=1);___SINGLE_BACKTICK___.
@endif
- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in ___SINGLE_BACKTICK_____construct()___SINGLE_BACKTICK___.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty ___SINGLE_BACKTICK_____construct()___SINGLE_BACKTICK___ methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
@if(empty($assist->enums()) || preg_match('/[A-Z]{3,8}/', $assist->enumContents()))
- Typically, keys in an Enum should be TitleCase. For example: ___SINGLE_BACKTICK___FavoritePerson___SINGLE_BACKTICK___, ___SINGLE_BACKTICK___BestLake___SINGLE_BACKTICK___, ___SINGLE_BACKTICK___Monthly___SINGLE_BACKTICK___.
@else
- That being said, keys in an Enum should follow existing application Enum conventions.
@endif
