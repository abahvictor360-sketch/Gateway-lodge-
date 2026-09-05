/* ==========================================================================
   Gateway Lodge Group — booking hand-off to the STAAH booking engine.

   >>> CONFIGURE THIS FILE <<<
   Replace the placeholder URLs below with the real booking URLs from your
   STAAH extranet (Distribution > Booking Engine > Booking Engine URL).
   Each property has its own URL; `fallback` is used for "All Properties".

   The form still works with JavaScript disabled — it simply submits to the
   fallback URL instead of routing to the chosen property.
   ========================================================================== */

var GATEWAY_BOOKING = {
  fallback: 'https://book-directly.staah.net/REPLACE-WITH-GROUP-CODE',

  properties: {
    'nova-ridge': 'https://book-directly.staah.net/REPLACE-WITH-NOVA-RIDGE-CODE',
    'lakeside':   'https://book-directly.staah.net/REPLACE-WITH-LAKESIDE-CODE',
    'tamale':     'https://book-directly.staah.net/REPLACE-WITH-TAMALE-CODE'
  },

  /* Query-string parameter names STAAH expects. Confirm these against a real
     booking URL from your extranet — some STAAH setups use arrival/departure. */
  params: {
    checkin:  'checkin',
    checkout: 'checkout',
    guests:   'adults'
  }
};

(function () {
  'use strict';

  var form = document.querySelector('.booking-fields');
  if (!form) return;

  var propertyEl = form.querySelector('[name="property"]');
  var checkinEl  = form.querySelector('[name="checkin"]');
  var checkoutEl = form.querySelector('[name="checkout"]');
  var guestsEl   = form.querySelector('[name="guests"]');

  /* Format/parse in LOCAL time. toISOString() shifts to UTC, which silently
     drops or adds a day for anyone east or west of Greenwich. */
  function fmt(d) {
    var m = String(d.getMonth() + 1);
    var day = String(d.getDate());
    return d.getFullYear() + '-' + (m.length < 2 ? '0' + m : m) + '-' + (day.length < 2 ? '0' + day : day);
  }

  function today() {
    return fmt(new Date());
  }

  function dayAfter(value) {
    var parts = String(value).split('-');
    if (parts.length !== 3) return '';
    var d = new Date(+parts[0], +parts[1] - 1, +parts[2]);
    if (isNaN(d.getTime())) return '';
    d.setDate(d.getDate() + 1);
    return fmt(d);
  }

  /* No stays in the past, and check-out must follow check-in. */
  if (checkinEl) {
    checkinEl.min = today();
    checkinEl.addEventListener('change', function () {
      if (!checkinEl.value) return;
      var next = dayAfter(checkinEl.value);
      checkoutEl.min = next;
      if (!checkoutEl.value || checkoutEl.value <= checkinEl.value) {
        checkoutEl.value = next;
      }
    });
  }
  if (checkoutEl) checkoutEl.min = dayAfter(today());

  form.addEventListener('submit', function (event) {
    var key = propertyEl ? propertyEl.value : '';
    var base = (key && GATEWAY_BOOKING.properties[key]) || GATEWAY_BOOKING.fallback;
    if (!base) return; // fall through to the form's own action

    event.preventDefault();

    var p = GATEWAY_BOOKING.params;
    var query = [];
    if (checkinEl && checkinEl.value)  query.push(p.checkin  + '=' + encodeURIComponent(checkinEl.value));
    if (checkoutEl && checkoutEl.value) query.push(p.checkout + '=' + encodeURIComponent(checkoutEl.value));
    if (guestsEl && guestsEl.value)     query.push(p.guests   + '=' + encodeURIComponent(guestsEl.value));

    var url = base + (query.length ? (base.indexOf('?') === -1 ? '?' : '&') + query.join('&') : '');
    window.location.assign(url);
  });
})();
