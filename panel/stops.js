const editor = document.querySelector('[data-stop-editor]');
if (editor) {
  const list = editor.querySelector('[data-stops]');
  const template = editor.querySelector('template');
  const add = editor.querySelector('[data-add-stop]');

  function updateButtons() {
    const rows = Array.from(list.children);
    add.disabled = rows.length >= 50;
    rows.forEach((row, index) => {
      row.querySelector('[data-up]').disabled = index === 0;
      row.querySelector('[data-down]').disabled = index === rows.length - 1;
    });
  }

  function addStop(value = '') {
    const row = template.content.firstElementChild.cloneNode(true);
    const select = row.querySelector('select');
    select.value = value;
    if (select.selectedIndex < 0) select.value = '';
    row.querySelector('[data-up]').addEventListener('click', () => {
      list.insertBefore(row, row.previousElementSibling);
      updateButtons();
      select.focus();
    });
    row.querySelector('[data-down]').addEventListener('click', () => {
      list.insertBefore(row.nextElementSibling, row);
      updateButtons();
      select.focus();
    });
    row.querySelector('[data-remove]').addEventListener('click', () => {
      row.remove();
      updateButtons();
      add.focus();
    });
    list.append(row);
    updateButtons();
    return select;
  }

  const initial = JSON.parse(editor.querySelector('[data-initial-stops]').value);
  initial.forEach(addStop);
  if (!initial.length) addStop();
  add.addEventListener('click', () => addStop().focus());
  document.querySelector('[data-save-itinerary]').disabled = false;
}
