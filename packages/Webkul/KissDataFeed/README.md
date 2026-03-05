# Kiss DataFeed Extension for UnoPim

Connects UnoPim to the Kiss Customer DataFeed API, enabling product data export with configurable field mapping.

## Requirements

- UnoPim (with `unopim/concord` module system)
- PHP 8.1+

## Installation

### Option A: Composer (recommended)

To install via Composer, the package must be published on [Packagist](https://packagist.org/). Once published, run the following from your **UnoPim project root** (the directory containing `artisan`, `vendor/`, `config/`, etc.):

```bash
composer require darkloop/unopim-kiss-datafeed
```

To install a specific branch (e.g. for development or testing):

```bash
# Install the main branch
composer require darkloop/unopim-kiss-datafeed:dev-main

# Install a specific branch (prefix branch name with dev-)
composer require darkloop/unopim-kiss-datafeed:dev-some-feature-branch
```

> **Note:** Installing dev branches requires `"minimum-stability": "dev"` in your root `composer.json`, or using the `--stability=dev` flag.

Laravel auto-discovery will register the service provider automatically.

Then run the installer:

```bash
php artisan kiss-datafeed:install
```

This runs migrations and seeds the database.

### Option B: Manual installation

1. Copy the `packages/Webkul/KissDataFeed` directory into your UnoPim project's `packages/Webkul/` directory.

2. Add the PSR-4 autoload entry to your **root** `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "Webkul\\KissDataFeed\\": "packages/Webkul/KissDataFeed/src/"
        }
    }
}
```

3. Register the service provider in `config/concord.php`:

```php
'modules' => [
    // ... other modules
    \Webkul\KissDataFeed\Providers\KissDataFeedServiceProvider::class,
],
```

4. Regenerate the autoloader and run the installer:

```bash
composer dump-autoload
php artisan kiss-datafeed:install
```

## What the installer does

The `kiss-datafeed:install` command will:

- Run database migrations (creates `kiss_datafeed_credentials`, `kiss_datafeed_field_mappings`, and `kiss_datafeed_data_mappings` tables)
- Run the package seeder

## Post-installation

After installation, log into the UnoPim admin panel. You will see a new **Kiss DataFeed** menu with three sub-items:

- **Credentials** -- Manage API connections (URL, client ID, client secret)
- **Field Mapping** -- Map UnoPim attributes to DataFeed API fields per credential
- **Export Products** -- Trigger bulk product export to a selected credential

## Publishing a new version

When you make changes to the extension and want to release a new version:

1. Commit and push your changes to GitHub:

```bash
git add .
git commit -m "Description of changes"
git push origin main
```

2. Create a version tag following [semantic versioning](https://semver.org/) (`major.minor.patch`):

```bash
git tag v1.0.0
git push origin v1.0.0
```

Packagist will automatically detect the new tag (if you have auto-update enabled) and make it available. If not, log into Packagist and click "Update" on your package page.

Without a tagged release, Composer will only find `dev-main` and require the `--stability=dev` flag.

## Updating on the UnoPim server

From the **UnoPim project root** on your server:

```bash
# Update to the latest stable release
composer update darkloop/unopim-kiss-datafeed

# Or update to a specific version
composer require darkloop/unopim-kiss-datafeed:^1.1

# Run any new migrations
php artisan migrate
```

If the update includes new migrations or seeders, the `migrate` command will apply them. Existing data is not affected.

### Clearing caches after an update

After updating the package, you **must** clear cached config and restart queue workers. Laravel caches config at boot, so changes to exporter registration, menu items, or ACL permissions won't take effect until caches are cleared.

```bash
# Clear cached config (required after any config changes)
php artisan config:clear

# Clear compiled views (required after any Blade template changes)
php artisan view:clear

# Restart queue workers so they pick up the new code
# (use sudo if the workers run as a different user, e.g. www-data)
sudo php artisan queue:restart
```

**When is this needed?**

| What changed                          | Commands to run                                          |
|---------------------------------------|----------------------------------------------------------|
| Config files (menu, ACL, exporters)   | `php artisan config:clear`                               |
| Blade templates (views)               | `php artisan view:clear`                                 |
| PHP code (controllers, services, etc) | `sudo php artisan queue:restart`                         |
| Any package update                    | All three commands above                                 |

> **Tip:** After any `composer update` of this package, it's safest to run all three commands.

## Uninstallation

1. Remove the package:

```bash
composer remove darkloop/unopim-kiss-datafeed
```

Or, if manually installed, remove the directory from `packages/Webkul/KissDataFeed`, the PSR-4 entry from `composer.json`, and the provider from `config/concord.php`.

2. Optionally drop the database tables:

```sql
DROP TABLE IF EXISTS kiss_datafeed_data_mappings;
DROP TABLE IF EXISTS kiss_datafeed_field_mappings;
DROP TABLE IF EXISTS kiss_datafeed_credentials;
```
