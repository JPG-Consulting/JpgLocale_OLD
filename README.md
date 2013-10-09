# JpgLocale


Zend Framework 2 module to create multilingual applications

## Configuration

    return array(
        'jpg-locale' => array(
            'adapter' => array( ... ),
            'handlers' => array( ... ),
         )
    );

- `adapter` Allows to choose one of the available locale adapters
- `handlers` The handlers we are going to use to detect the locale.

### Handlers

Currently we've got the following handlers:

- `Query` Looks for a query parameter `locale=` which will define the locale to use. For example _http://yourdomain.com/?locale=en-US_ will load the `en_US` locale.
- `Subdomain` This one searches in your subdomain. For example: _http://en-us.yourdomain.com/_ will load `en_US` locale.

Just set the ones you wish to use. I really can't find a reason for having several handlers, but I left it up to you to add multiple handlers. 

The following example adds the `Query` and `Subdomain` handlers

    ...
    'handlers' => array(
        'Query',
		'Subdomain'
    )
    ...

By default the parameter searched in the `Query` handler is _locale_. Handlers can be tweaked with otions. For example, if you prefere to use _lang_ you could set it as follows:

    ...
    'handlers' => array(
        array(
            'type'    => 'Query',
            'options' => array (
                'param' => 'lang'
            ),
        ),
		'Subdomain'
    )
    ...

### Adapters
Currently there is only one adapter:

- `Config` - uses local cofiguration files

#### Config adapter

    ...
    'adapter' => array(
        'type' => 'Config',
        'options' => array(
            'default' => 'en_US',
            'locales' => array(
                'en_US' => array(
                    'english_name' => 'English',
                    'native_name'  => 'English'
                ),
                'es_ES' => array(
                    'english_name' => 'Spanish',
                    'native_name'  => 'Español'
                )
            )
        )
    )
    ...

The adapter `type` is set to `Config` as we want to use this adapter. The `Config` adapter **requires** some options

- `default` (_optional_) sets the default locale. 
- `locales` (**mandatory**) sets the available locales for our application. 
    - `english_name` (_optional_) The english name of the locale.
    - `native_name` (_optional_) The native name of the locale.

If `english_name` and/or `native_name` are not set the _INTL_ extension will try to figure them out. However, if _INTL_ extension is not set in your system it will throw exceptions.


## TODO

- Language view helper
- Locale changed event
- Route handler
- cTLD handler
- Database Adapter
- Doctrine Adapter
