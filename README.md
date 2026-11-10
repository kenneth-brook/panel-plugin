# Civic Anvil Control Panel

The panel gives Event Forge users a shared entry point for events, business
listings and itineraries. Event editing uses the native Event Forge screens.
Event Forge administrators can set up, edit and publish businesses and
itineraries. Staff and staff managers retain their existing event access.
All three modules use the existing Event Forge database and accounts.

## Install on an existing site

1. Verify Event Forge 0.7.2 with schema 9 on staging and back up its database.
   Apply the companion Event Forge login and navigation change for a direct
   return to the panel after sign-in and a Control Panel link on Manage Events.
2. Copy the folders below into the existing Event Forge `events/` directory.
   Preserve the site's Event Forge configuration and uploads.

   | Folder | Source |
   | --- | --- |
   | `business/` | [Business Directory](https://github.com/kenneth-brook/business-plugin) |
   | `itinerary/` | [Itinerary Plugin](https://github.com/kenneth-brook/itinerary-plugin-) |
   | `panel/` | This repository |

3. Visit `/events/panel/index.php` under the site's existing Event Forge URL,
   sign in as an Event Forge administrator, and select **Set up available
   modules**. The database user needs CREATE and the privileges needed to
   read and update the module tables. Setup is safe to rerun after a partial
   install. Business Directory can be installed without Itinerary Plugin.
4. Publish a business, add it and a published event to an itinerary, and
   publish the itinerary. Wire the site's public frontend to
   `/events/business/api.php` and `/events/itinerary/api.php`. The latter
   accepts `?id=123` to return a specific itinerary and its ordered stops.
   Link to `/events/itinerary/index.php` for the visitor planner. Existing
   calendars keep using the existing Event Forge endpoints.

The admin pages and Event Forge must share a host and a PHP session cookie
that covers the `events/` directory. The public API endpoints need no login.
A site hosted on another origin can fetch the feeds from its backend.
Use JavaScript in the panel to add, remove and reorder itinerary stops.

The module setup creates only `business_directory_listings`,
`trip_forge_itineraries`, and `trip_forge_itinerary_items`. To disable the
add-ons, take their folders out of the web root. Keep their tables to retain
staff-entered data for reinstall.
