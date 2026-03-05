# Kiss DataFeed Extension for UnoPim

Connects UnoPim to the Kiss Customer DataFeed API, enabling product data export with configurable field mapping.

## Requirements

- UnoPim (with `unopim/concord` module system)
- PHP 8.1+

## Installation

### Option A: Composer (recommended)

To install via Composer, the package must be published on [Packagist](https://packagist.org/). Once published:

```bash
composer require darkloop/unopim-kiss-datafeed
```

To install a specific branch (e.g. for development or testing):

```bash
# Install the master branch
composer require darkloop/unopim-kiss-datafeed:dev-master

# Install a specific branch (prefix branch name with dev-)
composer require darkloop/unopim-kiss-datafeed:dev-feature-branch
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
