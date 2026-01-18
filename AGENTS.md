# Information for AI Agents

## Documentation

The `README.md` found in the project root directory provides an overview of the project and its usage.

Each section should have it's own documentation file. Example: The `README.md` for file-system specific stuff is located in `src/FS/README.md` in a specific section for CSV-Handling.

### PHP Documentation

Mimic the documentation found on php.net.

For every method/function, the following information should be provided:

 1. The name of the method/function
 2. Examples
 3. Description
 4. Parameters
 5. Return values
 6. Throws
 7. See also

Also set links to functions used in the respective method/function on php.net.

## Testing

Use PHPUnit for testing the codebase.

Run the codebase tests using the command `composer test`.

Tests must mirror the `src` directory structure. For example, tests for `src/FS/*` live in `tests/FS/`, and tests for `src/Json/*` live in `tests/Json/`.
