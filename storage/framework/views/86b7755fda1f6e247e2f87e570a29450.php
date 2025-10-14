## Filament 4

### Important Version 4 Changes
- File visibility is now ___SINGLE_BACKTICK___private___SINGLE_BACKTICK___ by default.
- The ___SINGLE_BACKTICK___deferFilters___SINGLE_BACKTICK___ method from Filament v3 is now the default behavior in Filament v4, so users must click a button before the filters are applied to the table. To disable this behavior, you can use the ___SINGLE_BACKTICK___deferFilters(false)___SINGLE_BACKTICK___ method.
- The ___SINGLE_BACKTICK___Grid___SINGLE_BACKTICK___, ___SINGLE_BACKTICK___Section___SINGLE_BACKTICK___, and ___SINGLE_BACKTICK___Fieldset___SINGLE_BACKTICK___ layout components no longer span all columns by default.
- The ___SINGLE_BACKTICK___all___SINGLE_BACKTICK___ pagination page method is not available for tables by default.
- All action classes extend ___SINGLE_BACKTICK___Filament\Actions\Action___SINGLE_BACKTICK___. No action classes exist in ___SINGLE_BACKTICK___Filament\Tables\Actions___SINGLE_BACKTICK___.
- The ___SINGLE_BACKTICK___Form___SINGLE_BACKTICK___ & ___SINGLE_BACKTICK___Infolist___SINGLE_BACKTICK___ layout components have been moved to ___SINGLE_BACKTICK___Filament\Schemas\Components___SINGLE_BACKTICK___, for example ___SINGLE_BACKTICK___Grid___SINGLE_BACKTICK___, ___SINGLE_BACKTICK___Section___SINGLE_BACKTICK___, ___SINGLE_BACKTICK___Fieldset___SINGLE_BACKTICK___, ___SINGLE_BACKTICK___Tabs___SINGLE_BACKTICK___, ___SINGLE_BACKTICK___Wizard___SINGLE_BACKTICK___, etc.
- A new ___SINGLE_BACKTICK___Repeater___SINGLE_BACKTICK___ component for Forms has been added.
- Icons now use the ___SINGLE_BACKTICK___Filament\Support\Icons\Heroicon___SINGLE_BACKTICK___ Enum by default. Other options are available and documented.

### Organize Component Classes Structure
- Schema components: ___SINGLE_BACKTICK___Schemas/Components/___SINGLE_BACKTICK___
- Table columns: ___SINGLE_BACKTICK___Tables/Columns/___SINGLE_BACKTICK___
- Table filters: ___SINGLE_BACKTICK___Tables/Filters/___SINGLE_BACKTICK___
- Actions: ___SINGLE_BACKTICK___Actions/___SINGLE_BACKTICK___
<?php /**PATH E:\projects\starter-kits\modus-starter-kit-v2\storage\framework\views/63fb4f8cadb0b461226431c12151a790.blade.php ENDPATH**/ ?>