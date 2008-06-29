<?php
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

//required
@session_start(); // start a session
$rand = rand(10000, 99999); // generate 5 digit random number
$_SESSION['fr_captcha_hash'] = md5($rand); // create the hash for the random number and put it in the session

//ensure no caching by browser - START
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); //A Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate"); // no cache for HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false); // no cache for HTTP/1.1
header("Pragma: no-cache"); // no cache for HTTP/1.0
//ensure no caching by browser - END

//output the image
header('Content-type: image/jpeg'); // tell the browser that this is an image
//You can change the following, providing it still uses $rand
$image = imagecreate(55, 15); // create the image
$bgColor = imagecolorallocate ($image, 255, 255, 255); // use white as the background image
$textColor = imagecolorallocate ($image, 0, 0, 0); // the text color is black
imagestring ($image, 5, 5, 0, $rand, $textColor); // write the random number
imagejpeg($image); // send the image to the browser
imagedestroy($image); // destroy the image to free up the memory

?>