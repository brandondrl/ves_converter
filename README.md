# VES Converter

WordPress plugin for currency conversion using Sovereign Bolivar (VES) to Dollar (USD) exchange rates.

## Description

VES Converter is a plugin that allows users to convert between Sovereign Bolivars (VES) and US Dollars (USD) using exchange rates provided by the VES Change Getter plugin. The plugin offers multiple types of exchange rates (usd, average, and euro) and allows users to save their conversion history.

## Features

- Integration with VES Change Getter to obtain updated exchange rates
- Multiple exchange rate types (usd, average, euro)
- Bidirectional conversion (USD to VES and VES to USD)
- Rate history tracking for registered users
- Intuitive and responsive user interface
- Shortcode for easy integration in pages and posts
- Admin dashboard for management and statistics
- REST API for integration with other systems

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Installation

1. Download the plugin
2. Upload the plugin to the `/wp-content/plugins/` folder of your WordPress installation
3. Activate the plugin from the WordPress plugins menu

## Usage

### Shortcode

You can use the `[ves_converter]` shortcode to display the converter on any page or post:

```
[ves_converter]
```

The shortcode accepts the following optional attributes:

- `default_rate`: Default rate type (usd, average, euro). Default: usd

Example:
```
[ves_converter default_rate="euro"]
```

### Admin Dashboard

The plugin provides an admin dashboard where you can:

- View usage statistics
- Configure converter options
- View rate history
- Export rate data

### REST API

The plugin offers the following endpoints:

1. **Save a rate**
   ```
   POST /wp-json/ves-converter/v1/save-conversion
   ```
   Parameters:
   - rate_type: Rate type (usd, average, euro)
   - rate_value: Rate value

2. **Get user rate history**
   ```
   GET /wp-json/ves-converter/v1/user-conversions
   ```
   Returns the rate history of the currently authenticated user.

## Support

To report issues or request features, please create an issue in the plugin repository.

## License

This plugin is licensed under the GNU General Public License v2 or later.

## Credits

Developed by IDSI. 