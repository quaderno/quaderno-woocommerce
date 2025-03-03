document.addEventListener('DOMContentLoaded', function () {

  // it is a copy of the inline edit function
  var wp_inline_edit_function = inlineEditPost.edit;

  // we overwrite it with our own
  inlineEditPost.edit = function (post_id) {

    // let's merge arguments of the original function
    wp_inline_edit_function.apply(this, arguments);

    // get the post ID from the argument
    var id = 0;
    if (typeof post_id === 'object') { // if it is object, get the ID number
      id = parseInt(this.getId(post_id));
    }

    // if post ID exists
    if (id > 0) {

      // add rows to variables
      var specific_post_edit_row = document.querySelector('#edit-' + id);
      var specific_post_row = document.querySelector('#post-' + id);
      var quaderno_tax_class_element = specific_post_row.querySelector('#quaderno_tax_class');
      var quaderno_tax_class = quaderno_tax_class_element ? quaderno_tax_class_element.textContent : '';

      // populate the inputs with column data
      var selectElement = specific_post_edit_row.querySelector('select.quaderno_tax_class');
      if (selectElement) {
        selectElement.value = quaderno_tax_class;
      }
    }
  }
});