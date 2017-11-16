$(document).ready(function() {
  $("a.grow-height").click(function() {
    $(this).closest('.expansion-wrapper').children('.expand').animate({'height': '+=400px'}, 500);
  });


  $("a.full-height").click(function() {
    $(this).closest('.expansion-wrapper').children('.expand').animate({'height': '100%'}, 500);
    $(this).hide();
  });
});
