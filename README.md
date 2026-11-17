# Civic Anvil Control Panel

The panel provides a shared entry point for Event Forge and business
management. Event Forge administrators can edit and publish expanded business
profiles and upload photo, logo and audio files. Existing staff and staff
manager access to Event Forge continues through its native screens.

Itineraries are created on the public website. The panel provides a **Visitor
trip planner** link. Visitors may assemble trips anonymously and create an
account or sign in when they want to save. Private visitor trips are managed
by their owners in the public planner.

## Install or upgrade

1. Back up the existing Event Forge database, `events/config/db.php` and media.
2. Upload the updated `business/`, `itinerary/` and `panel/` directories into
   the existing Event Forge `events/` folder. Preserve business media in
   `business/storage/uploads/` when replacing code. Copy its `.htaccess` too.
3. Open `/event-forge/events/panel/index.php`, using the site's actual prefix.
   Sign in with an Event Forge administrator account and select **Set up
   available modules** under Overview. This button also applies upgrades.
   The database user needs CREATE and ALTER for schema upgrades.
4. Confirm the module statuses show **Ready**, then edit a business profile.
   Public directory data comes from `business/api.php`. Link the site to
   `itinerary/index.php` for the visitor planner.

The Business Directory upgrade adds the fields missing from the initial
release. Existing directory IDs and values are retained. The itinerary
upgrade adds visitor accounts and ownership. Existing unowned itinerary
records remain available through the legacy published-itinerary API.
The database and staff login still come from Event Forge.

For this client's panel, use
https://www.franklin-county.com/event-forge/events/panel/index.php.

See the companion repositories for media configuration, the CLI legacy
Directus importer, visitor account behavior and API details.
