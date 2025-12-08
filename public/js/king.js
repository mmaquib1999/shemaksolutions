// Custom JS for K.I.N.G. registration and UI

document.addEventListener('DOMContentLoaded', function() {
  // Example: focus first input on page load
  var firstInput = document.querySelector('.king-input');
  if (firstInput) firstInput.focus();

  // Example: show/hide password toggle
  var toggles = document.querySelectorAll('.king-toggle-password');
  toggles.forEach(function(toggle) {
    toggle.addEventListener('click', function() {
      var input = document.getElementById(toggle.dataset.target);
      if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
      }
    });
  });
});
