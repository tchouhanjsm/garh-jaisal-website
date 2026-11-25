# Editing Garh Jaisal Haveli locally

Open `garhjaisal-local` in Finder. Double-click **Start Preview.command**, edit files inside `site/`, then refresh the browser. The preview serves this working copy directly at http://127.0.0.1:8080/.

| What you want to update | Where to edit inside `site/` |
| --- | --- |
| Menu dishes, prices and dietary labels | [Menu data](../include/menu-data.php), shared by Restaurant and Rooftop |
| Menu layout, paper styling and page turns | [Menu template](../include/ui/dining-menu.php), [styles](../css/restaurant.css), [script](../js/restaurant.js) |
| Room descriptions, features and room photos | [Room data](../include/rooms-data.php) and the image paths listed there |
| Arrival directions and transfers | [Directions](../direction.php), [arrival styles](../css/arrival-experience.css), [car services](../car-rental.php) |
| Tours and Journal | [Existing tours](../include/experience-data.php), [existing Journal guides](../include/editorial-guides-data.php), or [Content Studio](CONTENT-STUDIO.md) for drafts and structured updates |
| Hotel phone, email, map pin, social profiles and booking link | [Public site settings](../include/site-config.php) |
| Header, footer and site navigation | [Navigation labels and links](../include/nav-data.php), [header](../include/header-v2.php), [footer](../include/footer-v2.php) |
| Page title, search snippet or social preview | The `$page` settings in that route's PHP file, with shared output in [page head](../include/page-head.php) |

**Source files** has shortcuts to the original menu PDF and photo library. Keep originals and raw uploads outside `site/`. Put only selected web images inside the site and update their image paths in the matching data file. Use the existing **Start-Studio.command** inside `site/` for tour, Journal and Gallery drafting.

**Release ZIPs** is for cPanel packages. Create releases from a reviewed Git commit with [the package builder](../scripts/build_deploy_package.py). Top-level PHP page filenames are public URLs. Keep those filenames stable when editing or reorganizing content.
