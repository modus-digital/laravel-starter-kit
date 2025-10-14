## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- ___SINGLE_BACKTICK___corePlugins___SINGLE_BACKTICK___ is not supported in Tailwind v4.
- In Tailwind v4, you import Tailwind using a regular CSS ___SINGLE_BACKTICK___@import___SINGLE_BACKTICK___ statement, not using the ___SINGLE_BACKTICK___@tailwind___SINGLE_BACKTICK___ directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |
<?php /**PATH E:\projects\starter-kits\modus-starter-kit-v2\storage\framework\views/31655088860077ae5c1512b9e40a1a55.blade.php ENDPATH**/ ?>