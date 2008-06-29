/**
 * FriendsRoll Wordpress plugin
 * Copyright (c) 2008 76design/Thornley Fallis Communications
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
*/


jQuery.noConflict();

var friendsroll = function() {
	return {
		init: function() {
			friendsroll.maxPage = 0;
			friendsroll.currentPage = 0;
			jQuery.ajax({
				type: "GET",
				url: friendsroll_url + "/wp-content/plugins/friendsroll/friendsroll.php",
				data: "fr_maxpages=1",
				success: function(data) {
					friendsroll.maxPage = data;
				}
			});
		
			 jQuery.fn.hint = this.hint;
			 jQuery("#friendsroll_addbutton").bind("click", function() {
			   jQuery("#friendsroll_addform").toggle();
			   return false;
			 });
			 jQuery('#friendsroll input:text').hint();
			 
			 jQuery('#fr_show_all').bind('click', function() {
			 	jQuery("#fr_friendslist").slideUp();
			 	jQuery.ajax({
			 		type: "GET",
			 		url: friendsroll_url + "/wp-content/plugins/friendsroll/friendsroll.php",
			 		data: "fr_getallfriends=1",
			 		success: function(data) {
			 			jQuery("#fr_friendslist").html(data).slideDown();
			 			setTimeout(friendsroll.check_all_images, 2000);
			 		}
			 	});
			 	
			 	return false;
			 });
			 
			 jQuery('#friendsroll_addform').submit(function() {
			 	jQuery('#friendsroll_message').hide();
			 	var options = {
			 		type: "POST",
			 		url: friendsroll_url + "/wp-content/plugins/friendsroll/friendsroll.php?fr_ajaxsubmit=1",
			 		success: function(data) {
			 			var resp = eval('(' + data + ')');
			 			jQuery('#friendsroll_message').removeClass('success error').addClass(resp['status']).html(resp['message']).show();
			 			if (resp['status'] == 'success') {
			 				jQuery("#friendsroll_addform input:text").each(function() {
			 					jQuery(this).val(jQuery(this).attr('title'));
								jQuery(this).hint();
			 				});
			 				//jQuery("#friendsroll_addform").hide();
			 				jQuery("#friendsroll_message").fadeOut(5000);
			 				//jQuery("#friendsroll_addform").fadeTo(5000,1).hide();
							
			 			}
			 			jQuery('#friendsroll input:text').hint();
			 		}
			 	};
			 	jQuery(this).ajaxSubmit(options);
			 	return false;
			 });
			 
			 jQuery('#fr_next_page').bind('click', function(){
			    jQuery("#fr_friendslist").slideUp();
			  	jQuery.ajax({
			  		type: "GET",
			  		url: friendsroll_url + "/wp-content/plugins/friendsroll/friendsroll.php",
			  		data: "fr_getpagefriends=" + ++friendsroll.currentPage,
			  		success: function(data) {
			  			if(friendsroll.currentPage == (friendsroll.maxPage-1)){
			  				jQuery('#fr_next_page').hide();
			  				jQuery('#fr_prev_page').show();
			  			}
			  			if(friendsroll.currentPage > 0){
			  				jQuery('#fr_prev_page').show();
			  			}
			  			jQuery("#fr_friendslist").html(data).slideDown();
			  			setTimeout(friendsroll.check_all_images, 2000);
			  		}
			  	});
			
			  	return false;
			  });	
			  
			  jQuery('#fr_prev_page').bind('click', function(){
			    jQuery("#fr_friendslist").slideUp();
			  	jQuery.ajax({
			  		type: "GET",
			  		url: friendsroll_url + "/wp-content/plugins/friendsroll/friendsroll.php",
			  		data: "fr_getpagefriends=" + --friendsroll.currentPage,
			  		success: function(data) {
			  			if(friendsroll.currentPage <= 0){
			  				jQuery('#fr_prev_page').hide();
			  				jQuery('#fr_next_page').show();
			  			}
			  			if(friendsroll.currentPage < friendsroll.maxPage-1){
			  				jQuery('#fr_next_page').show();
			  			}
			  			jQuery("#fr_friendslist").html(data).slideDown();
			  			setTimeout(friendsroll.check_all_images, 2000);
			  		}
			  	});
			  	return false;
			  });
			 
			 setTimeout(friendsroll.check_all_images, 2000);
		},
			 
		check_all_images: function() {
			jQuery('#friendsroll OL LI IMG').each(function() {
				if (!friendsroll.image_ok(this)) {
					jQuery(this).attr('src', friendsroll_url + "/wp-content/plugins/friendsroll/images/default_favicon.gif");
				}
			});
		},
		
		image_ok: function (img) {
		    // During the onload event, IE correctly identifies any images that
		    // weren't downloaded as not complete. Others should too. Gecko-based
		    // browsers act like NS4 in that they report this incorrectly.
		    if (!img.complete) {
		        return false;
		    }
		
		    // However, they do have two very useful properties: naturalWidth and
		    // naturalHeight. These give the true size of the image. If it failed
		    // to load, either of these should be zero.
		    if (typeof img.naturalWidth != "undefined" && img.naturalWidth == 0) {
		        return false;
		    }
		
		    // No other way of checking: assume it's ok.
		    return true;
		},
		
		hint: function () {
		/*
		 * jQuery hint plugin from:
		 * http://remysharp.com/2007/01/25/jquery-tutorial-text-box-hints/
		 */
		  return this.each(function (){
		    // get jQuery version of 'this'
		    var t = jQuery(this); 
		    // get it once since it won't change
		    var title = t.attr('title'); 
		    // only apply logic if the element has the attribute
		    if (title) { 
		      // on blur, set value to title attr if text is blank
		      t.blur(function (){
		        if (t.val() == '') {
		          t.val(title);
		          t.addClass('blur');
		        }
		      });
		      // on focus, set value to blank if current value 
		      // matches title attr
		      t.focus(function (){
		        if (t.val() == title) {
		          t.val('');
		          t.removeClass('blur');
		        }
		      });
		
		      // clear the pre-defined text when form is submitted
		      t.parents('form:first()').submit(function(){
		          if (t.val() == title) {
		              t.val('');
		              t.removeClass('blur');
		          }
		      });
		
		      // now change all inputs to title
		      t.blur();
		    }
		  })
		},
		
		currentPage: 0,
		maxPage: 0
	}
}();


