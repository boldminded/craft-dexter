# Release Notes for Dexter

## 1.0.6 - UNRELEASED
- URLs are removed from the `__full_text` property

## 1.0.5 - 2025-10-23
- Small quality of life improvement. Create the config/dexter.php file on first install.
- Add instructions when no indices exist.

## 1.0.4 - 2025-10-21
- Fixed issue with deletions

## 1.0.3 - 2025-10-18
- Improved error handling for missing configuration values.
- Added frontend search endpoint `site.com/dexter/search`
- Added alt text support to FileDescribe
- Fixed recursion and efficiency issue when a File is updated after getting described.

## 1.0.2 - 2025-10-15
- Fixed a bug where the pipeline did not correctly handle non-JSON descriptions.
- Started adding support for alt text response in image descriptions

## 1.0.0 - 2025-10-15
- Initial release
