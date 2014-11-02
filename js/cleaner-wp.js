/**
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License as published by the Free Software Foundation; either version 2 of the License, 
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write 
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * 
 * Cleaner WP plugin.
 * @author Alexandre Kozoubsky <alexandre@alexandrekozoubsky.com> 
 * @link http://www.alexandrekozoubsky.com
 * Copyright (c) 2014 Alexandre Kozoubsky
 */

// Targeting empty paragraphs.
$('#content p')
    .each(function() {
    var $this = $(this);
    if($this.html()
    .replace(/\s| /g, '').length == 0)
    $this.addClass('empty');
});


