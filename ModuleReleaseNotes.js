$(document).ready(function() {
  $("a.grow-height").click(function() {
    $(this).closest('.expansion-wrapper').children('.expand').animate({'height': '+=400px'}, 500);
  });
});
