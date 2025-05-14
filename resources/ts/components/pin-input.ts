/* -------------------------------------------------------------------------- */
/*  Helpers                                                                    */
/* -------------------------------------------------------------------------- */

type NumericOrAlphanumeric = 'numeric' | 'alphanumeric';

type ClipboardFallback = { clipboardData: DataTransfer };

const isValidChar = (alphaNumeric: boolean) => (ch: string) =>
  alphaNumeric ? /^[a-zA-Z0-9]$/.test(ch) : /^[0-9]$/.test(ch);

/* -------------------------------------------------------------------------- */
/*  Main init                                                                  */
/* -------------------------------------------------------------------------- */

export function initPinInputs(): void {
  const groups = document.querySelectorAll<HTMLElement>('[data-pin-input]');

  groups.forEach((group) => {
    const length = Number(group.dataset.length) || 4;
    const alphaNumeric = group.dataset.alphaNumeric === '1';
    const inputs = Array.from(
      group.querySelectorAll<HTMLInputElement>('input[data-pin-inputs]'),
    );

    // Get name from inputs or fallback to 'pin'
    const name = inputs.length > 0 ? inputs[0].name.split('[')[0] : 'pin';

    // Find the hidden input using the data attribute
    const hiddenInput = group.querySelector<HTMLInputElement>(`input[data-pin-input-value="${name}"]`);

    // Guard: ensure DOM matches declared length
    if (inputs.length !== length) {
      // eslint-disable-next-line no-console
      console.warn(
        `PinInput: expected ${length} inputs but found ${inputs.length}. Adjusting...`,
      );
    }

    const valid = isValidChar(alphaNumeric);

    const currentValue = () => inputs.map((i) => i.value).join('');

    // Update hidden input with combined value
    const updateHiddenInput = () => {
      if (hiddenInput) {
        hiddenInput.value = currentValue();
        // Dispatch an input event to trigger any listeners
        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        // Also dispatch a change event for form validation
        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
      }
    };

    const maybeEmitComplete = () => {
      // Update the hidden input with the current value
      updateHiddenInput();

      if (currentValue().length === length) {
        group.dispatchEvent(
          new CustomEvent('pin-input:complete', {
            detail: { value: currentValue() },
          }),
        );
      }
    };

    // Initialize the hidden input value
    updateHiddenInput();

    inputs.forEach((input, idx) => {
      /* ---------------------------- KEYDOWN handler --------------------------- */
      input.addEventListener('keydown', (e) => {
        // Allow browser shortcuts (Ctrl/Cmd) → ensures paste works naturally
        if (e.ctrlKey || e.metaKey) return;

        const key = e.key;

        if (key === 'Backspace') {
          if (input.value === '' && idx > 0) inputs[idx - 1].focus();
          input.value = '';
          e.preventDefault();
          updateHiddenInput(); // Update hidden input on backspace
          return;
        }

        // Ignore navigation keys (let browser handle)
        if (['Tab', 'ArrowLeft', 'ArrowRight'].includes(key)) return;

        // Prevent overwriting if input already has a value
        if (input.value !== '') {
          e.preventDefault();
          return;
        }

        if (!valid(key)) {
          e.preventDefault();
          return;
        }

        input.value = key;
        e.preventDefault();
        if (idx + 1 < length) inputs[idx + 1].focus();
        maybeEmitComplete();
      });

      /* ------------------------------ PASTE handler --------------------------- */
      input.addEventListener('paste', (e) => {
        e.preventDefault();

        const clipboard =
          e.clipboardData ?? (window as unknown as ClipboardFallback).clipboardData;
        const pasted = clipboard?.getData('text') ?? '';
        if (!pasted) return;

        const chars = pasted.split('').filter(valid).slice(0, length - idx);
        if (chars.length === 0) return;

        chars.forEach((ch, offset) => {
          inputs[idx + offset].value = ch;
        });

        // Focus the last character we pasted (or stay on current)
        const targetIdx = Math.min(idx + chars.length, length) - 1;
        inputs[targetIdx].focus();

        maybeEmitComplete();
      });

      // Update hidden input when input value changes directly
      input.addEventListener('input', () => {
        updateHiddenInput();
      });
    });

    // Initialize for any pre-filled values
    updateHiddenInput();
  });
}

/* -------------------------------------------------------------------------- */
/*  Boot once DOM is ready                                                     */
/* -------------------------------------------------------------------------- */

document.addEventListener('DOMContentLoaded', () => {
  if (!(window as PinInputWindow).pinInputInitialized) {
    (window as PinInputWindow).pinInputInitialized = true;
    initPinInputs();
  }
});

type PinInputWindow = Window & { pinInputInitialized?: boolean };

export default initPinInputs;
