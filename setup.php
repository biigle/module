<?php

/**
 * Update the module name in all relevant files.
 */

const PHP_DIRECTORIES = ['src', 'tests'];

function replaceText(string $path, string $old, string $new, int $expectedCount = 1): bool
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

    file_put_contents($path, str_replace($old, $new, $content));

    return true;
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
 * @return string[] Paths of changed files.
 */
function replaceNamespacePrefixInPhpFiles(string $root, string $module): array
{
    $oldPrefix = 'Biigle\\Modules\\Module';
    $newPrefix = "Biigle\\Modules\\{$module}";
    $changedFiles = [];

    foreach (phpFiles($root) as $path) {
        $content = file_get_contents($path);
        if (!str_contains($content, $oldPrefix)) {
            continue;
        }

        file_put_contents($path, str_replace($oldPrefix, $newPrefix, $content));
        $changedFiles[] = $path;
    }

    return $changedFiles;
}

/**
 * @return string[] Paths of changed files.
 */
function replaceTextInPhpFiles(string $root, string $old, string $new): array
{
    $changedFiles = [];

    foreach (phpFiles($root) as $path) {
        $content = file_get_contents($path);
        if (!str_contains($content, $old)) {
            continue;
        }

        file_put_contents($path, str_replace($old, $new, $content));
        $changedFiles[] = $path;
    }

    return $changedFiles;
}

/**
 * @return array{0: string, 1: string[]} New path and paths of changed files.
 */
function renameServiceProviderFile(string $root, string $module): array
{
    $oldPath = "{$root}/src/ModuleServiceProvider.php";
    $newPath = "{$root}/src/" . ucfirst($module) . 'ServiceProvider.php';
    $changedFiles = [];

    if (file_exists($oldPath) && file_exists($newPath)) {
        throw new RuntimeException(
            "Both service provider files exist: {$oldPath} and {$newPath}. Please resolve this manually."
        );
    }

    if (file_exists($oldPath)) {
        rename($oldPath, $newPath);
        $changedFiles[] = $newPath;
    }

    if (!file_exists($newPath)) {
        throw new RuntimeException(
            "Service provider file not found. Expected one of: {$oldPath} or {$newPath}"
        );
    }

    return [$newPath, $changedFiles];
}

/**
 * @return string[] Paths of changed files.
 */
function renameServiceProviderTestFile(string $root, string $module): array
{
    $oldPath = "{$root}/tests/ModuleServiceProviderTest.php";
    $newPath = "{$root}/tests/{$module}ServiceProviderTest.php";
    $changedFiles = [];

    if (file_exists($oldPath) && file_exists($newPath)) {
        throw new RuntimeException(
            "Both service provider test files exist: {$oldPath} and {$newPath}. Please resolve this manually."
        );
    }

    if (file_exists($oldPath)) {
        rename($oldPath, $newPath);
        $changedFiles[] = $newPath;
    }

    if (!file_exists($newPath)) {
        throw new RuntimeException(
            "Service provider test file not found. Expected one of: {$oldPath} or {$newPath}"
        );
    }

    return $changedFiles;
}


if (count($argv) < 2) {
    fwrite(STDERR, "Usage: php setup.php <name>\n");
    exit(1);
}

$root = __DIR__;
$module = $argv[1];
$moduleLower = strtolower($module);
$providerClass = ucfirst($module) . 'ServiceProvider';

[$serviceProviderPath, $renamedFiles] = renameServiceProviderFile($root, $module);
$renamedFiles = array_merge($renamedFiles, renameServiceProviderTestFile($root, $module));

$replacements = [
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

$changedFiles = $renamedFiles;
foreach ($replacements as [$path, $old, $new, $expectedCount]) {
    if (replaceText($path, $old, $new, $expectedCount)) {
        $changedFiles[] = $path;
    }
}

$changedFiles = array_merge($changedFiles, replaceNamespacePrefixInPhpFiles($root, $module));
$changedFiles = array_merge(
    $changedFiles,
    replaceTextInPhpFiles($root, 'ModuleServiceProvider', $providerClass)
);

$changedFiles = array_unique($changedFiles);

if ($changedFiles) {
    $count = count($changedFiles);
    echo "Updated module name to '{$module}' in {$count} file(s).\n";
} else {
    echo "No changes needed. Module name already set to '{$module}'.\n";
}
