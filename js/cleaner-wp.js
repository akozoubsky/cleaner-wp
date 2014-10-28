<style type="text/javascript">

// targeting empty paragraphs.
$('#content p')
    .each(function() {
    var $this = $(this);
    if($this.html()
    .replace(/\s| /g, '').length == 0)
    $this.addClass('empty');
});

</style>
