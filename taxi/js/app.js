(function ($, Drupal) {
  let time = new Date(Cookies.get('timer') * 1000);
  let settings = drupalSettings.taxi_timer;

  function remove_cookie() {
    Cookies.remove('timer');
    $('.jq-toast-wrap')
        .css('transition', 'all 1s')
        .css('visibility', 'hidden')
        .css('opacity', 0);
  }

  function isValidDate(d) {
    return d instanceof Date && !isNaN(d);
  }

  if (isValidDate(time) && settings.enable) {
    $.toast({
      heading: settings.mes_head,
      text: settings.mes_text,
      bgColor: '#e5be01',
      textColor: '#000',
      hideAfter: false,
      position: settings.position,
    })

    $('.jq-toast-single').append("<div id='taxi-timer'></div>");

    $('#taxi-timer').countdown({
      year: time.getFullYear(),   // YYYY Format
      month: time.getMonth() + 1, // 1-12
      day: time.getDate(),        // 1-31
      hour: time.getHours(),      // 24 hours format 0-23
      minute: time.getMinutes(),  // 0-59
      second: time.getSeconds(),  // 0-59
      timezone: time.getTimezoneOffset() / 60 * -1,
      labels: false,
      onFinish: function () {
        remove_cookie()
      }
    });

    $('.close-jq-toast-single').click(function () {
      remove_cookie();
    });
  }
})(jQuery, Drupal);
