// Targeting empty paragraphs.
$('#content p')
    .each(function() {
    var $this = $(this);
    if($this.html()
    .replace(/\s| /g, '').length == 0)
    $this.addClass('empty');
});

/* Open "post format link" links in a new tab */  
jQuery(document).ready(function($) {
jQuery('.format-link .entry-content a').attr('target','_blank');
});

