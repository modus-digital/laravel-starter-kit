<div>
    <div class="my-auto">
        {!! $qrCode !!}

        <div class="text-center text-gray-600 dark:text-gray-400 mb-8 mt-2 px-4 text-sm">
            {{ __('notifications.modals.two-factor.enable.secret-key', ['secret' => $secret]) }}
        </div>
    </div>


    <form class="max-w-sm mx-auto w-full" wire:submit="enable">
        <div class="flex mb-2 justify-center space-x-2 rtl:space-x-reverse">
            <div>
                <label for="code-1" class="sr-only">First code</label>
                <input type="text" maxlength="1" data-focus-input-init data-focus-input-next="code-2" id="code-1" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" />
            </div>
            <div>
                <label for="code-2" class="sr-only">Second code</label>
                <input type="text" maxlength="1" data-focus-input-init data-focus-input-prev="code-1" data-focus-input-next="code-3" id="code-2" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" />
            </div>
            <div>
                <label for="code-3" class="sr-only">Third code</label>
                <input type="text" maxlength="1" data-focus-input-init data-focus-input-prev="code-2" data-focus-input-next="code-4" id="code-3" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" />
            </div>
            <div>
                <label for="code-4" class="sr-only">Fourth code</label>
                <input type="text" maxlength="1" data-focus-input-init data-focus-input-prev="code-3" data-focus-input-next="code-5" id="code-4" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" />
            </div>
            <div>
                <label for="code-5" class="sr-only">Fifth code</label>
                <input type="text" maxlength="1" data-focus-input-init data-focus-input-prev="code-4" data-focus-input-next="code-6" id="code-5" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" />
            </div>
            <div>
                <label for="code-6" class="sr-only">Sixth code</label>
                <input type="text" maxlength="1" data-focus-input-init data-focus-input-prev="code-5" id="code-6" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" />
            </div>
        </div>
        <p id="helper-text-explanation" class="mt-2 text-sm text-center text-gray-500 dark:text-gray-400">
            {{ __('notifications.modals.two-factor.enable.helper-text') }}
        </p>

        <input type="hidden" id="code" wire:model="code" />

        <button type="submit" class="w-full bg-primary-500 text-white px-4 py-2 rounded-md mt-4">
            {{ __('notifications.modals.two-factor.enable.button') }}
        </button>
    </form>

    <script>
        function focusNextInput(el, prevId, nextId) {
            if (el.value.length === 0) {
                if (prevId) {
                    document.getElementById(prevId).focus();
                }
            } else {
                if (nextId) {
                    document.getElementById(nextId).focus();
                }
            }
        }

        document.querySelectorAll('[data-focus-input-init]').forEach(function(element) {
            element.addEventListener('input', function() {
                const prevId = this.getAttribute('data-focus-input-prev');
                const nextId = this.getAttribute('data-focus-input-next');
                focusNextInput(this, prevId, nextId);
            });

            // Handle paste event to split the pasted code into each input
            element.addEventListener('paste', function(event) {
                event.preventDefault();
                const pasteData = (event.clipboardData || window.clipboardData).getData('text');
                const digits = pasteData.replace(/\D/g, ''); // Only take numbers from the pasted data

                // Get all input fields
                const inputs = document.querySelectorAll('[data-focus-input-init]');

                // Iterate over the inputs and assign values from the pasted string
                inputs.forEach((input, index) => {
                    if (digits[index]) {
                        input.value = digits[index];
                        // Focus the next input after filling the current one
                        const nextId = input.getAttribute('data-focus-input-next');
                        if (nextId) {
                            document.getElementById(nextId).focus();
                        }
                    }
                });
            });
        });

        // Update the hidden field based on all code input fields
        document.querySelectorAll('[data-focus-input-init]').forEach(function(element) {
            element.addEventListener('input', function() {
                const hiddenCodeInput = document.getElementById('code');
                hiddenCodeInput.value = Array.from(document.querySelectorAll('[data-focus-input-init]'))
                    .map(input => input.value)
                    .join('');
                // Manually dispatch an input event
                hiddenCodeInput.dispatchEvent(new Event('input'));
            });
        });

    </script>

</div>
