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
craft plugin/install emaillist
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

* component: The whole markup. See example below.
* content: Place any content above the input field here
* inputs/email/button: Your own input/submit button here. Must react to the same Alpine.js dat and trigger the same events as the default block.
* privacy: Your own privacy display here. Must react to the same Alpine.js data as the default block.
* message: Your own message display here. Must react to the same Alpine.js data as the default block.

Params:

* btnColor: primary (default) / light / white
* confirmPrivacy: whether the user must check a confirmation check box
* privacyLink: Url of your privacy page
* list: list handle. 'default' by default.

Wrap the component in a div if you want to control the width.

##  Advanced customization

Set up your own template that extends the plugins component and pull in/overwrite blocks as needed.

```twig
{% extends "emaillist/_register-form.twig" %}

{# Set parameters #}
{% set list = 'test1' %}
{% set confirmPrivacy = false %}

{# Overwrite all markup inside the Alpine.js component #}
{% block component %}
    <div class="flex space-x-8">
        <div>
            {# Your own intro text #}
            <div class="mt-2 text-2xl uppercase">
                Subscribe to the newsletter
            </div>
        </div>

        <div class="w-[500px]">
            <div>
                {# Pull in the email field #}
                {{ block('email') }}
            </div>

            <div class="mt-1 text-sm">

                {# Your own message #}
                <div x-show="message"
                     x-text="message"
                     x-transition
                     class="my-1.5 p-2 text-white rounded"
                     :class="success ? 'bg-success' : 'bg-warning '">
                </div>
                
                {# Your own privacy text #}
                By providing my e-mail address, I understand that you will send me information by e-mail (newsletter)
                about your festival and its events.
                My data will not be passed on to third parties.
                I am aware that I can unsubscribe at any time via the unsubscribe link in the newsletter.

                {% set privacyEntry = craft.entries.section('legal').type('privacy').one %}
                {% if privacyEntry %}
                    <div>
                        Please also note our <a class="underline" href="{{ privacyEntry.url }}">privacy policy.</a>
                    </div>
                {% endif %}
            </div>

        </div>
        <div>
            {# use block('button') to pull in the default button #}
            <button type="button" class="text-2xl uppercase underline" @click="register()">SIGN UP</button>
        </div>
    </div>
{% endblock %}
```

## Security

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
* Lists (Label/Handle)

## CP Section

* Add/edit/delete registrations
* Export registrations as csv

## Retrieve Registrations

```php
use wsydney76\emaillist\records\RegistrationRecord;

$emails = RegistrationRecord::find()
    ->orderBy('email')
    ->where(['list' => 'newsletter'])
    ->collect();
```

## Widget

Emaillist widgets shows numbers of active registrations.

## Todos

* Better customization for texts (confirmations emails).

