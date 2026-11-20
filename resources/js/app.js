import $ from './jquery-global';          // должен подключаться ПЕРВЫМ
import { Toast, Tooltip } from 'bootstrap';

$(function () {
  // ---------- Tooltip (подсказка с названием при наведении на постер) ----------
  $('[data-bs-toggle="tooltip"]').each(function () {
    new Tooltip(this, { container: 'body', placement: 'top' });
  });

  // ---------- Toast по кнопке «Загрузить» в шапке ----------
  const toastEl = document.getElementById('downloadToast');
  if (toastEl) {
    const toast = Toast.getOrCreateInstance(toastEl, { delay: 4000 });
    $(document).on('click', '.btn-download', function () {
      toast.show();
    });
  }

  // ---------- Клиентская валидация форм (HTML5 + Bootstrap) ----------
  $('.needs-validation').on('submit', function (event) {
    if (!this.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    }
    $(this).addClass('was-validated');
  });
});
