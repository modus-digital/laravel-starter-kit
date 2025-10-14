## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use ___SINGLE_BACKTICK___wire:model.live___SINGLE_BACKTICK___ for real-time updates, ___SINGLE_BACKTICK___wire:model___SINGLE_BACKTICK___ is now deferred by default.
    - Components now use the ___SINGLE_BACKTICK___App\Livewire___SINGLE_BACKTICK___ namespace (not ___SINGLE_BACKTICK___App\Http\Livewire___SINGLE_BACKTICK___).
    - Use ___SINGLE_BACKTICK___$this->dispatch()___SINGLE_BACKTICK___ to dispatch events (not ___SINGLE_BACKTICK___emit___SINGLE_BACKTICK___ or ___SINGLE_BACKTICK___dispatchBrowserEvent___SINGLE_BACKTICK___).
    - Use the ___SINGLE_BACKTICK___components.layouts.app___SINGLE_BACKTICK___ view as the typical layout path (not ___SINGLE_BACKTICK___layouts.app___SINGLE_BACKTICK___).

### New Directives
- ___SINGLE_BACKTICK___wire:show___SINGLE_BACKTICK___, ___SINGLE_BACKTICK___wire:transition___SINGLE_BACKTICK___, ___SINGLE_BACKTICK___wire:cloak___SINGLE_BACKTICK___, ___SINGLE_BACKTICK___wire:offline___SINGLE_BACKTICK___, ___SINGLE_BACKTICK___wire:target___SINGLE_BACKTICK___ are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for ___SINGLE_BACKTICK___livewire:init___SINGLE_BACKTICK___ to hook into Livewire initialization, and ___SINGLE_BACKTICK___fail.status === 419___SINGLE_BACKTICK___ for the page expiring:
@verbatim
<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>
@endverbatim
