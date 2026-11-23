/* Painel: confirmações, linhas de tamanhos e pré-visualização de fotos. Sem JS tudo funciona, com menos conforto. */
(function () {
  'use strict';

  // Confirmar antes de apagar.
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!window.confirm(form.getAttribute('data-confirm'))) event.preventDefault();
    });
  });

  // Adicionar linhas de tamanho a partir do <template>.
  var list = document.querySelector('[data-variants]');
  var template = document.querySelector('[data-variant-template]');
  var add = document.querySelector('[data-add-variant]');
  if (list && template && add) {
    var next = 1000;
    add.hidden = false;
    add.addEventListener('click', function () {
      var html = template.innerHTML.replace(/__KEY__/g, 'n' + (next++));
      list.insertAdjacentHTML('beforeend', html);
      var input = list.lastElementChild && list.lastElementChild.querySelector('input[type=text]');
      if (input) input.focus();
    });
  }

  // Pré-visualizar as fotos escolhidas antes de enviar.
  document.querySelectorAll('input[type=file][data-preview]').forEach(function (input) {
    var preview = input.closest('.adm-sec').querySelector('[data-preview-list]');
    if (!preview || !window.URL) return;
    input.addEventListener('change', function () {
      preview.querySelectorAll('img').forEach(function (img) { URL.revokeObjectURL(img.src); });
      preview.textContent = '';
      Array.prototype.forEach.call(input.files, function (file) {
        var li = document.createElement('li');
        var img = document.createElement('img');
        var name = document.createElement('span');
        img.src = URL.createObjectURL(file);
        img.alt = '';
        name.textContent = file.name;
        li.append(img, name);
        preview.appendChild(li);
      });
      preview.hidden = input.files.length === 0;
    });
  });
})();
