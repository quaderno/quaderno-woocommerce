// This script listens for the dismissal of a WordPress admin notice with the ID 'quaderno-alert'.
// When the dismissal button (.notice-dismiss) is clicked, an AJAX request is sent to the server to perform an action.

document.addEventListener('DOMContentLoaded', function () {
  document.addEventListener('click', function (event) {
    // Check if the clicked element is the dismiss button inside a .quaderno-notice
    var noticeElement = event.target.closest('.quaderno-notice');

    if (noticeElement && event.target.matches('.notice-dismiss')) {
      // Prepare the data to be sent in the POST request
      var formData = new FormData();
      formData.append('action', 'dismiss_quaderno_alert');
      formData.append('nonce', quadernoAjax.nonce);
      formData.append('alert_id', noticeElement.id);

      // Send the POST request using fetch
      fetch(quadernoAjax.ajaxurl, {
        method: 'POST',
        body: formData
      }).catch(function (error) {
        console.error('Error:', error);
      });
    }
  });
});