# VES Converter

WordPress plugin for currency conversion using Sovereign Bolivar (VES) to Dollar (USD) exchange rates.

## Description

VES Converter is a plugin that allows users to convert between Sovereign Bolivars (VES) and US Dollars (USD) using exchange rates provided by the VES Change Getter plugin. The plugin offers multiple types of exchange rates (BCV, average, and parallel) and allows users to save their conversion history.

## Features

- Integration with VES Change Getter to obtain updated exchange rates
- Multiple exchange rate types (BCV, average, parallel)
- Bidirectional conversion (USD to VES and VES to USD)
- Conversion history for registered users
- Intuitive and responsive user interface
- Shortcode for easy integration in pages and posts
- Admin dashboard for management and statistics
- REST API for integration with other systems

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- VES Change Getter plugin installed and activated

## Installation

1. Download the plugin
2. Upload the plugin to the `/wp-content/plugins/` folder of your WordPress installation
3. Activate the plugin from the WordPress plugins menu
4. Make sure you have the VES Change Getter plugin installed and activated

## Usage

### Shortcode

You can use the `[ves_converter]` shortcode to display the converter on any page or post:

```
[ves_converter]
```

The shortcode accepts the following optional attributes:

- `default_rate`: Default rate type (bcv, average, parallel). Default: bcv

Example:
```
[ves_converter default_rate="parallel"]
```

### Admin Dashboard

The plugin provides an admin dashboard where you can:

- View usage statistics
- Configure converter options
- View conversion history of all users
- Export conversion data

### REST API

The plugin offers the following endpoints:

1. **Save a conversion**
   ```
   POST /wp-json/ves-converter/v1/save-conversion
   ```
   Parameters:
   - rate_type: Rate type (bcv, average, parallel)
   - rate_value: Rate value
   - amount_usd: Amount in USD
   - amount_ves: Amount in VES

2. **Get user conversion history**
   ```
   GET /wp-json/ves-converter/v1/user-conversions
   ```
   Returns the conversion history of the currently authenticated user.

## Customization

### Styles

You can customize the appearance of the converter by overriding the CSS styles in your theme. The main classes are:

- `.ves-converter-container`: Main container
- `.ves-converter-rates`: Rate selection section
- `.ves-converter-form`: Conversion form
- `.ves-converter-input-group`: Input group
- `.ves-converter-button`: Converter buttons
- `.ves-converter-result`: Results section
- `.ves-converter-history`: History section

### JavaScript

The plugin uses jQuery for the converter functionality. You can extend or modify the behavior by overriding the functions in your own script.

## Support

To report issues or request features, please create an issue in the plugin repository.

## License

This plugin is licensed under the GNU General Public License v2 or later.

## Credits

Developed by Brand. 