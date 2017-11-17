$(document).ready(function() {

  /**
   * If the content of the expand box is likely less than the initially assigned height, set the height to auto and
   * remove the show-all link.
   */
  $('div.long').each(function() {
    $this = $(this);
    var content = $this.html();

    max_len = 1024;
    if (content.length <= max_len) {
      $this.height('100%');
      $this.next().hide();
    }
  });


  $("a.grow-height").click(function() {
    $(this).closest('.expansion-wrapper').children('.expand').animate({'height': '+=400px'}, 500);
  });


  $("a.full-height").click(function() {
    $(this).closest('.expansion-wrapper').children('.expand').animate({'height': '100%'}, 500);
    $(this).hide();
  });


});
