___SINGLE_BACKTICK___@endverbatim directive.
- You must check existing Volt components to determine if they're functional or class based. If you can't detect that, ask the user which they prefer before writing a Volt component.

### Volt Functional Component Example
@verbatim
<code-snippet name="Volt Functional Component Example" lang="php">
@volt
___OPEN_PHP_TAG___
use function Livewire\Volt\{state, computed};

state(['count' => 0]);

$increment = fn () => $this->count++;
$decrement = fn () => $this->count--;

$double = computed(fn () => $this->count * 2);
?>

<div>
    <h1>Count: {{ $count }}</h1>
    <h2>Double: {{ $this->double }}</h2>
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
