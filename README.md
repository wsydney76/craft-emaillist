# Register Email

Let users register their email (e.g. for a newsletter)

Work in progress.

Another proof-of-concept for decoupling and modernizing existing functionality.

Tbd.

## Installation

Update `composer.json`

```json
{
  "minimum-stability": "dev",
  "prefer-stable": true,
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/wsydney76/craft-emaillist"
    }
  ]
}
```

### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require wsydney76/craft-emaillist

# tell Craft to install the plugin
./craft plugin/install emaillist
```