/* Open "post format link" links in a new tab */  
jQuery(document).ready(function($) {
	jQuery('.format-link .entry-title a').attr('target','_blank');
	jQuery('.format-link .entry-content a').attr('target','_blank');
});
