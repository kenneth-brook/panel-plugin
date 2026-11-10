<?php if (!isset($panelUrl)) { http_response_code(404); exit; } ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Civic Anvil Control Panel</title>
  <link rel="stylesheet" href="panel.css">
  <script src="stops.js" defer></script>
</head>
<body>
<header>
  <h1>Civic Anvil Control Panel</h1>
  <nav aria-label="Modules">
    <a href="<?= panel_escape($panelUrl) ?>">Overview</a>
    <a href="<?= panel_escape(eventforge_admin_path()) ?>">Events</a>
    <?php if ($canManage): ?>
      <?php if ($installed['business']): ?><a href="?section=business">Businesses</a><?php endif; ?>
      <?php if ($installed['itinerary']): ?><a href="?section=itinerary">Itineraries</a><?php endif; ?>
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
        <form method="post" action="?section=<?= panel_escape($section) ?>&amp;id=<?= $id ?>">
          <?= eventforge_csrf_input() ?>
          <?php
          $fields = $section === 'business'
              ? ['name' => ['Name', 160], 'category' => ['Category', 100], 'address' => ['Address', 255],
                  'phone' => ['Phone', 64], 'website' => ['Website', 500]]
              : ['title' => ['Title', 160]];
          foreach ($fields as $field => [$label, $max]):
          ?>
            <label><?= $label ?>
              <input name="<?= $field ?>" type="<?= $field === 'website' ? 'url' : 'text' ?>" maxlength="<?= $max ?>"
                value="<?= panel_escape($record[$field] ?? '') ?>"<?= in_array($field, ['name', 'title'], true) ? ' required' : '' ?>>
            </label>
          <?php endforeach; ?>
          <label>Description
            <textarea name="description" maxlength="10000" rows="4"><?= panel_escape($record['description'] ?? '') ?></textarea>
          </label>
          <?php if ($section === 'itinerary'): ?>
            <fieldset data-stop-editor>
              <legend>Stops</legend>
              <p>Choose businesses and events in the order they should appear.</p>
              <ol data-stops></ol>
              <button type="button" data-add-stop>Add stop</button>
              <template>
                <li>
                  <label>Source
                    <select name="stops[]">
                      <option value="">Choose a stop</option>
                      <?php foreach ($choices as $type => $options): ?>
                        <optgroup label="<?= $type === 'business' ? 'Businesses' : 'Events' ?>">
                          <?php foreach ($options as $sourceId => $name): ?>
                            <option value="<?= $type ?>:<?= $sourceId ?>"><?= panel_escape($name) ?></option>
                          <?php endforeach; ?>
                        </optgroup>
                      <?php endforeach; ?>
                    </select>
                  </label>
                  <button type="button" data-up>Move up</button>
                  <button type="button" data-down>Move down</button>
                  <button type="button" data-remove>Remove</button>
                </li>
              </template>
              <input type="hidden" data-initial-stops value="<?= panel_escape(json_encode(array_values($stops))) ?>">
              <noscript><p>Enable JavaScript to edit and save itinerary stops.</p></noscript>
            </fieldset>
          <?php endif; ?>
          <label class="check"><input type="checkbox" name="is_published" value="1"<?= !empty($record['is_published']) ? ' checked' : '' ?>> Published</label>
          <button name="action" value="save"<?= $section === 'itinerary' ? ' disabled data-save-itinerary' : '' ?>>Save</button>
        </form>
      </section>
    </div>
  <?php endif; ?>
</main>
</body>
</html>
