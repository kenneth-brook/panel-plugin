<?php if (!isset($panelUrl)) { http_response_code(404); exit; } ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Civic Anvil Control Panel</title>
  <link rel="stylesheet" href="panel.css">
</head>
<body>
<header>
  <h1>Civic Anvil Control Panel</h1>
  <nav aria-label="Modules">
    <a href="<?= panel_escape($panelUrl) ?>">Overview</a>
    <a href="<?= panel_escape(eventforge_admin_path()) ?>">Events</a>
    <?php if ($canManage): ?>
      <?php if ($installed['business']): ?><a href="?section=business">Businesses</a><?php endif; ?>
      <?php if ($installed['itinerary']): ?><a href="<?= panel_escape(eventforge_public_path('itinerary/index.php')) ?>">Visitor trip planner</a><?php endif; ?>
    <?php endif; ?>
    <form action="<?= panel_escape($panelUrl) ?>" method="post">
      <?= eventforge_csrf_input() ?>
      <button name="action" value="logout">Sign out</button>
    </form>
  </nav>
</header>
<main>
  <?php if ($error !== ''): ?><p class="error" role="alert"><?= panel_escape($error) ?></p><?php endif; ?>
  <?php if (isset($_GET['saved']) && $error === ''): ?><p role="status">Saved.</p><?php endif; ?>
  <?php if ($section === 'home'): ?>
    <h2>Overview</h2>
    <p><a href="<?= panel_escape(eventforge_admin_path()) ?>">Open Event Forge</a> to manage events.</p>
    <?php if ($canManage): ?>
      <ul>
        <li>Business directory · <?= $installed['business'] ? 'Ready' : ($modules['business'] ? 'Setup required' : 'Module files missing') ?></li>
        <li>Itineraries · <?= $installed['itinerary'] ? 'Ready' : ($modules['itinerary'] ? 'Requires setup and the Business Directory' : 'Module files missing') ?></li>
      </ul>
      <?php if (($modules['business'] && !$installed['business']) || ($modules['business'] && $modules['itinerary'] && !$installed['itinerary'])): ?>
        <p>Apply setup or upgrades to the available modules.</p>
        <form method="post">
          <?= eventforge_csrf_input() ?>
          <button name="action" value="install">Set up available modules</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  <?php elseif ($canManage && $installed[$section] && ($id === 0 || $record)): ?>
    <div class="columns">
      <aside>
        <h2><?= $section === 'business' ? 'Businesses' : 'Itineraries' ?></h2>
        <a href="?section=<?= panel_escape($section) ?>">Create new</a>
        <ul>
          <?php foreach ($records as $entry): ?>
            <li><a href="?section=<?= panel_escape($section) ?>&amp;id=<?= $entry['id'] ?>"<?= $id === $entry['id'] ? ' aria-current="page"' : '' ?>><?= panel_escape($entry['name'] ?? $entry['title']) ?></a> · <?= $entry['is_published'] ? 'Published' : 'Draft' ?></li>
          <?php endforeach; ?>
        </ul>
      </aside>
      <section>
        <h2><?= $id > 0 ? 'Edit' : 'Create' ?> <?= $section === 'business' ? 'business' : 'itinerary' ?></h2>
        <form method="post" enctype="multipart/form-data" action="?section=<?= panel_escape($section) ?>&amp;id=<?= $id ?>">
          <?= eventforge_csrf_input() ?>
          <?php
          $fields = ['name' => ['Name', 160], 'category' => ['Category', 100],
              'subcategory' => ['Subcategory', 100], 'address' => ['Street address', 255],
              'city' => ['City', 100], 'state' => ['State', 100], 'zip' => ['Postal code', 100],
              'phone' => ['Phone', 100], 'owner' => ['Owner or contact name (internal)', 100],
              'email' => ['Public contact email', 100], 'website' => ['Website', 500],
              'sponsor_level' => ['Sponsorship level', 100], 'geolocation' => ['Coordinates (latitude, longitude)', 100],
              'qrcode' => ['QR destination URL', 255], 'photo' => ['Photo filename', 255],
              'logo' => ['Logo filename', 255], 'audio' => ['Audio filename', 255]];
          foreach ($fields as $field => [$label, $max]):
          ?>
            <label><?= $label ?>
              <input name="<?= $field ?>" type="<?= $field === 'email' ? 'email' : 'text' ?>" maxlength="<?= $max ?>"
                value="<?= panel_escape($record[$field] ?? '') ?>"<?= in_array($field, ['name', 'title'], true) ? ' required' : '' ?>>
            </label>
          <?php endforeach; ?>
          <label>Description
            <textarea name="description" maxlength="10000" rows="4"><?= panel_escape($record['description'] ?? '') ?></textarea>
          </label>
          <?php foreach (['photo' => 'Photo', 'logo' => 'Logo', 'audio' => 'Audio'] as $field => $label): ?>
            <label><?= $label ?> upload (up to 10 MB)
              <input type="file" name="<?= $field ?>_upload" accept="<?= $field === 'audio' ? '.mp3,.wav,.ogg' : '.jpg,.jpeg,.png,.webp,.gif' ?>">
            </label>
          <?php endforeach; ?>
          <label>Membership
            <select name="is_member">
              <option value=""<?= ($record['is_member'] ?? null) === null || ($record['is_member'] ?? null) === '' ? ' selected' : '' ?>>Unspecified</option>
              <option value="1"<?= (string) ($record['is_member'] ?? '') === '1' ? ' selected' : '' ?>>Member</option>
              <option value="0"<?= (string) ($record['is_member'] ?? '') === '0' ? ' selected' : '' ?>>Non-member</option>
            </select>
          </label>
          <label>Existing status code
            <input name="status" type="number" min="-2147483648" max="2147483647" value="<?= panel_escape($record['status'] ?? 0) ?>" required>
          </label>
          <label>Sort order
            <input name="sort" type="number" min="0" max="4294967295" value="<?= panel_escape($record['sort'] ?? '') ?>">
          </label>
          <label class="check"><input type="checkbox" name="is_published" value="1"<?= !empty($record['is_published']) ? ' checked' : '' ?>> Published</label>
          <button name="action" value="save">Save</button>
        </form>
      </section>
    </div>
  <?php endif; ?>
</main>
</body>
</html>
