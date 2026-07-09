<?php

/**
 * Update the module name in all relevant files.
 */

const PHP_DIRECTORIES = ['src', 'tests'];

/**
 * Check that a replacement can be applied without modifying anything.
 *
 * @return bool Whether the replacement still needs to be applied.
 */
function checkText(string $path, string $old, string $new, int $expectedCount): bool
{
    $content = file_get_contents($path);
    $currentCount = substr_count($content, $old);

    if ($currentCount === 0) {
        if (str_contains($content, $new)) {
            return false;
        }
        throw new RuntimeException("Did not find expected text in {$path}: {$old}");
    }

    if ($currentCount !== $expectedCount) {
        throw new RuntimeException(
            "Expected {$expectedCount} occurrence(s) in {$path}, found {$currentCount}: {$old}"
        );
    }

    return true;
}

function applyText(string $path, string $old, string $new): void
{
    file_put_contents($path, str_replace($old, $new, file_get_contents($path)));
}

function validateRename(string $oldPath, string $newPath): void
{
    if (file_exists($oldPath) && file_exists($newPath)) {
        throw new RuntimeException(
            "Both files exist: {$oldPath} and {$newPath}. Please resolve this manually."
        );
    }

    if (!file_exists($oldPath) && !file_exists($newPath)) {
        throw new RuntimeException(
            "File not found. Expected one of: {$oldPath} or {$newPath}"
        );
    }
}

/**
 * @return string[] Paths of PHP files.
 */
function phpFiles(string $root): array
{
    $files = [];

    foreach (PHP_DIRECTORIES as $directory) {
        $path = "{$root}/{$directory}";
        if (!is_dir($path)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
    }

    return $files;
}

/**
 * @param array{0: string, 1: string}[] $pairs Old and new text.
 *
 * @return string[] Paths of changed files.
 */
function replaceTextInPhpFiles(string $root, array $pairs): array
{
    $changedFiles = [];

    foreach (phpFiles($root) as $path) {
        $content = file_get_contents($path);
        $newContent = $content;

        foreach ($pairs as [$old, $new]) {
            $newContent = str_replace($old, $new, $newContent);
        }

        if ($newContent !== $content) {
            file_put_contents($path, $newContent);
            $changedFiles[] = $path;
        }
    }

    return $changedFiles;
}

/**
 * @return array{0: string, 1: string, 2: string, 3: int}[] Path, old text, new
 * text and expected occurrence count.
 */
function buildReplacements(
    string $root,
    string $module,
    string $moduleLower,
    string $providerClass,
    string $serviceProviderPath
): array {
    return [
        [
            "{$root}/src/Http/Controllers/QuotesController.php",
            "view('module::index')",
            "view('{$moduleLower}::index')",
            1,
        ],
        [
            "{$root}/src/resources/views/index.blade.php",
            'vendor/biigle/module/hot',
            "vendor/biigle/{$moduleLower}/hot",
            2,
        ],
        [
            "{$root}/src/resources/views/index.blade.php",
            "'vendor/module')",
            "'vendor/{$moduleLower}')",
            2,
        ],
        [
            $serviceProviderPath,
            "loadViewsFrom(__DIR__.'/resources/views', 'module');",
            "loadViewsFrom(__DIR__.'/resources/views', '{$moduleLower}');",
            1,
        ],
        [
            $serviceProviderPath,
            "\$modules->register('module', [",
            "\$modules->register('{$moduleLower}', [",
            1,
        ],
        [
            $serviceProviderPath,
            "public_path('vendor/module')",
            "public_path('vendor/{$moduleLower}')",
            1,
        ],
        [
            "{$root}/composer.json",
            '"name": "biigle/module"',
            "\"name\": \"biigle/{$moduleLower}\"",
            1,
        ],
        [
            "{$root}/composer.json",
            '"Biigle\\\\Modules\\\\Module\\\\": "src"',
            "\"Biigle\\\\Modules\\\\{$module}\\\\\": \"src\"",
            1,
        ],
        [
            "{$root}/composer.json",
            '"Biigle\\\\Modules\\\\Module\\\\ModuleServiceProvider"',
            "\"Biigle\\\\Modules\\\\{$module}\\\\{$providerClass}\"",
            1,
        ],
        [
            "{$root}/package.json",
            '"name": "biigle-module"',
            "\"name\": \"biigle-{$moduleLower}\"",
            1,
        ],
        [
            "{$root}/package.json",
            'Module\\\\\\\\ModuleServiceProvider',
            "{$module}\\\\\\\\{$providerClass}",
            1,
        ],
        [
            "{$root}/.github/workflows/test.yml",
            'MODULE_NAME: Module',
            "MODULE_NAME: {$module}",
            1,
        ],
    ];
}


if (count($argv) < 2) {
    fwrite(STDERR, "Usage: php setup.php <name>\n");
    exit(1);
}

$module = $argv[1];

if (!preg_match('/^[A-Z][A-Za-z0-9]*$/', $module)) {
    fwrite(STDERR, "The module name must start with an uppercase letter and may only contain letters and digits (e.g. MyModule).\n");
    exit(1);
}

$root = __DIR__;
$moduleLower = strtolower($module);
$providerClass = "{$module}ServiceProvider";

$providerOldPath = "{$root}/src/ModuleServiceProvider.php";
$providerNewPath = "{$root}/src/{$providerClass}.php";
$providerTestOldPath = "{$root}/tests/ModuleServiceProviderTest.php";
$providerTestNewPath = "{$root}/tests/{$providerClass}Test.php";

try {
    // Validate everything before modifying any file so a failure cannot leave
    // the module half renamed.
    validateRename($providerOldPath, $providerNewPath);
    validateRename($providerTestOldPath, $providerTestNewPath);

    $providerCurrentPath = file_exists($providerOldPath)
        ? $providerOldPath
        : $providerNewPath;

    $replacements = buildReplacements(
        $root, $module, $moduleLower, $providerClass, $providerCurrentPath
    );

    $pending = [];
    foreach ($replacements as [$path, $old, $new, $expectedCount]) {
        if (checkText($path, $old, $new, $expectedCount)) {
            $pending[] = [$path, $old, $new];
        }
    }

    $changedFiles = [];

    if (file_exists($providerOldPath)) {
        rename($providerOldPath, $providerNewPath);
        $changedFiles[] = $providerNewPath;
    }

    if (file_exists($providerTestOldPath)) {
        rename($providerTestOldPath, $providerTestNewPath);
        $changedFiles[] = $providerTestNewPath;
    }

    foreach ($pending as [$path, $old, $new]) {
        if ($path === $providerCurrentPath) {
            $path = $providerNewPath;
        }
        applyText($path, $old, $new);
        $changedFiles[] = $path;
    }

    // The test namespace prefix does not contain the regular namespace prefix
    // as a substring, so it must be replaced explicitly.
    $changedFiles = array_merge($changedFiles, replaceTextInPhpFiles($root, [
        ['Biigle\\Tests\\Modules\\Module', "Biigle\\Tests\\Modules\\{$module}"],
        ['Biigle\\Modules\\Module', "Biigle\\Modules\\{$module}"],
        ['ModuleServiceProvider', $providerClass],
    ]));
} catch (RuntimeException $e) {
    fwrite(STDERR, $e->getMessage()."\n");
    exit(1);
}

$changedFiles = array_unique($changedFiles);

if ($changedFiles) {
    $count = count($changedFiles);
    echo "Updated module name to '{$module}' in {$count} file(s):\n";
    foreach ($changedFiles as $path) {
        echo '  '.substr($path, strlen($root) + 1)."\n";
    }
} else {
    echo "No changes needed. Module name already set to '{$module}'.\n";
}
