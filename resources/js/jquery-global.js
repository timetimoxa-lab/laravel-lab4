import $ from 'jquery';

// Bootstrap ищет window.jQuery — делаем jQuery глобальным
window.$ = window.jQuery = $;

export default $;
