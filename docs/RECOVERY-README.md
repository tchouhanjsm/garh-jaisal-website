# Garh Jaisal Haveli V2 Recovery Package

This folder was reconstructed from the strongest available project snapshot plus the V2 Experiences Phase 2 patch.

## Source snapshots used
- `garh-jaisal-merged-v2-image-pass.zip` (Oct 4 recovery snapshot)
- `garh-jaisal-v2-experiences-phase2.zip` (Oct 6 experience architecture)
- `restaurant-v2-files.zip` content was already present in the Oct 4 recovery snapshot.

## Important
This is a recovery reconstruction, not a byte-for-byte recovery of a lost local folder. Where the original historical backup files were no longer available, `contact.php.backup` and `mail.php.backup` are safety copies of the recovered current files and are not claimed to be the original historical backups.

Operational details in experience content, especially prices, exact times, route stops, facilities and inclusions, must be verified before production publishing.

## Current architecture
- Shared V2 header/footer/config under `include/`
- Shared room data under `include/rooms-data.php`
- Shared experience data/renderer under `include/experience-data.php` and `include/experience-page.php`
- Global V2 CSS under `css/garh-v2.css`
- Experience UI under `css/experiences.css`
- Existing legacy CSS retained for legacy pages and compatibility
- Existing images, fonts, JS and PHP pages retained from the recovery snapshot

## Local source/runtime workflow
VS Code source is the source of truth. Sync to XAMPP with the existing `sync-local.sh` workflow.

## CSS inventory
The `css/` directory includes the recovered legacy/vendor stylesheets plus the current V2 stylesheet and the V2 experiences stylesheet. The root-level restaurant CSS/JS files are retained because that was part of the surviving project structure.

## First-run validation
- `php -l` should be run across all PHP files.
- Check the generated site at the V2 local hostname before copying to staging.
- Verify the package operational details in `include/experience-data.php` before publishing.
