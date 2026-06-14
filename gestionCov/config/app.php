<?php
// Application configuration for routing and points system.
// Points reward system: 1 km = 250 points for the driver.
// TODO: Update SESAME coordinates with exact campus location if needed.

define('SESAME_LAT', 36.8431000);
define('SESAME_LNG', 10.2057000);
define('DEFAULT_PRIX_PAR_KM', 250.0);   // Points per km (1 km = 250 points)
define('ROUTING_PROVIDER', 'osrm');
define('OSRM_ROUTE_URL', 'https://router.project-osrm.org/route/v1/driving');
