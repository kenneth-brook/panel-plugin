<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/installer.php';
require_once __DIR__ . '/../includes/auth.php';

header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$panelUrl = eventforge_public_path('panel/index.php');
$loginUrl = eventforge_admin_path('login.php') . '?panel=1';
if (!is_logged_in()) {
    header('Location: ' . $loginUrl);
    exit;
}

function panel_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$sections = ['home', 'business', 'itinerary'];
$section = $_GET['section'] ?? 'home';
if (!in_array($section, $sections, true)) {
    http_response_code(404);
    exit('Page not found.');
}
$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
if ($id === false) {
    http_response_code(400);
    exit('Invalid record ID.');
}

$modules = [
    'business' => is_file(__DIR__ . '/../business/store.php'),
    'itinerary' => is_file(__DIR__ . '/../itinerary/store.php'),
];
$error = '';
$canManage = false;
$installed = ['business' => false, 'itinerary' => false];
$records = [];
$record = [];
$choices = [];
$stops = [];

try {
    $db = eventforge_bootstrap_connection();
    if (!$db) {
        throw new RuntimeException('Event Forge database is unavailable.');
    }

    // Recheck the live account so a suspended or demoted user cannot use stale permissions.
    $username = $db->real_escape_string(current_admin_username());
    $result = $db->query("SELECT role, is_suspended FROM event_admin_users WHERE username = '{$username}' LIMIT 1");
    if (!$result) {
        throw new RuntimeException('Could not read the current user: ' . $db->error);
    }
    $account = $result->fetch_assoc();
    if (!$account || $account['is_suspended']) {
        unset($_SESSION['events_admin_logged_in'], $_SESSION['events_admin_username'], $_SESSION['events_admin_role']);
        header('Location: ' . $loginUrl);
        exit;
    }
    $_SESSION['events_admin_role'] = $account['role'];
    $canManage = is_admin();
    $post = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    if ($post) {
        eventforge_require_post_csrf();
        if (($_POST['action'] ?? '') === 'logout') {
            $_SESSION = [];
            session_destroy();
            header('Location: ' . $loginUrl, true, 303);
            exit;
        }
    }
    if (!$canManage && ($section !== 'home' || $post)) {
        http_response_code(403);
        exit('Only administrators can manage the directory and itineraries.');
    }

    if ($canManage) {
        $tables = $db->query("SELECT table_name FROM information_schema.tables
            WHERE table_schema = DATABASE() AND table_name IN
            ('business_directory_listings', 'trip_forge_itineraries', 'trip_forge_itinerary_items')");
        if (!$tables) {
            throw new RuntimeException('Could not check the module schemas: ' . $db->error);
        }
        $present = [];
        while ($table = $tables->fetch_row()) {
            $present[] = $table[0];
        }
        $installed['business'] = $modules['business'] && in_array('business_directory_listings', $present, true);
        $installed['itinerary'] = $modules['itinerary'] && $installed['business']
            && in_array('trip_forge_itineraries', $present, true)
            && in_array('trip_forge_itinerary_items', $present, true);

        if ($modules['business']) {
            require_once __DIR__ . '/../business/store.php';
        }
        if ($modules['business'] && $modules['itinerary']) {
            require_once __DIR__ . '/../itinerary/store.php';
        }

        if ($post && ($_POST['action'] ?? '') === 'install' && $section === 'home') {
            if ($modules['business'] && !$installed['business']) {
                business_install($db);
            }
            if ($modules['business'] && $modules['itinerary'] && !$installed['itinerary']) {
                itinerary_install($db);
            }
            header('Location: ' . $panelUrl, true, 303);
            exit;
        }

        if ($section !== 'home') {
            if (!$installed[$section]) {
                http_response_code(503);
                throw new InvalidArgumentException('Install this module from the panel home first.');
            }
            $records = $section === 'business' ? business_list($db, false) : itinerary_list($db, false);
            if ($id > 0) {
                $record = array_column($records, null, 'id')[$id] ?? [];
                if (!$record) {
                    http_response_code(404);
                    throw new InvalidArgumentException('Record not found.');
                }
            }
            if ($section === 'itinerary') {
                $choices = itinerary_choices($db);
                foreach (itinerary_items($db, $id, false) as $stop) {
                    $value = $stop['type'] . ':' . $stop['id'];
                    $stops[] = $value;
                    $choices[$stop['type']][$stop['id']] = $choices[$stop['type']][$stop['id']]
                        ?? $stop['business']['name'] ?? $stop['event']['title']
                        ?? 'Unavailable ' . $stop['type'] . ' #' . $stop['id'];
                }
            }
            if ($post && ($_POST['action'] ?? '') === 'save') {
                $saved = $section === 'business' ? business_save($db, $id, $_POST) : itinerary_save($db, $id, $_POST);
                header('Location: ' . $panelUrl . '?section=' . $section . '&id=' . $saved . '&saved=1', true, 303);
                exit;
            }
        }
    }
} catch (InvalidArgumentException $e) {
    if (http_response_code() === 200 || http_response_code() === false) {
        http_response_code(422);
    }
    $error = $e->getMessage();
    if (($_POST['action'] ?? '') === 'save') {
        foreach (['name', 'category', 'description', 'address', 'phone', 'website', 'title', 'is_published'] as $key) {
            $record[$key] = is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
        }
        if ($section === 'itinerary' && is_array($_POST['stops'] ?? null)) {
            $stops = array_slice(array_filter($_POST['stops'], 'is_string'), 0, 50);
        }
    }
} catch (Throwable $e) {
    error_log('Civic Anvil panel: ' . $e->getMessage());
    http_response_code(503);
    $error = 'The panel could not complete this request. Check the server error log.';
}

require __DIR__ . '/view.php';
