// This script listens for the dismissal of a WordPress admin notice with the ID 'quaderno-alert'.
// When the dismissal button (.notice-dismiss) is clicked, an AJAX request is sent to the server to perform an action.

document.addEventListener('DOMContentLoaded', function () {
  document.addEventListener('click', function (event) {
    // Check if the clicked element is the dismiss button
    if (event.target.closest('#quaderno-alert .notice-dismiss')) {
      // Prepare the data to be sent in the POST request
      var formData = new FormData();
      formData.append('action', 'dismiss_quaderno_alert');
      formData.append('nonce', quadernoAjax.nonce);

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