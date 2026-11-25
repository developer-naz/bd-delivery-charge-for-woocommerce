# Changelog

All notable changes to this project are documented in this file.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-11-25

### Added
- "BD Delivery Charge" shipping method for WooCommerce shipping zones.
- Inside Dhaka (whole district or Dhaka city thanas only), Dhaka sub-area and Outside Dhaka charges.
- Per-district rates by name or code, free delivery above an amount (all areas or Inside Dhaka only).
- Area shown in the rate name, saved on the order as `bddc_area`.
- Filters: `bddc_area_and_cost`, `bddc_dhaka_city_areas`.
- Classic checkout, Checkout block and HPOS compatibility.
