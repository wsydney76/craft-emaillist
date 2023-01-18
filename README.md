# Register Email

Let users register their email (e.g. for a newsletter)

Work in progress.

Another proof-of-concept for decoupling and modernizing existing functionality.

Tbd.
## Requirements

This plugin requires Craft CMS 4.3.x or later, and PHP 8.0.2 or later.

Latest version of [Craft 4 Starter](https://github.com/wsydney76/craft4-ddev-starter) must be installed.

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

## CSS

Add to `content` in `tailwind.config.js`:

```javascript
'./vendor/wsydney76/craft-emaillist/src/templates/**/*.twig'
```

Run `npm run build`.

## Usage

Add a form on your page that lets users register to an email list.

Supports multiple lists.

```twig

{% include 'emaillist/_register-form.twig' %}

 {% embed 'emaillist/_register-form.twig' with {
        btnColor: 'light', 
        confirmPrivacy: true, 
        privacyLink: 'http://example.com', 
        list: 'newsletter'} %}
    
    {% block content %}
        You can use this block for any headings/introduction 
        {{ parent() }}
    {% endblock %}
{% endembed %}
```

Blocks:

* content: Place any content above the input field here
* inputs: Your own input/submit button here. Must react to the same Alpine.js dat and trigger the same events as the default block. 
* privacy: Your own privacy display here. Must react to the same Alpine.js data as the default block.
* message: Your own message display here. Must react to the same Alpine.js data as the default block.

Params:

* btnColor: primary (default) / light / white
* confirmPrivacy: whether the user must check a confirmation check box
* privacyLink: Url of your privacy page
* list: list handle. 'default' by default.

Wrap the component in a div if you want to control the width.

Does not support any kind of spam protection right now.

You can use an event to enable this and other supporting functionality: 


```php
Event::on(
    EmaillistController::class,
    EmaillistController::EVENT_EMAILLIST_REGISTER,
    function(EmaillistRegisterEvent $event) {
        // Your checks here
        $event->handled = true; // if check fails
    }
);
```

## Settings

* Send notification mail?
* Use queue job for sending mails?

## Utility

* Add/delete registrations
* Export registrations as csv

## Retrieve Registrations

```php
use wsydney76\emaillist\records\EmaillistRecord;

$emails = EmaillistRecord::find()
    ->orderBy('email')
    ->where(['list' => 'newsletter'])
    ->collect();
```

## Todos

* Improve utility to handle larger lists (pagination, filter, sorting).
* Better customization for texts (confirmations emails).
* Make lists configurable
