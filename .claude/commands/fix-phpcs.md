Fix all remaining PHPCS violations in this project that phpcbf cannot handle automatically.

The pre-commit hook already runs phpcbf on staged files and re-stages the result, so this skill is for violations phpcbf left behind — use it when a commit is blocked by phpcs errors.

## Process

1. Run `yarn fix` to auto-correct everything phpcbf can handle (indentation, spacing, braces, etc.).
2. Run `yarn lint` and capture the output. If phpcs exits clean, report success and stop.
3. Parse the output: each violation line includes a file path, line number, column, severity, and rule name (e.g. `WordPress.NamingConventions.ValidVariableName.NotSnakeCase`).
4. Group violations by file. For each affected file:
   a. Read the file.
   b. Fix each violation at the reported line, guided by the rule name and message. Common issues phpcbf leaves behind:
      - **Naming conventions** (`WordPress.NamingConventions.*`): rename variables and functions to snake_case.
      - **Yoda conditions** (`WordPress.PHP.YodaConditions`): put the static value on the left side of the comparison (`'value' === $var`).
      - **Docblocks** (`Squiz.Commenting.*`, `WordPress.Commenting.*`): add missing `@param`, `@return`, or `@throws` tags; fix incorrect types.
      - **Inline comment punctuation** (`Squiz.Commenting.InlineComment`): ensure inline comments end with a full stop, exclamation mark, or question mark.
      - **Deprecated functions** (`WordPress.WP.DeprecatedFunctions`): replace with the current equivalent.
   c. After editing, run `vendor/bin/phpcs <file>` to verify no violations remain in that file before moving on.
5. Report a summary: what phpcbf auto-fixed, what was manually fixed, and any violations left unresolved with an explanation of why.
