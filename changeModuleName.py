#!/usr/bin/env python3

from pathlib import Path


def replace_text(path: Path, old: str, new: str, expected_count: int = 1) -> bool:
    content = path.read_text()
    current_count = content.count(old)

    if current_count == 0:
        if new in content:
            return False
        raise ValueError(f"Did not find expected text in {path}: {old}")

    if current_count != expected_count:
        raise ValueError(
            f"Expected {expected_count} occurrence(s) in {path}, found {current_count}: {old}"
        )

    updated = content.replace(old, new)
    path.write_text(updated)
    return True


def replace_namespace_prefix_in_php_files(root: Path, module: str) -> set[Path]:
    old_prefix = "Biigle\\Modules\\Module"
    new_prefix = f"Biigle\\Modules\\{module}"
    changed_files: set[Path] = set()

    for path in root.rglob("*.php"):
        content = path.read_text()
        if old_prefix not in content:
            continue

        path.write_text(content.replace(old_prefix, new_prefix))
        changed_files.add(path)

    return changed_files


def replace_text_in_php_files(root: Path, old: str, new: str) -> set[Path]:
    changed_files: set[Path] = set()

    for path in root.rglob("*.php"):
        content = path.read_text()
        if old not in content:
            continue

        path.write_text(content.replace(old, new))
        changed_files.add(path)

    return changed_files


def rename_service_provider_file(root: Path, module: str) -> tuple[Path, set[Path]]:
    old_path = root / "src/ModuleServiceProvider.php"
    new_path = root / f"src/{module.capitalize()}ServiceProvider.php"
    changed_files: set[Path] = set()

    if old_path.exists() and new_path.exists():
        raise ValueError(
            f"Both service provider files exist: {old_path} and {new_path}. Please resolve this manually."
        )

    if old_path.exists():
        old_path.rename(new_path)
        changed_files.add(new_path)

    if not new_path.exists():
        raise ValueError(
            f"Service provider file not found. Expected one of: {old_path} or {new_path}"
        )

    return new_path, changed_files


def rename_service_provider_test_file(root: Path, module: str) -> set[Path]:
    old_path = root / "tests/ModuleServiceProviderTest.php"
    new_path = root / f"tests/{module}ServiceProviderTest.php"
    changed_files: set[Path] = set()

    if old_path.exists() and new_path.exists():
        raise ValueError(
            f"Both service provider test files exist: {old_path} and {new_path}. Please resolve this manually."
        )

    if old_path.exists():
        old_path.rename(new_path)
        changed_files.add(new_path)

    if not new_path.exists():
        raise ValueError(
            f"Service provider test file not found. Expected one of: {old_path} or {new_path}"
        )

    return changed_files


def main() -> None:
    root = Path(__file__).resolve().parent
    module = root.name
    module_lower = module.lower()
    provider_class = f"{module.capitalize()}ServiceProvider"
    service_provider_path, renamed_files = rename_service_provider_file(root, module)
    renamed_files.update(rename_service_provider_test_file(root, module))

    replacements = [
        (
            root / "src/Http/Controllers/QuotesController.php",
            "view('module::index')",
            f"view('{module}::index')",
            1,
        ),
        (
            root / "src/resources/views/index.blade.php",
            "vendor/biigle/module/hot",
            f"vendor/biigle/{module}/hot",
            2,
        ),
        (
            service_provider_path,
            "loadViewsFrom(__DIR__.'/resources/views', 'module');",
            f"loadViewsFrom(__DIR__.'/resources/views', '{module}');",
            1,
        ),
        (
            service_provider_path,
            "$modules->register('module', [",
            f"$modules->register('{module}', [",
            1,
        ),
        (
            service_provider_path,
            "public_path('vendor/module')",
            f"public_path('vendor/{module}')",
            1,
        ),
        (
            root / "composer.json",
            '"name": "biigle/module"',
            f'"name": "biigle/{module_lower}"',
            1,
        ),
        (
            root / "composer.json",
            '"Biigle\\\\Modules\\\\Module\\\\": "src"',
            f'"Biigle\\\\Modules\\\\{module}\\\\": "src"',
            1,
        ),
        (
            root / ".github/workflows/test.yml",
            "MODULE_NAME: Module",
            f"MODULE_NAME: {module}",
            1,
        ),
    ]

    changed_files = set(renamed_files)
    for path, old, new, expected_count in replacements:
        changed = replace_text(path, old, new, expected_count)
        if changed:
            changed_files.add(path)

    changed_files.update(replace_namespace_prefix_in_php_files(root, module))
    changed_files.update(
        replace_text_in_php_files(root, "ModuleServiceProvider", provider_class)
    )
    if replace_text(
        root / "package.json", "ModuleServiceProvider", provider_class, expected_count=1
    ):
        changed_files.add(root / "package.json")

    if changed_files:
        print(f"Updated module name to '{module}' in {len(changed_files)} file(s).")
    else:
        print(f"No changes needed. Module name already set to '{module}'.")


if __name__ == "__main__":
    main()
