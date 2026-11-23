#!/usr/bin/env bash
#
# Bump php-concept/* require constraints across apps (prod composer.json),
# keep composer-dev path "versions" in sync, then refresh prod locks.
#
# Defaults (no specs): bump all extension-* and brick-* already in each app
# from sibling monorepo git tags (../concept-extensions, ../concept-bricks).
# Leaves core-2 / stack alone unless passed explicitly.
#
# Usage (prefer inside Docker — needs network for Packagist/GitHub on update):
#   docker compose exec php ./scripts/align-package-deps.sh
#   docker compose exec php ./scripts/align-package-deps.sh --extensions ^3.0 --bricks ^3.0
#   docker compose exec php ./scripts/align-package-deps.sh --dry-run
#   docker compose exec php ./scripts/align-package-deps.sh --no-update
#   docker compose exec php ./scripts/align-package-deps.sh --app web
#   docker compose exec php ./scripts/align-package-deps.sh extension-config:^3.0
#   docker compose exec php ./scripts/align-package-deps.sh --set php-concept/extension-=^3.0
#
# Spec: name[:constraint]  — one package
#   name       — extension-config | php-concept/extension-config
#   constraint — ^3.0 | 3.0 | 3.0.0  (bare X.Y → ^X.Y; path alias → X.Y.0)
#
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DRY_RUN=0
NO_UPDATE=0
APP_FILTERS=()
SPECS=()
SETS=()
EXTENSIONS_CONSTRAINT=""
BRICKS_CONSTRAINT=""
DO_EXTENSIONS=0
DO_BRICKS=0

while [[ $# -gt 0 ]]; do
    case "$1" in
        --dry-run)
            DRY_RUN=1
            shift
            ;;
        --no-update)
            NO_UPDATE=1
            shift
            ;;
        --app)
            [[ $# -ge 2 ]] || { echo "--app needs a name" >&2; exit 1; }
            APP_FILTERS+=("$2")
            shift 2
            ;;
        --extensions)
            DO_EXTENSIONS=1
            if [[ $# -ge 2 && "$2" != -* && "$2" != *:* ]]; then
                EXTENSIONS_CONSTRAINT="$2"
                shift 2
            else
                shift
            fi
            ;;
        --bricks)
            DO_BRICKS=1
            if [[ $# -ge 2 && "$2" != -* && "$2" != *:* ]]; then
                BRICKS_CONSTRAINT="$2"
                shift 2
            else
                shift
            fi
            ;;
        --set)
            [[ $# -ge 2 ]] || { echo "--set needs prefix=constraint" >&2; exit 1; }
            SETS+=("$2")
            shift 2
            ;;
        -h|--help)
            sed -n '3,22p' "$0"
            exit 0
            ;;
        -*)
            echo "Unknown option: $1" >&2
            exit 1
            ;;
        *)
            SPECS+=("$1")
            shift
            ;;
    esac
done

if ! command -v php >/dev/null 2>&1; then
    echo "php not found (run inside Docker: docker compose exec php ...)" >&2
    exit 1
fi

if [[ $NO_UPDATE -eq 0 && $DRY_RUN -eq 0 ]] && ! command -v composer >/dev/null 2>&1; then
    echo "composer not found (run inside Docker: docker compose exec php ...)" >&2
    exit 1
fi

tag_to_caret() {
    local tag="$1"
    tag="${tag#v}"
    local major minor
    IFS=. read -r major minor _ <<<"$tag"
    if [[ -z "${minor:-}" ]]; then
        echo "^${major}"
    else
        echo "^${major}.${minor}"
    fi
}

normalize_constraint() {
    local c="$1"
    c="${c#v}"
    if [[ "$c" =~ ^[0-9] ]]; then
        tag_to_caret "v$c"
    else
        echo "$c"
    fi
}

constraint_from_repo() {
    local repo="$1"
    local tag
    tag="$(git -C "$repo" describe --tags --abbrev=0 2>/dev/null || true)"
    if [[ -z "$tag" ]]; then
        return 1
    fi
    tag_to_caret "$tag"
}

# No explicit work requested → bump both families from sibling tags
if [[ ${#SPECS[@]} -eq 0 && ${#SETS[@]} -eq 0 && $DO_EXTENSIONS -eq 0 && $DO_BRICKS -eq 0 ]]; then
    DO_EXTENSIONS=1
    DO_BRICKS=1
fi

if [[ $DO_EXTENSIONS -eq 1 ]]; then
    if [[ -z "$EXTENSIONS_CONSTRAINT" ]]; then
        EXTENSIONS_CONSTRAINT="$(constraint_from_repo "$ROOT/../concept-extensions" || true)"
        if [[ -z "$EXTENSIONS_CONSTRAINT" ]]; then
            echo "No ../concept-extensions tag; pass --extensions ^X.Y" >&2
            exit 1
        fi
    else
        EXTENSIONS_CONSTRAINT="$(normalize_constraint "$EXTENSIONS_CONSTRAINT")"
    fi
    SETS+=("php-concept/extension-=${EXTENSIONS_CONSTRAINT}")
fi

if [[ $DO_BRICKS -eq 1 ]]; then
    if [[ -z "$BRICKS_CONSTRAINT" ]]; then
        BRICKS_CONSTRAINT="$(constraint_from_repo "$ROOT/../concept-bricks" || true)"
        if [[ -z "$BRICKS_CONSTRAINT" ]]; then
            echo "No ../concept-bricks tag; pass --bricks ^X.Y" >&2
            exit 1
        fi
    else
        BRICKS_CONSTRAINT="$(normalize_constraint "$BRICKS_CONSTRAINT")"
    fi
    SETS+=("php-concept/brick-=${BRICKS_CONSTRAINT}")
fi

export BUMP_ROOT="$ROOT"
export BUMP_DRY_RUN="$DRY_RUN"
export BUMP_NO_UPDATE="$NO_UPDATE"
export BUMP_APP_FILTERS="${APP_FILTERS[*]-}"
export BUMP_SPECS="${SPECS[*]-}"
export BUMP_SETS="${SETS[*]-}"

php <<'PHP'
<?php
declare(strict_types=1);

$root = getenv('BUMP_ROOT') ?: '';
$dryRun = getenv('BUMP_DRY_RUN') === '1';
$noUpdate = getenv('BUMP_NO_UPDATE') === '1';
$appFilters = array_values(array_filter(preg_split('/\s+/', getenv('BUMP_APP_FILTERS') ?: '') ?: []));
$specRaw = array_values(array_filter(preg_split('/\s+/', getenv('BUMP_SPECS') ?: '') ?: []));
$setRaw = array_values(array_filter(preg_split('/\s+/', getenv('BUMP_SETS') ?: '') ?: []));

function normalizePackage(string $raw): string
{
    $name = trim($raw);

    return str_starts_with($name, 'php-concept/') ? $name : 'php-concept/' . $name;
}

/** @return array{0: string, 1: string} constraint, pathVersion */
function normalizeConstraint(string $raw): array
{
    $c = trim($raw);
    if ($c !== '' && !preg_match('/^[\^~><=]/', $c)) {
        $constraint = '^' . $c;
        $numeric = $c;
    } else {
        $constraint = $c;
        $numeric = preg_replace('/^[^\d]*/', '', $c) ?? '';
    }
    $parts = array_values(array_filter(explode('.', $numeric), static fn(string $p): bool => $p !== ''));
    if ($parts === []) {
        throw new RuntimeException('Cannot parse version from constraint: ' . $raw);
    }
    while (count($parts) < 3) {
        $parts[] = '0';
    }

    return [$constraint, implode('.', array_slice($parts, 0, 3))];
}

/** @var list<array{0: string, 1: string, 2: string}> $prefixSets prefix, constraint, pathVersion */
$prefixSets = [];
foreach ($setRaw as $item) {
    if (!str_contains($item, '=')) {
        fwrite(STDERR, "Set must be prefix=constraint, got: {$item}\n");
        exit(1);
    }
    [$prefix, $constraintRaw] = explode('=', $item, 2);
    [$constraint, $pathVersion] = normalizeConstraint($constraintRaw);
    $prefixSets[] = [$prefix, $constraint, $pathVersion];
}

/** @var list<array{0: string, 1: string, 2: string}> $exactBumps package, constraint, pathVersion */
$exactBumps = [];
foreach ($specRaw as $spec) {
    if (!str_contains($spec, ':')) {
        fwrite(STDERR, "Spec must be name:constraint, got: {$spec}\n");
        exit(1);
    }
    [$nameRaw, $constraintRaw] = explode(':', $spec, 2);
    [$constraint, $pathVersion] = normalizeConstraint($constraintRaw);
    $exactBumps[] = [normalizePackage($nameRaw), $constraint, $pathVersion];
}

if ($prefixSets === [] && $exactBumps === []) {
    fwrite(STDERR, "Nothing to bump. Pass --extensions/--bricks, --set, or PKG:CONSTRAINT.\n");
    exit(1);
}

echo "Bump sets:\n";
foreach ($prefixSets as [$prefix, $constraint, $pathVersion]) {
    echo "  {$prefix}* → constraint={$constraint}, path={$pathVersion}\n";
}
foreach ($exactBumps as [$package, $constraint, $pathVersion]) {
    echo "  {$package}: constraint={$constraint}, path={$pathVersion}\n";
}

$appsRoot = $root . '/apps';
$appDirs = [];
foreach (glob($appsRoot . '/*/composer.json') ?: [] as $composerJson) {
    $dir = dirname($composerJson);
    $name = basename($dir);
    if ($appFilters !== [] && !in_array($name, $appFilters, true)) {
        continue;
    }
    $appDirs[] = $dir;
}
if ($appFilters !== []) {
    $found = array_map('basename', $appDirs);
    $missing = array_diff($appFilters, $found);
    if ($missing !== []) {
        fwrite(STDERR, 'Unknown --app: ' . implode(', ', $missing) . "\n");
        exit(1);
    }
}

/**
 * @return list<array{0: string, 1: string, 2: string}>
 */
$resolveTargets = static function(array $data) use ($prefixSets, $exactBumps): array {
    $out = [];
    $seen = [];

    foreach ($exactBumps as [$package, $constraint, $pathVersion]) {
        $out[] = [$package, $constraint, $pathVersion];
        $seen[$package] = true;
    }

    $keys = [];
    foreach (['require', 'require-dev'] as $section) {
        if (!isset($data[$section]) || !is_array($data[$section])) {
            continue;
        }
        foreach ($data[$section] as $key => $_) {
            if (is_string($key)) {
                $keys[$key] = true;
            }
        }
    }
    if (isset($data['repositories']) && is_array($data['repositories'])) {
        foreach ($data['repositories'] as $repo) {
            if (!is_array($repo) || !isset($repo['options']['versions']) || !is_array($repo['options']['versions'])) {
                continue;
            }
            foreach ($repo['options']['versions'] as $key => $_) {
                if (is_string($key)) {
                    $keys[$key] = true;
                }
            }
        }
    }

    foreach (array_keys($keys) as $package) {
        if (isset($seen[$package]) || !str_starts_with($package, 'php-concept/')) {
            continue;
        }
        foreach ($prefixSets as [$prefix, $constraint, $pathVersion]) {
            if (str_starts_with($package, $prefix)) {
                $out[] = [$package, $constraint, $pathVersion];
                $seen[$package] = true;
                break;
            }
        }
    }

    return $out;
};

$bumpRequire = static function(array &$data, string $package, string $constraint): bool {
    $changed = false;
    foreach (['require', 'require-dev'] as $section) {
        if (!isset($data[$section][$package]) || !is_string($data[$section][$package])) {
            continue;
        }
        if ($data[$section][$package] === $constraint) {
            continue;
        }
        $data[$section][$package] = $constraint;
        $changed = true;
    }

    return $changed;
};

$bumpPathVersions = static function(array &$data, string $package, string $pathVersion): bool {
    if (!isset($data['repositories']) || !is_array($data['repositories'])) {
        return false;
    }
    $changed = false;
    foreach ($data['repositories'] as &$repo) {
        if (!is_array($repo) || !isset($repo['options']['versions']) || !is_array($repo['options']['versions'])) {
            continue;
        }
        if (!array_key_exists($package, $repo['options']['versions'])) {
            continue;
        }
        if ($repo['options']['versions'][$package] === $pathVersion) {
            continue;
        }
        $repo['options']['versions'][$package] = $pathVersion;
        $changed = true;
    }
    unset($repo);

    return $changed;
};

/** @var array<string, list<string>> $packagesToUpdateByApp */
$packagesToUpdateByApp = [];

foreach ($appDirs as $appDir) {
    $label = 'apps/' . basename($appDir);
    $appUpdatedPackages = [];

    foreach ([['composer.json', false], ['composer-dev.json', true], ['composer-dev.json.example', true]] as [$filename, $withPath]) {
        $path = $appDir . '/' . $filename;
        if (!is_file($path)) {
            continue;
        }
        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $targets = $resolveTargets($data);
        $notes = [];
        foreach ($targets as [$package, $constraint, $pathVersion]) {
            if ($bumpRequire($data, $package, $constraint)) {
                $notes[] = "{$package} require → {$constraint}";
                $appUpdatedPackages[$package] = true;
            }
            if ($withPath && $bumpPathVersions($data, $package, $pathVersion)) {
                $notes[] = "{$package} path versions → {$pathVersion}";
            }
        }
        if ($notes === []) {
            continue;
        }
        $prefix = $dryRun ? 'dry-run would update' : 'updated';
        echo "==> {$label}/{$filename}: {$prefix}\n";
        foreach ($notes as $note) {
            echo "    - {$note}\n";
        }
        if (!$dryRun) {
            file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        }
    }

    if ($appUpdatedPackages !== []) {
        $packagesToUpdateByApp[basename($appDir)] = array_keys($appUpdatedPackages);
    }
}

if ($dryRun) {
    echo "dry-run: skip composer update\n";
    exit(0);
}
if ($noUpdate) {
    echo "--no-update: skip composer update\n";
    exit(0);
}

foreach ($appDirs as $appDir) {
    $appName = basename($appDir);
    $toUpdate = $packagesToUpdateByApp[$appName] ?? [];
    if ($toUpdate === []) {
        continue;
    }
    $composerJson = $appDir . '/composer.json';
    $lockFile = $appDir . '/composer.lock';
    $require = json_decode(file_get_contents($composerJson), true, 512, JSON_THROW_ON_ERROR)['require'] ?? [];
    $toUpdate = array_values(array_filter($toUpdate, static fn(string $p): bool => isset($require[$p])));
    if ($toUpdate === []) {
        continue;
    }

    // Partial update needs an existing lock; first run must be a full update.
    $cmd = ['composer', 'update', '--no-install', '--no-interaction'];
    if (is_file($lockFile)) {
        $cmd = array_merge($cmd, $toUpdate);
        echo '==> apps/' . $appName . ': composer update --no-install (' . implode(', ', $toUpdate) . ")\n";
    } else {
        echo '==> apps/' . $appName . ": composer update --no-install (no lock yet — full resolve)\n";
    }

    $proc = proc_open($cmd, [STDIN, STDOUT, STDERR], $pipes, $appDir, array_merge(getenv(), ['COMPOSER' => 'composer.json']));
    if (!is_resource($proc)) {
        fwrite(STDERR, "Failed to start composer in {$appDir}\n");
        exit(1);
    }
    $code = proc_close($proc);
    if ($code !== 0) {
        exit($code);
    }
}

echo "done\n";
PHP
